<?php

namespace App\Http\Controllers\MasterData;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Outlet;
use App\Models\OutletPrice;
use App\Models\OutletPriceDetail;
use Illuminate\Support\Facades\DB;

class OutletPriceController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Harga Outlet',
            'company'   => Company::where('status','1')->get(),
            'content'   => 'admin.master_data.outlet_price',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'company_id',
            'account_id',
            'outlet_id',
            'date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = OutletPrice::count();
        
        $query_data = OutletPrice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('outletPriceDetail',function($query)use($search,$request){
                                $query->whereHas('item',function($query)use($search,$request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name', 'like', "%$search%");
                                });
                            })
                            ->orWhereHas('customer',function($query)use($search,$request){
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name','like',"%$search%");
                            })
                            ->orWhereHas('outlet',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = OutletPrice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('outletPriceDetail',function($query)use($search,$request){
                                $query->whereHas('item',function($query)use($search,$request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name', 'like', "%$search%");
                                });
                            })
                            ->orWhereHas('customer',function($query)use($search,$request){
                                $query->where('employee_no', 'like', "%$search%")
                                    ->orWhere('name','like',"%$search%");
                            })
                            ->orWhereHas('outlet',function($query)use($search,$request){
                                $query->where('code', 'like', "%$search%")
                                    ->orWhere('name','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->user->name,
                    $val->code,
                    $val->company->name,
                    $val->account->name,
                    $val->outlet->name,
                    date('d/m/y',strtotime($val->date)),
                    $val->note,
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					'
                ];

                $nomor++;
            }
        }

        $response['recordsTotal'] = 0;
        if($total_data <> FALSE) {
            $response['recordsTotal'] = $total_data;
        }

        $response['recordsFiltered'] = 0;
        if($total_filtered <> FALSE) {
            $response['recordsFiltered'] = $total_filtered;
        }

        return response()->json($response);
    }

    public function create(Request $request){
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'company_id'                => 'required',
                'account_id'                => 'required',
                'outlet_id'                 => 'required',
                'date'                      => 'required',
                'arr_item'                  => 'required|array',
                'arr_price'                 => 'required|array',
                'arr_margin'                => 'required|array',
                'arr_disc1'                 => 'required|array',
                'arr_disc2'                 => 'required|array',
                'arr_disc3'                 => 'required|array',
                'arr_final_price'           => 'required|array',
            ], [
                'company_id.required' 	    => 'Perusahaan tidak boleh kosong.',
                'account_id.required' 	    => 'Customer tidak boleh kosong.',
                'outlet_id.required' 	    => 'Outlet tidak boleh kosong.',
                'date.required' 	        => 'Tanggal tidak boleh kosong.',
                'arr_item.required' 	    => 'Item tidak boleh kosong.',
                'arr_item.array' 	        => 'Item harus array.',
                'arr_price.required' 	    => 'Harga dasar tidak boleh kosong.',
                'arr_price.array' 	        => 'Harga dasar harus array.',
                'arr_margin.required' 	    => 'Harga margin tidak boleh kosong.',
                'arr_margin.array' 	        => 'Harga margin harus array.',
                'arr_disc1.required' 	    => 'Diskon 1 tidak boleh kosong.',
                'arr_disc1.array' 	        => 'Diskon 1 harus array.',
                'arr_disc2.required' 	    => 'Diskon 2 tidak boleh kosong.',
                'arr_disc2.array' 	        => 'Diskon 2 harus array.',
                'arr_disc3.required' 	    => 'Diskon 3 tidak boleh kosong.',
                'arr_disc3.array' 	        => 'Diskon 3 harus array.',
                'arr_final_price.required' 	=> 'Harga final tidak boleh kosong.',
                'arr_final_price.array' 	=> 'Harga final harus array.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {
                if($request->temp){
                    $query = OutletPrice::find($request->temp);
                    $query->user_id         = session('bo_id');
                    $query->company_id	    = $request->company_id;
                    $query->account_id      = $request->account_id;
                    $query->outlet_id       = $request->outlet_id;
                    $query->date            = $request->date;
                    $query->note            = $request->note;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();

                    $query->outletPriceDetail()->delete();
                }else{
                    $query = OutletPrice::create([
                        'user_id'           => session('bo_id'),
                        'code'              => strtoupper(Str::random(30)),
                        'company_id'	    => $request->company_id,
                        'account_id'        => $request->account_id,
                        'outlet_id'         => $request->outlet_id,
                        'date'              => $request->date,
                        'note'              => $request->note,
                        'status'            => $request->status ? $request->status : '2'
                    ]);
                }
                
                if($query) {

                    foreach($request->arr_item as $key => $row){
                        OutletPriceDetail::create([
                            'outlet_price_id'   => $query->id,
                            'item_id'           => intval($row),
                            'price'             => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                            'margin'            => str_replace(',','.',str_replace('.','',$request->arr_margin[$key])),
                            'percent_discount_1'=> str_replace(',','.',str_replace('.','',$request->arr_disc1[$key])),
                            'percent_discount_2'=> str_replace(',','.',str_replace('.','',$request->arr_disc2[$key])),
                            'discount_3'        => str_replace(',','.',str_replace('.','',$request->arr_disc3[$key])),
                            'final_price'       => str_replace(',','.',str_replace('.','',$request->arr_final_price[$key])),
                        ]);
                    }

                    activity()
                        ->performedOn(new OutletPrice())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit harga outlet.');

                    $response = [
                        'status'  => 200,
                        'message' => 'Data successfully saved.'
                    ];
                } else {
                    $response = [
                        'status'  => 500,
                        'message' => 'Data failed to save.'
                    ];
                }
            }

            DB::commit();

        }catch(\Exception $e){
            DB::rollback();
        }

        return response()->json($response);
    }

    public function rowDetail(Request $request)
    {
        $data   = OutletPrice::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="8">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Harga</th>
                                <th class="center-align">Margin</th>
                                <th class="center-align">Diskon 1 (%)</th>
                                <th class="center-align">Diskon 2 (%)</th>
                                <th class="center-align">Diskon 3</th>
                                <th class="center-align">Harga Final</th>
                            </tr>
                        </thead><tbody>';
        
        if(count($data->outletPriceDetail) > 0){
            foreach($data->outletPriceDetail as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->item->code.' - '.$row->item->name.'</td>
                    <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->margin,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->percent_discount_1,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->percent_discount_2,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->discount_3,2,',','.').'</td>
                    <td class="right-align">'.number_format($row->final_price,2,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="8">Data item tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $op = OutletPrice::find($request->id);
        $op['account_name'] = $op->account->name;
        $op['outlet_name'] = $op->outlet->name;

        $details = [];

        foreach($op->outletPriceDetail as $row){
            $details[] = [
                'item_id'               => $row->item_id,
                'item_name'             => $row->item->code.' - '.$row->item->name,
                'price'                 => number_format($row->price,2,',','.'),
                'margin'                => number_format($row->margin,2,',','.'),
                'percent_discount_1'    => number_format($row->percent_discount_1,2,',','.'),
                'percent_discount_2'    => number_format($row->percent_discount_2,2,',','.'),
                'discount_3'            => number_format($row->discount_3,2,',','.'),
                'final_price'           => number_format($row->final_price,2,',','.'),
            ];
        }

        $op['details'] = $details;
        				
		return response()->json($op);
    }

    public function destroy(Request $request){
        $query = Outlet::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Outlet())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the outlet data');

            $response = [
                'status'  => 200,
                'message' => 'Data deleted successfully.'
            ];
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }
}

<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Place;
use App\Models\PurchaseOrder;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\GoodReceive;
use App\Models\GoodReceiveDetail;
use App\Models\User;
use App\Models\Company;
use App\Models\Department;
use App\Models\GoodReceiptDetail;
use App\Helpers\CustomHelper;
use App\Exports\ExportGoodReceive;

class GoodReceiveController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user->userPlaceArray();
    }

    public function index()
    {
        $data = [
            'title'     => 'Barang Masuk',
            'content'   => 'admin.inventory.good_receive',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'currency'  => Currency::where('status','1')->get(),
            'department'=> Department::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'post_date',
            'currency_id',
            'currency_rate',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = GoodReceive::count();
        
        $query_data = GoodReceive::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiveDetail', function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = GoodReceive::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('goodReceiveDetail', function($query) use($search, $request){
                                $query->whereHas('item',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%")
                                        ->orWhere('name','like',"%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
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
                    '<button class="btn-floating green btn-small" data-id="' . $val->id . '"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->company->name,
                    date('d M Y',strtotime($val->post_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,3,',','.'),
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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
        $validation = Validator::make($request->all(), [
            'company_id'                => 'required',
			'post_date'		            => 'required',
			'currency_id'		        => 'required',
            'currency_rate'		        => 'required',
            'arr_price'                 => 'required|array',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_coa'                   => 'required|array',
            'arr_warehouse'             => 'required|array',
		], [
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'currency_id.required' 				=> 'Tanggal kadaluwarsa tidak boleh kosong.',
            'Currency_rate.required' 			=> 'Tanggal dokumen tidak boleh kosong.',
			'warehouse_id.required'				=> 'Gudang tujuan tidak boleh kosong',
            'arr_price.required'                => 'Harga satuan tidak boleh kosong',
            'arr_price.array'                   => 'Harga satuan harus dalam bentuk array',
            'arr_item.required'                 => 'Item tidak boleh kosong',
            'arr_item.array'                    => 'Item harus dalam bentuk array',
            'arr_qty.required'                  => 'Qty item tidak boleh kosong',
            'arr_qty.array'                     => 'Qty item harus dalam bentuk array',
            'arr_coa.required'                  => 'Coa tidak boleh kosong',
            'arr_coa.array'                     => 'Coa harus dalam bentuk array',
            'arr_warehouse.required'            => 'Gudang tidak boleh kosong',
            'arr_warehouse.array'               => 'Gudang harus dalam bentuk array',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $passed = true;

            foreach($request->arr_item as $key => $row){
                if(isset($request->arr_price[$key]) && isset($request->arr_qty[$key])){
                    if(str_replace(',','.',str_replace('.','',$request->arr_price[$key])) == 0 || str_replace(',','.',str_replace('.','',$request->arr_qty[$key])) == 0){
                        $passed = false;
                    }
                }else{
                    $passed = false;
                }
            }

            if(!$passed){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Silahkan cek detail form anda, tidak boleh ada data 0 atau kosong.'
                ]);
            }

            $grandtotal = 0;

            foreach($request->arr_item as $key => $row){
                $grandtotal += str_replace(',','.',str_replace('.','',$request->arr_price[$key])) * str_replace(',','.',str_replace('.','',$request->arr_qty[$key]));
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = GoodReceive::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->approval()){
                        foreach($query->approval()->approvalMatrix as $row){
                            if($row->status == '2'){
                                return response()->json([
                                    'status'  => 500,
                                    'message' => 'Purchase Request telah diapprove, anda tidak bisa melakukan perubahan.'
                                ]);
                            }
                        }
                    }

                    if($query->status == '1'){
                        if($request->has('file')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('file')->store('public/good_receives');
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->grandtotal = $grandtotal;
                        $query->save();

                        foreach($query->goodReceiveDetail as $row){
                            $row->delete();
                        }

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status barang masuk sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = GoodReceive::create([
                        'code'			        => GoodReceive::generateCode(),
                        'user_id'		        => session('bo_id'),
                        'company_id'		    => $request->company_id,
                        'post_date'             => $request->post_date,
                        'currency_id'           => $request->currency_id,
                        'currency_rate'         => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'document'              => $request->file('document') ? $request->file('document')->store('public/good_receives') : NULL,
                        'note'                  => $request->note,
                        'status'                => '1',
                        'grandtotal'            => $grandtotal
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    foreach($request->arr_item as $key => $row){
                        
                        GoodReceiveDetail::create([
                            'good_receive_id'       => $query->id,
                            'item_id'               => $row,
                            'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'price'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                            'total'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])) * str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'note'                  => $request->arr_note[$key],
                            'coa_id'                => $request->arr_coa[$key],
                            'warehouse_id'          => $request->arr_warehouse[$key],
                            'place_id'              => $request->arr_place[$key],
                            'department_id'         => $request->arr_department[$key]
                        ]);

                    }

                    CustomHelper::sendApproval('good_receives',$query->id,$query->note);
                    CustomHelper::sendNotification('good_receives',$query->id,'Barang Masuk No. '.$query->code,$query->note,session('bo_id'));
                    
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                activity()
                    ->performedOn(new GoodReceive())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit penerimaan barang.');

				$response = [
					'status'    => 200,
					'message'   => 'Data successfully saved.',
				];
			} else {
				$response = [
					'status'  => 500,
					'message' => 'Data failed to save.'
				];
			}
		}
		
		return response()->json($response);
    }

    public function rowDetail(Request $request){
        $data   = GoodReceive::find($request->id);
        
        $string = '<div class="row pt-1 pb-1 lime lighten-4"><div class="col s12">
                    <table style="max-width:800px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="11">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="right-align">Harga Satuan</th>
                                <th class="right-align">Harga Total</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Coa</th>
                                <th class="center-align">Site</th>
                                <th class="center-align">Departemen</th>
                                <th class="center-align">Gudang</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->goodReceiveDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->item->name.'</td>
                <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                <td class="center-align">'.$row->item->uomUnit->code.'</td>
                <td class="center-align">'.number_format($row->price,3,',','.').'</td>
                <td class="center-align">'.number_format($row->total,3,',','.').'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.$row->coa->code.' - '.$row->coa->name.'</td>
                <td class="center-align">'.$row->place->name.' - '.$row->place->company->name.'</td>
                <td class="center-align">'.$row->department->name.'</td>
                <td class="center-align">'.$row->warehouse->name.'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:800px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval()){                
            foreach($data->approval()->approvalMatrix as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                    <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                    <td class="center-align">'.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br></td>
                    <td class="center-align">'.$row->note.'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $gr = GoodReceive::where('code',CustomHelper::decrypt($request->id))->first();
        $gr['currency_rate'] = number_format($gr->currency_rate,3,',','.');

        $arr = [];
        
        foreach($gr->goodReceiveDetail as $row){
            $arr[] = [
                'item_id'       => $row->item_id,
                'item_name'     => $row->item->code.' - '.$row->item->name,
                'qty'           => number_format($row->qty,3,',','.'),
                'unit'          => $row->item->uomUnit->code,
                'price'         => number_format($row->price,3,',','.'),
                'total'         => number_format($row->total,3,',','.'),
                'coa_id'        => $row->coa_id,
                'coa_name'      => $row->coa->code.' - '.$row->coa->name,
                'place_id'      => $row->place_id,
                'department_id' => $row->department_id,
                'warehouse_id'  => $row->warehouse_id,
                'warehouse_name'=> $row->warehouse->name,
                'note'          => $row->note,
            ];
        }

        $gr['details'] = $arr;
        				
		return response()->json($gr);
    }

    public function voidStatus(Request $request){
        $query = GoodReceive::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                CustomHelper::removeJournal('good_receives',$query->id);
                CustomHelper::removeCogs('good_receives',$query->id);
    
                activity()
                    ->performedOn(new GoodReceive())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the good receive data');
    
                CustomHelper::sendNotification('good_receives',$query->id,'Barang Masuk No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('good_receives',$query->id);
                
                $response = [
                    'status'  => 200,
                    'message' => 'Data closed successfully.'
                ];
            }
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function destroy(Request $request){
        $query = GoodReceive::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->approval()){
            foreach($query->approval()->approvalMatrix as $row){
                if($row->status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Purchase Order telah diapprove / sudah dalam progres, anda tidak bisa melakukan perubahan.'
                    ]);
                }
            }
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->goodReceiveDetail()->delete();

            CustomHelper::removeApproval('good_receives',$query->id);

            activity()
                ->performedOn(new GoodReceive())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the good receive data');

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

    public function approval(Request $request,$id){
        
        $gr = GoodReceive::where('code',CustomHelper::decrypt($id))->first();
                
        if($gr){
            $data = [
                'title'     => 'Print Goods Receive (Barang Masuk)',
                'data'      => $gr
            ];

            return view('admin.approval.good_receive', $data);
        }else{
            abort(404);
        }
    }

    public function print(Request $request){

        $data = [
            'title' => 'GOOD RECEIVE REPORT',
            'data' => GoodReceive::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('goodReceiveDetail', function($query) use($request){
                                $query->whereHas('item',function($query) use($request){
                                    $query->where('code', 'like', "%$request->search%")
                                        ->orWhere('name','like',"%$request->search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->get()
		];
		
		return view('admin.print.inventory.good_receive', $data);
    }

    public function export(Request $request){
		return Excel::download(new ExportGoodReceive($request->search,$request->status,$this->dataplaces), 'good_receive_'.uniqid().'.xlsx');
    }
}
<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\DeliveryCost;

class CurrencyController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Biaya Pengiriman',
            'content'   => 'admin.master_data.delivery_cost',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'account_id',
            'valid_from',
            'valid_to',
            'from_city_id',
            'from_subdistrict_id',
            'to_city_id',
            'to_subdistrict_id',
            'tonnage',
            'nominal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = DeliveryCost::count();
        
        $query_data = DeliveryCost::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where('name','like',"%$search%")
                    ->orWhereHas('account',function($query) use ($search) {
                        $query->where('employee_no', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('fromCity',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('fromSubdistrict',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('toCity',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('toSubdistrict',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }

                if($request->account_id){
                    $query->where('account_id', $request->account_id);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('valid_from', '>=', $request->start_date)
                        ->whereDate('valid_to', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('valid_from','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('valid_to','<=', $request->finish_date);
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = DeliveryCost::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where('name','like',"%$search%")
                    ->orWhereHas('account',function($query) use ($search) {
                        $query->where('employee_no', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('fromCity',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('fromSubdistrict',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('toCity',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    })
                    ->orWhereHas('toSubdistrict',function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
                    });
                }

                if($request->account_id){
                    $query->where('account_id', $request->account_id);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('valid_from', '>=', $request->start_date)
                        ->whereDate('valid_to', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('valid_from','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('valid_to','<=', $request->finish_date);
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
                    $nomor,
                    $val->code,
                    $val->name,
                    $val->account->name,
                    date('d/m/y',strtotime($val->valid_from)),
                    date('d/m/y',strtotime($val->valid_to)),
                    
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
        $validation = Validator::make($request->all(), [
            'code' 				=> $request->temp ? ['required', Rule::unique('currencies', 'code')->ignore($request->temp)] : 'required|unique:currencies,code',
            'name'              => 'required',
            'document_text'     => 'required',
            'symbol'            => 'required',
        ], [
            'code.required' 	    => 'Kode tidak boleh kosong.',
            'code.unique'           => 'Kode telah terpakai.',
            'name.required'         => 'Nama tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = Currency::find($request->temp);
                    $query->code            = $request->code;
                    $query->name	        = $request->name;
                    $query->document_text   = $request->document_text;
                    $query->symbol          = $request->symbol;
                    $query->status          = $request->status ? $request->status : '2';
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Currency::create([
                        'code'          => $request->code,
                        'name'			=> $request->name,
                        'document_text' => $request->document_text,
                        'symbol'        => $request->symbol,
                        'status'        => $request->status ? $request->status : '2'
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new Currency())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit currency.');

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
		
		return response()->json($response);
    }

    public function show(Request $request){
        $currency = Currency::find($request->id);
        				
		return response()->json($currency);
    }

    public function destroy(Request $request){
        $query = Currency::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Currency())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the currency data');

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
<?php

namespace App\Http\Controllers\MasterData;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\DeliveryCost;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportDeliveryCost;
use App\Models\Transportation;

class DeliveryCostController extends Controller
{
    public function index()
    {
        $data = [
            'title'             => 'Biaya Pengiriman',
            'transport'         => Transportation::where('status','1')->get(),
            'content'           => 'admin.master_data.delivery_cost',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'name',
            'account_id',
            'transportation_id',
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
                    $query->where(function($query) use ($request) {
                        $query->whereDate('valid_from', '>=', $request->start_date)
                            ->whereDate('valid_from', '<=', $request->finish_date);
                    })->orWhere(function($query) use ($request) {
                        $query->whereDate('valid_to', '>=', $request->start_date)
                            ->whereDate('valid_to', '<=', $request->finish_date);
                    });
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
                    $query->where(function($query) use ($request) {
                        $query->whereDate('valid_from', '>=', $request->start_date)
                            ->whereDate('valid_from', '<=', $request->finish_date);
                    })->orWhere(function($query) use ($request) {
                        $query->whereDate('valid_to', '>=', $request->start_date)
                            ->whereDate('valid_to', '<=', $request->finish_date);
                    });
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
                    $val->account->name ?? '',
                    $val->transportation->name,
                    date('d/m/Y',strtotime($val->valid_from)),
                    date('d/m/Y',strtotime($val->valid_to)),
                    $val->fromCity->name,
                    $val->fromSubdistrict->name,
                    $val->toCity->name,
                    $val->toSubdistrict->name,
                    number_format($val->tonnage,2,',','.'),
                    number_format($val->nominal,2,',','.'),
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
            'code' 				=> $request->temp ? ['required', Rule::unique('delivery_costs', 'code')->ignore($request->temp)] : 'required|unique:delivery_costs,code',
            'name'                  => 'required',
            'valid_from'            => 'required',
            'valid_to'              => 'required',
            'from_city_id'          => 'required',
            'from_subdistrict_id'   => 'required',
            'to_city_id'            => 'required',
            'to_subdistrict_id'     => 'required',
            'account_id'            => 'required',
            'transportation_id'     => 'required',
            'tonnage'               => 'required',
            'nominal'               => 'required',
        ], [
            'code.required' 	            => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah terpakai.',
            'name.required'                 => 'Nama tidak boleh kosong.',
            'valid_from.required'           => 'Tanggal valid dari tidak boleh kosong.',
            'valid_to.required'             => 'Tanggal valid sampai tidak boleh kosong.',
            'from_city_id.required'         => 'Asal kota tidak boleh kosong.',
            'from_subdistrict_id.required'  => 'Asal kecamatan tidak boleh kosong.',
            'to_city_id.required'           => 'Tujuan kota tidak boleh kosong.',
            'to_subdistrict_id.required'    => 'Tujuan kecamatan tidak boleh kosong.',
            'account_id.required'           => 'Partner Bisnis tidak boleh kosong.',
            'transportation_id.required'    => 'Jenis kendaraan tidak boleh kosong.',
            'tonnage.required'              => 'Tonase tidak boleh kosong.',
            'nominal.required'              => 'Nominal harga tidak boleh kosong.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            DB::beginTransaction();
            try {

                if($request->temp){
                    $query = DeliveryCost::find($request->temp);
                    $query->code                = $request->code;
                    $query->user_id             = session('bo_id');
                    $query->account_id          = $request->account_id;
                    $query->transportation_id   = $request->transportation_id;
                    $query->name	            = $request->name;
                    $query->valid_from          = $request->valid_from;
                    $query->valid_to            = $request->valid_to;
                    $query->from_city_id        = $request->from_city_id;
                    $query->from_subdistrict_id = $request->from_subdistrict_id;
                    $query->to_city_id          = $request->to_city_id;
                    $query->to_subdistrict_id   = $request->to_subdistrict_id;
                    $query->tonnage             = str_replace(',','.',str_replace('.','',$request->tonnage));
                    $query->nominal             = str_replace(',','.',str_replace('.','',$request->nominal));
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();
                }else{
                    $query = DeliveryCost::create([
                        'code'                  => $request->code,
                        'user_id'               => session('bo_id'),
                        'account_id'            => $request->account_id,
                        'transportation_id'     => $request->transportation_id,
                        'name'			        => $request->name,
                        'valid_from'            => $request->valid_from,
                        'valid_to'              => $request->valid_to,
                        'from_city_id'          => $request->from_city_id,
                        'from_subdistrict_id'   => $request->from_subdistrict_id,
                        'to_city_id'            => $request->to_city_id,
                        'to_subdistrict_id'     => $request->to_subdistrict_id,
                        'tonnage'               => str_replace(',','.',str_replace('.','',$request->tonnage)),
                        'nominal'               => str_replace(',','.',str_replace('.','',$request->nominal)),
                        'status'                => $request->status ? $request->status : '2'
                    ]);
                }
                
                if($query) {

                    activity()
                        ->performedOn(new DeliveryCost())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit delivery cost.');

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
                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
		}
		
		return response()->json($response);
    }

    public function show(Request $request){
        $dc = DeliveryCost::find($request->id);
        $dc['from_city_name'] = $dc->fromCity->name;
        $dc['to_city_name'] = $dc->toCity->name;
        $dc['from_subdistrict_list'] = $dc->fromCity->getSubdistrict();
        $dc['to_subdistrict_list'] = $dc->toCity->getSubdistrict();
        $dc['tonnage'] = number_format($dc->tonnage,2,',','.');
        $dc['nominal'] = number_format($dc->nominal,2,',','.');
        $dc['account_name'] = $dc->account()->exists() ? $dc->account->name : '';
        				
		return response()->json($dc);
    }

    public function destroy(Request $request){
        $query = DeliveryCost::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new DeliveryCost())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the delivery cost');

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

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'mimes:xlsx',
                'max:2048',
                function ($attribute, $value, $fail) {
                    $rows = Excel::toArray([], $value)[0];
                    if (count($rows) < 2) {
                        $fail('The file must contain at least two rows.');
                    }
                }
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'status' => 432,
                'error'  => $validator->errors()
            ];
            return response()->json($response);
        }

        try {
            Excel::import(new ImportDeliveryCost, $request->file('file'));

            return response()->json([
                'status'    => 200,
                'message'   => 'Import sukses!'
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $errors = [];
            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            }
            $response = [
                'status' => 422,
                'error'  => $errors
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            $response = [
                'status'  => 500,
                'message' => "Data failed to save"
            ];
            return response()->json($response);
        }
    }
}
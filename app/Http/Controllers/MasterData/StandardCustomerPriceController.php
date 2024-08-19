<?php

namespace App\Http\Controllers\MasterData;

use App\Exports\ExportTransactionPageStandarCustomerPrice;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StandardCustomerPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class StandardCustomerPriceController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Relasi BOM',
            'content'   => 'admin.master_data.standard_customer_price',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'name',
            'group_id',
            'user_id',
            'price',
            'start_date',
            'end_date',
            'note',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = StandardCustomerPrice::count();
        
        $query_data = StandardCustomerPrice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('price', 'like', "%$search%")
                            ->orWhere('start_date', 'like', "%$search%")
                            ->orWhere('end_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('group',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                ->orWhere('code','like',"%$search%");
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

        $total_filtered = StandardCustomerPrice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhere('price', 'like', "%$search%")
                            ->orWhere('start_date', 'like', "%$search%")
                            ->orWhere('end_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('group',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                ->orWhere('code','like',"%$search%");
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
                    $nomor,
                    $val->code,
                    $val->name,
                    $val->group->code,
                    $val->user->name,
                    number_format($val->price,2,',','.'),
                    date('d/m/Y',strtotime($val->start_date)),
                    date('d/m/Y',strtotime($val->end_date)),
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
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'name'                      => 'required',
            'group_id'                  => 'required',
            'price'                     => 'required',
            'start_date'                => 'required',
            'end_date'                  => 'required',
            'note'                      => 'required',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'name.required'                     => 'Plant Tidak boleh kosong',
            'group_id.required'                 => 'Perusahaan tidak boleh kosong.',
            'start_date.required'               => 'Nomor kendaraan tidak boleh kosong.',
            'end_date.required'                 => 'Nama supir tidak boleh kosong.',
            'note.required'                     => 'Plant tidak boleh kosong.',
			
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
                    $query = StandardCustomerPrice::find($request->temp);
                    $cek = StandardCustomerPrice::where('group_id',$request->group_id)->get();
                    $sama = 0;
                    foreach($cek as $row_cek){
                        if (($request->start_date >= $row_cek->start_date && $request->start_date <= $row_cek->end_date) ||
                        ($request->end_date >= $row_cek->start_date && $request->end_date <= $row_cek->end_date) || 
                        ($request->start_date <= $row_cek->start_date && $request->end_date >= $row_cek->end_date)) {
                            $sama = 1; 
                            break; 
                        }
                    }
                    if ($sama == 0) {
                        $query->code = $request->code;
                        $query->name = $request->name;
                      
                        $query->group_id = $request->group_id;
                        $query->price = str_replace(',','.',str_replace('.','',$request->price));
                        $query->start_date = $request->start_date;
                        $query->end_date = $request->end_date;
                        $query->note = $request->note;
                        
                        $query->status = $request->status ? $request->status : '2';
                        $query->save();
                        DB::commit();
                    }else{
                        $response = [
                            'status'  => 500,
                            'message' => 'Tanggal ada yang Overlap'
                        ];
                        return response()->json($response);
                    }
                    
                }catch(\Exception $e){
                    DB::rollback();
                }

			}else{
                DB::beginTransaction();
                try {
                    $cek = StandardCustomerPrice::where('group_id',$request->group_id)->get();
                    $sama = 0;
                    foreach($cek as $row_cek){
                        if (($request->start_date >= $row_cek->start_date && $request->start_date <= $row_cek->end_date) ||
                        ($request->end_date >= $row_cek->start_date && $request->end_date <= $row_cek->end_date) || 
                        ($request->start_date <= $row_cek->start_date && $request->end_date >= $row_cek->end_date)) {
                            $sama = 1; 
                            break; 
                        }
                    }
                    if($sama == 0){
                        $query = StandardCustomerPrice::create([
                            'code'			        => $request->code,
                            'name'			        => $request->name,
                            'group_id'              => $request->group_id,
                            'user_id'	            => session('bo_id'),
                            'price'                 => str_replace(',','.',str_replace('.','',$request->price)),
                            'start_date'            => $request->start_date,
                            'end_date'              => $request->end_date,
                            'note'                  => $request->note,
                            'status'                => $request->status ? $request->status : '2'
                        ]);
                        DB::commit();
                    }else{
                        $response = [
                            'status'  => 500,
                            'message' => 'Tanggal ada yang Overlap'
                        ];
                        return response()->json($response);
                    }
                    
                   
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                activity()
                    ->performedOn(new StandardCustomerPrice())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit standar price pelanggan data.');


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

    public function show(Request $request){
        $scp = StandardCustomerPrice::find($request->id);
        $scp['group'] = $scp->group;
        $scp['user'] = $scp->user;
 		return response()->json($scp);
    }

    public function destroy(Request $request){
        $query = StandardCustomerPrice::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new StandardCustomerPrice())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the standar price customer data');

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

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
        $company = $request->company ? $request->company : 0;
        $type = $request->type ? $request->type : '';
		
		// return Excel::download(new ExportCoa($search,$status,$company,$type), 'coa_'.uniqid().'.xlsx');
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $status = $request->status ? $request->status : '';
		return Excel::download(new ExportTransactionPageStandarCustomerPrice($search,$status), 'standar_harga_pelanggan_'.uniqid().'.xlsx');
    }
}

<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\EmployeeLeaveQuotas;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class EmployeeLeaveQuotasController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Kuota Ijin Cuti',
            'content'       => 'admin.master_data.employee_leave_quota',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'user_id',
            'leave_type_id',
            'paid_leave_quotas',
            'start_date',
            'end_date',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = EmployeeLeaveQuotas::count();
        
        $query_data = EmployeeLeaveQuotas::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('start_date', 'like', "%$search%")
                            ->orWhere('end_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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

        $total_filtered = EmployeeLeaveQuotas::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('start_date', 'like', "%$search%")
                            ->orWhere('end_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
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
               
                $btn = 
                '
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
                ';
                      
                $response['data'][] = [
                    $nomor,
                    $val->user->name,
                    $val->leaveType->name,
                    $val->paid_leave_quotas,
                    $val->start_date,
                    $val->end_date,
                    $val->status(),
                    $btn
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
            'user_id'			        => 'required',
            'leave_type_id'             => 'required',
            'paid_leave_quotas'         => 'required',
            'start_date'                => 'required',
            'end_date'                  => 'required'
        ], [
            'user_id.unique'                   => 'User tidak boleh kosong',
            'leave_type_id.required'                 => 'Harap Pilih Tipe Cuti.',
            'paid_leave_quotas.required'                 => 'Kuota Tidak Boleh kosong',
            'start_date.required'          => 'Tanggal Mulai dari kuota cuti tidak boleh kosong',
            'end_date.required'        => 'Tanggal expire dari kuota cuti tidak boleh kosong'
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
                    $query = EmployeeLeaveQuotas::find($request->temp);
                    $query->user_id                = $request->user_id;
                    $query->leave_type_id                = $request->leave_type_id;
                    $query->paid_leave_quotas                = $request->paid_leave_quotas;
                    $query->start_date         = $request->start_date;
                    $query->status              = $request->status ? $request->status : '1';
                    $query->end_date       = $request->end_date;
                    $query->save();
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
               
                try {
                    
                    $query = EmployeeLeaveQuotas::create([
                        'user_id'                  => $request->user_id,
                        'leave_type_id'			        => $request->leave_type_id,
                        'paid_leave_quotas'                  => $request->paid_leave_quotas,
                        'start_date'           => $request->start_date,
                        'end_date'         => $request->end_date,
                        'status'                => $request->status ? $request->status : '1',
                    ]);
                  
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

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
        $Level = EmployeeLeaveQuotas::with('user')->find($request->id);
        				
		return response()->json($Level);
    }

    public function destroy(Request $request){
        $query = EmployeeLeaveQuotas::find($request->id);
		
        if($query->delete()) {
           
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

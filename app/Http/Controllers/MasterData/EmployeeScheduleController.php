<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Imports\ImportEmployeeSchedule;
use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class EmployeeScheduleController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Jadwal Pegawai',
            'content'       => 'admin.master_data.employee_schedule',
            'user'          => User::join('departments','departments.id','=','users.department_id')->select('departments.name as department_name','users.*')->orderBy('department_name')->get(),
            'shift'         => Shift::where('status',1)->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
    
    public function datatable(Request $request){
        $column = [
            'user_id',
            'user_id',
            'user_id',
            'date',
            'shift_id',
            
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = EmployeeSchedule::count();
        
        $query_data = EmployeeSchedule::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('date', 'like', "%$search%")
                        ->orWhereHas('user',function($query)  use ($search, $request){
                            $query->where('name','like',"%$search%");
                        });
                    });
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = EmployeeSchedule::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('date', 'like', "%$search%")
                    ->orWhereHas('user',function($query)  use ($search, $request){
                        $query->where('name','like',"%$search%");
                    });
                });
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
                    $val->user->employee_no,
                    $val->user->name,
                    $val->date,
                    $val->shift->time_in.'-'.$val->shift->time_out,
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

    public function show(Request $request){
        $query = EmployeeSchedule::find($request->id);
        $query["employee_name"]=$query->user->name;
        $query["shifted"]=$query->shift->name.'||'.$query->shift->code;
		return response()->json($query);
    }

    public function createSingle(Request $request)
    {
        if($request->temp){
            $validation = Validator::make($request->all(), [
                'date_detail'          => 'required',
                'employee_id_detail'      => 'required',
                'shift_id_detail'       => 'required'
            ], [
                'date_detail.required'         => 'Tanggal tidak boleh kosong.',
                'employee_id_detail.required'     => 'Pegawai tidak boleh kosong.',
                'shift_id_detail.required'      => 'Shift tidak boleh kosong.',
            ]);
        }else{
            $validation = Validator::make($request->all(), [
                'date'          => 'required',
                'employee_id'      => 'required',
                'shift_id'       => 'required'
            ], [
                'date.required'         => 'Tanggal tidak boleh kosong.',
                'employee_id.required'     => 'Pegawai tidak boleh kosong.',
                'shift_id.required'      => 'Shift tidak boleh kosong.',
            ]);
        }
        

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
           
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = EmployeeSchedule::find($request->temp);

                    $query->shift_id             = $request->shift_id_detail;
                    $query->user_id              = $request->employee_id_detail;
                    $query->date                 = $request->date_detail;
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = EmployeeSchedule::create([
                        'shift_id'          => $request->shift_id,
                        'date'	            => $request->date,
                        'user_id'           => $request->employee_id,
                    ]);

                    DB::commit();
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

    public function destroy(Request $request){
        $query = EmployeeSchedule::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new EmployeeSchedule())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Schedule data');

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
            Excel::import(new ImportEmployeeSchedule, $request->file('file'));

            return response()->json([
                'status'    => 200,
                'message'   => 'Import sukses!'
            ]);
            
        } catch (ValidationException $e) {
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

    public function createMulti(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'employee_shift'          => 'required',
            'arr_employee'      => 'required',
        ], [
            'employee_shift.required'         => 'Belum ada shift dalam form',
            'arr_employee.required'     => 'Belum ada pegawai yang dipilih.',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            DB::beginTransaction();
            $employee_shift_decode=json_decode($request->employee_shift);
        
            
            try {
                foreach($employee_shift_decode as $shift){
                    
                    $start_date = Carbon::parse($shift->start_date);
                    $end_date = Carbon::parse($shift->end_date);
                    foreach ($request->arr_employee as $user_id) {
                        $current_date = $start_date->copy(); // Make a copy of the start date to avoid modifying the original date
                        while ($current_date->lte($end_date)) {
                            $query = EmployeeSchedule::create([
                                'shift_id'          => $shift->shift_id,
                                'date'	            => $current_date,
                                'user_id'           => $user_id,
                            ]);

                            $current_date->addDay(); // Or use addWeek(), addMonth(), etc., depending on your needs
                        }
                        
                    }
                }
                
                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
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
}

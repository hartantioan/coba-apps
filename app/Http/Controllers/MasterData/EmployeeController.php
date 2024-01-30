<?php

namespace App\Http\Controllers\MasterData;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmployeeSchedule;
use App\Models\EmployeeSalaryComponent;
use App\Models\Group;
use App\Models\Menu;
use App\Models\Place;
use App\Models\Position;
use App\Models\Shift;
use App\Models\User;
use App\Models\UserEducation;
use App\Models\AttendancePeriod;
use App\Models\UserFamily;
use App\Models\UserWorkExperience;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Karyawan',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->get(),
            'warehouse'     => Warehouse::where('status','1')->get(),
            'department'    => Department::where('status','1')->get(),
            'user'          => User::where('status','1')->where('type','1')->get(),
            'position'      => Position::where('status','1')->get(),
            'group'         => Group::where('status','1')->get(['id','name','type'])->toArray(),
            'menu'          => Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get(),
            'content'       => 'admin.master_data.employee'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    } 

    public function datatable(Request $request){
        $column = [
            'id',
            'name',
            'username',
            'address',
            'id_card'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = User::where('type','1')->count();
        
        $query_data = User::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('username', 'like', "%$search%")
                            ->orWhere('employee_no', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('id_card', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->where('type','1')
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = User::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('username', 'like', "%$search%")
                            ->orWhere('employee_no', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('id_card', 'like', "%$search%");
                    });
                }
                
                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->where('type','1')
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $url=request()->root()."/admin/hr/employee_transfer?employee_code=".CustomHelper::encrypt($val->id);
				
                $btn = 
                '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small" data-popup="tooltip" title="Education" onclick="showEducation(' . $val->id . ')"><i class="material-icons dp48">local_library</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Work Experience" onclick="showExperience(' . $val->id . ')"><i class="material-icons dp48">location_city</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Family" onclick="showFamily(' . $val->id . ')"><i class="material-icons dp48">recent_actors</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light light-blue darken-3 white-text btn-small" data-popup="tooltip" title="Lihat Jadwal" onclick="getSchedule(`' . $val->employee_no . '`)"><i class="material-icons dp48">event</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light deep-orange accent-1 white-text btn-small" data-popup="tooltip" title="Copy Schedule" onclick="openCopy(`' . $val->employee_no . '`)"><i class="material-icons dp48">content_copy</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-text btn-small" data-popup="tooltip" title="Employee Transfer" onclick="goto(\'' . $url . '\')"><i class="material-icons dp48">settings_ethernet</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light indigo darken-1 white-text btn-small" data-popup="tooltip" title="Komponen Gaji" onclick="getSalaryComponent(' . $val->id . ')"><i class="material-icons dp48">wb_iridescent</i></button>';
                
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->employee_no).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->name,
                    $val->username,
                    $val->employee_no,
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
    public function datatableFamily(Request $request){
        $column = [
            'user_id',
            'code',
            'name',
            'relation',
            'emergency_contact',
            'address',
            'id_number',
            'marriage_status',
            'religion',
            'job',
            'birth_date',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = UserFamily::count();
        
        $query_data = UserFamily::where(function($query) use ($search, $request) {
                $query->where('user_id',$request->id);

                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('id_number', 'like', "%$search%");
                    });
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = UserFamily::where(function($query) use ($search, $request) {
                $query->where('user_id',$request->id);

                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('id_number', 'like', "%$search%");
                    });
                }

            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $btn = 
                '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>';

                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->name,
                    $val->relation(),
                    $val->emergency_contact,
                    $val->address,
                    $val->id_number,
                    $val->marriageStatus(),
                    $val->religion(),
                    $val->job,
                    $val->birth_date,
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
    public function datatableWorkExperience(Request $request){
        $column = [
            'user_id',
            'code',
            'month_start',
            'month_end',
            'position',
            'company_name',
            'job_description',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = UserWorkExperience::count();
        
        $query_data = UserWorkExperience::where(function($query) use ($search, $request) {
                $query->where('user_id',$request->id);
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('company_name', 'like', "%$search%")
                            ->orWhere('position', 'like', "%$search%")
                            ->orWhere('month_start', 'like', "%$search%")
                            ->orWhere('month_end', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = UserWorkExperience::where(function($query) use ($search, $request) {
            $query->where('user_id',$request->id);
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('company_name', 'like', "%$search%")
                            ->orWhere('position', 'like', "%$search%")
                            ->orWhere('month_start', 'like', "%$search%")
                            ->orWhere('month_end', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type', $request->type);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $btn = 
                '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>';

                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->company_name,
                    $val->month_start,
                    $val->month_end,
                    $val->position,
                    $val->job_description,
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
    public function datatableEducation(Request $request){
        $column = [
            'user_id',
            'stage',
            'code',
            'school_name',
            'major',
            'final_score',
            'year_start',
            'year_end',
        ];
        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = UserEducation::count();
        
        $query_data = UserEducation::where(function($query) use ($search, $request) {
                $query->where('user_id',$request->id);
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('school_name', 'like', "%$search%")
                            ->orWhere('stage','like',"%$search%")
                            ->orWhere('major', 'like', "%$search%")
                            ->orWhere('final_score', 'like', "%$search%")
                            ->orWhere('year_start', 'like', "%$search%");
                    });
                }

            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = UserEducation::where(function($query) use ($search, $request) {
            $query->where('user_id',$request->id);
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('code', 'like', "%$search%")
                        ->orWhere('stage','like',"%$search%")
                        ->orWhere('school_name', 'like', "%$search%")
                        ->orWhere('major', 'like', "%$search%")
                        ->orWhere('final_score', 'like', "%$search%")
                        ->orWhere('year_start', 'like', "%$search%");
                });
            }

        })
        ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $btn = 
                '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>';

                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->school_name,
                    $val->major ?? '-',
                    $val->stage(),
                    $val->final_score??'-',
                    $val->year_start,
                    $val->year_end,
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

    public function salaryComponentEmployee(Request $request){
        $query_component_salary=EmployeeSalaryComponent::where('user_id',$request->id)->get();
        $arr=[];
        foreach($query_component_salary as $row_component){
            $arr[] = [
                'uid' => $row_component->user_id,
                'id_component'=>$row_component->id,
                'nominal'=>$row_component->nominal,
                'component_name'=>$row_component->salaryComponent->name,
            ];
        };
        return response()->json($arr);
    }

    public function saveEmployeeSalaryComponent (Request $request){
        $validation = Validator::make($request->all(), [
            'arr_component'          => 'required',
            
        ], [
            'arr_component.required'         => 'isi dari nominal tidak boleh kosong',
        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
           
			if($request->temp_salary){
                DB::beginTransaction();
                try {
                    foreach ($request->input('arr_component') as $key => $value) {
                        // Access each value
                        $dataId = $request->input('arr_id_component')[$key];
                        $query = EmployeeSalaryComponent::find($dataId);
                        
                        $query->nominal = $value;
                        $query->save();
                    }
                    
                    

                    DB::commit();
                    $jalan = true;
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($jalan) {               

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

    public function createEducation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'school_name'          => 'required',
            'stage'      => 'required',
            'year_start'       => 'required',
            'year_end'       => 'required',
        ], [
            'school_name.required'         => 'Nama Institusi tidak boleh kosong.',
            'stage.required'     => 'Jenjang tidak boleh kosong.',
            'year_start.required'      => 'Tahun Mulai tidak boleh kosong.',
            'year_end.required'      => 'Tahun Selesai tidak boleh kosong.',
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
                    $query = UserEducation::find($request->temp);

                    $query->user_id             = $request->id;
                    $query->school_name        = $request->school_name;
                    $query->major        = $request->major;
                    $query->final_score        = $request->final_score;
                    $query->stage            = $request->stage;
                    $query->year_start             = $request->year_start;
                    $query->year_end             = $request->end;
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = UserEducation::create([
                        'code'              => UserEducation::generateCode(),
                        'school_name'	    => $request->school_name,
                        'user_id'           => $request->id,
                        'year_start'        => $request->year_start,
                        'year_end'          => $request->year_end,
                        'stage'             => $request->stage,
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

    public function createExperience(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'company_name'          => 'required',
            'position'      => 'required',
            'month_start'       => 'required',
            'month_end'       => 'required',
        ], [
            'company_name.required'         => 'Nama Perusahaan tidak boleh kosong.',
            'position.required'     => 'Posisi tidak boleh kosong.',
            'month_start.required'      => 'Bulan tidak boleh kosong.',
            'month_end.required'      => 'Bulan tidak boleh kosong.',
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
                    $query = UserWorkExperience::find($request->temp);
                    $query->user_id             =$request->id;
                    $query->company_name        = $request->company_name;
                    $query->month_start                = $request->month_start;
                    $query->month_end            = $request->month_end;
                    $query->position             = $request->position;
                    $query->job_description             = $request->job_description;
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = UserWorkExperience::create([
                        'user_id'           =>$request->id,
                        'code'              => UserWorkExperience::generateCode(),
                        'company_name'			    => $request->company_name,
                        'month_start'           => $request->month_start,
                        'month_end'          => $request->month_end,
                        'position'           => $request->position,
                        'job_description'           => $request->job_description,
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

    public function createFamily(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'                      => 'required',
            'relation'                  => 'required',
            'address'                   => 'required',
            'emergency_contact'         => 'required',
            'birth_date'         => 'required',
        ], [
            'name.required'         => 'Nama tidak boleh kosong.',
            'relation.required'     => 'Relasi tidak boleh kosong.',
            'address.required'      => 'Alamat tidak boleh kosong.',
            'emergency_contact.required'      => 'Kontak tidak boleh kosong.',
            'birht_date.required'      => 'Tanggal Lahir tidak boleh kosong.',
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
                    $query = UserFamily::find($request->temp);

                    $query->user_id             = session('bo_id');
                    $query->name                = $request->name;
                    $query->relation            = $request->relation;
                    $query->emergency_contact             = $request->emergency_contact;
                    $query->address             = $request->address;
                    $query->id_number                = $request->id_number;
                    $query->marriage_status            = $request->marriage_status;
                    $query->religion            = $request->religion;
                    $query->job            = $request->job;
                    $query->birth_date            = $request->birth_date;

                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = UserFamily::create([
                        'code'              => UserFamily::generateCode(),
                        'name'			    => $request->name,
                        'user_id'			=> $request->id,
                        'relation'          => $request->relation,
                        'emergency_contact'          => $request->emergency_contact,
                        'address'           => $request->address,
                        'id_number'           => $request->id_number,
                        'marriage_status'              => $request->marriage_status,
                        'religion'            => $request->religion,
                        'job'              => $request->job,
                        'birth_date'              => $request->birth_date,
                        
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

    public function showFamily(Request $request){
        $line = UserFamily::find($request->id);
        				
		return response()->json($line);
    }

    public function showEducation(Request $request){
        $line = UserEducation::find($request->id);
        				
		return response()->json($line);
    }

    public function showWorkExperience(Request $request){
        $line = UserWorkExperience::find($request->id);
        				
		return response()->json($line);
    }

    public function copySchedule(Request $request){
        
        $validation = Validator::make($request->all(), [
            'period_id'            => 'required',
            'arr_employee'         => 'required',
        ], [
            'period_id.required'         => 'Harap pilih periode.',
            'arr_employee.required'     => 'Harap pilih pegawai.',

        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $period = AttendancePeriod::find($request->period_id);
            $start_date = Carbon::parse($period->start_date)->format('Y-m-d');
            $end_date = Carbon::parse($period->end_date)->format('Y-m-d');
            
            $query_schedule = EmployeeSchedule::where('user_id', $request->user_id)
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->get();
            info($query_schedule);
            if($query_schedule){
                foreach($request->arr_employee as $employee_id){
                   
                    foreach($query_schedule as $schedule){
                        DB::beginTransaction();
                        try {
                            
                            $query = EmployeeSchedule::create([
                                'shift_id'          => $schedule->shift_id,
                                'date'	            => $schedule->date,
                                'user_id'           => $employee_id,
                                'status'            => 1,
                            ]);

                            DB::commit();
                        }catch(\Exception $e){
                            DB::rollback();
                        }
                        
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
            }else{
                $kambing["kambing"][]="User ini belum memiliki jadwal yang lebih dari tanggal hari ini.";
                $response = [
                    'status' => 422,
                    'error'  => $kambing
                ]; 
            }
        }
        return response()->json($response);
    }


    public function rowDetail(Request $request)
    {
        $data   = User::where('employee_no',CustomHelper::decrypt($request->id))->first();

        $banks = [];
        $infos = [];

        foreach($data->userBank as $row){
            $banks[] = $row->bank->name.' No. rek '.$row->no.' Cab. '.$row->branch.' '.$row->isDefault();
        }

        foreach($data->userData as $row){
            $infos[] = $row->title.' '.$row->content;
        }

        $string = '<table>
                        <thead>
                            <tr>
                                <th>No Identitas</th>
                                <th>'.$data->id_card.'</th>
                            </tr>
                            <tr>
                                <th>Alamat Identitas</th>
                                <th>'.$data->id_card_address.'</th>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <th>'.$data->address.'</th>
                            </tr>
                            <tr>
                                <th>Kecamatan</th>
                                <th>'.($data->subdistrict_id ? $data->subdistrict->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Kota/Kabupaten</th>
                                <th>'.$data->city->name.'</th>
                            </tr>
                            <tr>
                                <th>Provinsi</th>
                                <th>'.$data->province->name.'</th>
                            </tr>
                            <tr>
                                <th>Kota/Kabupaten</th>
                                <th>'.$data->country->name.'</th>
                            </tr>
                            <tr>
                                <th>Cabang</th>
                                <th>'.($data->company()->exists() ? $data->company->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Penempatan</th>
                                <th>'.($data->place()->exists() ? $data->place->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Jenis Kelamin</th>
                                <th>'.$data->gender().'</th>
                            </tr>
                            <tr>
                                <th>Status Pernikahan</th>
                                <th>'.$data->marriedStatus().'</th>
                            </tr>
                            <tr>
                                <th>Tgl. Menikah</th>
                                <th>'.date('d/m/Y',strtotime($data->married_date)).'</th>
                            </tr>
                            <tr>
                                <th>Jumlah Anak</th>
                                <th>'.$data->children.'</th>
                            </tr>
                           
                            <tr>
                                <th>Posisi/Level</th>
                                <th>'.($data->position()->exists() ? $data->position->name : "-").'</th>
                            </tr>
                            <tr>
                                <th>NPWP</th>
                                <th>'.$data->tax_id.'</th>
                            </tr>
                            <tr>
                                <th>Nama NPWP</th>
                                <th>'.$data->tax_name.'</th>
                            </tr>
                            <tr>
                                <th>Alamat NPWP</th>
                                <th>'.$data->tax_address.'</th>
                            </tr>
                            <tr>
                                <th>PIC</th>
                                <th>'.$data->pic.'</th>
                            </tr>
                            <tr>
                                <th>Kontak PIC</th>
                                <th>'.$data->pic_no.'</th>
                            </tr>
                            <tr>
                                <th>Kontak Kantor</th>
                                <th>'.$data->office_no.'</th>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <th>'.$data->email.'</th>
                            </tr>
                            <tr>
                                <th>Deposit</th>
                                <th>'.number_format($data->deposit,0,',','.').'</th>
                            </tr>
                            <tr>
                                <th>Limit Credit</th>
                                <th>'.number_format($data->limit_credit,0,',','.').'</th>
                            </tr>
                            <tr>
                                <th>TOP (Tempo Pembayaran)</th>
                                <th>'.$data->top.' hari</th>
                            </tr>
                            <tr>
                                <th>TOP Internal</th>
                                <th>'.$data->top_internal.' hari</th>
                            </tr>
                            <tr>
                                <th>Daftar Rekening</th>
                                <th>'.implode('<br>',$banks).'</th>
                            </tr>
                            <tr>
                                <th>Info Tambahan</th>
                                <th>'.implode('<br>',$infos).'</th>
                            </tr>
                            <tr>
                                <th>Kelompok</th>
                                <th>'.($data->group()->exists() ? $data->group->name : '-').'</th>
                            </tr>
                            <tr>
                                <th>Terakhir Ubah Password</th>
                                <th>'.$data->last_change_password.'</th>
                            </tr>
                        </thead>
                    </table>';
		
        return response()->json($string);
    }

    public function indexEducation(Request $request)
    {
        $data = [
            'title'         => 'Edukasi',
            'content'       => 'admin.master_data.employee_education'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
    
    public function indexFamily()
    {
        $data = [
            'title'         => 'Partner Bisnis',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->get(),
            'warehouse'     => Warehouse::where('status','1')->get(),
            'department'    => Department::where('status','1')->get(),
            'position'      => Position::where('status','1')->get(),
            'group'         => Group::where('status','1')->get(['id','name','type'])->toArray(),
            'menu'          => Menu::whereNull('parent_id')->where('status','1')->oldest('order')->get(),
            'content'       => 'admin.master_data.employee_family'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
    public function indexWorkExperience()
    {
        $data = [
            'title'         => 'Partner Bisnis',
            'content'       => 'admin.master_data.employee_experience'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getSchedule(Request $request){

        $currentDate = Carbon::now()->subMonth()->toDateString();

        $oneMonthFromNow = Carbon::now()->addMonth()->toDateString();

        $query_shift = EmployeeSchedule::where('user_id', $request->id)
            ->whereNotIn('status',['2','3'])
            ->whereIn('status',['1',null])
            // ->whereDate('date', '>', $currentDate)
            // ->whereDate('date', '<', $oneMonthFromNow)
            ->get();
        $schedules=[];
        foreach($query_shift as $schedule){
            $schedules[]=[
                "date"=>$schedule->user_id,
                "user"=>$schedule->user,
                "shift"=>$schedule->shift,
            ];
        }
        return response()->json($query_shift);
    }
    
    public function destroyFamily(Request $request){
        $query = UserFamily::find($request->id);
		
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

    public function destroyEducation(Request $request){
        $query = UserEducation::find($request->id);
		
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

    public function destroyWorkExperience(Request $request){
        $query = UserWorkExperience::find($request->id);
		
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

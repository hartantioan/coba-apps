<?php

namespace App\Http\Controllers\MasterData;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Shift;
use App\Models\Place;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportShift;

class ShiftController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Shift',
            'content'       => 'admin.master_data.shift',
            'place'         => Place::where('status','1')->get(),
            'department'    => Department::where('status','1')->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function indexHr()
    {
        $data = [
            'title'         => 'Pengaturan Waktu Shift',
            'content'       => 'admin.master_data.time_shift'
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'place_id',
            'department_id',
            'name',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Shift::count();
        
        $query_data = Shift::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('place',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('department',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
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

        $total_filtered = Shift::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('place',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('department',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
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
                    $val->place->name,
                    $val->department->name,
                    $val->name,
                    $val->min_time_in,
                    $val->time_in,
                    $val->time_out,
                    $val->max_time_out,
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

    public function datatableHr(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'place_id',
            'department_id',
            'name',
            'min_time_in',
            'time_in',
            'time_out',
            'max_time_out',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Shift::count();
        
        $query_data = Shift::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('place',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('department',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
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

        $total_filtered = Shift::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%")
                            ->orWhereHas('place',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
                            })->orWhereHas('department',function($query) use($search,$request){
                                $query->where('name','like',"%$search%");
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
                    $val->user->name,
                    $val->place->name,
                    $val->department->name,
                    $val->name,
                    $val->min_time_in,
                    $val->time_in,
                    $val->time_out,
                    $val->max_time_out,
                    $val->status(),
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
					',
                    $val->min_time_in == NULL || $val->time_in == NULL || $val->time_out == NULL || $val->max_time_out == NULL  ? '1' : ''
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
            'name'                      => 'required',
            'place_id'                  => 'required',
            'department_id'             => 'required',
        ], [
            'name.required'                 => 'Nama Shift tidak boleh kosong.',
            'place_id.required'             => 'Site tidak boleh kosong.',
            'department_id.required'        => 'Departemen tidak boleh kosong',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $time_in = strtotime($request->time_in);
            $min_time_in = strtotime($request->min_time_in);
            $time_out = strtotime($request->time_out);
            $max_time_out = strtotime($request->max_time_out);

            if($time_in < $min_time_in){
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Jam masuk tidak boleh kurang dari minimum jam masuk.',
                ]);
            }

            if($max_time_out < $time_out){
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Jam maksimum pulang tidak boleh kurang dari jam pulang.',
                ]);
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = Shift::find($request->temp);

                    if($query->edit_id){
                        return response()->json([
                            'status'    => 500,
                            'message'   => 'Anda tidak bisa melakukan perubahan, waktu shift telah dirubah oleh HRD.',
                        ]);
                    }

                    $query->name                = $request->name;
                    $query->user_id             = session('bo_id');
                    $query->place_id            = $request->place_id;
                    $query->department_id       = $request->department_id;
                    $query->name                = $request->name;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = Shift::create([
                        'code'              => Shift::generateCode(),
                        'name'			    => $request->name,
                        'user_id'           => session('bo_id'),
                        'place_id'          => $request->place_id,
                        'department_id'     => $request->department_id,
                        'min_time_in'       => $request->min_time_in,
                        'time_in'           => $request->time_in,
                        'time_out'          => $request->time_out,
                        'max_time_out'      => $request->max_time_out,
                        'status'            => $request->status ? $request->status : '2',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {               

                activity()
                    ->performedOn(new Shift())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit shift data.');

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

    public function createHr(Request $request){
        
        $validation = Validator::make($request->all(), [
            'temp'                      => 'required',
            'min_time_in'               => 'required',
            'time_in'                   => 'required',
            'time_out'                  => 'required',
            'max_time_out'              => 'required',
        ], [
            'temp.required'                 => 'Shift tidak boleh kosong',
            'minimum_time_in.required'      => 'Minimum jam masuk tidak boleh kosong',
            'time_in.required'              => 'Jam masuk tidak boleh kosong',
            'time_out.required'             => 'Jam pulang tidak boleh kosong',
            'max_time_out.required'         => 'Maksimum jam pulang tidak boleh kosong'
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $time_in = strtotime($request->time_in);
            $min_time_in = strtotime($request->min_time_in);
            $time_out = strtotime($request->time_out);
            $max_time_out = strtotime($request->max_time_out);

            if($time_in < $min_time_in){
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Jam masuk tidak boleh kurang dari minimum jam masuk.',
                ]);
            }

            if($max_time_out < $time_out){
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Jam maksimum pulang tidak boleh kurang dari jam pulang.',
                ]);
            }

            $query = Shift::find($request->temp);

            DB::beginTransaction();
            try {
                
                $query->edit_id             = session('bo_id');
                $query->min_time_in         = $request->min_time_in;
                $query->time_in             = $request->time_in;
                $query->time_out            = $request->time_out;
                $query->max_time_out        = $request->max_time_out;
                $query->status              = $request->status ? $request->status : '2';
                $query->save();

                DB::commit();
            }catch(\Exception $e){
                DB::rollback();
            }
			
			if($query) {               

                activity()
                    ->performedOn(new Shift())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Edit shift data.');

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
        $shift = Shift::find($request->id);
        $shift['min_time_in'] = date('H:i',strtotime($shift->min_time_in));
        $shift['time_in'] = date('H:i',strtotime($shift->time_in));
        $shift['time_out'] = date('H:i',strtotime($shift->time_out));
        $shift['max_time_out'] = date('H:i',strtotime($shift->max_time_out));
        
		return response()->json($shift);
    }

    public function showHr(Request $request){
        $shift = Shift::find($request->id);
        $shift['min_time_in'] = date('H:i',strtotime($shift->min_time_in));
        $shift['time_in'] = date('H:i',strtotime($shift->time_in));
        $shift['time_out'] = date('H:i',strtotime($shift->time_out));
        $shift['max_time_out'] = date('H:i',strtotime($shift->max_time_out));
        
		return response()->json($shift);
    }

    public function destroy(Request $request){
        $query = Shift::find($request->id);
		
        if($query->edit_id){
            return response()->json([
                'status'    => 500,
                'message'   => 'Anda tidak bisa menghapus, waktu shift telah dirubah oleh HRD.',
            ]);
        }

        if($query->delete()) {
            activity()
                ->performedOn(new Shift())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the shift data');

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

    public function destroyHr(Request $request){
        $query = Shift::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Shift())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the shift data');

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

    public function print(Request $request){

        $data = [
            'title' => 'SHIFT REPORT',
            'data' => Shift::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('name', 'like', "%$request->search%")
                            ->orWhereHas('place',function($query) use($request){
                                $query->where('name','like',"%$request->search%");
                            })->orWhereHas('department',function($query) use($request){
                                $query->where('name','like',"%$request->search%");
                            });
                    });
                }

                if ($request->status) {
                    $query->where('status',$request->status);
                }
            })->get()
		];
		
		return view('admin.print.master_data.shift', $data);
    }

    public function export(Request $request){
        $search = $request->search ? $request->search : '';
        $status = $request->status ? $request->status : '';
		
		return Excel::download(new ExportShift($search,$status), 'shift_'.uniqid().'.xlsx');
    }
}
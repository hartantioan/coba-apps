<?php

namespace App\Http\Controllers\Personal;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $userCode = session('bo_id');

        $data = [
            'title'         => 'Task Reminder - Personal',
            'content'       => 'admin.personal.task',
            'data_user'     => User::find(session('bo_id')),
            'serverTime'    => Carbon::now()->toIso8601String(),
            'code'          => $request->code ?? ''

        ];

        return view('admin.layouts.index', ['data' => $data]);
    }
    
    public function create(Request $request)
    {   
        
        $validation = Validator::make($request->all(), [
            'name'              => 'required',
            'note'			    => 'required',
            'start_date'	    => 'required',
            'end_date'			=> 'required',
            'age_limit_reminder'=> 'required'
        ], [
            'name.required'                 => 'nama Task diperlukan',
            'note.required'                 => 'keterangan Harap diisi',
            'start_date.required' 	        => 'Tanggal mulai tidak boleh kosong.',
            'end_date.required'             => 'Tanggal Akhir tidak boleh kosong.',
            'age_limit_reminder.required'   => 'Umur pengingat harus diisi'
        ]);
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        }else {
            if($request->start_date > $request->end_date){
                $response = [
					'status'    => 500,
					'message'   => 'Tanggal start melebihi tanggal akhir',
				];
            }else{
                DB::beginTransaction();
                try {
                    $start_date = Carbon::parse($request->start_date);
                    $end_date = Carbon::parse($request->end_date);
                    $diffInDays = $end_date->diffInDays($start_date);
                    $query = Task::create([
                        'name'			                => $request->name,
                        'user_id'		                => session('bo_id'),
                        'start_date'                    => $request->start_date,
                        'end_date'                      => $request->end_date,
                        'age'                           => $diffInDays,
                        'age_limit_reminder'	        => $request->age_limit_reminder,
                        'status'                        => 1,
                        'note'                          => $request->note
    
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
    
    public function show(Request $request){
        $activity = Task::find(CustomHelper::decrypt($request->id));
        
		return response()->json($activity);
    }

    public function destroy(Request $request){
        $query = Task::find(CustomHelper::decrypt($request->id));
		
        if($query->delete()) {
            activity()
                ->performedOn(new Task())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Task data');

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
    public function datatable(Request $request){
        $column = [
            'note',
            'name',
            'user_id',
            'start_date',
            'end_date',
            'age',
            'age_limit_reminder',
            'status'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Task::count();
        
        $query_data = Task::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%");
                            
                    });
                }
                $query->where(function($query) use ($search, $request) {
                    $query->WhereHas('user',function($query) use ($search, $request){
                            $query->where('id','like',session('bo_id'));
                        });
                });

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

            
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = Task::where(function($query) use ($search, $request) {
            if($search) {
                $query->where(function($query) use ($search, $request) {
                    $query->where('name', 'like', "%$search%");
                        
                });
            }
            $query->where(function($query) use ($search, $request) {
                $query->WhereHas('user',function($query) use ($search, $request){
                        $query->where('id','like',session('bo_id'));
                    });
            });

            if($request->status){
                $query->whereIn('status', $request->status);
            }

        
        })
        ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				$start_date = Carbon::parse($val->start_date);
                $end_date = Carbon::parse($val->end_date);

                $now = Carbon::now();
                $daysPassed = $now->diffInDays($start_date);
                $totalDays = $end_date->diffInDays($start_date);
                $daysLeft = $end_date->diffInDays($now);

                $progressPercentage = ($daysLeft / $totalDays) * 100;
                $color = '#0fdc17';
                if($progressPercentage < $val->age_limit_reminder){
                    $color = '#f2d60e';
                }else if($progressPercentage == 0 / $progressPercentage < 0){
                    $color = '#ff0505';
                }
                $response['data'][] = [
                    $nomor,
                    $val->name,
                    $val->note,
                    $val->start_date,
                    $val->end_date,
                    '<div class="progress pink lighten-5 mt-0">
                        <div class="determinate" style="width: '.$progressPercentage.'%;background-color:'.$color.'"></div>
                    </div>',
                    $val->age_limit_reminder . ' Days',
                    
                    $val->status,
                    '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->id) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->id) . '`)"><i class="material-icons dp48">delete</i></button>
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
}

<?php

namespace App\Http\Controllers\Setting;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\ChangeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChangeLogController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Log Update Aplikasi',
            'content'   => 'admin.setting.change_log',
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function index_log_update(Request $request){
        $data = [
            'title'     => 'Log Update Aplikasi',
            'content'   => 'admin.other.application_update_timeline',
            'change_log'=>  ChangeLog::where('status', '1')
            ->orderBy('release_date', 'desc')
            ->get(),
        ];
        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'version',
            'release_date',
            'title',
            'description',
            'status',
            'user_id'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ChangeLog::count();
        
        $query_data = ChangeLog::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('version', 'like', "%$search%")
                            ->orWhere('description', 'like', "%$search%")
                            ->orWhere('release_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
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

        $total_filtered = ChangeLog::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('version', 'like', "%$search%")
                            ->orWhere('description', 'like', "%$search%")
                            ->orWhere('release_date', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
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
                    $val->version,
                    $val->user->name,
                    date('d/m/Y',strtotime($val->release_date)),
                    $val->title,
                    substr(strip_tags($val->description), 0, 50),
                    $val->status(),
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

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'version'			    => 'required',
            'title' 				=> 'required',
            'release_date'			=> 'required'
        ], [
            'version.required' 	                => 'Versi tidak boleh kosong',
            
            'title.required' 				    => 'Title tidak boleh kosong.',
            'release_date.required' 			=> 'Tanggal rilis tidak boleh kosong.',
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
                    $query = ChangeLog::where('id',CustomHelper::decrypt($request->temp))->first();

                    $query->user_id = session('bo_id');
                    $query->version = $request->version;
                    $query->release_date = $request->release_date;
                    $query->description = $request->description;
                    $query->title = $request->title;
                    $query->status = $request->status;

                    $query->save();

                    DB::commit();
                    
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = ChangeLog::create([
                        'version'			        => $request->version,
                        'user_id'		            => session('bo_id'),
                        'release_date'              => $request->release_date,
                        'description'	            => $request->description,
                        'title'	                    => $request->title,
                        'status'                    => $request->status,
                        
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                CustomHelper::sendNotification('Change Logs',$query->id,'Update Aplikasi Versi. '.$query->version,session('bo_id'));

                activity()
                    ->performedOn(new ChangeLog())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit ChangeLog.');

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
        $change_log = ChangeLog::where('id',CustomHelper::decrypt($request->id))->first();
			
		return response()->json($change_log);
    }

    public function destroy(Request $request){
        $query = ChangeLog::where('id',CustomHelper::decrypt($request->id))->first();

        if($query->delete()) {

            activity()
                ->performedOn(new ChangeLog())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Change Log App');

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

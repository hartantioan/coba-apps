<?php

namespace App\Http\Controllers\HR;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\OvertimeRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Ijin Lembur',
            'content'       => 'admin.master_data.overtime_request',
            
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'shift_id',
            'user_id',
            'time_in',
            'time_out',
            'date',
            'code',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = OvertimeRequests::where('status',1)->count();
        
        $query_data = OvertimeRequests::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('time_in', 'like', "%$search%")
                            ->orWhere('time_out', 'like', "%$search%")
                            ->orWhere('date', 'like', "%$search%")
                            ->orWhereHas('user', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%")
                                    ->orWhere('employee_no', 'like', "%$search%");
                                });
                            ;
                    });
                }

            })
        
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = OvertimeRequests::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('time_in', 'like', "%$search%")
                            ->orWhere('time_out', 'like', "%$search%")
                            ->orWhere('date', 'like', "%$search%")
                            ->orWhereHas('user', function ($query) use ($search) {
                                    $query->where('name', 'like', "%$search%")
                                    ->orWhere('employee_no', 'like', "%$search%");
                                });
                            ;
                    });
                }
                
            })
           
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $url=request()->root()."/admin/hr/employee_transfer?employee_code=".CustomHelper::encrypt($val->id);
				
                $btn = 
                '
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(' . $val->id . ')"><i class="material-icons dp48">create</i></button>
                    <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(' . $val->id . ')"><i class="material-icons dp48">delete</i></button>
                ';
                
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->user->name,
                    $val->shift->name ?? 'tidak memilih shift',
                    $val->date,
                    $val->time_in,
                    $val->time_out,
                    
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
    public function destroy(Request $request){
        $query = OvertimeRequests::find($request->id);
		
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

    public function show(Request $request){
        $project = OvertimeRequests::find($request->id);
        $project['place_name'] = $project->place->name;
        $project['level_name'] = $project->level->name;			
		return response()->json($project);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            
            
            'user_id'       => 'required',
            'time_in'       => 'required',
            'time_out'      => 'required',
            'date'          => 'required',
            'code'          => 'required',
            
        ], [
            'user_id.required' 	            => 'User Tidak boleh kosong',
            'time_in.required'              => 'Jam masuk Harap Dipilih',
            'time_out.required'             => 'Jam Keluar Harap Dipilih',
            'date.required'                 => 'Tanggal Tidak Boleh kosong',
            'code.required'                 => 'Kode tidak boleh kosong',
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
                    $query = OvertimeRequests::find($request->temp);
                    $approved = false;
                    $revised = false;

                    if($query->approval()){
                        foreach ($query->approval() as $detail){
                            foreach($detail->approvalMatrix as $row){
                                if($row->approved){
                                    $approved = true;
                                }

                                if($row->revised){
                                    $revised = true;
                                }
                            }
                        }
                    }

                    if($approved && !$revised){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Lembur telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){
                    $query->user_id                = $request->user_id;
                    $query->time_in                = $request->time_in;
                    $query->time_out               = $request->time_out;
                    $query->date                   = $request->date;
                    $query->code                   = $request->code;
                    $query->save();

                    DB::commit();
                }else{
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Status purchase order sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                    ]);
                }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = OvertimeRequests::create([
                        'shift_id'              => $request->name,
                        'user_id'			    => $request->place_id,
                        'time_in'			    => $request->level_id,
                        'time_out'			    => $request->start_date,
                        'date'			    => $request->end_date,
                        'code'			    => $request->type,
                        
                        'status'			    => $request->status,
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Purchase Order No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new OvertimeRequests())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Ijin Lembur.');

             
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

    public function approval(Request $request,$id){
        
        $pr = OvertimeRequests::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Ijin Lembur',
                'data'      => $pr
            ];

            return view('admin.approval.overtime_request', $data);
        }else{
            abort(404);
        }
    }
}

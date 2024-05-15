<?php

namespace App\Http\Controllers\HR;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Place;
use App\Models\Menu;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OvertimeRequestController extends Controller
{
    public function index()
    {$lastSegment = request()->segment(count(request()->segments()));
       
        $menu = Menu::where('url', $lastSegment)->first();
        $data = [
            'title'         => 'Ijin Lembur',
            'content'       => 'admin.hr.overtime_request',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->get(),
            'newcode'   =>  $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'schedule_id',
            'user_id',
            'account_id',
            'company_id',
            'post_date',
            'note',
            'time_in',
            'time_out',
            'date',
            'code',
            'status',
            'void_id',
            'void_note',
            'void_date',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = OvertimeRequest::where('status',1)->count();
        
        $query_data = OvertimeRequest::where(function($query) use ($search, $request) {
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

        $total_filtered = OvertimeRequest::where(function($query) use ($search, $request) {
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
                    $val->company->name,
                    $val->account->name,
                    $val->schedule->shift->name ?? 'tidak memilih shift',
                    $val->time_in,
                    $val->time_out,
                    $val->date,
                    $val->post_date,
                    $val->note,
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
        $query = OvertimeRequest::find($request->id);
        
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
        if($query->delete()) {
            CustomHelper::removeApproval('overtime_requests',$query->id);

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);
       
            activity()
            ->performedOn(new OvertimeRequest())
            ->causedBy(session('bo_id'))
            ->withProperties($query)
            ->log('Delete the overtime Request data');

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

    public function getCode(Request $request){
        $code = OvertimeRequest::generateCode($request->val);
       
		return response()->json($code);
    }

    public function show(Request $request){
        $project = OvertimeRequest::find($request->id);
        $project['place_name'] = $project->place->name;
        $project['level_name'] = $project->level->name;			
		return response()->json($project);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            
            
            'account_id'       => 'required',
            'time_in'       => 'required',
            'company_id'    => 'required',
            'time_out'      => 'required',
            'date'          => 'required',
            'code'          => 'required',
            
        ], [
            'account_id.required' 	            => 'User Tidak boleh kosong',
            'time_in.required'              => 'Jam masuk Harap Dipilih',
            'time_out.required'             => 'Jam Keluar Harap Dipilih',
            'company_id.required'             => 'Harap Pilih Perusahaan',
            'date.required'                 => 'Tanggal Tidak Boleh kosong',
            'code.required'                 => 'Kode tidak boleh kosong',
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        }else {
            $user_q = User::find($request->account_id);
            $CountSchedule = EmployeeSchedule::where(function($query) use($request, $user_q) {
                $query->where('employee_schedules.user_id', $user_q->employee_no)
                      ->where('employee_schedules.date', $request->date)
                      ->where('employee_schedules.status', '1');
            })
            ->join('shifts', 'employee_schedules.shift_id', '=', 'shifts.id')
            ->orderBy('shifts.time_in', 'ASC') // Order by time_in in ascending order
            ->count();

            

            if($request->time_in > $request->time_out){
                $kambing["kambing"][]="Jam masuk lebih besar dari pada jam keluar... Apabila berbeda hari harap buat lembur di tanggal yang berbeda";
                $response = [
                    'status' => 422,
                    'error'  => $kambing
                ];
                return response()->json($response);
            }

            if($CountSchedule > 2){
                $kambing["kambing"][]="Schedule pada hari itu lebih dari 3 jadi tak bisa menambah lembur";
                $response = [
                    'status' => 422,
                    'error'  => $kambing
                ];
                return response()->json($response);
            }

            $firstRecord = EmployeeSchedule::where(function($query) use($request, $user_q) {
                $query->where('employee_schedules.user_id', $user_q->employee_no)
                      ->where('employee_schedules.date', $request->date)
                      ->where('employee_schedules.status', '1');
            })
            ->join('shifts', 'employee_schedules.shift_id', '=', 'shifts.id')
            ->orderBy('shifts.time_in', 'ASC') // Order by time_in in ascending order
            ->first(); // Get the first record
        
            // Get the last record
            $lastRecord = EmployeeSchedule::where(function($query) use($request, $user_q) {
                    $query->where('employee_schedules.user_id', $user_q->employee_no)
                        ->where('employee_schedules.date', $request->date)
                        ->where('employee_schedules.status', '1');
                })
            ->join('shifts', 'employee_schedules.shift_id', '=', 'shifts.id')
            ->orderBy('shifts.time_in', 'DESC') // Order by time_in in descending order
            ->first();
            if($firstRecord){
                $first_time_out =Carbon::parse($request->date)->format('Y-m-d') . ' ' . $firstRecord->time_out;
                $last_time_in =Carbon::parse($request->date)->format('Y-m-d') . ' ' . $lastRecord->time_in;
                $timeDifference = Carbon::parse($first_time_out)->diff(Carbon::parse($last_time_in));
                $hoursDifference = $timeDifference->h;
                if($hoursDifference > 1 && $CountSchedule > 1){
                    
                    $hoursDifferenceRequest =  Carbon::parse($first_time_out)->diff(Carbon::parse( $last_time_in));
                    
                    if($hoursDifference<$hoursDifferenceRequest){
                        $kambing["kambing"][]="Inputan Time In dan Timeout melebihi schedule yang ada atau berselisihan";
                        $response = [
                            'status' => 422,
                            'error'  => $kambing
                        ];
                        return response()->json($response);
                    }
                }else{
                    if($request->time_in > $firstRecord->time_in && $request->time_in < $lastRecord->time_out){
                        $kambing["kambing"][]="jam masuk berada diantara masuk dan keluarnya shift hari itu";
                        $response = [
                            'status' => 422,
                            'error'  => $kambing
                        ];
                        return response()->json($response);
                    }
                    if($request->time_out < $lastRecord->time_out && $request->time_out > $firstRecord->time_in ){
                        $kambing["kambing"][]="jam keluar kurang dari shift yang ada di hari itu ";
                        $response = [
                            'status' => 422,
                            'error'  => $kambing
                        ];
                        return response()->json($response);
                    }
                    if($request->time_in<$firstRecord->time_in && $request->time_out > $lastRecord->time_out ){
                        $kambing["kambing"][]="jam masuk dan keluar seperti menggantikan shift pastikan bahwa sudah benar ";
                        $response = [
                            'status' => 422,
                            'error'  => $kambing
                        ];
                        return response()->json($response);
                    }
                }
                
                
            }
            
			if($request->temp){
                
                DB::beginTransaction();
                try {
                    $query = OvertimeRequest::find($request->temp);
                    if($request->schedule_id != $query->schedule_id){
                        $exist_schedule_id = OvertimeRequest::where('schedule_id', $request->schedule_id)
                        ->whereIn('status',['1','2','6'])->exists();
                        if($exist_schedule_id){
                            $kambing["kambing"][]="Schedule telah digunakan ";
                            $response = [
                                'status' => 422,
                                'error'  => $kambing
                            ];
                            return response()->json($response);
                        }
                    }
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
                    
                    $query->time_in                = $request->time_in;
                    $query->time_out               = $request->time_out;
                    $query->date                   = $request->date;
                    $query->account_id             = $request->account_id;
                    $query->company_id             = $request->company_id;
                    $query->post_date              = $request->post_date;
                    $query->schedule_id            = $request->schedule_id;
                    $query->note                   = $request->note;
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
                if($request->schedule_id){
                    $exist_schedule_id = OvertimeRequest::where('schedule_id', $request->schedule_id)
                    ->whereIn('status',['1','2','6'])->exists();
                    if($exist_schedule_id){
                        $kambing["kambing"][]="Schedule telah digunakan ";
                        $response = [
                            'status' => 422,
                            'error'  => $kambing
                        ];
                        return response()->json($response);
                    }
                }
                DB::beginTransaction();
                try {
                    
                    $query = OvertimeRequest::create([
                        'schedule_id'           => $request->schedule_id,
                        'user_id'			    => session('bo_id'),
                        'account_id'			=> $request->account_id,
                        'post_date'			    => $request->post_date,
                        'company_id'			=> $request->company_id,
                        'time_in'			    => $request->time_in,
                        'time_out'			    => $request->time_out,
                        'date'			        => $request->date,
                        'code'			        => $request->code,
                        'note'                  => $request->note,
                        'total'                 => 0,
                        'grandtotal'            => 0,
                        'status'                => '1',
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
                    ->performedOn(new OvertimeRequest())
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
        
        $pr = OvertimeRequest::where('code',CustomHelper::decrypt($id))->first();
                
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

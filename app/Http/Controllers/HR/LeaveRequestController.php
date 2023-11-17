<?php

namespace App\Http\Controllers\HR;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\EmployeeLeaveQuotas;
use App\Models\EmployeeSchedule;
use App\Models\EmployeeTransfer;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestShift;
use App\Models\LeaveType;
use App\Models\Place;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Validator;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Permohonan Ijin',
            'content'       => 'admin.hr.leave_request',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->get(),
            'newcode'       => 'LREQ-'.date('y'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'name',
            'user_id',
            'company_id',
            'account_id',
            'leave_type_id',
            'start_time',
            'end_time',
            'end_date',
            'post_date',
            'start_date',
            'note',
            'document',
            'void_id',
            'void_date',
            'void_note',
            'status',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = LeaveRequest::count();
        
        $query_data = LeaveRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
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

        $total_filtered = LeaveRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('name', 'like', "%$search%");
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
                <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                ';
                      
                $response['data'][] = [
                    $nomor,
                    $val->code,
                  
                    $val->user->name,
                    $val->company->name,
                    $val->account->name,
                    $val->leaveType->name,
                    $val->start_time,
                    $val->end_time,
                    $val->start_date,
                    $val->end_date,
                    $val->note,
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
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

        if($request->leave_type_id){
           
            $query_pengambil_tipe = LeaveType::find($request->leave_type_id);
          
            if($query_pengambil_tipe->type == '1'){         
                $validation = Validator::make($request->all(), [
                    'code'			           => $request->temp ? ['required', Rule::unique('leave_requests', 'code')->ignore($request->temp)] : 'required|unique:leave_requests,code',
                    'company_id'               => 'required',
                    'account_id'               => 'required',
                    'leave_type_id'            => 'required',
                    'post_date'                => 'required',
                    'start_date'               => 'required',
                    'note'                     => 'required',
                    'arr_schedule'         => 'required',
                ], [
                    'code.required' 	            => 'Kode tidak boleh kosong.',
                    'code.unique'                   => 'Kode telah dipakai',
                    'company_id.required'                 => 'Perusahaan tidak boleh kosong.',
                    'account_id.required'                 => 'Pegawai tidak boleh kosong.',
                    'post_date.required'                => 'Tanggal Post Tidak Boleh kosong',
                    'leave_type_id.required'                 => 'Tipe Ijin tidak boleh kosong.',
                    'start_date.required'                 => 'Tanggal Awal tidak boleh kosong.',
                    'note.required'                 => 'Catatan tidak boleh kosong.',
                    'arr_schedule.required'                  =>'Harap pilih Schedule'
                ]);
            } if($query_pengambil_tipe->type == '2'){
                $validation = Validator::make($request->all(), [
                    'code'			           => $request->temp ? ['required', Rule::unique('leave_requests', 'code')->ignore($request->temp)] : 'required|unique:leave_requests,code',
                    'post_date'                => 'required',
                    'company_id'               => 'required',
                    'account_id'               => 'required',
                    'leave_type_id'            => 'required',
                    'start_time'               => 'required',
                    
                    
                    'start_date'               => 'required',
                    'note'                     => 'required',
                    'arr_schedule'         => 'required',
                ], [
                    'code.required' 	            => 'Kode tidak boleh kosong.',
                    'code.unique'                   => 'Kode telah dipakai',
                    'company_id.required'                 => 'Perusahaan tidak boleh kosong.',
                    'account_id.required'                 => 'Pegawai tidak boleh kosong.',
                    'start_time.required'                 => 'Jam Awal tidak boleh kosong.',
                    
                    'post_date.required'                => 'Tanggal Post Tidak Boleh kosong',
                    'leave_type_id.required'                 => 'Tipe Ijin tidak boleh kosong.',
                    
                    'start_date.required'                 => 'Tanggal Awal tidak boleh kosong.',
                    'note.required'                 => 'Catatan tidak boleh kosong.',
                    'arr_schedule.required'                  =>'Harap pilih Schedule'
                ]);
            }if($query_pengambil_tipe->type == '3'){
                $validation = Validator::make($request->all(), [
                    'code'			           => $request->temp ? ['required', Rule::unique('leave_requests', 'code')->ignore($request->temp)] : 'required|unique:leave_requests,code',
                    'post_date'                => 'required',
                    'company_id'               => 'required',
                    'account_id'               => 'required',
                    'leave_type_id'            => 'required',
                    'arr_schedule'         => 'required',
                    'end_date'                 => 'required',
                    'start_date'               => 'required',
                    'note'                     => 'required',
                ], [
                    'code.required' 	            => 'Kode tidak boleh kosong.',
                    'code.unique'                   => 'Kode telah dipakai',
                    'company_id.required'                 => 'Perusahaan tidak boleh kosong.',
                    'account_id.required'                 => 'Pegawai tidak boleh kosong.',
                    'arr_schedule.required'                  =>'Harap pilih Schedule',
                    'post_date.required'                => 'Tanggal Post Tidak Boleh kosong',
                    'leave_type_id.required'                 => 'Tipe Ijin tidak boleh kosong.',
                    'end_date.required'                 => 'Tanggal Akhir tidak boleh kosong.',
                    'start_date.required'                 => 'Tanggal Awal tidak boleh kosong.',
                    'note.required'                 => 'Catatan tidak boleh kosong.',
                ]);
            }if($query_pengambil_tipe->type == '4'){
                $validation = Validator::make($request->all(), [
                    'code'			           => $request->temp ? ['required', Rule::unique('leave_requests', 'code')->ignore($request->temp)] : 'required|unique:leave_requests,code',
                    'post_date'                => 'required',
                    'company_id'               => 'required',
                    'account_id'               => 'required',
                    'leave_type_id'            => 'required',
                    'start_time'               => 'required',
                    'end_time'                 => 'required',
                    'start_date'               => 'required',
                    'note'                     => 'required',
                    'arr_schedule'         => 'required',
                ], [
                    'code.required' 	            => 'Kode tidak boleh kosong.',
                    'arr_schedule.required'                  =>'Harap pilih Schedule',
                    'code.unique'                   => 'Kode telah dipakai',
                    'company_id.required'                 => 'Perusahaan tidak boleh kosong.',
                    'account_id.required'                 => 'Pegawai tidak boleh kosong.',
                    'start_time.required'                 => 'Jam Awal s tidak boleh kosong.',
                    'end_time.required'                 => 'Jam Awal tidak boleh kosong.',
                    'post_date.required'                => 'Tanggal Post Tidak Boleh kosong',
                    'leave_type_id.required'                 => 'Tipe Ijin tidak boleh kosong.',
                    'start_date.required'                 => 'Tanggal Awal tidak boleh kosong.',
                    'note.required'                 => 'Catatan tidak boleh kosong.',
                ]);
            }if($query_pengambil_tipe->type == '7'){         
                $validation = Validator::make($request->all(), [
                    'code'			           => $request->temp ? ['required', Rule::unique('leave_requests', 'code')->ignore($request->temp)] : 'required|unique:leave_requests,code',
                    'company_id'               => 'required',
                    'account_id'               => 'required',
                    'leave_type_id'            => 'required',
                    'post_date'                => 'required',
                    'start_date'               => 'required',
                    'note'                     => 'required',
                   
                ], [
                    'code.required' 	            => 'Kode tidak boleh kosong.',
                    'code.unique'                   => 'Kode telah dipakai',
                    'company_id.required'                 => 'Perusahaan tidak boleh kosong.',
                    'account_id.required'                 => 'Pegawai tidak boleh kosong.',
                    'post_date.required'                => 'Tanggal Post Tidak Boleh kosong',
                    'leave_type_id.required'                 => 'Tipe Ijin tidak boleh kosong.',
                    'start_date.required'                 => 'Tanggal Awal tidak boleh kosong.',
                    'note.required'                 => 'Catatan tidak boleh kosong.',
                   
                ]);
            }
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {
                if (in_array($query_pengambil_tipe->furlough_type, ['4', '5'])) {
                    if($query_pengambil_tipe->furloughType == '1'){
                        $dateFromRequest = Carbon::parse($request->start_date);
                        $today = Carbon::today();
                        if ($dateFromRequest->isAfter($today) || $dateFromRequest->isSameDay($today)) {
                            
                        } else {
                            $response = [
                                'status'  => 500,
                                'message' => 'Tanggal Kurang dari tanggal hari ini.'
                            ];
                        }
                    }
                }
                
                
                $query_employee_leave_q = EmployeeLeaveQuotas::where('user_id',$request->account_id)->where('start_date','like',date('Y')."%")->first();
                
                if($query_employee_leave_q || $query_pengambil_tipe->furlough_type != '1'){
                    if( $query_pengambil_tipe->furlough_type != '1' || count($request->arr_schedule)  <= $query_employee_leave_q->paid_leave_quotas){
                        if($request->temp){
                            DB::beginTransaction();
                            
                            try {
                                $query = LeaveRequest::find($request->temp);
                                if(in_array($query->status,['1','6'])){
                                    //perhitungan kuota cuti pertama mengambil total lalu menambahkan dulu baru mengurangi dengan shift total terbaru
                                    if($query_pengambil_tipe->furlough_type != '1'){
                                        $total = $request->temp_schedule + $query_employee_leave_q->paid_leave_quotas - count($request->arr_schedule);
                                        $query_employee_leave_q->paid_leave_quotas = $total;
                                        $query_employee_leave_q->save();
                                    }
                                    
                                    
                                    $query->user_id               = session('bo_id');
                                    $query->company_id              = $request->company_id;
                                    $query->account_id               = $request->account_id;
                                    $query->leave_type_id               = $request->leave_type_id;
                                    $query->start_time               = $request->start_time;
                                    $query->end_time               = $request->end_time;
                                    $query->end_date               = $request->end_date ?? $request->start_date;
                                    $query->post_date               = $request->post_date;
                                    $query->start_date               = $request->start_date;
                                    $query->note               = $request->note;
                                    $query->status               = $request->status ? $request->status : '1';
                                    $query->save();
    
                                    foreach($query->leaveRequestShift as $row){
                                        $query_schedule = EmployeeSchedule::find($row->employee_schedule_id);
                                        $query_schedule->status = 1;
                                        $query_schedule->save();
                                        $row->delete();
                                    }
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
                                $query = LeaveRequest::create([
                                    'code'                  => LeaveRequest::generateCode($request->code),
                                    'user_id'                 => session('bo_id'),
                                    'company_id'                 => $request->company_id,
                                    'account_id'                 => $request->account_id,
                                    'leave_type_id'                 => $request->leave_type_id,
                                    'start_time'                 => $request->start_time,
                                    'end_time'                 => $request->end_time,
                                    'end_date'                 => $request->end_date ?? $request->start_date,
                                    'post_date'                 => $request->post_date,
                                    'start_date'                 => $request->start_date,
                                    'note'                 => $request->note,
                                    'document'               => $request->file('document') ? $request->file('document')->store('public/leave_request') : NULL,
                                    'status'                => $request->status ? $request->status : '1',
                                ]);
                                //pengurangan kuota
                                if($query_pengambil_tipe->furlough_type == '1'){
                                    $total =$query_employee_leave_q->paid_leave_quotas - count($request->arr_schedule);
                                    
                                    $query_employee_leave_q->paid_leave_quotas = $total;
                                    $query_employee_leave_q->save();
                                }

                                DB::commit();
                            }catch(\Exception $e){
                                DB::rollback();
                            }
                        }
                    }else{
                        $response = [
                            'status'  => 500,
                            'message' => 'Melebihi Kuota Cuti Harap ganti ke tipe ijin'
                        ];
                        return response()->json($response);
                    }
                }else{
                    $response = [
                        'status'  => 500,
                        'message' => 'Masih Belum memiliki kuota cuti mohon melakukan permintaan untuk membuat kuota cuti'
                    ];
                    return response()->json($response);
                }
                
                
                
                
                if($query) {
                    if(in_array($query->leaveType->furlough_type,['1','2','3','8','9'])){
                        foreach($request->arr_schedule as $row_schedule) {
                            $query_leave_shift = LeaveRequestShift::create([
                                'leave_request_id'=>$query->id,
                                'employee_schedule_id'=>$row_schedule
                            ]);
                            $query_schedule = EmployeeSchedule::find($row_schedule);
                            $query_schedule->status = 3;
                            $query_schedule->save();
                           
                        }
                    }elseif($query->leaveType->furlough_type == '7'){
                        
                    }else{
                        foreach($request->arr_schedule as $row_schedule) {
                            $query_leave_shift = LeaveRequestShift::create([
                                'leave_request_id'=>$query->id,
                                'employee_schedule_id'=>$row_schedule
                            ]);
                        }
                    }
                    

                    CustomHelper::sendApproval('leave_requests',$query->id,$query->note);
                    CustomHelper::sendNotification('leave_requests',$query->id,'Pengajuan Permohonan Dana No. '.$query->code,$query->note,session('bo_id'));
                    
                    activity()
                        ->performedOn(new LeaveRequest())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit Leave Request.');
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
           
        }else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to save.'
            ];
        }
        
        
		
		return response()->json($response);
    }

    public function approval(Request $request,$id){
        
        $pr = LeaveRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Permohonan Dana',
                'data'      => $pr
            ];

            return view('admin.approval.leave_request', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $Level = LeaveRequest::find($request->id);
        $Level['leaveType']=$Level->leaveType;
        $Level['account']=$Level->account;
        $arr=[];
        foreach($Level->leaveRequestShift as $row){
            $arr[] = [
                'leave_request_id'                    => $row->leave_request_id,
                'employee_schedule_id'                => $row->employee_schedule_id,
                'employee_schedule_shift'             => $row->employeeSchedule->date.'|'.$row->employeeSchedule->shift->name.' Jam:'.$row->employeeSchedule->shift->time_in.' - '.$row->employeeSchedule->shift->time_out,
            ];
        }
        $Level['details'] = $arr;
        $Level['count'] = count($arr);				
		return response()->json($Level);
    }

    public function voidStatus(Request $request){
        $query = LeaveRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new LeaveRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the  leave reqeuest data');
    
                CustomHelper::sendNotification('leave_requests',$query->id,'Leave Request . '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('leave_requests',$query->id);

                $response = [
                    'status'  => 200,
                    'message' => 'Data closed successfully.'
                ];
            }
        } else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function destroy(Request $request){
        $query = LeaveRequest::find($request->id);
		
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

    public function getCode(Request $request){
        $code = LeaveRequest::generateCode($request->val);
       
		return response()->json($code);
    }
}

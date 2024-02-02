<?php

namespace App\Http\Controllers\HR;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Place;
use App\Models\ShiftRequest;
use App\Models\ShiftRequestDetail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Storage;
use Illuminate\Support\Facades\Validator;

class ShiftRequestController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Permohonan Ijin',
            'content'       => 'admin.hr.shift_request',
            'company'       => Company::where('status','1')->get(),
            'place'         => Place::where('status','1')->get(),
            'newcode'       => 'SREQ-'.date('y'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'code',
            'user_id',
            'company_id',
            'employee_id',
            'post_date',
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

        $total_data = ShiftRequest::count();
        
        $query_data = ShiftRequest::where(function($query) use ($search, $request) {
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

        $total_filtered = ShiftRequest::where(function($query) use ($search, $request) {
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
                    $val->post_date,
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
            'code'			            => $request->temp ? ['required', Rule::unique('shift_requests', 'code')->ignore($request->temp)] : 'required|unique:shift_requests,code',
            'post_date'                 => 'required',
            'company_id'                => 'required',
            'account_id'                => 'required',
            'arr_schedule'              => 'required|array',
            'arr_date'                  => 'required|array',
            'arr_shift'                 => 'required|array',
            
        ], [
            'code.required' 	            => 'Kode tidak boleh kosong.',
            'code.unique'                   => 'Kode telah dipakai',
            'company_id.required'                 => 'Perusahaan tidak boleh kosong.',
            'account_id.required'                 => 'Pegawai tidak boleh kosong.',
            'post_date.required'                => 'Tanggal Post Tidak Boleh kosong',
            'arr_schedule.required'                => 'Jadwal tidak boleh kosong.',
            'arr_schedule.array'                   => 'Jadwal harus array.',
            'arr_date.required'                => 'Tanggal tidak boleh kosong.',
            'arr_date.array'                   => 'Tanggal harus array.',
            'arr_shift.required'                => 'Shift tidak boleh kosong.',
            'arr_shift.array'                   => 'Shift harus array.',
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
                    $query = ShiftRequest::find($request->temp);
                    if(in_array($query->status,['1','6'])){

                        $query->user_id               = session('bo_id');
                        $query->company_id              = $request->company_id;
                        $query->account_id               = $request->account_id;
                        $query->post_date               = $request->post_date;
                        $query->status               = $request->status ? $request->status : '1';
                        foreach($query->shiftRequestDetail as $row){
                            $row->delete();
                        }
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
                    $query_employeee= User::find($request->account_id);
                    $query = ShiftRequest::create([
                        'code'                  => ShiftRequest::generateCode($request->code),
                        'user_id'                 => session('bo_id'),
                        'company_id'                 => $request->company_id,
                        'account_id'                 => $query_employeee->employee_no,
                        'post_date'                 => $request->post_date,
                        'status'                => $request->status ? $request->status : '1',
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
            }
            
            if($query) {
                DB::beginTransaction();
                try {
                    foreach($request->arr_schedule as $key => $row){
        

    
                            $querydetail = ShiftRequestDetail::create([
                                'shift_request_id'              => $query->id,
                                'employee_schedule_id'          => $row,
                                'shift_id'                      => $request->arr_shift[$key],
                                'date'                          => $request->arr_date[$key]
                            ]);
                        
                    }
                    

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
                CustomHelper::sendApproval('shift_requests',$query->id,$query->note);
                CustomHelper::sendNotification('shift_requests',$query->id,'Pengajuan Permohonan Dana No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new ShiftRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit Shift Request.');
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
        
        $pr = ShiftRequest::where('code',CustomHelper::decrypt($id))->first();
        $SR['shift_request'] = $pr;
        $shiftdetail = $pr->shiftRequestDetail;
        foreach($shiftdetail as $row_detail){
            $temp = [
                'shift' => $row_detail->shift,
                'date'  => $row_detail->date,
            ];
            $SR['shift'][]= $temp;
        }
        
        $SR['user']= $pr->account;   
        $SR['company']= $pr->company;    
        if($pr){
            $data = [
                'title'     => 'Print Permohonan Tukar Shift',
                'data'      => $pr
            ];

            return view('admin.approval.shift_request', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $Level = ShiftRequest::find($request->id);
        $Level['user']=$Level->user;
        $Level['company']=$Level->company;
        $Level['account']=$Level->account;				
		return response()->json($Level);
    }

    public function voidStatus(Request $request){
        $query = ShiftRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    ->performedOn(new ShiftRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the  shift request data');
    
                CustomHelper::sendNotification('Shift Request',$query->id,'Shift Request No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('Shift Request',$query->id);

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
        $query = ShiftRequest::find($request->id);
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
                'message' => 'Pertukaran shift tidak dapat dihapus karena sudah di approve harap melakukan void untuk membatalkan.'
            ]);
        }
        if($query->delete()) {
            CustomHelper::removeApproval('shift_requests',$query->id);
            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);
       
            activity()
            ->performedOn(new ShiftRequest())
            ->causedBy(session('bo_id'))
            ->withProperties($query)
            ->log('Delete the purchase memo data');
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
        $code = ShiftRequest::generateCode($request->val);
        				
		return response()->json($code);
    }
}

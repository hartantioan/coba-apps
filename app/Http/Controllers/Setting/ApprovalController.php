<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Models\GoodReceipt;
use App\Models\LandedCost;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Approval;
use App\Models\User;
use App\Models\ApprovalMatrix;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportCoa;
use Illuminate\Database\Eloquent\Builder;
use App\Helpers\CustomHelper;

class ApprovalController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Approval',
            'content'   => 'admin.setting.approval',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'name',
            'document_text',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = Approval::count();
        
        $query_data = Approval::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('document_text', 'like', "%$search%");
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

        $total_filtered = Approval::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('name', 'like', "%$search%")
                            ->orWhere('document_text', 'like', "%$search%");
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
                    $val->name,
                    $val->document_text,
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

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'name'          => 'required',
            'document_text' => 'required'
        ], [
            'name.required' => 'Nama area tidak boleh kosong.',
            'document_text' => 'Teks dokumen tidak boleh kosong.'
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
                    $query = Approval::find($request->temp);
                    $query->name                = $request->name;
                    $query->document_text       = $request->document_text;
                    $query->status              = $request->status ? $request->status : '2';
                    $query->save();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{

                DB::beginTransaction();
                try {
                    $query = Approval::create([
                        'name'			    => $request->name,
                        'document_text'		=> $request->document_text,
                        'status'            => $request->status ? $request->status : '2',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {               

                activity()
                    ->performedOn(new Approval())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit approval data.');

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
        $approval = Approval::find($request->id);
        				
		return response()->json($approval);
    }

    public function destroy(Request $request){
        $query = Approval::find($request->id);
		
        if($query->delete()) {
            activity()
                ->performedOn(new Approval())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the approval data');

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

    public function approvalIndex()
    {
        $data = [
            'title'     => 'Approval',
            'content'   => 'admin.approval.approval',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function approvalDatatable(Request $request){
        $column = [
            'id',
            'code',
            'date_request'            
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $user = User::find(session('bo_id'));

        $dataplaces = $user->userPlaceArray();

        $total_data = ApprovalMatrix::where('user_id',session('bo_id'))
                        ->whereIn('status',['1','2'])
                        ->where(function($query) use($dataplaces){ 
                            $query->whereHas('approvalSource',function($query) use($dataplaces){ 
                                $query->whereIn('place_id',$dataplaces); 
                            });
                        })->count();
        
        $query_data = ApprovalMatrix::where(function($query) use ($search, $request, $dataplaces) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('date_request', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('approvalSource',function($query) use($search,$request){
                                $query->whereHasMorph('lookable',[PurchaseRequest::class,PurchaseOrder::class,PurchaseInvoice::class,PurchaseDownPayment::class,LandedCost::class,GoodReceipt::class],function (Builder $query) use ($search) {
                                    $query->where('code','like',"%$search%");
                                })
                                ->orWhereHas('user',function($query) use($search,$request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                })
                                ->orWhere('note','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                $query->whereHas('approvalSource',function($query) use($dataplaces){ $query->whereIn('place_id',$dataplaces); });
            })
            ->whereIn('status',['1','2'])
            ->where('user_id',session('bo_id'))
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ApprovalMatrix::where(function($query) use ($search, $request, $dataplaces) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('date_request', 'like', "%$search%")
                            ->orWhere('note','like',"%$search%")
                            ->orWhereHas('approvalSource',function($query) use($search,$request){
                                $query->whereHasMorph('lookable',[PurchaseRequest::class,PurchaseOrder::class,PurchaseInvoice::class,PurchaseDownPayment::class,LandedCost::class,GoodReceipt::class],function (Builder $query) use ($search) {
                                    $query->where('code','like',"%$search%");
                                })
                                ->orWhereHas('user',function($query) use($search,$request){
                                    $query->where('code','like',"%$search%")
                                        ->orWhere('name','like',"%$search%");
                                })
                                ->orWhere('note','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                $query->whereHas('approvalSource',function($query) use($dataplaces){ $query->whereIn('place_id',$dataplaces); });
            })
            ->whereIn('status',['1','2'])
            ->where('user_id',session('bo_id'))
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
				
                $response['data'][] = [
                    $val->status == '1' ? '<span class="pick" data-id="'.CustomHelper::encrypt($val->code).'">'.$nomor.'</span>' : $nomor,
                    $val->code,
                    date('d M Y H:i:s',strtotime($val->date_request)),
                    $val->approvalSource->user->name,
                    $val->approvalSource->lookable->code,
                    $val->approvalSource->note,
                    $val->status == '1' ? '
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-text btn-small btn-approve" data-popup="tooltip" title="show" onclick="show(`' . url('admin/'.$val->approvalTable->menu->fullUrl() . '/approval/' . CustomHelper::encrypt($val->approvalSource->lookable->code)) . '`,`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons dp48">pageview</i></button>
					' : ($val->approved ? 'Disetujui' : ($val->rejected ? 'Ditolak' : 'Invalid')),
                    $val->status(),
                    $val->note,
                    $val->status == '1' ? 'pending' : ''
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

    public function approve(Request $request){
        $validation = Validator::make($request->all(), [
            'temp'          => 'required',
            'note'          => 'required',
        ], [
            'temp.required' => 'Approval tidak boleh kosong.',
            'note.required' => 'Keterangan tidak boleh kosong.'
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $query = ApprovalMatrix::where('code',CustomHelper::decrypt($request->temp))->where('status','1')->first();
			
			if($query) {

                DB::beginTransaction();
                try {

                    $query->note = $request->note;

                    if($request->approve_reject){
                        $query->approved = '1';
                        $query->rejected = NULL;
                        $text = 'disetujui';
                    }else{
                        $query->approved = NULL;
                        $query->rejected = '1';
                        $text = 'ditolak';
                    }

                    $query->date_process = date('Y-m-d H:i:s');
                    $query->status = '2';
                    $query->save();
                    
                    if($request->approve_reject){
                        if($query->passedApprove()){
                            if($query->updateNextLevelApproval() !== ''){
                                $am = ApprovalMatrix::where('approval_table_id',$query->updateNextLevelApproval())->where('approval_source_id',$query->approval_source_id)->where('status','0')->get();
    
                                if($am){
                                    foreach($am as $row){
                                        $row->update([
                                            'status'    => '1'
                                        ]);
                                    }
                                }
                            }else{

                                $pr = $query->approvalSource->lookable;
                                $pr->update([
                                    'status'    => '2'
                                ]);
                                
                                CustomHelper::sendJournal($query->approvalSource->lookable_type,$query->approvalSource->lookable_id,$query->approvalSource->lookable->account_id);
                            }
                        }
                    }else{
                        if($query->passedReject()){
                            $updaterealtable = $query->approvalSource->lookable;
                            $updaterealtable->update([
                                'status'    => '4'
                            ]);
                        }
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendNotification($query->approvalSource->lookable_type,$query->approvalSource->lookable_id,'Pengajuan '.$query->approvalTable->menu->fullName().' No. '.$query->approvalSource->lookable->code.' telah '.$text.' di level '.$query->approvalTable->level.'.',$query->note,$query->approvalSource->lookable->user_id);

                activity()
                    ->performedOn(new ApprovalMatrix())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit approval data.');

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

    public function approveMulti(Request $request){
        $validation = Validator::make($request->all(), [
            'tempMulti'          => 'required',
            'note_multi'         => 'required',
        ], [
            'tempMulti.required'    => 'Approval tidak boleh kosong.',
            'note_multi.required'   => 'Keterangan tidak boleh kosong.'
        ]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $arrMulti = explode(',',$request->tempMulti);

            $success = true;

            foreach($arrMulti as $row){
                $query = ApprovalMatrix::where('code',CustomHelper::decrypt($row))->where('status','1')->first();
			
                if($query) {
    
                    DB::beginTransaction();
                    try {
    
                        $query->note = $request->note_multi;

                        if($request->approve_reject_multi){
                            $query->approved = '1';
                            $query->rejected = NULL;
                            $text = 'disetujui';
                        }else{
                            $query->approved = NULL;
                            $query->rejected = '1';
                            $text = 'ditolak';
                        }

                        $query->date_process = date('Y-m-d H:i:s');
                        $query->status = '2';
                        $query->save();
                        
                        if($request->approve_reject_multi){
                            if($query->passedApprove()){
                                if($query->updateNextLevelApproval() !== ''){
                                    $am = ApprovalMatrix::where('approval_table_id',$query->updateNextLevelApproval())->where('approval_source_id',$query->approval_source_id)->where('status','0')->get();
        
                                    if($am){
                                        foreach($am as $rowdetail){
                                            $rowdetail->update([
                                                'status'    => '1'
                                            ]);
                                        }
                                    }
                                }else{
                                    $pr = $query->approvalSource->lookable;
                                    $pr->update([
                                        'status'    => '2'
                                    ]);

                                    CustomHelper::sendJournal($query->approvalSource->lookable_type,$query->approvalSource->lookable_id,$query->approvalSource->lookable->account_id);
                                }
                            }
                        }else{
                            if($query->passedReject()){
                                $updaterealtable = $query->approvalSource->lookable;
                                $updaterealtable->update([
                                    'status'    => '4'
                                ]);
                            }
                        }
    
                        CustomHelper::sendNotification($query->approvalSource->lookable_type,$query->approvalSource->lookable_id,'Pengajuan '.$query->approvalTable->menu->fullName().' No. '.$query->approvalSource->lookable->code.' telah '.$text.' di level '.$query->approvalTable->level.'.',$query->note);
    
                        activity()
                            ->performedOn(new ApprovalMatrix())
                            ->causedBy(session('bo_id'))
                            ->withProperties($query)
                            ->log('Add / edit approval data.');
    
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }

                } else {
                    $success = false;
                }
            }

            if($success){
                $response = [
                    'status'    => 200,
                    'message'   => 'Data successfully saved.',
                ];
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Data failed to save.'
                ];
            }
		}
		
		return response()->json($response);
    }
}
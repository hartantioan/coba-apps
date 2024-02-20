<?php

namespace App\Http\Controllers\Setting;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageDetail;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;

class ApprovalStageController extends Controller
{
    public function index()
    {
        $data = [
            'title'     => 'Approval Tingkat / Stage',
            'content'   => 'admin.setting.approval_stage',
            'approval'  => Approval::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'approval_id',
            'level',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ApprovalStage::count();
        
        $query_data = ApprovalStage::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('level', 'like', "%$search%")
                            ->orWhereHas('approval',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('document_text','like',"%$search%");
                            })
                            ->orWhereHas('approvalStageDetail', function($query) use($search){
                                $query->orWhereHas('user',function($query) use($search){
                                    $query->where('name','like',"%$search%");
                                });
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

        $total_filtered = ApprovalStage::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('level', 'like', "%$search%")
                            ->orWhereHas('approval',function($query) use($search){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('document_text','like',"%$search%");
                            })
                            ->orWhereHas('approvalStageDetail', function($query) use($search){
                                $query->orWhereHas('user',function($query) use($search){
                                    $query->where('name','like',"%$search%");
                                });
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
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->approval->name.' - '.$val->approval->document_text,
                    $val->level,
                    $val->min_approve,
                    $val->min_reject,
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
            'code' 				=> $request->temp ? ['required', Rule::unique('approval_stages', 'code')->ignore($request->temp)] : 'required|unique:approval_stages,code',
            'approval_id'       => 'required',
            'level'             => 'required',
            'min_approve'       => 'required',
            'min_reject'        => 'required',
            'arr_user'          => 'required|array'
        ], [
            'code.required' 	        => 'Kode tidak boleh kosong.',
            'code.unique' 	            => 'Kode telah terpakai.',
            'approval_id.required' 	    => 'Tipe Approval tidak boleh kosong.',
            'level.required'	        => 'Level tidak boleh kosong.',
            'min_approve.required'      => 'Minimal approve tidak boleh kosong.',
            'min_reject.required'       => 'Minimal reject tidak boleh kosong.',
            'arr_user.required'         => 'Data karyawan tidak boleh kosong.',
            'arr_user.array'            => 'Data karyawan harus array'
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
                    $query = ApprovalStage::find($request->temp);
                    $query->code = $request->code;
                    $query->approval_id = $request->approval_id;
                    $query->level = $request->level;
                    $query->min_approve = $request->min_approve;
                    $query->min_reject = $request->min_reject;
                    $query->status = $request->status ? $request->status : '2';
                    
                    $query->save();

                    foreach($query->approvalStageDetail as $row){
                        $row->delete();
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

            }else{

                DB::beginTransaction();
                try {
                    $query = ApprovalStage::create([
                        'code'			        => $request->code,
                        'approval_id'			=> $request->approval_id,
                        'level'                 => $request->level,
                        'status'                => $request->status ? $request->status : '2',
                        'min_approve'           => $request->min_approve,
                        'min_reject'            => $request->min_reject
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
            }
            
            if($query) {

                foreach($request->arr_user as $key => $row){
                    DB::beginTransaction();
                    try {
                        ApprovalStageDetail::create([
                            'approval_stage_id'     => $query->id,
                            'user_id'               => $row,
                        ]);
                        DB::commit();
                    }catch(\Exception $e){
                        DB::rollback();
                    }
                }

                activity()
                    ->performedOn(new ApprovalStage())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit approval stage table data.');

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

    public function rowDetail(Request $request){
        $data   = ApprovalStage::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row"><div class="col s12 mt-2"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="'.count($data->approvalStageDetail).'">Approval</th>
                            ';

        foreach($data->approvalStageDetail as $key => $row){                
            $string .= '<th class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</th>';
        }

        $string .= '</tr></thead></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $approval = ApprovalStage::find($request->id);
        
        $details = [];

        foreach($approval->approvalStageDetail as $row){
            $details[] = [
                'user_id'   => $row->user_id,
                'user_name' => $row->user->employee_no.' - '.$row->user->name.' - '.$row->user->phone.' Pos. '.$row->user->position->Level->name.' Div. '.$row->user->position->division->name,
            ];
        }

        $approval['details'] = $details;
        				
		return response()->json($approval);
    }

    public function destroy(Request $request){
        $query = ApprovalStage::find($request->id);
		
        if($query->delete()) {

            $query->approvalStageDetail()->delete();

            activity()
                ->performedOn(new ApprovalStage())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the approval stage data');

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
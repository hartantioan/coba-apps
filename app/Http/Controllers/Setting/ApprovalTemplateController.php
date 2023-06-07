<?php

namespace App\Http\Controllers\Setting;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\ApprovalTemplate;
use App\Models\ApprovalTemplateMenu;
use App\Models\ApprovalTemplateOriginator;
use App\Models\ApprovalTemplateStage;
use App\Models\ApprovalStage;
use App\Models\Approval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;

class ApprovalTemplateController extends Controller
{
    public function index()
    {
        $data = [
            'title'         => 'Approval Template',
            'content'       => 'admin.setting.approval_template',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'name',
            'is_check_nominal',
            'sign',
            'nominal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ApprovalTemplate::count();
        
        $query_data = ApprovalTemplate::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
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

        $total_filtered = ApprovalStage::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search) {
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
				
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" onclick="rowDetail('.$val->id.',this)"><i class="material-icons">add</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->name,
                    $val->is_check_nominal ? 'Ya' : 'Tidak',
                    $val->sign,
                    number_format($val->nominal,2,',','.'),
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
        if($request->is_check_nominal){
            $validation = Validator::make($request->all(), [
                'code' 				    => $request->temp ? ['required', Rule::unique('approval_templates', 'code')->ignore($request->temp)] : 'required|unique:approval_templates,code',
                'name'                  => 'required',
                'is_check_nominal'      => 'required',
                'sign'                  => 'required',
                'nominal'               => 'required',
                'arr_user'              => 'required|array',
                'arr_approval_stage'    => 'required|array',
                'arr_approval_menu'     => 'required|array',
            ], [
                'code.required' 	            => 'Kode tidak boleh kosong.',
                'code.unique' 	                => 'Kode telah terpakai.',
                'name.required' 	            => 'Nama tidak boleh kosong.',
                'is_check_nominal.required'     => 'is Check nominal tidak boleh kosong.',
                'sign.required'                 => 'Tanda matematika tidak boleh kosong.',
                'nominal.required'              => 'Nominal tidak boleh kosong.',
                'arr_user.required'             => 'Originator tidak boleh kosong.',
                'arr_user.array'                => 'Originator harus dalam bentuk array.',
                'arr_approval_stage.required'   => 'Stage Approval tidak boleh kosong.',
                'arr_approval_stage.array'      => 'Stage Approval harus dalam bentuk array.',
                'arr_approval_menu.required'    => 'Menu / form tidak boleh kosong.',
                'arr_approval_menu.array'       => 'Menu / form harus dalam bentuk array.',
            ]);
        }else{
            $validation = Validator::make($request->all(), [
                'code' 				=> $request->temp ? ['required', Rule::unique('approval_templates', 'code')->ignore($request->temp)] : 'required|unique:approval_templates,code',
                'name'                  => 'required',
                'arr_user'              => 'required|array',
                'arr_approval_stage'    => 'required|array',
                'arr_approval_menu'     => 'required|array',
            ], [
                'code.required' 	            => 'Kode tidak boleh kosong.',
                'code.unique' 	                => 'Kode telah terpakai.',
                'name.required' 	            => 'Nama tidak boleh kosong.',
                'arr_user.required'             => 'Originator tidak boleh kosong.',
                'arr_user.array'                => 'Originator harus dalam bentuk array.',
                'arr_approval_stage.required'   => 'Stage Approval tidak boleh kosong.',
                'arr_approval_stage.array'      => 'Stage Approval harus dalam bentuk array.',
                'arr_approval_menu.required'    => 'Menu / form tidak boleh kosong.',
                'arr_approval_menu.array'       => 'Menu / form harus dalam bentuk array.',
            ]);
        }
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if($request->temp){
                DB::beginTransaction();
                try {
                    $query = ApprovalTemplate::find($request->temp);
                    $query->code = $request->code;
                    $query->user_id = session('bo_id');
                    $query->name = $request->name;
                    $query->is_check_nominal = $request->is_check_nominal ? $request->is_check_nominal : NULL;
                    $query->sign = $request->is_check_nominal ? $request->sign : NULL;
                    $query->nominal = $request->is_check_nominal ? str_replace(',','.',str_replace('.','',$request->nominal)) : NULL;
                    $query->status = $request->status ? $request->status : '2';
                    
                    $query->save();

                    $query->approvalTemplateOriginator()->delete();
                    $query->approvalTemplateStage()->delete();
                    $query->approvalTemplateMenu()->delete();

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

            }else{

                DB::beginTransaction();
                try {
                    $query = ApprovalTemplate::create([
                        'code'			        => $request->code,
                        'user_id'			    => session('bo_id'),
                        'name'                  => $request->name,
                        'is_check_nominal'      => $request->is_check_nominal ? $request->is_check_nominal : NULL,
                        'sign'                  => $request->is_check_nominal ? $request->sign : NULL,
                        'nominal'               => $request->is_check_nominal ? str_replace(',','.',str_replace('.','',$request->nominal)) : NULL,
                        'status'                => $request->status ? $request->status : '2',
                    ]);
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
            }
            
            if($query) {
                DB::beginTransaction();
                try {

                    if($request->arr_user){
                        foreach($request->arr_user as $key => $row){
                            ApprovalTemplateOriginator::create([
                                'approval_template_id'      => $query->id,
                                'user_id'                   => $row,
                            ]);
                        }
                    }

                    if($request->arr_approval_stage){
                        foreach($request->arr_approval_stage as $key => $row){
                            ApprovalTemplateStage::create([
                                'approval_template_id'      => $query->id,
                                'approval_stage_id'         => $row,
                            ]);
                        }
                    }

                    if($request->arr_approval_menu){
                        foreach($request->arr_approval_menu as $key => $row){
                            ApprovalTemplateMenu::create([
                                'approval_template_id'      => $query->id,
                                'menu_id'                   => $row,
                                'table_name'                => Menu::find(intval($row))->table_name,
                            ]);
                        }
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                activity()
                    ->performedOn(new ApprovalTemplate())
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
        $data   = ApprovalTemplate::find($request->id);
        
        $string = '<div class="row"><div class="col s12 mt-2"><table style="max-width:500px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="'.count($data->approvalTemplateOriginator).'">Originator</th>
                            ';

        foreach($data->approvalTemplateOriginator as $key => $row){                
            $string .= '<th class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</th>';
        }

        $string .= '</tr></thead></table>
        <table style="max-width:700px;" class="mt-3">
            <thead>
                <tr>
                    <th class="center-align" colspan="5">Tingkat/Stage</th>
                </tr>
                <tr>
                    <th class="center-align">No</th>
                    <th class="center-align">Stage</th>
                    <th class="center-align">Min Approve</th>
                    <th class="center-align">Min Reject</th>
                    <th class="center-align">Approver</th>
                </tr>
            </thead>';
            
        foreach($data->approvalTemplateStage as $key => $row){
            $string .= '<tr>
                    <td class="center-align">'.($key + 1).'.</td>
                    <td class="center-align">'.$row->approvalStage->code.'</td>
                    <td class="center-align">'.$row->approvalStage->min_approve.'</td>
                    <td class="center-align">'.$row->approvalStage->min_reject.'</td>
                    <td class="center-align">'.$row->approvalStage->listApprover().'</td>
            </tr>';
        }

        $string .= '</table><table style="max-width:500px;" class="mt-3">
        <thead>
            <tr>
                <th class="center-align" colspan="2">Menu/Form</th>
            </tr>
        </thead>';

        foreach($data->approvalTemplateMenu as $key => $row){
            $string .= '<tr>
                    <td class="center-align">'.($key + 1).'.</td>
                    <td class="center-align">'.$row->menu->fullName().'</td>
            </tr>';
        }

        $string .= '</table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $approval = ApprovalTemplate::find($request->id);
        
        $details = [];
        $stages = [];
        $menus = [];

        foreach($approval->approvalTemplateOriginator as $row){
            $details[] = [
                'user_id'   => $row->user_id,
                'user_name' => $row->user->name.' - '.$row->user->phone.' Pos. '.$row->user->position->name.' Dep. '.$row->user->department->name,
            ];
        }

        foreach($approval->approvalTemplateStage as $row){
            $stages[] = [
                'approval_stage_id'     => $row->approval_stage_id,
                'approval_stage_code'   => $row->approvalStage->code.' - '.$row->approvalStage->approval->name,
            ];
        }

        foreach($approval->approvalTemplateMenu as $row){
            $menus[] = [
                'menu_id'     => $row->menu_id,
                'menu_name'   => $row->menu->fullName(),
            ];
        }

        $approval['details'] = $details;
        $approval['stages'] = $stages;
        $approval['menus'] = $menus;
        				
		return response()->json($approval);
    }

    public function destroy(Request $request){
        $query = ApprovalTemplate::find($request->id);
		
        if($query->delete()) {

            $query->approvalTemplateOriginator()->delete();
            $query->approvalTemplateStage()->delete();
            $query->approvalTemplateMenu()->delete();

            activity()
                ->performedOn(new ApprovalTemplate())
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
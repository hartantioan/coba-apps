<?php

namespace App\Http\Controllers\Misc;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Helpers\WaBlas;
use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageDetail;
use App\Models\ApprovalTemplate;
use App\Models\ApprovalTemplateMenu;
use App\Models\ApprovalTemplateOriginator;
use App\Models\ApprovalTemplateStage;
use App\Models\ChangeLog;
use App\Models\Company;
use App\Models\Menu;
use App\Models\MenuUser;
use App\Models\OfficialReport;
use App\Models\OfficialReportApprover;
use App\Models\OfficialReportDetail;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Date;

class OfficialReportController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }

    public function index(Request $request)
    {

        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $data = [
            'title'     => 'Berita Acara',
            'content'   => 'admin.misc.official_report',
            'company'   => Company::where('status','1')->get(),
            'place'     => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'newcode'   => $menu->document_code.date('y'),
            'menucode'  => $menu->document_code,
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'modedata'  => $menuUser->mode ? $menuUser->mode : '',
            'minDate'   => $request->get('minDate'),
            'maxDate'   => $request->get('maxDate'),
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function getCode(Request $request){
        $code = OfficialReport::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'account_id',
            'post_date',
            'incident_date',
            'plant_id',
            'source_document',
            'target_document',
            'chronology',
            'action',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = OfficialReport::where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = OfficialReport::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('source_document', 'like', "%$search%")
                            ->orWhere('target_document', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

            })
            ->where(function($query)use($request){
                if(!$request->modedata){
                    $query->where('user_id',session('bo_id'));
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = OfficialReport::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('source_document', 'like', "%$search%")
                            ->orWhere('target_document', 'like', "%$search%")
                            ->orWhere('code', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

            })
            ->where(function($query)use($request){
                if(!$request->modedata){
                    $query->where('user_id',session('bo_id'));
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
                    $val->user->name,
                    $val->company->name,
                    $val->account->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->incident_date)),
                    $val->place->code,
                    $val->source_document,
                    $val->target_document,
                    $val->chronology,
                    $val->action,
                    $val->note,
                    $val->attachments(),
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);

        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();

        $pr = OfficialReport::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($pr){
            $pdf = PrintHelper::print($pr,'Berita Acara','a4','portrait','admin.print.misc.official_report_individual',$menuUser->mode);

            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));

            $content = $pdf->download()->getOriginalContent();

            $document_po = PrintHelper::savePrint($content);     $var_link=$document_po;


            return $document_po;
        }else{
            abort(404);
        }
    }

    public static function filterArray($arr,$count){
        $newArr = array_count_values($arr);
        $id = 0;
        foreach($newArr as $key => $row){
            if($row == $count){
                $id = $key;
            }
        }
        return $id;
    }

    public function rowDetail(Request $request)
    {
        $data   = OfficialReport::where('code',CustomHelper::decrypt($request->id))->first();

        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.'</div><div class="col s12 mt-1"><table style="min-width:100%;">
            <thead>
                <tr>
                    <th class="center-align" colspan="5">Approval</th>
                </tr>
                <tr>
                    <th class="center-align">Level</th>
                    <th class="center-align">Kepada</th>
                    <th class="center-align">Status</th>
                    <th class="center-align">Catatan</th>
                    <th class="center-align">Tanggal</th>
                </tr>
            </thead><tbody>';

        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="5"><h6>'.$detail->getTemplateName().'</h6></td>
                </tr>';
                foreach($detail->approvalMatrix as $key => $row){
                    $icon = '';

                    if($row->status == '1' || $row->status == '0'){
                        $icon = '<i class="material-icons">hourglass_empty</i>';
                    }elseif($row->status == '2'){
                        if($row->approved){
                            $icon = '<i class="material-icons">thumb_up</i>';
                        }elseif($row->rejected){
                            $icon = '<i class="material-icons">thumb_down</i>';
                        }elseif($row->revised){
                            $icon = '<i class="material-icons">border_color</i>';
                        }
                    }

                    $string .= '<tr>
                        <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                        <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                        <td class="center-align">'.$icon.'<br></td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="center-align">' . ($row->date_process ? \Carbon\Carbon::parse($row->date_process)->format('d/m/Y H:i:s') : '-') . '</td>
                    </tr>';
                }
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="5">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';

        return response()->json($string);
    }

    public function create(Request $request){
        DB::beginTransaction();
        try {
            $validation = Validator::make($request->all(), [
                'code'                      => 'required',
                'code_place_id'             => 'required',
                'company_id'			    => 'required',
                'place_id'                  => 'required',
                'post_date'		            => 'required',
                'incident_date'		        => 'required',
                'account_id'                => 'required',
            ], [
                'code_place_id.required'            => 'Plant Tidak boleh kosong',
                'code.required' 	                => 'Kode tidak boleh kosong.',
                'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
                'place_id'                          => 'Plant tidak boleh kosong.',
                'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
                'incident_date.required'            => 'Tanggal kejadian tidak boleh kosong.',
                'account_id.required'               => 'Partner Bisnis tidak boleh kosong.',
            ]);

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {

                $arrFile = [];

                $lastSegment = $request->lastsegment;
                $menu = Menu::where('url', $lastSegment)->first();
                
                if($request->temp){
                    $query = OfficialReport::where('code',CustomHelper::decrypt($request->temp))->first();

                    /* $approved = false;
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
                            'message' => 'Berita acara telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    } */

                    if(in_array($query->status,['1','2','3','6'])){

                        $query->user_id = session('bo_id');
                        /* $query->company_id = $request->company_id;
                        $query->account_id = $request->account_id;
                        $query->post_date = $request->post_date;
                        $query->incident_date = $request->incident_date;
                        $query->place_id = $request->place_id;
                        $query->source_document = $request->source_document; */
                        $query->target_document = $request->target_document;
                        /* $query->chronology = $request->chronology;
                        $query->action = $request->action;
                        $query->note = $request->note;
                        $query->status = '1'; */

                        $query->save();

                        /* foreach($query->officialReportDetail as $row){
                            $row->deleteFile();
                            $row->delete();
                        }

                        $query->officialReportApprover()->delete();

                        CustomHelper::removeApproval($query->getTable(),$query->id); */
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status Berita Acara sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{

                    $newCode = OfficialReport::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

                    $query = OfficialReport::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'account_id'                => $request->account_id,
                        'post_date'                 => $request->post_date,
                        'incident_date'             => $request->incident_date,
                        'place_id'                  => $request->place_id,
                        'source_document'	        => $request->source_document,
                        'target_document'	        => $request->target_document,
                        'chronology'                => $request->chronology,
                        'action'                    => $request->action,
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);
                }
                
                if($query) {

                    if(!$request->temp){
                        if($request->file('file')){
                            foreach($request->file('file') as $key => $file)
                            {
                                $arrFile[] = $file->store('public/official_reports');
                            }
                        }
    
                        if(count($arrFile) > 0){
                            foreach($arrFile as $row){
                                OfficialReportDetail::create([
                                    'official_report_id'    => $query->id,
                                    'document'              => $row,
                                ]);
                            }
                        }
    
                        if($request->arr_user){
                            $tempArrayStage = [];
                            $countApprover = 0;
                            foreach($request->arr_user as $rowuser){
                                $approvalStage = ApprovalStage::where('code','like',"BA-%")->where('status','1')->where('level',1)->whereHas('approvalStageDetail',function($query)use($rowuser,$request){
                                    $query->where('user_id',intval($rowuser));
                                })->get();
                                if(count($approvalStage) > 0){
                                    foreach($approvalStage as $rowstage){
                                        $has = false;
                                        foreach($rowstage->approvalStageDetail as $rowstagedetail){
                                            if(!in_array($rowstagedetail->user_id,$request->arr_user)){
                                                $has = true;
                                            }
                                        }
                                        if(!$has){
                                            $tempArrayStage[] = $rowstage->id;
                                        }
                                    }
                                }
                                OfficialReportApprover::create([
                                    'official_report_id'    => $query->id,
                                    'user_id'               => $rowuser,
                                ]);
                                $countApprover++;
                            }
                            $id = self::filterArray($tempArrayStage,count($request->arr_user));
                            $approvalTemplate = NULL;
                            $passed = true;
                            if($id > 0){
                                $approvalTemplate = ApprovalTemplate::whereHas('approvalTemplateStage',function($query)use($id){
                                    $query->where('approval_stage_id',$id);
                                })->whereHas('approvalTemplateMenu',function($query){
                                    $query->where('table_name','official_reports');
                                })->where('status','1')->first();
                                if(!$approvalTemplate){
                                    $passed = false;
                                }else{
                                    $cekuser = $approvalTemplate->approvalTemplateOriginator()->where('user_id',session('bo_id'))->count();
                                    if($cekuser == 0){
                                        ApprovalTemplateOriginator::create([
                                            'approval_template_id'      => $approvalTemplate->id,
                                            'user_id'                   => session('bo_id'),
                                        ]);
                                    }
                                }
                            }else{
                                $passed = false;
                                $approvalStageKuy = ApprovalStage::create([
                                    'code'			        => 'BA-'.Str::random(10),
                                    'approval_id'			=> 1,
                                    'level'                 => 1,
                                    'status'                => '1',
                                    'min_approve'           => $countApprover,
                                    'min_reject'            => 1,
                                ]);
                                foreach($request->arr_user as $rowuser){
                                    ApprovalStageDetail::create([
                                        'approval_stage_id'     => $approvalStageKuy->id,
                                        'user_id'               => $rowuser,
                                    ]);
                                }
                                $id = $approvalStageKuy->id;
                            }
                            if(!$passed){
                                $codename = 'BA-'.Str::random(10);
                                $approvalTemplate = ApprovalTemplate::create([
                                    'code'              => $codename,
                                    'user_id'           => session('bo_id'),
                                    'name'              => $codename,
                                    'nominal_final'     => 0,
                                    'status'            => '1',
                                ]);
                                ApprovalTemplateOriginator::create([
                                    'approval_template_id'      => $approvalTemplate->id,
                                    'user_id'                   => session('bo_id'),
                                ]);
                                ApprovalTemplateStage::create([
                                    'approval_template_id'      => $approvalTemplate->id,
                                    'approval_stage_id'         => $id,
                                ]);
                                ApprovalTemplateMenu::create([
                                    'approval_template_id'      => $approvalTemplate->id,
                                    'menu_id'                   => $menu->id,
                                    'table_name'                => $menu->table_name,
                                ]);
                            }
    
                            $source = ApprovalSource::create([
                                'code'			=> strtoupper(uniqid()),
                                'user_id'		=> session('bo_id'),
                                'date_request'	=> date('Y-m-d H:i:s'),
                                'lookable_type'	=> $query->getTable(),
                                'lookable_id'	=> $query->id,
                                'note'			=> 'Approval Berita Acara '.$query->code,
                            ]);
    
                            foreach($approvalTemplate->approvalTemplateStage as $rowTemplateStage){
                                foreach($rowTemplateStage->approvalStage->approvalStageDetail as $rowStageDetail){
                                    ApprovalMatrix::create([
                                        'code'							=> strtoupper(Str::random(30)),
                                        'approval_template_stage_id'	=> $rowTemplateStage->id,
                                        'approval_source_id'			=> $source->id,
                                        'user_id'						=> $rowStageDetail->user_id,
                                        'date_request'					=> date('Y-m-d H:i:s'),
                                        'status'						=> '1'
                                    ]);
                                    if($rowStageDetail->user->phone == '085729547103'){
                                        WaBlas::kirim_wa('085729547103','Dokumen '.$source->lookable->code.' menunggu persetujuan anda. Silahkan klik link : '.env('APP_URL').'/admin/approval');
                                        WaBlas::kirim_wa('081330074432','Dokumen '.$source->lookable->code.' menunggu persetujuan anda. Silahkan klik link : '.env('APP_URL').'/admin/approval');
                                    }
                                }
                            }
                        }
    
                        CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Berita Acara No. '.$query->code,$query->note,session('bo_id'));
    
                        activity()
                            ->performedOn(new OfficialReport())
                            ->causedBy(session('bo_id'))
                            ->withProperties($query)
                            ->log('Add / edit official report.');
                    }else{
                        activity()
                            ->performedOn(new OfficialReport())
                            ->causedBy(session('bo_id'))
                            ->withProperties($query)
                            ->log('Update official report.');
                    }

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
            DB::commit();
        }catch(\Exception $e){
            info($e->getMessage());
            DB::rollback();
        }

		return response()->json($response);
    }

    public function show(Request $request){
        $or = OfficialReport::where('code',CustomHelper::decrypt($request->id))->first();
        $or['account_name'] = $or->account->employee_no.' - '.$or->account->name;
        $or['code_place_id'] = substr($or->code,7,2);

        $approver = [];

        foreach($or->officialReportApprover as $row){
            $approver[] = [
                'id'    => $row->user_id,
                'text'  => $row->user->employee_no.' - '.$row->user->name.' Pos. '.($row->user->position()->exists() ? $row->user->position->name : 'N/A')
            ];
        }

        $or['approver'] = $approver;
			
		return response()->json($or);
    }

    public function approval(Request $request,$id){

        $pr = OfficialReport::where('code',CustomHelper::decrypt($id))->first();

        if($pr){
            $data = [
                'title'     => 'Berita Acara',
                'data'      => $pr
            ];

            return view('admin.approval.official_report', $data);
        }else{
            abort(404);
        }
    }

    public function destroy(Request $request){
        $query = OfficialReport::where('code',CustomHelper::decrypt($request->id))->first();

        if($query->status == '1'){
            if($query->delete()) {

                $query->update([
                    'delete_id'     => session('bo_id'),
                    'delete_note'   => $request->msg,
                ]);

                foreach($query->officialReportDetail as $row){
                    $row->deleteFile();
                    $row->delete();
                }
    
                $query->officialReportApprover()->delete();
    
                CustomHelper::removeApproval($query->getTable(),$query->id);
    
                activity()
                    ->performedOn(new OfficialReport())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Delete the berita acara official report');
    
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
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }
}

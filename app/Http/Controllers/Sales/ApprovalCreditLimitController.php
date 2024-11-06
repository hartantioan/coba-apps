<?php

namespace App\Http\Controllers\Sales;


use App\Exports\ExporApprovalCreditLimitTransactionPage;
use App\Http\Controllers\Controller;
use App\Models\ApprovalCreditLimit;
use App\Models\Company;
use App\Helpers\TreeHelper;
use App\Models\MarketingOrder;
use App\Models\CancelDocument;
use App\Models\MarketingOrderDownPayment;
use App\Models\Place;
use App\Models\TaxSeries;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Currency;
use App\Models\MarketingOrderDownPaymentDetail;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\User;
use App\Models\Tax;
use App\Models\Menu;
use App\Models\UsedData;
use App\Models\MenuUser;
class ApprovalCreditLimitController extends Controller
{
    protected $dataplaces, $dataplacecode;

    public function __construct(){
        $user = User::find(Session::get('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }

    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
       
        $menu = Menu::where('url', $lastSegment)->first();
        $data = [
            'title'         => 'Approval Credit Limit',
            'content'       => 'admin.sales.approval_credit_limit',
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'menucode'      => $menu->document_code
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = ApprovalCreditLimit::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getTaxSeries(Request $request){
        return response()->json(TaxSeries::getTaxCode($request->company_id,$request->date,$request->prefix_tax));
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'account_id',
            'post_date',
            'note',
            'current_credit_limit',
            'new_credit_limit',
            'grandtotal',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = ApprovalCreditLimit::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = ApprovalCreditLimit::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
                
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = ApprovalCreditLimit::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
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
                    $val->note,
                    number_format($val->current_credit_limit,2,',','.'),
                    number_format($val->new_credit_limit,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    $val->status(),
                    (
                        ($val->status == 3 && is_null($val->done_id)) ? 'SYSTEM' :
                        (
                            ($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name :
                            (
                                ($val->status != 3 && !is_null($val->void_id) && !is_null($val->void_date)) ? $val->voidUser->name :
                                (
                                    ($val->status != 3 && is_null($val->void_id) && !is_null($val->void_date)) ? 'SYSTEM' :
                                    (
                                        ($val->status != 3 && is_null($val->void_id) && is_null($val->void_date)) ? 'SYSTEM' : 'SYSTEM'
                                    )
                                )
                            )
                        )
                    ),
                    $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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

    public function create(Request $request){
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'code_place_id'             => 'required',
            'account_id' 				=> 'required',
            'company_id'                => 'required',
            'post_date'                 => 'required',
            'current_credit_limit'      => 'required',
            'new_credit_limit'          => 'required',
            'grandtotal'                => 'required',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
			'account_id.required' 				=> 'Mitra Bisnis tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'post_date.required'                => 'Tgl post tidak boleh kosong.',
            'current_credit_limit.required'     => 'Limit Kredit Sekarang tidak boleh kosong.',
            'new_credit_limit.required'         => 'Limit Kredit Baru tidak boleh kosong.',
            'grandtotal.required'               => 'Grandtotal tidak boleh kosong.',
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
                    $query = ApprovalCreditLimit::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Approval Credit Limit telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if(in_array($query->status,['1','6'])){

                        if($request->has('file')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('file')->store('public/approval_credit_limits');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->document = $document;
                        $query->post_date = $request->post_date;
                        $query->note = $request->note;
                        $query->current_credit_limit = str_replace(',','.',str_replace('.','',$request->current_credit_limit));
                        $query->new_credit_limit = str_replace(',','.',str_replace('.','',$request->new_credit_limit));
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->status = '1';

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
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=ApprovalCreditLimit::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);

                    $query = ApprovalCreditLimit::create([
                        'code'			            => $newCode,
                        'user_id' => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'document'                  => $request->file('file') ? $request->file('file')->store('public/approval_credit_limits') : NULL,
                        'post_date'                 => $request->post_date,
                        'note'                      => $request->note,
                        'current_credit_limit'      => str_replace(',','.',str_replace('.','',$request->current_credit_limit)),
                        'new_credit_limit'          => str_replace(',','.',str_replace('.','',$request->new_credit_limit)),
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'status'                    => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                if($query->grandtotal > 0){
                    CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                }else{
                    ApprovalCreditLimit::find($query->id)->update([
                        'status'    => '3'
                    ]);
                    $query->account->update([
                        'limit_credit'  => $query->new_credit_limit,
                    ]);
                }
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Approval Credit Limit No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new ApprovalCreditLimit())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit approval credit limit.');

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

    public function rowDetail(Request $request)
    {
        $data   = ApprovalCreditLimit::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div>
                        <div class="col s12 mt-1"><table style="min-width:100%;">
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

        $string .= '</tbody></table></div>
            ';
        $string.= '<div class="col s12 mt-2" style="font-weight:bold;">List Pengguna Dokumen :</div><ol class="col s12">';
        if($data->used()->exists()){
            $string.= '<li>'.$data->used->user->name.' - Tanggal Dipakai: '.$data->used->created_at.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol><div class="col s12 mt-2" style="font-weight:bold;color:red;"> Jika ingin dihapus hubungi tim EDP dan info kode dokumen yang terpakai atau user yang memakai bisa re-login ke dalam aplikasi untuk membuka lock dokumen.</div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $mod = ApprovalCreditLimit::where('code',CustomHelper::decrypt($id))->first();
                
        if($mod){
            $data = [
                'title'     => 'Print Approval Credit Limit',
                'data'      => $mod
            ];

            return view('admin.approval.approval_credit_limit', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $modp = ApprovalCreditLimit::where('code',CustomHelper::decrypt($request->id))->first();
        $modp['code_place_id'] = substr($modp->code,7,2);
        $modp['account_name'] = $modp->account->employee_no.' - '.$modp->account->name;
        $modp['current_credit_limit'] = CustomHelper::formatConditionalQty($modp->current_credit_limit);
        $modp['new_credit_limit'] = CustomHelper::formatConditionalQty($modp->new_credit_limit);
        $modp['grandtotal'] = CustomHelper::formatConditionalQty($modp->grandtotal);
        $modp['brand_name'] = $modp->account->brand()->exists() ? $modp->account->brand->name : '-';

		return response()->json($modp);
    }

    public function destroy(Request $request){
        $query = ApprovalCreditLimit::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Dokumen telah diapprove, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if(in_array($query->status,['2','3','4','5'])){
            return response()->json([
                'status'  => 500,
                'message' => 'Dokumen sudah diupdate, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new ApprovalCreditLimit())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the Approval Credit Limit data');

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

    public function voidStatus(Request $request){
        $query = ApprovalCreditLimit::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            if(!CustomHelper::checkLockAcc($query->post_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                ]);
            }

            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }else{

                if(in_array($query->status,['2','3'])){
                    $query->account->update([
                        'limit_credit'  => $query->current_credit_limit,
                    ]);
                }

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s'),
                ]);

                activity()
                    ->performedOn(new ApprovalCreditLimit())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the  data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Approval Credit Limit No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval($query->getTable(),$query->id);

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

    public function exportFromTransactionPage(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $account = $request->account ? $request->account : '';
        $company = $request->company ? $request->company : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
      
		return Excel::download(new ExporApprovalCreditLimitTransactionPage($search,$status,$account,$company,$end_date,$start_date), 'approval_credit_limit'.uniqid().'.xlsx');
    }
}
<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use App\Models\ChecklistDocumentList;
use App\Models\Currency;
use App\Models\GoodReceipt;
use App\Models\Menu;
use App\Models\CloseBill;
use App\Models\GoodReturnPO;
use App\Models\JournalDetail;
use App\Models\LandedCost;
use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\GoodScale;
use App\Models\GoodIssue;
use App\Models\InventoryTransferOut;
use App\Models\MaterialRequest;
use App\Models\Tax;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\FundRequest;
use App\Models\FundRequestDetail;
use App\Models\User;
use App\Helpers\TreeHelper;
use App\Models\Place;
use App\Models\Department;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Exports\ExportFundRequest;
use App\Exports\ExportOutstandingFundRequest;
use App\Models\Company;
use App\Models\Division;
use App\Models\GoodIssueRequest;
use App\Models\Line;
use App\Models\Machine;
use App\Models\MenuUser;
use App\Exports\ExportFundRequestTransactionPage;
use App\Models\UsedData;
class FundRequestController extends Controller
{
    protected $dataplaces, $datauser, $dataplacecode, $menu;

    public function __construct(){
        $user = User::find(session('bo_id'));
        $this->datauser = $user;
        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->menu = Menu::where('url', 'fund_request')->first();
    }

    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Permohonan Dana',
            'content'       => 'admin.finance.fund_request',
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

   public function getCode(Request $request){
        UsedData::where('user_id', session('bo_id'))->delete();
        $code = FundRequest::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'account_id',
            'type',
            'post_date',
            'required_date',
            'currency_id',
            'currency_rate',
            'note',
            'payment_type',
            'document_no',
            'document_date',
            'tax_no',
            'tax_cut_no',
            'cut_date',
            'spk_no',
            'invoice_no',
            'is_reimburse',
            'name_account',
            'no_account',
            'bank_account',
            'total',
            'tax',
            'wtax',
            'grandtotal'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = FundRequest::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = FundRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->document){
                    $query->where('document_status', $request->document);
                }
                if($request->status_document == 1 &&$request->status_document){
                    $query->where('balance_status',$request->status_document);
                }
                if(!$request->modedata){
                    $query->where('user_id',session('bo_id'));
                }
            })
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = FundRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('post_date', '>=', $request->start_date)
                        ->whereDate('post_date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('post_date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('post_date','<=', $request->finish_date);
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
                }

                if($request->document){
                    $query->where('document_status', $request->document);
                }
                if($request->status_document == 1 &&$request->status_document){
                    $query->where('balance_status',$request->status_document);
                }
                if(!$request->modedata){
                    $query->where('user_id',session('bo_id'));
                }
            })
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
           
            foreach($query_data as $val) {
                $dis = '';
                // if($val->isOpenPeriod()){

                //     $dis = 'style="cursor: default;
                //     pointer-events: none;
                //     color: #9f9f9f !important;
                //     background-color: #dfdfdf !important;
                //     box-shadow: none;"';
                   
                // }
                
                $totalReceivable = $val->totalReceivable();
                $totalReceivableUsed = $val->totalReceivableUsedPaid();
                $totalReceivableBalance = $totalReceivable - $totalReceivableUsed;
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name ?? '',
                    $val->account->name ?? '',
                    $val->type(),
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->required_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    $val->paymentType(),
                    $val->document_no,
                    $val->document_date ? date('d/m/Y',strtotime($val->document_date)) : '',
                    $val->tax_no,
                    $val->tax_cut_no,
                    $val->cut_date ? date('d/m/Y',strtotime($val->cut_date)) : '',
                    $val->spk_no,
                    $val->invoice_no,
                    $val->isReimburse(),
                    $val->name_account,
                    $val->no_account,
                    $val->bank_account,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($totalReceivableUsed,2,',','.'),
                    number_format($totalReceivableBalance,2,',','.'),
                    number_format($val->totalPaymentRequest(),2,',','.'),
                    number_format($val->totalOutgoingPayment(),2,',','.'),
                    $val->attachments(),
                    '
                        <select class="browser-default" onfocus="updatePrevious(this);" onchange="updateDocumentStatus(`'.CustomHelper::encrypt($val->code).'`,this)" style="width:150px;">
                            <option value="1" '.($val->document_status == '1' ? 'selected' : '').'>MENUNGGU</option>
                            <option value="2" '.($val->document_status == '2' ? 'selected' : '').'>LENGKAP</option>
                            <option value="3" '.($val->document_status == '3' ? 'selected' : '').'>TIDAK LENGKAP</option>
                        </select>
                    ',
                    '
                        <input style="width:250px;" type="text" data-id="'.CustomHelper::encrypt($val->code).'" value="'.$val->additional_note.'" onkeyup="updateAdditionalNote(this,`1`);">
                    ',
                    '
                        <input style="width:250px;" type="text" data-id="'.CustomHelper::encrypt($val->code).'" value="'.$val->additional_note_pic.'" onkeyup="updateAdditionalNote(this,`2`);">
                    ',
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
                    $val->balanceStatus(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light red accent-2 white-text" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        
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

    public function updateAdditionalNote(Request $request){
        $type = $request->type;
        $fr = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        if($fr){
            if($type == '1'){
                $fr->update([
                    'additional_note'   => $request->val,
                ]);
            }elseif($type == '2'){
                $fr->update([
                    'additional_note_pic'   => $request->val,
                ]);
            }
            
            activity()
                    ->performedOn(new FundRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($fr)
                    ->log('Add / edit fund request additional note / pic note.');

            return response()->json([
                'status'    => 200,
                'message'   => 'Tambahan catatan berhasil disimpan.',
            ]);
        }
    }

    public function rowDetail(Request $request){
        $data   = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
            $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4">'.$x.'<div class="col s12">
                    <table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="9">Daftar Item Dokumen '.$data->code.'</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="right-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="right-align">Harga Satuan</th>
                                <th class="right-align">Subtotal</th>
                                <th class="right-align">PPN</th>
                                <th class="right-align">PPh</th>
                                <th class="right-align">Grandtotal</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Divisi</th>
                                <th class="center-align">Proyek</th>
                            </tr>
                        </thead><tbody>';
        $totalqty=0;
        $totalhargasatuan=0;
        $totalsubtotal=0;
        $totalppn=0;
        $totalpph=0;
        $totalgrandtotal=0;
        foreach($data->fundRequestDetail as $key => $row){
            $totalqty+=$row->qty;
            $totalhargasatuan+=$row->price;
            $totalsubtotal+=$row->total;
            $totalppn+=$row->tax;
            $totalpph+=$row->wtax;
            $totalgrandtotal+=$row->grandtotal;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="">'.$row->note.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->unit->code.'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->wtax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
                <td class="">'.($row->place()->exists() ? $row->place->code : '-').'</td>
                <td class="">'.($row->line()->exists() ? $row->line->code : '-').'</td>
                <td class="">'.($row->machine()->exists() ? $row->machine->name : '-').'</td>
                <td class="">'.($row->division()->exists() ? $row->division->name : '-').'</td>
                <td class="">'.($row->project()->exists() ? $row->project->name : '-').'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="2"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalqty, 3, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;"></td>   
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalhargasatuan, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalsubtotal, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalppn, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalpph, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalgrandtotal, 2, ',', '.') . '</td>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="5"></td>    
            </tr>  
        ';
        
        $string .= '</tbody>
                        <tfoot>
                            <tr>
                                <th class="right-align" colspan="8">Total</th>
                                <th class="right-align">'.number_format($data->grandtotal,2,',','.').'</th>
                                <th class="right-align" colspan="5"></th>
                            </tr>
                        </tfoot>
                        </table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
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

    public function voidStatus(Request $request){
        $query = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            // if(!CustomHelper::checkLockAcc($request->post_date)){
            //     return response()->json([
            //         'status'  => 500,
            //         'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
            //     ]);
            // }

            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada Payment Request.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                if($query->type == '1' && $query->account->type == '1'){
                    CustomHelper::removeCountLimitCredit($query->account_id,$query->grandtotal);
                }
    
                activity()
                    ->performedOn(new FundRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the fund request data');
    
                CustomHelper::sendNotification('fund_requests',$query->id,'Permohonan Dana No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('fund_requests',$query->id);

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

    public function print(Request $request){

        $validation = Validator::make($request->all(), [
            'arr_id'                => 'required',
        ], [
            'arr_id.required'       => 'Tolong pilih Item yang ingin di print terlebih dahulu.',
        ]);
        
        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            $var_link=[];
            $currentDateTime = Date::now();
            $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
            foreach($request->arr_id as $key =>$row){
                $pr = FundRequest::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Fund Request',
                        'data'      => $pr
                    ];
                    
                    $pdf = PrintHelper::print($pr,'Fund Request','a4','portrait','admin.print.finance.fund_request_individual');
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }
                    
            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();


            $document_po = PrintHelper::savePrint($result);

            $response =[
                'status'=>200,
                'message'  =>$document_po
            ];
        }
        
		
		return response()->json($response);

    }

    public function printByRange(Request $request){
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');
        if($request->type_date == 1){
            $validation = Validator::make($request->all(), [
                'range_start'                => 'required',
                'range_end'                  => 'required',
            ], [
                'range_start.required'       => 'Isi code awal yang ingin di pilih menjadi awal range',
                'range_end.required'         => 'Isi code terakhir yang menjadi akhir range',
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $total_pdf = intval($request->range_end)-intval($request->range_start);
                $temp_pdf=[];
                if($request->range_start>$request->range_end){
                    $kambing["kambing"][]="code awal lebih besar daripada code akhir";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ]; 
                }
                elseif($total_pdf>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{   
                    for ($nomor = intval($request->range_start); $nomor <= intval($request->range_end); $nomor++) {
                        $lastSegment = $request->lastsegment;
                      
                        $menu = Menu::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);
                        
                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);

                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded; 
                        $query = FundRequest::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'Fund Request','a4','portrait','admin.print.finance.fund_request_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();
                    $document_po = PrintHelper::savePrint($result);
        
                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
                    ];
                } 

            }
        }elseif($request->type_date == 2){
            $validation = Validator::make($request->all(), [
                'range_comma'                => 'required',
                
            ], [
                'range_comma.required'       => 'Isi input untuk comma',
                
            ]);
            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            }else{
                $arr = explode(',', $request->range_comma);
                
                $merged = array_unique(array_filter($arr));

                if(count($merged)>31){
                    $kambing["kambing"][]="PDF lebih dari 30 buah";
                    $response = [
                        'status' => 422,
                        'error'  => $kambing
                    ];
                }else{
                    foreach($merged as $code){
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = FundRequest::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Fund Request',
                                    'data'      => $query
                            ];
                            $pdf = PrintHelper::print($query,'Fund Request','a4','portrait','admin.print.finance.fund_request_individual');
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    
                    
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }
    
    
                    $result = $merger->merge();
    
    
                    $document_po = PrintHelper::savePrint($result);
        
                    $response =[
                        'status'=>200,
                        'message'  =>$document_po
                    ];
                }
            }
        }
        return response()->json($response);
    }

    public function printIndividual(Request $request,$id){
        $lastSegment = request()->segment(count(request()->segments())-2);
       
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        
        $pr = FundRequest::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){

            $pdf = PrintHelper::print($pr,'Fund Request','a4','portrait','admin.print.finance.fund_request_individual',$menuUser->mode);
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(495, 760, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(505, 770, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 790, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $temp_pdf[]=$content;

            #surat penjara
           /*  $pdfjail = Pdf::loadView('admin.print.finance.jail_letter', $data)->setPaper('a5', 'landscape');
            $pdfjail->render();

            $font = $pdfjail->getFontMetrics()->get_font("helvetica", "bold");
            $pdfjail->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdfjail->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $contentjail = $pdfjail->download()->getOriginalContent();

            $temp_pdf[]=$contentjail;

            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }

            $result = $merger->merge(); */
            
            $randomString = Str::random(10); 
         
            $filePath = 'public/pdf/' . $randomString . '.pdf';
            
            Storage::put($filePath, $content);
            
            $document_po = asset(Storage::url($filePath));
            $var_link=$document_po;
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		
		return Excel::download(new ExportFundRequest($post_date,$end_date,$mode), 'fund_request_'.uniqid().'.xlsx');
    }

    public function userIndex(Request $request)
    {
        $url = 'fund_request';
        $cekDate = $this->datauser->cekMinMaxPostDate($url);
        $menu = $this->menu;

        $data = [
            'title'         => 'Pengajuan Permohonan Dana - Pengguna',
            'content'       => 'admin.personal.fund_request',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'company'       => Company::where('status','1')->get(),
            'division'      => Division::where('status','1')->get(),
            'line'          => Line::where('status','1')->get(),
            'machine'       => Machine::where('status','1')->get(),
            'currency'      => Currency::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'minDate'       => $cekDate ? date('Y-m-d', strtotime('-'.$cekDate->userDate->count_backdate.' days')) : date('Y-m-d'),
            'maxDate'       => $cekDate ? date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$cekDate->userDate->count_futuredate.' days')) : date('Y-m-d'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'menu'          => $menu,
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function userDatatable(Request $request){
        $column = [
            'id',
            'code',
            'account_id',
            'type',
            'post_date',
            'required_date',
            'currency_id',
            'currency_rate',
            'note',
            'payment_type',
            'document_no',
            'document_date',
            'tax_no',
            'tax_cut_no',
            'cut_date',
            'spk_no',
            'invoice_no',
            'is_reimburse',
            'name_account',
            'no_account',
            'bank_account',
            'total',
            'tax',
            'wtax',
            'grandtotal'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = FundRequest::where('user_id',session('bo_id'))->count();
        
        $query_data = FundRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->status_document == 1 &&$request->status_document){
                    $query->where('balance_status',$request->status_document);
                }
                if($request->status){
                    $query->whereIn('status', $request->status);
                }
                
            })
            ->where('user_id',session('bo_id'))
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = FundRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }
                if($request->status_document == 1 &&$request->status_document){
                    $query->where('balance_status',$request->status_document);
                }
                if($request->status){
                    $query->whereIn('status', $request->status);
                }
            })
            ->where('user_id',session('bo_id'))
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $totalReceivable = $val->totalReceivable();
                $totalReceivableUsed = $val->totalReceivableUsedPaid();
                $totalReceivableBalance = $totalReceivable - $totalReceivableUsed;
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->account->name,
                    $val->type(),
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->required_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    $val->paymentType(),
                    $val->document_no,
                    $val->document_date ? date('d/m/Y',strtotime($val->document_date)) : '',
                    $val->tax_no,
                    $val->tax_cut_no,
                    $val->cut_date ? date('d/m/Y',strtotime($val->cut_date)) : '',
                    $val->spk_no,
                    $val->invoice_no,
                    $val->isReimburse(),
                    $val->name_account,
                    $val->no_account,
                    $val->bank_account,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($totalReceivableUsed,2,',','.'),
                    number_format($totalReceivableBalance,2,',','.'),
                    $val->attachments(),
                    $val->documentStatus(),
                    $val->additional_note,
                    $val->additional_note_pic,
                    $val->status(),
                    $val->balanceStatus(),
                    '
                        
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light blue accent-2 white-text" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light yellow accent-2 white-text" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light red accent-2 white-text" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
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

    public function getAccountInfo(Request $request){
        $data = User::find($request->id);

        $banks = [];

        if($data){
            foreach($data->userBank()->orderByDesc('is_default')->get() as $row){
                $banks[] = [
                    'id'        => $row->id,
                    'bank'      => $row->bank,
                    'name'      => $row->name,
                    'no'        => $row->no,
                ];
            }
        }

        $data['banks'] = $banks;

        return response()->json($data);
    }

    public function userCreate(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'code_place_id'             => 'required',
            /* 'code'			            => $request->temp ? ['required', Rule::unique('fund_requests', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:fund_requests,code',
             */'account_id'                => 'required',
            'company_id'                => 'required',
            'type'                      => 'required',
            'is_reimburse'              => 'required',
			'post_date' 				=> 'required',
			'required_date'		        => 'required',
            'note'		                => 'required',
            'payment_type'		        => 'required',
            'currency_id'		        => 'required',
            'currency_rate'		        => 'required',
            'document_status'		    => 'required',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_unit'                  => 'required|array',
            'arr_price'                 => 'required|array',
            'arr_total'                 => 'required|array',
            'arr_place'                 => 'required|array',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'account_id.required'               => 'Target Partner Bisnis tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong',
            'type.required'                     => 'Tipe tidak boleh kosong',
            'is_reimburse.required'             => 'Reimburse tidak boleh kosong',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'required_date.required' 			=> 'Tanggal request pembayaran tidak boleh kosong.',
			'note.required'				        => 'Keterangan tidak boleh kosong',
            'payment_type.required'				=> 'Tipe pembayaran tidak boleh kosong',
            'currency_id.required'				=> 'Mata uang tidak boleh kosong',
            'currency_rate.required'			=> 'Konversi tidak boleh kosong',
            'document_status.required'			=> 'Status dokumen tidak boleh kosong',
            'arr_item.required'                 => 'Item tidak boleh kosong',
            'arr_item.array'                    => 'Item harus dalam bentuk array.',
            'arr_qty.required'                  => 'Qty tidak boleh kosong.',
            'arr_qty.array'                     => 'Qty harus dalam bentuk array.',
            'arr_unit.required'                 => 'Satuan tidak boleh kosong.',
            'arr_unit.array'                    => 'Satuan harus dalam bentuk array.',
            'arr_price.required'                => 'Harga tidak boleh kosong.',
            'arr_price.array'                   => 'Harga harus dalam bentuk array.',
            'arr_total.required'                => 'Harga total tidak boleh kosong.',
            'arr_total.array'                   => 'Harga total harus dalam bentuk array.',
            'arr_place.required'                => 'Dist. Biaya Plant tidak boleh kosong.',
            'arr_place.array'                   => 'Dist. Biaya Plant total harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $bp = User::find($request->account_id);
            $passedLimit = true;
            $balance = 0;
            if($bp){
                if($request->type == '1' && $bp->type == '1'){
                    if($request->temp){
                        $cek = FundRequest::where('code',CustomHelper::decrypt($request->temp))->first();
                        $balance = $bp->limit_credit - $bp->count_limit_credit + $cek->grandtotal;
                    }else{
                        $balance = $bp->limit_credit - $bp->count_limit_credit;
                    }
                    if(str_replace(',','.',str_replace('.','',$request->grandtotal)) > $balance){
                        $passedLimit = false;
                    }
                }
            }
            if(!$passedLimit){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf saldo limit BS & Pinjaman anda adalah '.number_format($balance,2,',','.'),
                ]);
            }

            if($request->type == '2'){
                if(!$request->document_status || $request->document_status == '2'){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Tipe Permohonan PINJAMAN harus memilih Status Dokumen TIDAK LENGKAP',
                    ]);
                }
            }
                    
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = FundRequest::where('code',CustomHelper::decrypt($request->temp))->first();

                    if($query->hasChildDocument()){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Permohonan Dana telah digunakan di dokumen lain, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    $bp->update([
                        'count_limit_credit'    => $bp->count_limit_credit - $query->grandtotal,
                    ]);
                    // if(!CustomHelper::checkLockAcc($query->post_date)){
                    //     return response()->json([
                    //         'status'  => 500,
                    //         'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                    //     ]);
                    // }
                    if(in_array($query->status,['1','2','6'])){
                        if($request->has('file')) {

                            if($query->document){
                                $arrFile = explode(',',$query->document);
                                foreach($arrFile as $row){
                                    if(Storage::exists($row)){
                                        Storage::delete($row);
                                    }
                                }
                            }

                            $arrFile = [];

                            foreach($request->file('file') as $key => $file)
                            {
                                $arrFile[] = $file->store('public/fund_requests');
                            }

                            $document = implode(',',$arrFile);
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->type = $request->type;
                        $query->post_date = $request->post_date;
                        $query->required_date = $request->required_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->note = $request->note;
                        $query->payment_type = $request->payment_type;
                        $query->document_no = $request->document_no;
                        $query->document_date = $request->document_date;
                        $query->tax_no = $request->tax_no;
                        $query->tax_cut_no = $request->tax_cut_no;
                        $query->cut_date = $request->cut_date;
                        $query->spk_no = $request->spk_no;
                        $query->invoice_no = $request->invoice_no;
                        $query->is_reimburse = $request->is_reimburse;
                        $query->name_account = $request->name_account;
                        $query->no_account = $request->no_account;
                        $query->bank_account = $request->bank_account;
                        $query->document = $document;
                        $query->total = str_replace(',','.',str_replace('.','',$request->total));
                        $query->tax = str_replace(',','.',str_replace('.','',$request->tax));
                        $query->wtax = str_replace(',','.',str_replace('.','',$request->wtax));
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->document_status = $request->document_status;
                        $query->status = '1';
                        
                        $query->save();

                        foreach($query->fundRequestDetail as $row){
                            $row->delete();
                        }

                        $query->checklistDocumentList()->delete();

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status purchase request sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=FundRequest::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    $fileUpload = '';

                    if($request->file('file')){
                        $arrFile = [];
                        foreach($request->file('file') as $key => $file)
                        {
                            $arrFile[] = $file->store('public/fund_requests');
                        }
                        $fileUpload = implode(',',$arrFile);
                    }
                    $query = FundRequest::create([
                        'code'			=> $newCode,
                        'user_id'		=> session('bo_id'),
                        'account_id'    => $request->account_id,
                        'company_id'    => $request->company_id,
                        'type'          => $request->type,
                        'post_date'     => $request->post_date,
                        'required_date' => $request->required_date,
                        'currency_id'   => $request->currency_id,
                        'currency_rate' => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'note'          => $request->note,
                        'payment_type'  => $request->payment_type,
                        'document_no'   => $request->document_no,
                        'document_date' => $request->document_date,
                        'tax_no'        => $request->tax_no,
                        'tax_cut_no'    => $request->tax_cut_no,
                        'cut_date'      => $request->cut_date,
                        'spk_no'        => $request->spk_no,
                        'invoice_no'    => $request->invoice_no,
                        'is_reimburse'  => $request->is_reimburse,
                        'name_account'  => $request->name_account,
                        'no_account'    => $request->no_account,
                        'bank_account'  => $request->bank_account,
                        'document'      => $fileUpload ? $fileUpload : NULL,
                        'total'         => str_replace(',','.',str_replace('.','',$request->total)),
                        'tax'           => str_replace(',','.',str_replace('.','',$request->tax)),
                        'wtax'          => str_replace(',','.',str_replace('.','',$request->wtax)),
                        'grandtotal'    => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'document_status'   => $request->document_status,
                        'status'        => '1',
                        /* 'balance_status'=> $request->document_status == '2' ? '1' : NULL, */
                    ]);

                    DB::commit();
                
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    foreach($request->arr_item as $key => $row){
                        FundRequestDetail::create([
                            'fund_request_id'       => $query->id,
                            'note'                  => $row,
                            'qty'                   => str_replace(',','.',str_replace('.','',$request->arr_qty[$key])),
                            'unit_id'               => $request->arr_unit[$key],
                            'price'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                            'tax_id'                => $request->arr_tax_id[$key],
                            'percent_tax'           => $request->arr_percent_tax[$key],
                            'is_include_tax'        => $request->arr_is_include_tax[$key],
                            'wtax_id'               => $request->arr_wtax_id[$key],
                            'percent_wtax'          => $request->arr_percent_wtax[$key],
                            'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                            'tax'                   => str_replace(',','.',str_replace('.','',$request->arr_tax[$key])),
                            'wtax'                  => str_replace(',','.',str_replace('.','',$request->arr_wtax[$key])),
                            'grandtotal'            => str_replace(',','.',str_replace('.','',$request->arr_grandtotal[$key])),
                            'place_id'              => $request->arr_place[$key] ?? NULL,
                            'line_id'               => $request->arr_line[$key] ?? NULL,
                            'machine_id'            => $request->arr_machine[$key] ?? NULL,
                            'division_id'           => $request->arr_division[$key] ?? NULL,
                            'project_id'            => $request->arr_project[$key] ?? NULL,
                        ]);
                    }

                    if($request->arr_checklist_box){
                        foreach($request->arr_checklist_box as $key => $row){
                            ChecklistDocumentList::create([
                                'checklist_document_id'         => $row,
                                'lookable_type'                 => $query->getTable(),
                                'lookable_id'                   => $query->id,
                                'value'                         => '1',
                                'note'                          => $request->arr_checklist_note[$key],
                            ]);
                        }
                    }
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                if($query->type == '1' && $query->account->type == '1'){
                    CustomHelper::addCountLimitCredit($request->account_id,$query->grandtotal);
                }

                CustomHelper::sendApproval('fund_requests',$query->id,$query->note);
                CustomHelper::sendNotification('fund_requests',$query->id,'Pengajuan Permohonan Dana No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new FundRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit fund request.');

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

    public function userFinish(Request $request){
        
        DB::beginTransaction();
        try {
            $query = FundRequest::where('code',CustomHelper::decrypt($request->code))->first();
            if($query->status == '2'){
                /* if($query->document_status == '3'){ */
                    $query->status = '3';
                    $query->save();
    
                    $response = [
                        'status'    => 200,
                        'message'   => 'Data successfully updated.',
                    ];

                    CustomHelper::sendNotification('fund_requests',$query->id,'Status Permohonan Dana No. '.$query->code.' dinyatakan SELESAI','Status dokumen anda telah dinyatakan selesai.',session('bo_id'));
    
                    DB::commit();
                /* }else{
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Status dokumen harus TIDAK LENGKAP, anda tidak bisa melakukan perubahan.'
                    ]);
                } */
            }else{
                return response()->json([
                    'status'  => 500,
                    'message' => 'Status permohonan dana harus PROSES, anda tidak bisa melakukan perubahan.'
                ]);
            }
        }catch(\Exception $e){
            DB::rollback();
        }

		return response()->json($response);
    }

    public function userRowDetail(Request $request){
        $data   = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $menu = $this->menu;
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">
                    <table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="9">Daftar Item Dokumen '.$data->code.'</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="right-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="right-align">Harga Satuan</th>
                                <th class="right-align">Subtotal</th>
                                <th class="right-align">PPN</th>
                                <th class="right-align">PPh</th>
                                <th class="right-align">Grandtotal</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Line</th>
                                <th class="center-align">Mesin</th>
                                <th class="center-align">Divisi</th>
                                <th class="center-align">Proyek</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->fundRequestDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="">'.$row->note.'</td>
                <td class="center-align">'.CustomHelper::formatConditionalQty($row->qty).'</td>
                <td class="center-align">'.$row->unit->code.'</td>
                <td class="right-align">'.$data->currency->code.'.'.number_format($row->price,2,',','.').'</td>
                <td class="right-align">'.$data->currency->code.'.'.number_format($row->total,2,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->wtax,2,',','.').'</td>
                <td class="right-align">'.$data->currency->code.'.'.number_format($row->grandtotal,2,',','.').'</td>
                <td class="">'.($row->place()->exists() ? $row->place->code : '-').'</td>
                <td class="">'.($row->line()->exists() ? $row->line->code : '-').'</td>
                <td class="">'.($row->machine()->exists() ? $row->machine->name : '-').'</td>
                <td class="">'.($row->division()->exists() ? $row->division->name : '-').'</td>
                <td class="">'.($row->project()->exists() ? $row->project->name : '-').'</td>
            </tr>';
        }
        
        $string .= '</tbody>
                        <tfoot>
                            <tr>
                                <th class="right-align" colspan="8">Total</th>
                                <th class="right-align">'.number_format($data->grandtotal,2,',','.').'</th>
                            </tr>
                        </tfoot>
                        </table></div><div class="col s12 mt-2"><h6>Daftar Lampiran</h6>';

        foreach($menu->checklistDocument as $row){
            $rowceklist = $row->checkDocument($data->getTable(),$data->id);
            $string .= '<label style="margin: 0 5px 0 0;">
            <input class="validate" required="" type="checkbox" value="{{ $row->id }}" '.($rowceklist ? 'checked' : '').'>
            <span>'.$row->title.' ('.$row->type().')'.'</span>
            '.($rowceklist ? $rowceklist->note : '').'
            </label>';
        }

        $string .= '</div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:800px;">
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

    public function userShow(Request $request){
        $fr = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $fr['code_place_id'] = substr($fr->code,7,2);
        $fr['limit_credit'] = number_format(floatval($fr->account->limit_credit - $fr->account->count_limit_credit + $fr->grandtotal),2,',','.');
        $fr['limit_credit_raw'] = floatval($fr->account->limit_credit - $fr->account->count_limit_credit + $fr->grandtotal);
        $fr['account_name'] = $fr->account->name;
        $fr['currency_rate'] = number_format($fr->currency_rate,2,',','.');
        $fr['total'] = number_format($fr->total,2,',','.');
        $fr['tax'] = number_format($fr->tax,2,',','.');
        $fr['wtax'] = number_format($fr->wtax,2,',','.');
        $fr['grandtotal'] = number_format($fr->grandtotal,2,',','.');

        $arr = [];
        $arrChecklist = [];

        foreach($fr->checklistDocumentList as $row){
            $arrChecklist[] = [
                'id'    => $row->checklist_document_id,
                'note'  => $row->note ? $row->note : '',
            ];
        }

        foreach($fr->fundRequestDetail as $row){
            $arr[] = [
                'item'              => $row->note,
                'qty'               => CustomHelper::formatConditionalQty($row->qty),
                'unit_id'           => $row->unit_id,
                'unit_name'         => $row->unit->code.' - '.$row->unit->name,
                'price'             => number_format($row->price,2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'percent_tax'       => $row->percent_tax,
                'percent_wtax'      => $row->percent_wtax,
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                'format_grandtotal' => number_format($row->grandtotal,2,',','.'),
                'tax_id'            => $row->tax_id,
                'wtax_id'           => $row->wtax_id,
                'is_include_tax'    => $row->is_include_tax,
                'place_id'          => $row->place_id,
                'line_id'           => $row->line_id,
                'machine_id'        => $row->machine_id,
                'division_id'       => $row->division_id,
                'project_id'        => $row->project_id ?? '',
                'project_name'      => $row->project()->exists() ? $row->project->name : '',
            ];
        }

        $fr['details'] = $arr;
        $fr['checklist'] = $arrChecklist;
        				
		return response()->json($fr);
    }

    public function userDestroy(Request $request){
        $query = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Jurnal / dokumen sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);
            
            $query->fundRequestDetail()->delete();
            CustomHelper::removeApproval('fund_requests',$query->id);

            if($query->type == '1' && $query->account->type == '1'){
                CustomHelper::removeCountLimitCredit($query->account_id,$query->grandtotal);
            }

            activity()
                ->performedOn(new FundRequest())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the fund request data');

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

    public function approval(Request $request,$id){
        
        $pr = FundRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Permohonan Dana',
                'data'      => $pr
            ];

            return view('admin.approval.fund_request', $data);
        }else{
            abort(404);
        }
    }

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $fr = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query->post_date))],
                  ],
                'url'   =>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($query->code),
                "title" =>$query->code,
            ];
        $data_go_chart[]=$fr;
        
        
        

        if($query) {
           
            
           
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_frs',$query->id);
            $array1 = $result[0];
            $array2 = $result[1];
            $data_go_chart = $array1;
            $data_link = $array2; 

            function unique_key($array,$keyname){

                $new_array = array();
                foreach($array as $key=>$value){
                
                    if(!isset($new_array[$value[$keyname]])){
                    $new_array[$value[$keyname]] = $value;
                    }
                
                }
                $new_array = array_values($new_array);
                return $new_array;
            }

           
            $data_go_chart = unique_key($data_go_chart,'name');
            $data_link=unique_key($data_link,'string_link');

            $response = [
                'status'  => 200,
                'message' => $data_go_chart,
                'link'    => $data_link
            ];
            
        
        } else {
            $data_good_receipt = [];
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function updateDocumentStatus(Request $request){
        $data = FundRequest::where('code',CustomHelper::decrypt($request->code))->first();
        if($data){
            if(!$data->hasChildDocument()){
                if($data->status == '2'){
                    $data->update([
                        'document_status'   => $request->status ? $request->status : NULL,
                    ]);
    
                    CustomHelper::sendNotification('fund_requests',$data->id,'Status Dokumen Permohonan Dana No. '.$data->code.' telah diupdate','Status dokumen anda telah dinyatakan '.$data->documentStatus().'.',session('bo_id'));
    
                    $response = [
                        'status'  => 200,
                        'message' => 'Status berhasil dirubah.',
                        'value'   => $data->document_status,
                    ];
                }else{
                    $response = [
                        'status'  => 422,
                        'message' => 'Maaf, status dokumen bukan PROSES, jadi tidak bisa dirubah ya.',
                        'value'   => $data->document_status,
                    ];
                }
            }else{
                $response = [
                    'status'  => 422,
                    'message' => 'Maaf, data sudah terpakai di dokumen lainnya.',
                    'value'   => $data->document_status,
                ];
            }
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Maaf, data tidak ditemukan.',
                'value'   => '',
            ];
        }

        return response()->json($response);
    }

    public function getOutstanding(Request $request){
       
		
		return Excel::download(new ExportOutstandingFundRequest(), 'outstanding_fund_request_'.uniqid().'.xlsx');
    }

    public function done(Request $request){
        $query_done = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new FundRequest())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the FundRequest data');
    
                $response = [
                    'status'  => 200,
                    'message' => 'Data updated successfully.'
                ];
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Data tidak bisa diselesaikan karena status bukan MENUNGGU / PROSES.'
                ];
            }

            return response()->json($response);
        }
    }

    public function exportFromTransactionPage(Request $request){
        $search = $request->search? $request->search : '';
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $status = $request->status ? $request->status : '';
        $status_dokumen = $request->status_dokumen? $request->status_dokumen : '';
        $document = $request->document ? $request->document : '';
		$modedata = $request->modedata ? $request->modedata : '';
		return Excel::download(new ExportFundRequestTransactionPage($search,$document,$post_date,$end_date,$status,$modedata,$status_dokumen), 'fund_request_'.uniqid().'.xlsx');
    }
}
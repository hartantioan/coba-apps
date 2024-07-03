<?php

namespace App\Http\Controllers\Personal;
use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use App\Models\CloseBill;
use App\Models\CloseBillDetail;
use App\Models\CostDistribution;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\LandedCost;
use App\Models\InventoryTransferOut;
use App\Models\GoodScale;
use App\Models\GoodIssue;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\MaterialRequest;
use App\Models\PurchaseRequest;
use App\Models\Menu;
use App\Models\PersonalCloseBillCost;
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
use App\Exports\ExportPersonalCloseBill;
use App\Models\Currency;
use App\Models\FundRequest;
use App\Models\OutgoingPayment;
use App\Models\PersonalCloseBill;
use App\Models\PersonalCloseBillDetail;
use App\Models\Tax;
use Faker\Provider\ar_EG\Person;

class PersonalCloseBillController extends Controller
{
    protected $dataplaces, $datauser, $dataplacecode, $menu;

    public function __construct(){
        $user = User::find(session('bo_id'));
        $this->datauser = $user;
        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->menu = Menu::where('url', 'close_bill_personal')->first();
    }

    public function index(Request $request)
    {
        $menu = $this->menu;
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Tutup BS Personal',
            'content'       => 'admin.finance.personal_close_bill',
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = PersonalCloseBill::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'post_date',
            'currency_id',
            'currency_rate',
            'note',
            'document_no',
            'document_date',
            'tax_no',
            'tax_cut_no',
            'cut_date',
            'spk_no',
            'invoice_no',
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

        $total_data = PersonalCloseBill::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = PersonalCloseBill::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
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

        $total_filtered = PersonalCloseBill::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
                }

                if($request->status){
                    $query->whereIn('status', $request->status);
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
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account()->exists() ? $val->account->name : '-',
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    $val->document_no,
                    $val->document_date ? date('d/m/Y',strtotime($val->document_date)) : '',
                    $val->tax_no,
                    $val->tax_cut_no,
                    $val->cut_date ? date('d/m/Y',strtotime($val->cut_date)) : '',
                    $val->spk_no,
                    $val->invoice_no,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light blue accent-2 white-text" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`,`'.$val->code.'`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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

    public function rowDetail(Request $request){
        $data   = PersonalCloseBill::where('code',CustomHelper::decrypt($request->id))->first();
        $menu = $this->menu;
        
        $totalDocument = 0;
        $string = $data->code.'<div class="row pt-1 pb-1 lighten-4"><div class="col s8"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Daftar Permohonan Dana Terpakai</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">No.Dokumen</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Nominal</th>
                            </tr>
                        </thead><tbody>';

        foreach($data->personalCloseBillDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->fundRequest->code.'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
            </tr>';
            $totalDocument += $row->nominal;
        }

        $string .= '</tbody>
                        <tfoot>
                            <tr>
                                <th class="right-align" colspan="3">Total</th>
                                <th class="right-align">'.number_format($totalDocument,2,',','.').'</th>
                            </tr>
                        </tfoot>
                        </table></div>';

        $string .= '<div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="14">Daftar Biaya</th>
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
        
        foreach($data->personalCloseBillCost as $key => $row){
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
        
        $string .= '</tbody>
                        <tfoot>
                            <tr>
                                <th class="right-align" colspan="8">Total</th>
                                <th class="right-align">'.number_format($data->grandtotal,2,',','.').'</th>
                            </tr>
                        </tfoot>
                        </table></div>';

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
        $string.= '<div class="col s12 mt-2" style="font-weight:bold;">List Pengguna Dokumen :</div><ol>';
        if($data->used()->exists()){
            $string.= '<li>'.$data->used->lookable->user->name.' - Tanggal Dipakai: '.$data->used->lookable->post_date.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol></div>';
		
        return response()->json($string);
    }

    public function voidStatus(Request $request){
        $query = PersonalCloseBill::where('code',CustomHelper::decrypt($request->id))->first();
        
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
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada dokumen lainnya.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new PersonalCloseBill())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the personal close bill data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Tutup BS Personal No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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
                    
                    $pdf = PrintHelper::print($pr,'Tutup BS Personal','a4','portrait','admin.print.personal.close_bill_individual');
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
                            $pdf = PrintHelper::print($query,'Tutup BS Personal','a4','portrait','admin.print.personal.close_bill_individual');
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
                            $pdf = PrintHelper::print($query,'Tutup BS Personal','a4','portrait','admin.print.personal.close_bill_individual');
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
        
        $pr = PersonalCloseBill::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){

            $pdf = PrintHelper::print($pr,'Tutup BS Personal','a4','portrait','admin.print.personal.close_bill_individual');
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $temp_pdf[]=$content;
            
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

    public function userPrintIndividual(Request $request,$id){
        
        $pr = PersonalCloseBill::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){

            $pdf = PrintHelper::print($pr,'Tutup BS Personal','a4','Portrait','admin.print.personal.close_bill_individual');
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(495, 740, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(505, 750, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 760, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            $temp_pdf[]=$content;
            
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
		
		return Excel::download(new ExportPersonalCloseBill($post_date,$end_date,$mode), 'personal_close_bill_'.uniqid().'.xlsx');
    }

    public function userIndex(Request $request)
    {
        $url = 'personal_close_bill';
        $cekDate = $this->datauser->cekMinMaxPostDate($url);
        $menu = $this->menu;

        $data = [
            'title'         => 'Tutup BS Personal',
            'content'       => 'admin.personal.close_bill',
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
            'post_date',
            'currency_id',
            'currency_rate',
            'note',
            'document_no',
            'document_date',
            'tax_no',
            'tax_cut_no',
            'cut_date',
            'spk_no',
            'invoice_no',
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

        $total_data = PersonalCloseBill::where('user_id',session('bo_id'))->count();
        
        $query_data = PersonalCloseBill::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
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

        $total_filtered = PersonalCloseBill::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('account',function($query) use ($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            });
                    });
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
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->account()->exists() ? $val->account->name : '-',
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    $val->document_no,
                    $val->document_date ? date('d/m/Y',strtotime($val->document_date)) : '',
                    $val->tax_no,
                    $val->tax_cut_no,
                    $val->cut_date ? date('d/m/Y',strtotime($val->cut_date)) : '',
                    $val->spk_no,
                    $val->invoice_no,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light blue accent-2 white-text" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`,`'.$val->code.'`)"><i class="material-icons dp48">local_printshop</i></button>
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

    public function getData(Request $request){
        $details = [];
        $data = FundRequest::where('type','1')->whereIn('status',['2','3'])->where('document_status','3')->whereHas('hasPaymentRequestDetail',function($query){
            $query->whereHas('paymentRequest',function($query){
                $query->whereHas('outgoingPayment');
            });
        })->whereDoesntHave('used')->where('account_id',$request->account_id)->get();

        foreach($data as $row){
            $totalReceivable = $row->totalReceivable();
            $totalReceivableUsed = $row->totalReceivableUsedPaid();
            $balance = $totalReceivable - $totalReceivableUsed;
            if(!$row->used()->exists() && $balance > 0){
                $details[] = [
                    'id'                    => $row->id,
                    'type'                  => $row->getTable(),
                    'code'                  => $row->code,
                    'post_date'             => date('d/m/Y',strtotime($row->post_date)),
                    'total'                 => number_format($totalReceivable,2,',','.'),
                    'used'                  => number_format($totalReceivableUsed,2,',','.'),
                    'balance'               => number_format($balance,2,',','.'),
                    'note'                  => $row->note,
                ];
            }
        }

        return response()->json([
            'status'    => 200,
            'data'      => $details,
            'message'   => 'Data tidak ditemukan.' 
        ]);
    }

    public function getAccountData(Request $request){
        $details = [];

        if($request->arr_type){
            foreach($request->arr_type as $key => $row){
                if($row == 'fund_requests'){
                    $fr = FundRequest::whereDoesntHave('used')->where('id',$request->arr_id[$key])->first();
                    if($fr){
                        CustomHelper::sendUsedData($fr->getTable(),$fr->id,'Form Tutupan BS Personal');
                        $totalReceivable = $fr->totalReceivable();
                        $totalReceivableUsed = $fr->totalReceivableUsedPaid();
                        $balance = $totalReceivable - $totalReceivableUsed;
                        if($balance > 0){
                            $arrDetail = [];
                            foreach($fr->fundRequestDetail as $rowdetail){
                                $arrDetail[] = [
                                    'id'                => $fr->id,
                                    'note'              => $rowdetail->note,
                                    'qty'               => CustomHelper::formatConditionalQty($rowdetail->qty),
                                    'unit_id'           => $rowdetail->unit_id,
                                    'unit_name'         => $rowdetail->unit->code.' - '.$rowdetail->unit->name,
                                    'price'             => number_format($rowdetail->price,2,',','.'),
                                    'tax_id'            => $rowdetail->tax_id ?? '0',
                                    'percent_tax'       => $rowdetail->percent_tax,
                                    'is_include_tax'    => $rowdetail->is_include_tax,
                                    'wtax_id'           => $rowdetail->wtax_id ?? '0',
                                    'percent_wtax'      => $rowdetail->percent_wtax,
                                    'total'             => number_format($rowdetail->total,2,',','.'),
                                    'tax'               => number_format($rowdetail->tax,2,',','.'),
                                    'wtax'              => number_format($rowdetail->wtax,2,',','.'),
                                    'grandtotal'        => number_format($rowdetail->grandtotal,2,',','.'),
                                    'place_id'          => $rowdetail->place_id ?? '',
                                    'line_id'           => $rowdetail->line_id ?? '',
                                    'machine_id'        => $rowdetail->machine_id ?? '',
                                    'division_id'       => $rowdetail->division_id ?? '',
                                    'project_id'        => $rowdetail->project_id ?? '',
                                    'project_name'      => $rowdetail->project()->exists() ? $rowdetail->project->code.' - '.$rowdetail->project->name : '',
                                ];
                            }
                            $details[] = [
                                'type'          => $fr->getTable(),
                                'id'            => $fr->id,
                                'code'          => $fr->code,
                                'post_date'     => date('d/m/Y',strtotime($fr->post_date)),
                                'total'         => number_format($totalReceivable,2,',','.'),
                                'used'          => number_format($totalReceivableUsed,2,',','.'),
                                'balance'       => number_format($balance,2,',','.'),
                                'note'          => $fr->note,
                                'detail'        => $arrDetail,
                                'currency_rate' => number_format($fr->currency_rate,2,',','.'),
                                'currency_id'   => $fr->currency_id,
                                'document_no'   => $fr->document_no ?? '',
                                'tax_no'        => $fr->tax_no ?? '',
                                'tax_cut_no'    => $fr->tax_cut_no ?? '',
                                'cut_date'      => $fr->cut_date ?? '',
                                'spk_no'        => $fr->spk_no ?? '',
                                'document_date' => $fr->document_date ?? '',
                                'invoice_no'    => $fr->invoice_no ?? '',
                            ];
                        }
                    }
                }
            }
        }

        $data['details'] = $details;

        return response()->json($data);
    }

    public function userCreate(Request $request){
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'code_place_id'             => 'required',
            'account_id'                => 'required',
            'company_id'                => 'required',
			'post_date' 				=> 'required',
            'note'		                => 'required',
            'currency_id'		        => 'required',
            'currency_rate'		        => 'required',
            'arr_nominal'               => 'required|array',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_unit'                  => 'required|array',
            'arr_price'                 => 'required|array',
            'arr_total'                 => 'required|array',
            'arr_place'                 => 'required|array',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong',
            'account_id.required'               => 'Partner Bisnis tidak boleh kosong',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'note.required'				        => 'Keterangan tidak boleh kosong',
            'currency_id.required'				=> 'Mata uang tidak boleh kosong',
            'currency_rate.required'			=> 'Konversi tidak boleh kosong',
            'arr_nominal.required'              => 'Nominal tidak boleh kosong',
            'arr_nominal.array'                 => 'Nominal harus dalam bentuk array.',
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

            $balance = 0;
            
            foreach($request->arr_nominal as $key => $row){
                $balance += str_replace(',','.',str_replace('.','',$row));
            }

            foreach($request->arr_grandtotal as $key => $row){
                $balance -= str_replace(',','.',str_replace('.','',$row));
            }

            if($balance < 0 || $balance > 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Mohon maaf data tidak bisa disimpan karena ada selisih dokumen terpakai dan biaya sebesar '.number_format($balance,2,',','.'),
                ]);
            }
                    
			if($request->temp){
                $query = PersonalCloseBill::where('code',CustomHelper::decrypt($request->temp))->first();

                if(in_array($query->status,['1','2','6'])){
                    if($request->has('file')) {
                        if($query->document){
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                        }
                        $document = $request->file('file')->store('public/personal_close_bills');
                    } else {
                        $document = $query->document;
                    }
                    
                    $query->code = $request->code;
                    $query->user_id = session('bo_id');
                    $query->company_id = $request->company_id;
                    $query->account_id = $request->account_id;
                    $query->post_date = $request->post_date;
                    $query->currency_id = $request->currency_id;
                    $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                    $query->note = $request->note;
                    $query->document_no = $request->document_no;
                    $query->document_date = $request->document_date;
                    $query->tax_no = $request->tax_no;
                    $query->tax_cut_no = $request->tax_cut_no;
                    $query->cut_date = $request->cut_date;
                    $query->spk_no = $request->spk_no;
                    $query->invoice_no = $request->invoice_no;
                    $query->document = $document;
                    $query->total = str_replace(',','.',str_replace('.','',$request->total));
                    $query->tax = str_replace(',','.',str_replace('.','',$request->tax));
                    $query->wtax = str_replace(',','.',str_replace('.','',$request->wtax));
                    $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                    $query->status = '1';
                    
                    $query->save();

                    $query->personalCloseBillCost()->delete();
                    $query->personalCloseBillDetail()->delete();
                }else{
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Status tutup bs personal sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                    ]);
                }
			}else{
                
                $menu = Menu::where('url', 'close_bill_personal')->first();
                $newCode=PersonalCloseBill::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                
                $query = PersonalCloseBill::create([
                    'code'			    => $newCode,
                    'user_id'		    => session('bo_id'),
                    'company_id'        => $request->company_id,
                    'account_id'        => $request->account_id,
                    'post_date'         => $request->post_date,
                    'currency_id'       => $request->currency_id,
                    'currency_rate'     => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                    'note'              => $request->note,
                    'document_no'       => $request->document_no,
                    'document_date'     => $request->document_date,
                    'tax_no'            => $request->tax_no,
                    'tax_cut_no'        => $request->tax_cut_no,
                    'cut_date'          => $request->cut_date,
                    'spk_no'            => $request->spk_no,
                    'invoice_no'        => $request->invoice_no,
                    'document'          => $request->file('file') ? $request->file('file')->store('public/personal_close_bills') : NULL,
                    'total'             => str_replace(',','.',str_replace('.','',$request->total)),
                    'tax'               => str_replace(',','.',str_replace('.','',$request->tax)),
                    'wtax'              => str_replace(',','.',str_replace('.','',$request->wtax)),
                    'grandtotal'        => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                    'status'            => '1',
                ]);

			}
			
			if($query) {
                foreach($request->arr_nominal as $key => $row){
                    PersonalCloseBillDetail::create([
                        'personal_close_bill_id'        => $query->id,
                        'fund_request_id'               => $request->arr_id[$key],
                        'nominal'                       => str_replace(',','.',str_replace('.','',$row)),
                        'note'                          => $request->arr_note_source[$key],
                    ]);
                }

                foreach($request->arr_item as $key => $row){
                    PersonalCloseBillCost::create([
                        'personal_close_bill_id'=> $query->id,
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

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Tutupan BS Personal No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new PersonalCloseBill())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit close bill personal.');

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
        $data   = PersonalCloseBill::where('code',CustomHelper::decrypt($request->id))->first();
        $menu = $this->menu;
        
        $totalDocument = 0;
        $string = $data->code.'<div class="row pt-1 pb-1 lighten-4"><div class="col s8"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Daftar Permohonan Dana Terpakai</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">No.Dokumen</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Nominal</th>
                            </tr>
                        </thead><tbody>';

        foreach($data->personalCloseBillDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->fundRequest->code.'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
            </tr>';
            $totalDocument += $row->nominal;
        }

        $string .= '</tbody>
                        <tfoot>
                            <tr>
                                <th class="right-align" colspan="3">Total</th>
                                <th class="right-align">'.number_format($totalDocument,2,',','.').'</th>
                            </tr>
                        </tfoot>
                        </table></div>';

        $string .= '<div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="14">Daftar Biaya</th>
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
        
        foreach($data->personalCloseBillCost as $key => $row){
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
        $string.= '<div class="col s12 mt-2" style="font-weight:bold;">List Pengguna Dokumen :</div><ol>';
        if($data->used()->exists()){
            $string.= '<li>'.$data->used->lookable->user->name.' - Tanggal Dipakai: '.$data->used->lookable->post_date.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol></div>';
		
        return response()->json($string);
    }

    public function userShow(Request $request){
        $fr = PersonalCloseBill::where('code',CustomHelper::decrypt($request->id))->first();
        $fr['code_place_id'] = substr($fr->code,7,2);
        $fr['currency_rate'] = number_format($fr->currency_rate,2,',','.');
        $fr['account_name'] = $fr->account->employee_no.' - '.$fr->account->name;
        $fr['total'] = number_format($fr->total,2,',','.');
        $fr['tax'] = number_format($fr->tax,2,',','.');
        $fr['wtax'] = number_format($fr->wtax,2,',','.');
        $fr['source'] = number_format($fr->totalSource(),2,',','.');
        $fr['balance'] = number_format($fr->totalBalance(),2,',','.');
        $fr['grandtotal'] = number_format($fr->grandtotal,2,',','.');

        $arr = [];
        $arrDoc = [];
        $account_id = 0;

        foreach($fr->personalCloseBillDetail as $row){
            $totalReceivable = $row->fundRequest->totalReceivable();
            $totalReceivableUsed = $row->fundRequest->totalReceivableUsedPaid();
            $balance = $totalReceivable - $totalReceivableUsed;
            $account_id = $row->fundRequest->account_id;
            $arrDoc[] = [
                'account_id'    => $row->fundRequest->account_id,
                'type'          => $row->fundRequest->getTable(),
                'id'            => $row->fundRequest->id,
                'code'          => $row->fundRequest->code,
                'post_date'     => date('d/m/Y',strtotime($row->fundRequest->post_date)),
                'total'         => number_format($totalReceivable,2,',','.'),
                'used'          => number_format($totalReceivableUsed,2,',','.'),
                'balance'       => number_format($balance + $row->nominal,2,',','.'),
                'nominal'       => number_format($row->nominal,2,',','.'),
                'note'          => $row->note,
            ];
        }

        foreach($fr->personalCloseBillCost as $row){
            $arr[] = [
                'account_id'        => $account_id,
                'item'              => $row->note,
                'qty'               => CustomHelper::formatConditionalQty($row->qty),
                'unit_id'           => $row->unit_id,
                'unit_name'         => $row->unit->code.' - '.$row->unit->name,
                'price'             => number_format($row->price,2,',','.'),
                'percent_tax'       => $row->percent_tax,
                'percent_wtax'      => $row->percent_wtax,
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
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
        $fr['docs'] = $arrDoc;
        				
		return response()->json($fr);
    }

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->type,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function userDestroy(Request $request){
        $query = PersonalCloseBill::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Dokumen sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }

        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);
            
            $query->personalCloseBillCost()->delete();
            $query->personalCloseBillDetail()->delete();
            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new PersonalCloseBill())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the personal close bill data');

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
        
        $pr = PersonalCloseBill::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Tutup BS Personal',
                'data'      => $pr
            ];

            return view('admin.approval.personal_close_bill', $data);
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
        $query = PersonalCloseBill::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $pcb = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query->post_date))],
                  ],
                'url'   =>request()->root()."/admin/finance/close_bill_personal?code=".CustomHelper::encrypt($query->code),
                "title" =>$query->code,
            ];
        $data_go_chart[]=$pcb;
        
        

        if($query) {
            
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_pcb',$query->id);
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
}
<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\ApprovalMatrix;
use App\Models\ApprovalSource;
use App\Models\Currency;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\JournalDetail;
use App\Models\LandedCost;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
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
use App\Models\Place;
use App\Models\Department;
use App\Helpers\CustomHelper;
use App\Exports\ExportFundRequest;

class FundRequestController extends Controller
{
    protected $dataplaces, $datauser;

    public function __construct(){
        $user = User::find(session('bo_id'));
        $this->datauser = $user;
        $this->dataplaces = $user ? $user->userPlaceArray() : [];
    }

    public function index(Request $request)
    {
        $data = [
            'title'     => 'Permohonan Dana',
            'content'   => 'admin.finance.fund_request',
            'code'      => $request->code ? CustomHelper::decrypt($request->code) : '',
            'tax'       => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'      => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = FundRequest::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'user_id',
            'code',
            'place_id',
            'department_id',
            'account_id',
            'type',
            'post_date',
            'required_date',
            'currency_id',
            'currency_rate',
            'note',
            'termin_note',
            'payment_type',
            'name_account',
            'no_account',
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

        $total_data = FundRequest::whereIn('place_id',$this->dataplaces)->count();
        
        $query_data = FundRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('required_date', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%");
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
                    $query->where('status', $request->status);
                }

                if($request->document){
                    $query->where('document_status', $request->document);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
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
                            ->orWhere('note', 'like', "%$search%");
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
                    $query->where('status', $request->status);
                }

                if($request->document){
                    $query->where('document_status', $request->document);
                }
            })
            ->whereIn('place_id',$this->dataplaces)
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $totalReceivable = $val->totalReceivable();
                $totalReceivableUsed = $val->totalReceivableUsed();
                $totalReceivableBalance = $totalReceivable - $totalReceivableUsed;
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->user->name,
                    $val->code,
                    $val->place->name.' - '.$val->place->company->name,
                    $val->department->name,
                    $val->account->name,
                    $val->type(),
                    date('d M Y',strtotime($val->post_date)),
                    date('d M Y',strtotime($val->required_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    $val->termin_note,
                    $val->paymentType(),
                    $val->name_account,
                    $val->no_account,
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($totalReceivable,2,',','.'),
                    number_format($totalReceivableUsed,2,',','.'),
                    number_format($totalReceivableBalance,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    '
                        <select class="browser-default" onchange="updateDocumentStatus(`'.CustomHelper::encrypt($val->code).'`,this)" style="width:150px;">
                            <option value="1" '.($val->document_status == '1' ? 'selected' : '').'>MENUNGGU</option>
                            <option value="2" '.($val->document_status == '2' ? 'selected' : '').'>LENGKAP</option>
                            <option value="3" '.($val->document_status == '3' ? 'selected' : '').'>TIDAK LENGKAP</option>
                        </select>
                    ',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light red accent-2 white-text" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
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
        $data   = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">
                    <table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="right-align">Harga Satuan</th>
                                <th class="right-align">Subtotal</th>
                                <th class="right-align">PPN</th>
                                <th class="right-align">PPh</th>
                                <th class="right-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->fundRequestDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                <td class="center-align">'.$row->unit->code.'</td>
                <td class="right-align">'.number_format($row->price,2,',','.').'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->wtax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->grandtotal,2,',','.').'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="4"><h6>'.$detail->getTemplateName().'</h6></td>
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
                    </tr>';
                }
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function voidStatus(Request $request){
        $query = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
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
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.finance.fund_request_individual', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                    $content = $pdf->download()->getOriginalContent();
                    $temp_pdf[]=$content;
                }
                    
            }
            $merger = new Merger();
            foreach ($temp_pdf as $pdfContent) {
                $merger->addRaw($pdfContent);
            }


            $result = $merger->merge();


            Storage::put('public/pdf/bubla.pdf',$result);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
            $var_link=$document_po;

            $response =[
                'status'=>200,
                'message'  =>$var_link
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
                        $query = FundRequest::where('Code', 'LIKE', '%'.$nomor)->first();
                        if($query){
                            $data = [
                                'title'     => 'Fund Request',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.fund_request_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }


                    $result = $merger->merge();


                    Storage::put('public/pdf/bubla.pdf',$result);
                    $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
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
                        $query = FundRequest::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $data = [
                                'title'     => 'Fund Request',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.fund_request_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
                            $content = $pdf->download()->getOriginalContent();
                            $temp_pdf[]=$content;
                           
                        }
                    }
                    
                    
                    $merger = new Merger();
                    foreach ($temp_pdf as $pdfContent) {
                        $merger->addRaw($pdfContent);
                    }
    
    
                    $result = $merger->merge();
    
    
                    Storage::put('public/pdf/bubla.pdf',$result);
                    $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
                    $var_link=$document_po;
        
                    $response =[
                        'status'=>200,
                        'message'  =>$var_link
                    ];
                }
            }
        }
        return response()->json($response);
    }

    public function printIndividual(Request $request,$id){
        
        $pr = FundRequest::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){

            $data = [
                'title'     => 'Fund Request',
                'data'      => $pr
            ];

            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.finance.fund_request_individual', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
            Storage::put('public/pdf/bubla.pdf',$content);
            $document_po = asset(Storage::url('public/pdf/bubla.pdf'));
    
    
            return $document_po;
        }else{
            abort(404);
        }
    }

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
		
		return Excel::download(new ExportFundRequest($post_date,$end_date), 'fund_request_'.uniqid().'.xlsx');
    }

    public function userIndex(Request $request)
    {
        $url = 'fund_request';
        $cekDate = $this->datauser->cekMinMaxPostDate($url);

        $data = [
            'title'         => 'Pengajuan Permohonan Dana - Pengguna',
            'content'       => 'admin.personal.fund_request',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'department'    => Department::where('status','1')->get(),
            'currency'      => Currency::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'minDate'       => $cekDate ? date('Y-m-d', strtotime('-'.$cekDate->userDate->count_backdate.' days')) : date('Y-m-d'),
            'maxDate'       => $cekDate ? date('Y-m-d', strtotime(date('Y-m-d'). ' + '.$cekDate->userDate->count_futuredate.' days')) : date('Y-m-d'),
            'newcode'       => 'FREQ-'.date('y'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function userDatatable(Request $request){
        $column = [
            'id',
            'code',
            'place_id',
            'department_id',
            'account_id',
            'type',
            'post_date',
            'required_date',
            'currency_id',
            'currency_rate',
            'note',
            'termin_note',
            'payment_type',
            'name_account',
            'no_account',
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
                            ->orWhere('note', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
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
                            ->orWhere('note', 'like', "%$search%");
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }
            })
            ->where('user_id',session('bo_id'))
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                $totalReceivable = $val->totalReceivable();
                $totalReceivableUsed = $val->totalReceivableUsed();
                $totalReceivableBalance = $totalReceivable - $totalReceivableUsed;
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->place->name.' - '.$val->place->company->name,
                    $val->department->name,
                    $val->account->name,
                    $val->type(),
                    date('d M Y',strtotime($val->post_date)),
                    date('d M Y',strtotime($val->required_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    $val->termin_note,
                    $val->paymentType(),
                    $val->name_account,
                    $val->no_account,
                    number_format($val->grandtotal,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($totalReceivable,2,',','.'),
                    number_format($totalReceivableUsed,2,',','.'),
                    number_format($totalReceivableBalance,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->documentStatus(),
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-small btn-flat waves-effect waves-light orange accent-2 white-text" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
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
                    'bank_id'   => $row->bank_id,
                    'name'      => $row->name,
                    'bank_name' => $row->bank->name,
                    'no'        => $row->no,
                ];
            }
        }

        $data['banks'] = $banks;

        return response()->json($data);
    }

    public function userCreate(Request $request){
        $validation = Validator::make($request->all(), [
            'code'			            => $request->temp ? ['required', Rule::unique('fund_requests', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|unique:fund_requests,code',
            'account_id'                => 'required',
            'type'                      => 'required',
			'post_date' 				=> 'required',
			'required_date'		        => 'required',
            'place_id'                  => 'required',
            'department_id'             => 'required',
            'note'		                => 'required',
            'payment_type'		        => 'required',
            'currency_id'		        => 'required',
            'currency_rate'		        => 'required',
            'arr_item'                  => 'required|array',
            'arr_qty'                   => 'required|array',
            'arr_unit'                  => 'required|array',
            'arr_price'                 => 'required|array',
            'arr_total'                 => 'required|array',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.unique'                       => 'Kode telah dipakai.',
            'account_id.required'               => 'Target Partner Bisnis tidak boleh kosong',
            'type.required'                     => 'Tipe tidak boleh kosong',
			'post_date.required' 				=> 'Tanggal posting tidak boleh kosong.',
			'required_date.required' 			=> 'Tanggal request pembayaran tidak boleh kosong.',
            'place_id.required'                 => 'Penempatan lokasi tidak boleh kosong.',
            'department_id.required'            => 'Departemen tidak boleh kosong.',
			'note.required'				        => 'Keterangan tidak boleh kosong',
            'payment_type.required'				=> 'Tipe pembayaran tidak boleh kosong',
            'currency_id.required'				=> 'Mata uang tidak boleh kosong',
            'currency_rate.required'			=> 'Konversi tidak boleh kosong',
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
                    $query = FundRequest::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Pengajuan dana telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){
                        if($request->has('file')) {
                            if(Storage::exists($query->document)){
                                Storage::delete($query->document);
                            }
                            $document = $request->file('file')->store('public/fund_requests');
                        } else {
                            $document = $query->document;
                        }
                        
                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->place_id = $request->place_id;
                        $query->department_id = $request->department_id;
                        $query->account_id = $request->account_id;
                        $query->type = $request->type;
                        $query->post_date = $request->post_date;
                        $query->required_date = $request->required_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->note = $request->note;
                        $query->termin_note = $request->termin_note;
                        $query->payment_type = $request->payment_type;
                        $query->name_account = $request->name_account;
                        $query->no_account = $request->no_account;
                        $query->document = $document;
                        $query->total = str_replace(',','.',str_replace('.','',$request->total));
                        $query->tax = str_replace(',','.',str_replace('.','',$request->tax));
                        $query->wtax = str_replace(',','.',str_replace('.','',$request->wtax));
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->document_status = '1';
                        $query->status = '1';
                        
                        $query->save();

                        foreach($query->fundRequestDetail as $row){
                            $row->delete();
                        }

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
                try {
                    $query = FundRequest::create([
                        'code'			=> $request->code,
                        'user_id'		=> session('bo_id'),
                        'place_id'      => $request->place_id,
                        'department_id'	=> $request->department_id,
                        'account_id'    => $request->account_id,
                        'type'          => $request->type,
                        'post_date'     => $request->post_date,
                        'required_date' => $request->required_date,
                        'currency_id'   => $request->currency_id,
                        'currency_rate' => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'note'          => $request->note,
                        'termin_note'   => $request->termin_note,
                        'payment_type'  => $request->payment_type,
                        'name_account'  => $request->name_account,
                        'no_account'    => $request->no_account,
                        'document'      => $request->file('file') ? $request->file('file')->store('public/fund_requests') : NULL,
                        'total'         => str_replace(',','.',str_replace('.','',$request->total)),
                        'tax'           => str_replace(',','.',str_replace('.','',$request->tax)),
                        'wtax'          => str_replace(',','.',str_replace('.','',$request->wtax)),
                        'grandtotal'    => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'document_status'   => '1',
                        'status'        => '1',
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    foreach($request->arr_item as $key => $row){
                        FundRequestDetail::create([
                            'fund_request_id'       => $query->id,
                            'note'                  => $row,
                            'qty'                   => $request->arr_qty[$key],
                            'unit_id'               => $request->arr_unit[$key],
                            'price'                 => str_replace(',','.',str_replace('.','',$request->arr_price[$key])),
                            'tax_id'                => $request->arr_tax_id[$key],
                            'percent_tax'           => $request->arr_percent_tax[$key],
                            'is_include_tax'        => $request->arr_is_include_tax[$key],
                            'wtax_id'               => $request->arr_wtax_id[$key],
                            'percent_wtax'          => $request->arr_percent_wtax[$key],
                            'total'                 => str_replace(',','.',str_replace('.','',$request->arr_total[$key])),
                            'tax'                   => $request->arr_tax[$key],
                            'wtax'                  => $request->arr_wtax[$key],
                            'grandtotal'            => round($request->arr_grandtotal[$key],2),
                        ]);
                    }
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
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

    public function userRowDetail(Request $request){
        $data   = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">
                    <table style="max-width:800px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Item</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Satuan</th>
                                <th class="right-align">Harga Satuan</th>
                                <th class="right-align">Subtotal</th>
                                <th class="right-align">PPN</th>
                                <th class="right-align">PPh</th>
                                <th class="right-align">Grandtotal</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->fundRequestDetail as $key => $row){
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.number_format($row->qty,3,',','.').'</td>
                <td class="center-align">'.$row->unit->code.'</td>
                <td class="right-align">'.number_format($row->price,3,',','.').'</td>
                <td class="right-align">'.number_format($row->total,3,',','.').'</td>
                <td class="right-align">'.number_format($row->tax,3,',','.').'</td>
                <td class="right-align">'.number_format($row->wtax,3,',','.').'</td>
                <td class="right-align">'.number_format($row->grandtotal,3,',','.').'</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="max-width:800px;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="4">Approval</th>
                            </tr>
                            <tr>
                                <th class="center-align">Level</th>
                                <th class="center-align">Kepada</th>
                                <th class="center-align">Status</th>
                                <th class="center-align">Catatan</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->approval() && $data->hasDetailMatrix()){
            foreach($data->approval() as $detail){
                $string .= '<tr>
                    <td class="center-align" colspan="4"><h6>'.$detail->getTemplateName().'</h6></td>
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
                    </tr>';
                }
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function userShow(Request $request){
        $fr = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $fr['account_name'] = $fr->account->name;
        $fr['currency_rate'] = number_format($fr->currency_rate,2,',','.');
        $fr['total'] = number_format($fr->total,2,',','.');
        $fr['tax'] = number_format($fr->tax,2,',','.');
        $fr['wtax'] = number_format($fr->wtax,2,',','.');
        $fr['grandtotal'] = number_format($fr->grandtotal,2,',','.');

        $arr = [];

        foreach($fr->fundRequestDetail as $row){
            $arr[] = [
                'item'              => $row->note,
                'qty'               => number_format($row->qty,3,',','.'),
                'unit_id'           => $row->unit_id,
                'unit_name'         => $row->unit->code.' - '.$row->unit->name,
                'price'             => number_format($row->price,2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'percent_tax'       => $row->percent_tax,
                'percent_wtax'      => $row->percent_wtax,
                'tax'               => $row->tax,
                'wtax'              => $row->wtax,
                'grandtotal'        => $row->grandtotal,
                'tax_id'            => $row->tax_id,
                'wtax_id'           => $row->wtax_id,
                'is_include_tax'    => $row->is_include_tax,
            ];
        }

        $fr['details'] = $arr;
        				
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
            
            $query->fundRequestDetail()->delete();
            CustomHelper::removeApproval('fund_requests',$query->id);

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
        $query = FundRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $fr = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/y',strtotime($query->post_date))],
                  ],
                'url'   =>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($query->code),
                "title" =>$query->code,
            ];
        $data_go_chart[]=$fr;
        
        $data_id_dp=[];
        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
        $data_id_pyrs=[];
        $data_id_lc=[];
        $data_id_greturns=[];
        $data_id_pr=[];
        $data_id_memo=[];
        $data_id_pyrcs=[];

        if($query) {

            //Pengambilan Main Branch beserta id terkait
            if($query->hasPaymentRequestDetail()->exists()){
                foreach($query->hasPaymentRequestDetail as $row_pyr_detail){
                    $data_pyr_tempura=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                        ],
                        "key" => $row_pyr_detail->paymentRequest->code,
                        "name" => $row_pyr_detail->paymentRequest->code,
                        'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                    ];

                    $data_go_chart[]=$data_pyr_tempura;
                    $data_link[]=[
                        'from'=>$query->code,
                        'to'=>$row_pyr_detail->paymentRequest->code,
                        'string_link'=>$query->code.$row_pyr_detail->paymentRequest->code,
                    ]; 
                    $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  

                    if($row_pyr_detail->fundRequest()){
                       
                        $data_fund_tempura=[
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                            ],
                            "key" => $row_pyr_detail->lookable->code,
                            "name" => $row_pyr_detail->lookable->code,
                            'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                        ];
                        
                        $data_go_chart[]=$data_fund_tempura;
                        $data_link[]=[
                            'from'=>$row_pyr_detail->lookable->code,
                            'to'=>$row_pyr_detail->paymentRequest->code,
                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                        ]; 
                        $data_id_frs[]= $row_pyr_detail->lookable->id;  
                            
                        
                        
                    }
                    foreach($row_pyr_detail->paymentRequest->paymentRequestDetail as $row_pyrd){
                        if($row_pyrd->purchaseDownPayment()){
                        
                            $data_downp_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyrd->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyrd->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyrd->lookable->code,
                                "name" => $row_pyrd->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyrd->lookable->code),  
                            ];

                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$row_pyrd->lookable->code,
                                'to'=>$row_pyrd->paymentRequest->code,
                                'string_link'=>$row_pyrd->lookable->code.$row_pyrd->paymentRequest->code,
                            ]; 
                            $data_id_dp[]= $row_pyrd->lookable->id;  
                                
                            
                        }  
                    }
                }
            }

            $added = true;
           
            while($added){
               
                $added=false;
                // Pengambilan foreign branch gr
                foreach($data_id_gr as $gr_id){
                    $query_gr = GoodReceipt::where('id',$gr_id)->first();
                    foreach($query_gr->goodReceiptDetail as $good_receipt_detail){
                        $po = [
                            'properties'=> [
                                ['name'=> "Tanggal: ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
                                ['name'=> "Vendor  : ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->supplier->name],
                                ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->purchaseOrderDetail->purchaseOrder->grandtotal,2,',','.')]
                            ],
                            'key'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'name'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($good_receipt_detail->purchaseOrderDetail->purchaseOrder->code),
                        ];

                        $data_go_chart[]=$po;
                        $data_link[]=[
                            'from'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code,
                            'to'=>$query_gr->code,
                            'string_link'=>$good_receipt_detail->purchaseOrderDetail->purchaseOrder->code.$query_gr->code
                        ];
                        $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 

                        if($good_receipt_detail->goodReturnPODetail()->exists()){
                            foreach($good_receipt_detail->goodReturnPODetail as $goodReturnPODetail){
                                $good_return_tempura =[
                                    "name"=> $goodReturnPODetail->goodReturnPO->code,
                                    "key" => $goodReturnPODetail->goodReturnPO->code,
                                    
                                    'properties'=> [
                                        ['name'=> "Tanggal :". $goodReturnPODetail->goodReturnPO->post_date],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt( $goodReturnPODetail->goodReturnPO->code),
                                ];
                                                    
                                $data_go_chart[] = $good_return_tempura;
                                $data_link[]=[
                                    'from'=> $query_gr->code,
                                    'to'=>$goodReturnPODetail->goodReturnPO->code,
                                    'string_link'=>$query_gr->code.$goodReturnPODetail->goodReturnPO->code,
                                ];
                                $data_id_greturns[]=  $goodReturnPODetail->goodReturnPO->id;

                            }
                             
                                
                            
                        }
                        //landed cost searching
                        if($good_receipt_detail->landedCostDetail()->exists()){
                            foreach($good_receipt_detail->landedCostDetail as $landed_cost_detail){
                                $data_lc=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$landed_cost_detail->landedCost->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($landed_cost_detail->landedCost->grandtotal,2,',','.')]
                                    ],
                                    'key'=>$landed_cost_detail->landedCost->code,
                                    'name'=>$landed_cost_detail->landedCost->code,
                                    'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($landed_cost_detail->landedCost->code),    
                                ];

                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$landed_cost_detail->landedCost->code,
                                    'string_link'=>$query_gr->code.$landed_cost_detail->landedCost->code,
                                ];
                                $data_id_lc[] = $landed_cost_detail->landedCost->id;
                                
                                
                            }
                        }
                        //invoice searching
                        if($good_receipt_detail->purchaseInvoiceDetail()->exists()){
                            foreach($good_receipt_detail->purchaseInvoiceDetail as $invoice_detail){
                                $invoice_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$invoice_detail->purchaseInvoice->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                        
                                    ],
                                    'key'=>$invoice_detail->purchaseInvoice->code,
                                    'name'=>$invoice_detail->purchaseInvoice->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($invoice_detail->purchaseInvoice->code)
                                ];

                                $data_go_chart[]=$invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_gr->code,
                                    'to'=>$invoice_detail->purchaseInvoice->code,
                                    'string_link'=>$query_gr->code.$invoice_detail->purchaseInvoice->code
                                ];
                                
                                if(!in_array($invoice_detail->purchaseInvoice->id, $data_id_invoice)){
                                    $data_id_invoice[] = $invoice_detail->purchaseInvoice->id;
                                    $added = true; 
                                }
                            }
                        }

                    }
                }




                //mencari goodreturn foreign
                foreach($data_id_greturns as $good_return_id){
                    $query_return = GoodReturnPO::where('id',$good_return_id)->first();
                    foreach($query_return->goodReturnPODetail as $good_return_detail){
                        $data_good_receipt = [
                            "name"=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                            "key" => $good_return_detail->goodReceiptDetail->goodReceipt->code,
                            "color"=>"lightblue",
                            'properties'=> [
                                ['name'=> "Tanggal :".$good_return_detail->goodReceiptDetail->goodReceipt->post_date],
                            ],
                            'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_return_detail->goodReceiptDetail->goodReceipt->code),
                        ];
                        
                        $data_good_receipt[]=$data_good_receipt;
                        $data_go_chart[]=$data_good_receipt;
                        $data_link[]=[
                            'from'=>$data_good_receipt["key"],
                            'to'=>$query_return->code,
                            'string_link'=>$data_good_receipt["key"].$query_return->code,
                        ];
                        
                        if(!in_array($good_return_detail->goodReceiptDetail->goodReceipt->id, $data_id_gr)){
                            $data_id_gr[] = $good_return_detail->goodReceiptDetail->goodReceipt->id;
                            $added = true;
                        }
                    }
                }

                // invoice insert foreign

                foreach($data_id_invoice as $invoice_id){
                    $query_invoice = PurchaseInvoice::where('id',$invoice_id)->first();
                    foreach($query_invoice->purchaseInvoiceDetail as $row){
                        if($row->purchaseOrderDetail()){
                            $row_po=$row->lookable->purchaseOrder;
                                $po =[
                                    "name"=>$row_po->code,
                                    "key" => $row_po->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po->post_date],
                                        ['name'=> "Vendor  : ".$row_po->supplier->name],
                                        ['name'=> "Nominal : Rp.:".number_format($row_po->grandtotal,2,',','.')]
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->post_date),           
                                ];

                                $data_go_chart[]=$po;
                                $data_link[]=[
                                    'from'=>$row_po->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_po->code.$query_invoice->code
                                ]; 
                                $data_id_po[]= $purchase_order_detail->purchaseOrder->id;  
                                      
                                foreach($row_po->purchaseOrderDetail as $po_detail){
                                    if($po_detail->goodReceiptDetail()->exists()){
                                        foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                            $data_good_receipt=[
                                                'properties'=> [
                                                    ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                    ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                                 ],
                                                "key" => $good_receipt_detail->goodReceipt->code,
                                                "name" => $good_receipt_detail->goodReceipt->code,
                                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                            ];
                                            
                                            $data_go_chart[]=$data_good_receipt;
                                            $data_link[]=[
                                                'from'=>$row_po->code,
                                                'to'=>$data_good_receipt["key"],
                                                'string_link'=>$row_po->code.$data_good_receipt["key"]
                                            ];
                                            $data_id_gr[]=$good_receipt_detail->goodReceipt->id;  
                                            
                                        }
                                    }
                                }
                            
                        }
                        /*  melihat apakah ada hubungan grpo tanpa po */
                        if($row->goodReceiptDetail()){
        
                            $data_good_receipt=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->goodReceipt->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->goodReceipt->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->goodReceipt->code,
                                "name" => $row->lookable->goodReceipt->code,
                                'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($row->lookable->goodReceipt->code),
                            ];

                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$data_good_receipt["key"],
                                'to'=>$query_invoice->code,
                                'string_link'=>$data_good_receipt["key"].$query_invoice->code,
                            ];
                            if(!in_array($row->lookable->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[] = $row->lookable->goodReceipt->id; 
                                $added = true;
                            } 
                        }
                        /* melihat apakah ada hubungan lc */
                        if($row->landedCostDetail()){
                            $data_lc=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->landedCost->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->landedCost->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->landedCost->code,
                                "name" => $row->lookable->landedCost->code,
                                'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->lookable->landedCost->code),
                            ];

                            $data_go_chart[]=$data_lc;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row->lookable->landedCost->code,
                                'string_link'=>$query_invoice->code.$row->lookable->landedCost->code,
                            ];
                            $data_id_lc[] = $row->lookable->landedCost->id;
                            
                        }

                        if($row->purchaseMemoDetail()->exists()){
                            foreach($row->purchaseMemoDetail as $purchase_memodetail){
                                $data_memo = [
                                    "name"=>$purchase_memodetail->purchaseMemo->code,
                                    "key" => $purchase_memodetail->purchaseMemo->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                     ],
                                    'url'=>request()->root()."/admin/purchase/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
                                ];
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$purchase_memodetail->purchaseMemo->code,
                                    'string_link'=>$query_invoice->code.$purchase_memodetail->purchaseMemo->code,
                                ];
                                $data_id_memo[]=$purchase_memodetail->purchaseMemo->id;
                                $data_go_chart[]=$data_memo;
                            }
                        }
                        
                    }
                    if($query_invoice->purchaseInvoiceDp()->exists()){
                        foreach($query_invoice->purchaseInvoiceDp as $row_pi){
                            $data_down_payment=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pi->purchaseDownPayment->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pi->purchaseDownPayment->code,
                                "name" => $row_pi->purchaseDownPayment->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
                            ];
                                $data_go_chart[]=$data_down_payment;
                                $data_link[]=[
                                    'from'=>$row_pi->purchaseDownPayment->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_pi->purchaseDownPayment->code.$query_invoice->code,
                                ];
            
                            if($row_pi->purchaseDownPayment->hasPaymentRequestDetail()->exists()){
                                foreach($row_pi->purchaseDownPayment->hasPaymentRequestDetail as $row_pyr_detail){
                                    $data_pyr_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                            ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pyr_detail->paymentRequest->code,
                                        "name" => $row_pyr_detail->paymentRequest->code,
                                        'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                                    ];
                                    $data_go_chart[]=$data_pyr_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pi->purchaseDownPayment->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                        'string_link'=>$row_pi->purchaseDownPayment->code.$row_pyr_detail->paymentRequest->code,
                                    ]; 
                                    $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                        


                                    if($row_pyr_detail->fundRequest()){
                                        $data_fund_tempura=[
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                        ];
                                       
                                        $data_go_chart[]=$data_fund_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                        ];        
                                        
                                    }
                                    if($row_pyr_detail->purchaseDownPayment()){
                                        $data_downp_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                         
                                        $data_go_chart[]=$data_downp_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                        ]; 
                                        $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                            
                                        
                                    }
                                    if($row_pyr_detail->purchaseInvoice()){
                                        $data_invoices_tempura = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                            ],
                                            "key" => $row_pyr_detail->lookable->code,
                                            "name" => $row_pyr_detail->lookable->code,
                                            'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                        ];
                                   
                                               
                                        $data_go_chart[]=$data_invoices_tempura;
                                        $data_link[]=[
                                            'from'=>$row_pyr_detail->lookable->code,
                                            'to'=>$row_pyr_detail->paymentRequest->code,
                                            'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code
                                        ];
                                        
                                        if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                            $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                            $added=true;
                                        }
                                    }

                                }
                            }
                        }
                    }
                    if($query_invoice->hasPaymentRequestDetail()->exists()){
                        foreach($query_invoice->hasPaymentRequestDetail as $row_pyr_detail){
                            $data_pyr_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->paymentRequest->code,
                                "name" => $row_pyr_detail->paymentRequest->code,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                            ];
                            
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$query_invoice->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$query_invoice->code.$row_pyr_detail->paymentRequest->code,
                            ]; 
                            $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                
                            
                            if($row_pyr_detail->fundRequest()){
                                $data_fund_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                                ];
                             
                                
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code
                                ];             
                                
                            }
                            if($row_pyr_detail->purchaseDownPayment()){
                                $data_downp_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];

                                $data_go_chart[]=$data_downp_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ]; 
                                $data_id_dp[]= $row_pyr_detail->lookable->id;  
                                    
                                
                            }
                            if($row_pyr_detail->purchaseInvoice()){
                                $data_invoices_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                                ];
                                
                                       
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                                
                                if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                    $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                    $added=true;
                                }
                            }
                        }
                    }
                }

                foreach($data_id_pyrs as $payment_request_id){
                    $query_pyr = PaymentRequest::find($payment_request_id);

                    if($query_pyr->outgoingPayment()->exists()){
                        $outgoing_payment = [
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_pyr->outgoingPayment->post_date],
                                ['name'=> "Nominal : Rp.".number_format($query_pyr->outgoingPayment->grandtotal,2,',','.')]
                            ],
                            "key" => $query_pyr->outgoingPayment->code,
                            "name" => $query_pyr->outgoingPayment->code,
                            'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyr->outgoingPayment->code),  
                        ];

                        $data_go_chart[]=$outgoing_payment;
                        $data_link[]=[
                            'from'=>$query_pyr->code,
                            'to'=>$query_pyr->outgoingPayment->code,
                            'string_link'=>$query_pyr->code.$query_pyr->outgoingPayment->code,
                        ]; 
                        
                    }
                    
                    foreach($query_pyr->paymentRequestDetail as $row_pyr_detail){
                        
                        $data_pyr_tempura=[
                            'properties'=> [
                                ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                            ],
                            "key" => $row_pyr_detail->paymentRequest->code,
                            "name" => $row_pyr_detail->paymentRequest->code,
                            'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                        ];
                        if($row_pyr_detail->fundRequest()){
                            
                            $data_fund_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code), 
                            ];
                           
                               
                                $data_go_chart[]=$data_fund_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                            
                        }
                        if($row_pyr_detail->purchaseDownPayment()){
                            $data_downp_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];       
                            
                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                            ]; 
                            
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row_pyr_detail->lookable->id;
                                $added = true; 
                               
                            }
                        }
                        if($row_pyr_detail->purchaseInvoice()){
                            $data_invoices_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];
                          
                                   
                                $data_go_chart[]=$data_invoices_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_detail->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                            
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                                $data_id_invoice[] = $row_pyr_detail->lookable->id;
                                $added=true;
                            }
                        }

                        if($row_pyr_detail->paymentRequestCross()){
                            $data_pyrc_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->lookable->code,
                                "name" => $row_pyr_detail->lookable->code,
                                'url'=>request()->root()."/admin/purchase/payment_request_cross?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                            ];
        
                            $data_go_chart[]=$data_pyrc_tempura;
                            $data_link[]=[
                                'from'=>$row_pyr_detail->lookable->code,
                                'to'=>$row_pyr_detail->paymentRequest->code,
                                'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                            ];
                            
                            if(!in_array($row_pyr_detail->lookable->id, $data_id_pyrcs)){
                                $data_id_pyrcs[] = $row_pyr_detail->lookable->id;
                            }
                        }
                    }
                    
                }
                foreach($data_id_pyrcs as $payment_request_cross_id){
                    $query_pyrc = PaymentRequestCross::find($payment_request_cross_id);
                    if($query_pyrc->paymentRequest->exists()){
                        $data_pyr_tempura = [
                            'key'   => $query_pyrc->paymentRequest->code,
                            "name"  => $query_pyrc->paymentRequest->code,
                            'properties'=> [
                                 ['name'=> "Tanggal: ".date('d/m/y',strtotime($query_pyrc->paymentRequest->post_date))],
                              ],
                            'url'   =>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($query_pyrc->paymentRequest->code),
                            "title" =>$query_pyrc->paymentRequest->code,
                        ];
                        $data_go_chart[]=$data_pyr_tempura;
                        $data_link[]=[
                            'from'=>$query_pyrc->code,
                            'to'=>$query_pyrc->paymentRequest->code,
                            'string_link'=>$query_pyrc->code.$query_pyrc->paymentRequest->code,
                        ];
                        
                        if(!in_array($query_pyrc->id, $data_id_pyrs)){
                            $data_id_pyrs[] = $query_pyrc->id;
                            $added=true;
                        }
                    }
                    if($query_pyrc->outgoingPayment()){
                        $outgoing_tempura = [
                            'properties'=> [
                                ['name'=> "Tanggal :".$query_pyrc->lookable->post_date],
                                ['name'=> "Nominal : Rp.".number_format($query_pyrc->lookable->grandtotal,2,',','.')]
                            ],
                            "key" => $query_pyrc->lookable->code,
                            "name" => $query_pyrc->lookable->code,
                            'url'=>request()->root()."/admin/purchase/payment_request_cross?code=".CustomHelper::encrypt($query_pyrc->lookable->code),  
                        ];
    
                        $data_go_chart[]=$outgoing_tempura;
                        $data_link[]=[
                            'from'=>$query_pyrc->lookable->code,
                            'to'=>$query_pyrc->paymentRequest->code,
                            'string_link'=>$query_pyrc->lookable->code.$query_pyrc->paymentRequest->code,
                        ];
                    }
                }
                foreach($data_id_dp as $downpayment_id){
                    $query_dp = PurchaseDownPayment::find($downpayment_id);
                    foreach($query_dp->purchaseDownPaymentDetail as $row){
                        if($row->purchaseOrder->exists()){
                            $po=[
                                "name"=>$row->purchaseOrder->code,
                                "key" => $row->purchaseOrder->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->purchaseOrder->post_date],
                                    ['name'=> "Vendor  : ".$row->purchaseOrder->supplier->name],
                                    ['name'=> "Nominal : Rp.:".number_format($row->purchaseOrder->grandtotal,2,',','.')],
                                ],
                                'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row->purchaseOrder->code),
                            ];
                          
                            $data_go_chart[]=$po;
                            $data_link[]=[
                                'from'=>$row->purchaseOrder->code,
                                'to'=>$query_dp->code,
                                'string_link'=>$row->purchaseOrder->code.$query_dp->code,
                            ];
                            
                            $data_id_po []=$row->purchaseOrder->id; 
                                
                            
                           
                            
                            
                            /* mendapatkan request po */
                            foreach($row->purchaseOrder->purchaseOrderDetail as $po_detail){

                                if($po_detail->purchaseRequestDetail()->exists()){
                                   
                                    $pr = [
                                        "key" => $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'name'=> $po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$po_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                           
                                         ],
                                        'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($po_detail->purchaseRequestDetail->purchaseRequest->code),
                                    ];
                                    $data_go_chart[]=$pr;
                                    $data_link[]=[
                                        'from'=>$po_detail->purchaseRequestDetail->purchaseRequest->code,
                                        'to'=>$row->purchaseOrder->code,
                                        'string_link'=>$po_detail->purchaseRequestDetail->purchaseRequest->code.$row->purchaseOrder->code
                                    ];
                                    $data_id_pr[]=$po_detail->purchaseRequestDetail->purchaseRequest->id;
                                        
                                    
                                }
                                /* mendapatkan gr po */
                                if($po_detail->goodReceiptDetail()->exists()){
                                    foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                            
                                        $data_good_receipt = [
                                            'properties'=> [
                                                ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                ['name'=> "Nominal : Rp.:".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
                                             ],
                                            "key" => $good_receipt_detail->goodReceipt->code,
                                            "name" => $good_receipt_detail->goodReceipt->code,
                                            'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),  
                                        ];
                                               
                                        $data_go_chart[]=$data_good_receipt;
                                        $data_link[]=[
                                            'from'=>$row->purchaseOrder->code,
                                            'to'=>$data_good_receipt["key"],
                                            'string_link'=>$row->purchaseOrder->code.$data_good_receipt["key"],
                                        ];
                                           
                                        
                                        if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                            $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                            $added = true;
                                        }
                    
                                    }
                                }
                            }
                             
        
                        }
                        
                    }

                    foreach($query_dp->purchaseInvoiceDp as $purchase_invoicedp){
                        
                        $invoice_tempura = [
                            "name"=>$purchase_invoicedp->purchaseInvoice->code,
                            "key" => $purchase_invoicedp->purchaseInvoice->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$purchase_invoicedp->purchaseInvoice->post_date],
                                ['name'=> "Nominal : Rp.:".number_format($purchase_invoicedp->purchaseInvoice->grandtotal,2,',','.')],
                                ],
                            'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoicedp->purchaseInvoice->code),           
                        ];
                        
                           
                        $data_go_chart[]=$invoice_tempura;
                        $data_link[]=[
                            'from'=>$query_dp->code,
                            'to'=>$purchase_invoicedp->purchaseInvoice->code,
                            'string_link'=>$query_dp->code.$purchase_invoicedp->purchaseInvoice->code,
                        ];
                        
                        if(!in_array($purchase_invoicedp->purchaseInvoice->id, $data_id_invoice)){
                            
                            $data_id_invoice[] = $purchase_invoicedp->purchaseInvoice->id;
                            $added = true; 
                        }
                    }

                    foreach($query_dp->purchaseMemoDetail as $purchase_memodetail){
                        $data_memo=[
                            "name"=>$purchase_memodetail->purchaseMemo->code,
                            "key" => $purchase_memodetail->purchaseMemo->code,
                            'properties'=> [
                                ['name'=> "Tanggal :".$purchase_memodetail->purchaseMemo->post_date],
                                ['name'=> "Nominal : Rp.:".number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                ],
                            'url'=>request()->root()."/admin/purchase/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
                        ];
                        $data_go_chart[]=$data_memo;
                        $data_link[]=[
                            'from'=>$query_dp->code,
                            'to'=>$purchase_memodetail->purchaseMemo->code,
                            'string_link'=>$query_dp->code.$purchase_memodetail->purchaseMemo->code,
                        ];
                        

                    }

                }

                foreach($data_id_memo as $memo_id){
                    $query = PurchaseMemo::find($memo_id);
                    foreach($query->purchaseMemoDetail as $row){
                        if($row->lookable_type == 'purchase_invoice_details'){
                            $data_invoices_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->purchaseInvoice->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->purchaseInvoice->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->purchaseInvoice->code,
                                "name" => $row->lookable->purchaseInvoice->code,
                                'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row->lookable->purchaseInvoice->code),
                            ];
        
                            $data_go_chart[]=$data_invoices_tempura;
                            $data_link[]=[
                                'from'=>$data_invoices_tempura["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_invoices_tempura["key"].$query->code,
                            ];
                            if(!in_array($row->lookable->purchaseInvoice->id, $data_id_invoice)){
                                $data_id_invoice[] = $row->lookable->purchaseInvoice->id;
                                $added=true;
                            }
                        }elseif($row->lookable_type == 'purchase_down_payments'){
                            $data_downp_tempura=[
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row->lookable->post_date],
                                    ['name'=> "Nominal : Rp.".number_format($row->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $row->lookable->code,
                                "name" => $row->lookable->code,
                                'url'=>request()->root()."/admin/purchase/purchase_down_payment?code=".CustomHelper::encrypt($row->lookable->code),
                            ];
        
                            $data_go_chart[]=$data_downp_tempura;
                            $data_link[]=[
                                'from'=>$data_downp_tempura["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_downp_tempura["key"].$query->code,
                            ];
                            if(!in_array($row->lookable->id, $data_id_dp)){
                                $data_id_dp[] = $row->lookable->id;
                                $added=true;
                            }
                        }
                        
                    }
                }
                
                foreach($data_id_lc as $landed_cost_id){
                    $query= LandedCost::find($landed_cost_id);
                    foreach($query->landedCostDetail as $lc_detail ){
                        if($lc_detail->goodReceiptDetail()){
                            $data_good_receipt = [
                                "key" => $lc_detail->lookable->goodReceipt->code,
                                'name'=> $lc_detail->lookable->goodReceipt->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->goodReceipt->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->goodReceipt->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/purchase/good_receipt?code=".CustomHelper::encrypt($lc_detail->lookable->goodReceipt->code),
                            ];
                            
                            $data_go_chart[]=$data_good_receipt;
                            $data_link[]=[
                                'from'=>$data_good_receipt["key"],
                                'to'=>$query->code,
                                'string_link'=>$data_good_receipt["key"].$query->code,
                            ];
                               
                            
                            if(!in_array($lc_detail->lookable->goodReceipt->id, $data_id_gr)){
                                $data_id_gr[] = $lc_detail->lookable->goodReceipt->id;
                                $added = true;
                            }

                        }
                        if($lc_detail->landedCostDetail()){
                            $lc_other = [
                                "key" => $lc_detail->lookable->landedCost->code,
                                "name" => $lc_detail->lookable->landedCost->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$lc_detail->lookable->landedCost->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($lc_detail->lookable->landedCost->grandtotal,2,',','.')],
                                 ],
                                'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($lc_detail->lookable->landedCost->code),
                            ];

                            $data_go_chart[]=$lc_other;
                            $data_link[]=[
                                'from'=>$query->code,
                                'to'=>$lc_detail->lookable->landedCost->code,
                                'string_link'=>$query->code.$lc_detail->lookable->landedCost->code,
                            ];
                            $data_id_lc[] = $lc_detail->lookable->landedCost->id;
                                              
                        }
                    }
                }

                //Pengambilan foreign branch po
                foreach($data_id_po as $po_id){
                    $query_po = PurchaseOrder::find($po_id);
                   
                    foreach($query_po->purchaseOrderDetail as $purchase_order_detail){
                       
                        if($purchase_order_detail->purchaseRequestDetail()->exists()){
                        
                            $pr_tempura=[
                                'key'   => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                "name"  => $purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                            
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$purchase_order_detail->purchaseRequestDetail->purchaseRequest->post_date],
                                   
                                ],
                                'url'   =>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($purchase_order_detail->purchaseRequestDetail->purchaseRequest->code),
                            ];
                    
                            $data_go_chart[]=$pr_tempura;
                            $data_link[]=[
                                'from'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code,
                                'to'=>$query_po->code,
                                'string_link'=>$purchase_order_detail->purchaseRequestDetail->purchaseRequest->code.$query_po->code,
                            ];
                            $data_id_pr[]=$purchase_order_detail->purchaseRequestDetail->purchaseRequest->id;
                            
                        }
                        if($purchase_order_detail->goodReceiptDetail()->exists()){
                            foreach($purchase_order_detail->goodReceiptDetail as $good_receipt_detail){
                                $data_good_receipt = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')]
                                    ],
                                    "key" => $good_receipt_detail->goodReceipt->code,
                                    "name" => $good_receipt_detail->goodReceipt->code,
                                    
                                    'url'=>request()->root()."/admin/inventory/good_receipt_po?code=".CustomHelper::encrypt($good_receipt_detail->goodReceipt->code),
                                    
                                ];
                                
                                $data_link[]=[
                                    'from'=>$purchase_order_detail->purchaseOrder->code,
                                    'to'=>$data_good_receipt["key"],
                                    'string_link'=>$purchase_order_detail->purchaseOrder->code.$data_good_receipt["key"],
                                ];
                                
                                $data_go_chart[]=$data_good_receipt;  
                                
                                if(!in_array($good_receipt_detail->goodReceipt->id, $data_id_gr)){
                                    $data_id_gr[] = $good_receipt_detail->goodReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                    }

                }

                foreach($data_id_pr as $pr_id){
                    $query_pr = PurchaseRequest::find($pr_id);
                    foreach($query_pr->purchaseRequestDetail as $purchase_request_detail){
                        if($purchase_request_detail->purchaseOrderDetail()->exists()){
                        
                            foreach($purchase_request_detail->purchaseOrderDetail as $purchase_order_detail){
                                $po_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$purchase_order_detail->purchaseOrder->post_date],
                                        ['name'=> "Vendor  : ".$purchase_order_detail->purchaseOrder->supplier->name],
                                     ],
                                    'key'=>$purchase_order_detail->purchaseOrder->code,
                                    'name'=>$purchase_order_detail->purchaseOrder->code,
                                    'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($purchase_order_detail->purchaseOrder->code),
                                ];
    
                                $data_go_chart[]=$po_tempura;
                                $data_link[]=[
                                    'from'=>$query_pr->code,
                                    'to'=>$purchase_order_detail->purchaseOrder->code,
                                    'string_link'=>$query_pr->code.$purchase_order_detail->purchaseOrder->code,
                                ];
                                if(!in_array($purchase_order_detail->purchaseOrder->id,$data_id_po)){
                                    $data_id_po[] = $purchase_order_detail->purchaseOrder->id;
                                    $added = true;
                                }
                            }                     
                           
                        }
                    }
                }
            }   
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
            if(!$data->hasPaymentRequestDetail()->exists()){
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
                    'message' => 'Maaf, data sudah dijadikan payment request.',
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
}
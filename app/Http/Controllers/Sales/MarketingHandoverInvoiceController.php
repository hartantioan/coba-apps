<?php

namespace App\Http\Controllers\Sales;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\IncomingPayment;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderHandoverInvoice;
use App\Models\MarketingOrderHandoverReceipt;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderInvoiceDetail;
use App\Models\MarketingOrderMemo;
use App\Models\MarketingOrderReceipt;
use App\Models\MarketingOrderReturn;
use App\Models\MarketingOrderHandoverInvoiceDetail;
use Illuminate\Support\Str;
use App\Models\Place;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Models\User;
use App\Models\Menu;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Support\Facades\Date;

class MarketingHandoverInvoiceController extends Controller
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
        $data = [
            'title'         => 'Tanda Terima Invoice',
            'content'       => 'admin.sales.handover_invoice',
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = MarketingOrderHandoverInvoice::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getMarketingInvoice(Request $request){
        $data = MarketingOrderInvoice::whereIn('status',['2','3'])
                ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
                ->whereDoesntHave('marketingOrderHandoverInvoiceDetail')
                ->where('balance','>=',0)->get();

        $arr = [];
        foreach($data as $row){
            /* if($row->balancePaymentIncoming() > 0){ */
                $arr[] = [
                    'code'              => $row->code,
                    'enc_code'          => CustomHelper::encrypt($row->code),
                    'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                    'customer_name'     => $row->account->name,
                    'total'             => number_format($row->total,2,',','.'),
                    'tax'               => number_format($row->tax,2,',','.'),
                    'total_after_tax'   => number_format($row->total_after_tax,2,',','.'),
                    'rounding'          => number_format($row->rounding,2,',','.'),
                    'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                    'downpayment'       => number_format($row->downpayment,2,',','.'),
                    'balance'           => number_format($row->balance,2,',','.'),
                    'paid'              => number_format($row->totalPay(),2,',','.'),
                    'memo'              => number_format($row->totalMemo(),2,',','.'),
                    'final'             => number_format($row->balancePaymentIncoming(),2,',','.'),
                    'type'              => $row->getTable(),
                ];
            /* } */
        }

        return response()->json($arr);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'company_id',
            'post_date',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = MarketingOrderHandoverInvoice::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
        $query_data = MarketingOrderHandoverInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
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

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }

            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = MarketingOrderHandoverInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use ($search, $request){
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
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->note,
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->status(),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
						<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
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
           /*  'code'			            => $request->temp ? ['required', Rule::unique('marketing_order_handover_invoices', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:marketing_order_handover_invoices,code',
             */'code_place_id'             => 'required',
            'company_id'			    => 'required',
            'post_date'		            => 'required',
            'arr_id'                    => 'required|array',
            'arr_type'                  => 'required|array',
        ], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
          /*   'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai', */
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'company_id.required' 			    => 'Perusahaan tidak boleh kosong.',
            'post_date.required' 			    => 'Tanggal posting tidak boleh kosong.',
            'arr_id.required'                   => 'AR Invoice tidak boleh kosong.',
            'arr_id.array'                      => 'AR Invoice harus array.',
            'arr_type.required'                 => 'Tipe dokumen tidak boleh kosong.',
            'arr_type.array'                    => 'Tipe dokumen harus array.',
        ]);

        DB::beginTransaction();
        try {

            if($validation->fails()) {
                $response = [
                    'status' => 422,
                    'error'  => $validation->errors()
                ];
            } else {
                
                if($request->temp){
                    $query = MarketingOrderHandoverInvoice::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Tanda Terima Invoice telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/marketing_handover_invoices');
                        } else {
                            $document = $query->document;
                        }

                        $query->user_id = session('bo_id');
                        $query->code = $request->code;
                        $query->company_id = $request->company_id;
                        $query->post_date = $request->post_date;
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();
                        
                        foreach($query->marketingOrderHandoverInvoiceDetail as $row){
                            $row->delete();
                        }
                    }else{
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Status Tanda Terima Invoice sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }else{
                    $lastSegment = $request->lastsegment;
                    $menu = Menu::where('url', $lastSegment)->first();
                    $newCode=MarketingOrderHandoverInvoice::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = MarketingOrderHandoverInvoice::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'post_date'                 => $request->post_date,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/marketing_handover_invoices') : NULL,
                        'note'                      => $request->note,
                        'status'                    => '1',
                    ]);
                }
                
                if($query) {
                        
                    foreach($request->arr_id as $key => $row){
                        $moi = null;
                        $moi = MarketingOrderInvoice::where('code',CustomHelper::decrypt($row))->first();
                        if($moi){
                            MarketingOrderHandoverInvoiceDetail::create([
                                'marketing_order_handover_invoice_id'   => $query->id,
                                'lookable_type'                         => $request->arr_type[$key],
                                'lookable_id'                           => $moi->id,
                            ]);
                        }
                    }

                    CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                    CustomHelper::sendNotification($query->getTable(),$query->id,'Pengajuan Tanda Terima Invoice No. '.$query->code,$query->note,session('bo_id'));

                    activity()
                        ->performedOn(new MarketingOrderHandoverInvoice())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query)
                        ->log('Add / edit marketing order handover invoice.');

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
            DB::rollback();
        }

		return response()->json($response);
    }

    public function show(Request $request){
        $po = MarketingOrderHandoverInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        $po['code_place_id'] = substr($po->code,7,2);

        $arr = [];
        
        foreach($po->marketingOrderHandoverInvoiceDetail as $row){
            $arr[] = [
                'code'              => $row->lookable->code,
                'enc_code'          => CustomHelper::encrypt($row->lookable->code),
                'post_date'         => date('d/m/Y',strtotime($row->lookable->post_date)),
                'customer_name'     => $row->lookable->account->name,
                'total'             => number_format($row->lookable->total,2,',','.'),
                'tax'               => number_format($row->lookable->tax,2,',','.'),
                'total_after_tax'   => number_format($row->lookable->total_after_tax,2,',','.'),
                'rounding'          => number_format($row->lookable->rounding,2,',','.'),
                'grandtotal'        => number_format($row->lookable->grandtotal,2,',','.'),
                'downpayment'       => number_format($row->lookable->downpayment,2,',','.'),
                'balance'           => number_format($row->lookable->balance,2,',','.'),
                'paid'              => number_format($row->totalPay(),2,',','.'),
                'memo'              => number_format($row->totalMemo(),2,',','.'),
                'final'             => number_format($row->balancePaymentIncoming(),2,',','.'),
                'type'              => $row->lookable_type,
            ];
        }

        $po['details'] = $arr;
        				
		return response()->json($po);
    }

    public function rowDetail(Request $request)
    {
        $data   = MarketingOrderHandoverInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"> <div class="col s12">'.$data->code.'</div><div class="col s12"><table style="min-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="11">Daftar AR Invoice</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">No.AR Invoice</th>
                                <th class="center-align">Customer</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Tax</th>
                                <th class="center-align">Total Stlh Pajak</th>
                                <th class="center-align">Pembulatan</th>
                                <th class="center-align">Grandtotal</th>
                                <th class="center-align">Downpayment</th>
                                <th class="center-align">Tagihan</th>
                                <th class="center-align">Terbayar</th>
                                <th class="center-align">Memo</th>
                                <th class="center-align">Final</th>
                            </tr>
                        </thead><tbody>';
        $totals=0;
        $totaltax=0;
        $totalaftertax=0;
        $totalrounding=0;
        $totalgrandtotal=0;
        $totaldownpayment=0;
        $totalbalance=0;
        $totalpay=0;
        $totalmemo=0;
        $totalbalancepayment=0;
        foreach($data->marketingOrderHandoverInvoiceDetail as $key => $row){
            $totals+=$row->lookable->total;
            $totaltax+=$row->lookable->tax;
            $totalaftertax+=$row->lookable->total_after_tax;
            $totalrounding+=$row->lookable->rounding;
            $totalgrandtotal+=$row->lookable->grandtotal;
            $totaldownpayment+=$row->lookable->downpayment;
            $totalbalance+=$row->lookable->balance;
            $totalpay+=$row->lookable->totalPay();
            $totalmemo+=$row->lookable->totalMemo();
            $totalbalancepayment+=$row->lookable->balancePaymentIncoming();
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="">'.$row->lookable->code.'</td>
                <td class="">'.$row->lookable->account->name.'</td>
                <td class="center-align">'.date('d/m/Y',strtotime($row->lookable->post_date)).'</td>
                <td class="right-align">'.number_format($row->lookable->total,2,',','.').'</td>
                <td class="right-align">'.number_format($row->lookable->tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->lookable->total_after_tax,2,',','.').'</td>
                <td class="right-align">'.number_format($row->lookable->rounding,2,',','.').'</td>
                <td class="right-align">'.number_format($row->lookable->grandtotal,2,',','.').'</td>
                <td class="right-align">'.number_format($row->lookable->downpayment,2,',','.').'</td>
                <td class="right-align">'.number_format($row->lookable->balance,2,',','.').'</td>
                <td class="right-align">'.number_format($row->lookable->totalPay(),2,',','.').'</td>
                <td class="right-align">'.number_format($row->lookable->totalMemo(),2,',','.').'</td>
                <td class="right-align">'.number_format($row->lookable->balancePaymentIncoming(),2,',','.').'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="3"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaltax, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalaftertax, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalrounding, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalgrandtotal, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totaldownpayment, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalbalance, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalpay, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalmemo, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalbalancepayment, 2, ',', '.') . '</td>
            </tr>  
        ';
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;">
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
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $mod = MarketingOrderHandoverInvoice::where('code',CustomHelper::decrypt($id))->first();
                
        if($mod){
            $data = [
                'title'     => 'Tanda Terima Invoice',
                'data'      => $mod
            ];

            return view('admin.approval.marketing_order_handover_invoice', $data);
        }else{
            abort(404);
        }
    }

    public function printIndividual(Request $request,$id){
        
        $pr = MarketingOrderHandoverInvoice::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Tanda Terima Invoice',
                'data'      => $pr
            ];

            $opciones_ssl=array(
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
            );
            CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.sales.handover_invoice_individual', $data)->setPaper('a5', 'landscape');
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            
            $content = $pdf->download()->getOriginalContent();
            
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
            foreach($request->arr_id as $key => $row){
                $pr = MarketingOrderHandoverInvoice::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Tanda Terima Invoice',
                        'data'      => $pr,
                    ];
                    CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.sales.handover_invoice_individual', $data)->setPaper('a5', 'landscape');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
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

            $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
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
                        $lastSegment = $request->lastsegment;
                      
                        $menu = Menu::where('url', $lastSegment)->first();
                        $nomorLength = strlen($nomor);
                        
                        // Calculate the number of zeros needed for padding
                        $paddingLength = max(0, 8 - $nomorLength);

                        // Pad $nomor with leading zeros to ensure it has at least 8 digits
                        $nomorPadded = str_repeat('0', $paddingLength) . $nomor;
                        $x =$menu->document_code.$request->year_range.$request->code_place_range.'-'.$nomorPadded; 
                        $query = MarketingOrderHandoverInvoice::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Tanda Terima Invoice',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.handover_invoice_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
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

                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
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
                        $query = MarketingOrderHandoverInvoice::where('Code', 'LIKE', '%'.$code)->first();
                        if($query){
                            $data = [
                                'title'     => 'Tanda Terima Invoice',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.sales.handover_invoice_individual', $data)->setPaper('a5', 'landscape');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 340, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
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

                    $randomString = Str::random(10); 

         
                    $filePath = 'public/pdf/' . $randomString . '.pdf';
                    

                    Storage::put($filePath, $result);
                    
                    $document_po = asset(Storage::url($filePath));
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

    public function voidStatus(Request $request){
        $query = MarketingOrderHandoverInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {
            if(in_array($query->status,['4','5'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocument()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada form lainnya.'
                ];
            }else{
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new MarketingOrderHandoverInvoice())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the marketing order handover invoice data');
    
                CustomHelper::sendNotification($query->getTable(),$query->id,'Tanda Terima Invoice No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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

    public function destroy(Request $request){
        $query = MarketingOrderHandoverInvoice::where('code',CustomHelper::decrypt($request->id))->first();

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

            $query->marketingOrderHandoverInvoiceDetail()->delete();

            CustomHelper::removeApproval($query->getTable(),$query->id);

            activity()
                ->performedOn(new MarketingOrderHandoverInvoice())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the marketing order delivery data');

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

    public function viewStructureTree(Request $request){
        $query = MarketingOrderHandoverInvoice::where('code',CustomHelper::decrypt($request->id))->first();
        
        $data_id_good_scale = [];
        $data_id_good_issue = [];
        $data_id_mr = [];
        $data_id_dp=[];
        $data_id_po = [];
        $data_id_gr = [];
        $data_id_invoice=[];
        $data_id_pyrs=[];
        $data_id_lc=[];
        $data_id_inventory_transfer_out=[];
        $data_id_greturns=[];
        $data_id_pr=[];
        $data_id_memo=[];
        $data_id_pyrcs=[];
        $data_id_gir = [];
        $data_id_cb  =[];
        $data_id_frs  =[];
        $data_id_op=[];

        $data_id_mo=[];
        $data_id_mo_delivery = [];
        $data_id_mo_dp=[];
        $data_id_hand_over_invoice = [];
        $data_id_mo_return=[];
        $data_id_mo_invoice=[];
        $data_id_mo_memo=[];
        $data_id_mo_delivery_process=[];
        $data_id_mo_receipt = [];
        $data_incoming_payment=[];
        $data_id_hand_over_receipt=[];

        $data_go_chart=[];
        $data_link=[];

        if($query){
            $data_mo_delivery = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal : Rp.:".number_format($query->grandtotal,2,',','.')]
                 ],
                'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->code),           
            ];

            $data_go_chart[]= $data_mo_delivery;
            $data_id_mo_delivery[]=$query->id;

            if($query->marketingOrder()->exists()){
                $data_marketing_order = [
                    "name"=> $query->marketingOrder->code,
                    "key" => $query->marketingOrder->code,
                    
                    'properties'=> [
                        ['name'=> "Tanggal :".$query->marketingOrder->post_date],
                        ['name'=> "Nominal : Rp.:".number_format($query->marketingOrder->grandtotal,2,',','.')]
                     ],
                    'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->marketingOrder->code),           
                ];
    
                $data_go_chart[]= $data_marketing_order;
                $data_id_mo[]=$query->marketingOrder->id;
                
                $data_link[]=[
                    'from'=>$query->marketingOrder->code,
                    'to'=>$query->code,
                    'string_link'=>$query->marketingOrder->code.$query->code
                ]; 
            }
            if($query->marketingOrderDeliveryProcess()->exists()){
                $data_mo_de_pro=[
                    "name"=>$query->marketingOrderDeliveryProcess->code,
                    "key" => $query->marketingOrderDeliveryProcess->code,
                    
                    'properties'=> [
                        ['name'=> "Tanggal :".$query->marketingOrderDeliveryProcess->post_date],
                        ['name'=> "Nominal : Rp.:".number_format($query->marketingOrderDeliveryProcess->grandtotal,2,',','.')]
                     ],
                    'url'=>request()->root()."/admin/sales/sales_order?code=".CustomHelper::encrypt($query->marketingOrderDeliveryProcess->code),                
                ];

                
                $data_go_chart[]= $data_mo_de_pro;
                $data_id_mo_[]=$query->marketingOrderDeliveryProcess->id;
                
                $data_link[]=[
                    'from'=>$query->code,
                    'to'=>$query->marketingOrderDeliveryProcess->code,
                    'string_link'=>$query->code.$query->marketingOrderDeliveryProcess->code
                ]; 
            }
            $finished_data_id_ip = [];
            $finished_data_id_dp = [];
            $finished_data_id_mo_receipt = [];
            $finished_data_id_mo_delivery_process = [];
            $finished_data_id_handover= [];
            $finished_data_id_handover_invoice = [];
            $finished_data_id_invoice = [];
            $finished_data_id_memo = [];
            $finished_data_id_mo_return = [];
            $finished_data_id_mo_delivery = [];
            $finished_data_id_mo = [];

            $added = true;
            while($added){//beda tree
                $added=false;
                
                // mencaari incoming payment
                foreach($data_incoming_payment as $row_id_ip){
                    if(!in_array($row_id_ip, $finished_data_id_ip)){
                        $finished_data_id_ip[]=$row_id_ip;
                        $query_ip = IncomingPayment::find($row_id_ip);
                        foreach($query_ip->incomingPaymentDetail as $row_ip_detail){
                            if($row_ip_detail->marketingOrderDownPayment()->exists()){
                                $mo_downpayment=[
                                    "name"=>$row_ip_detail->marketingOrderDownPayment->code,
                                    "key" => $row_ip_detail->marketingOrderDownPayment->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_ip_detail->marketingOrderDownPayment->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_ip_detail->marketingOrderDownPayment->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/finance/incoming_payment?code=".CustomHelper::encrypt($row_ip_detail->marketingOrderDownPayment->code),
                                ];
                                $data_go_chart[]=$mo_downpayment;
                                $data_link[]=[
                                    'from'=>$row_ip_detail->marketingOrderDownPayment->code,
                                    'to'=>$query_ip->code,
                                    'string_link'=>$row_ip_detail->marketingOrderDownPayment->code.$query_ip->code,
                                ];
                                $data_id_mo_dp[] = $row_ip_detail->marketingOrderDownPayment->id;
                                
                            }
                            if($row_ip_detail->marketingOrderInvoice()->exists()){
                                $mo_invoice=[
                                    "name"=>$row_ip_detail->marketingOrderInvoice->code,
                                    "key" => $row_ip_detail->marketingOrderInvoice->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_ip_detail->marketingOrderInvoice->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_ip_detail->marketingOrderInvoice->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_ip_detail->marketingOrderInvoice->code),
                                ];
                                $data_go_chart[]=$mo_invoice;
                                $data_link[]=[
                                    'from'=>$row_ip_detail->marketingOrderInvoice->code,
                                    'to'=>$query_ip->code,
                                    'string_link'=>$row_ip_detail->marketingOrderInvoice->code.$query_ip->code,
                                ];
                                $data_id_mo_invoice[] = $row_ip_detail->marketingOrderInvoice->id;
                                
                            }
                        }
                    }
                }
                // menacari down_payment
                foreach($data_id_mo_dp as $row_id_dp){
                    if(!in_array($row_id_dp, $finished_data_id_dp)){
                        $finished_data_id_dp[]=$row_id_dp;
                        $query_dp= MarketingOrderDownPayment::find($row_id_dp);
                        
                        if($query_dp->incomingPaymentDetail()->exists()){
                            foreach($query_dp->incomingPaymentDetail as $row_incoming_payment){
                                $mo_incoming_payment=[
                                    "name"=>$row_incoming_payment->incomingPayment->code,
                                    "key" => $row_incoming_payment->incomingPayment->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_incoming_payment->incomingPayment->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_incoming_payment->incomingPayment->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_incoming_payment->incomingPayment->code),
                                ];
                                $data_go_chart[]=$mo_incoming_payment;
                                $data_link[]=[
                                    'from'=>$query_dp->code,
                                    'to'=>$row_incoming_payment->incomingPayment->code,
                                    'string_link'=>$query_dp->code.$row_incoming_payment->incomingPayment->code,
                                ];
                                if(!in_array($row_incoming_payment->incomingPayment->id, $data_incoming_payment)){
                                    $data_incoming_payment[] = $row_incoming_payment->incomingPayment->id;
                                    $added = true;
                                }
                            }
                        }
                        
                        if($query_dp->marketingOrderInvoiceDetail()->exists()){
                            $arr = [];
                            foreach($query_dp->marketingOrderInvoiceDetail as $row_invoice_detail){
                                if($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess()->exists()){
                                    foreach($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess as $rowmoidp){
                                        $arr[] = $rowmoidp->lookable->marketingOrderDelivery->marketingOrderDeliveryProcess->code;  
                                    }
                                }
                                
                                $newArray = array_unique($arr);
                                $string = implode(', ', $newArray);
                                $data_invoice = [
                                    "name"=>$row_invoice_detail->marketingOrderInvoice->code,
                                    "key" => $row_invoice_detail->marketingOrderInvoice->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_invoice_detail->marketingOrderInvoice->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_invoice_detail->marketingOrderInvoice->grandtotal,2,',','.')],
                                        ['name'=> "No Surat Jalan  :".$string.""]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_invoice_detail->marketingOrderInvoice->code),
                                ];
                                
                                $data_go_chart[]=$data_invoice;
                                $data_link[]=[
                                    'from'=>$row_invoice_detail->marketingOrderInvoice->code,
                                    'to'=>$query_dp->code,
                                    'string_link'=>$query_dp->code.$row_invoice_detail->marketingOrderInvoice->code,
                                ];
                                
                                if(!in_array($row_invoice_detail->marketingOrderInvoice->id, $data_id_mo_invoice)){
                                    $data_id_mo_invoice[] = $row_invoice_detail->marketingOrderInvoice->id;
                                    $added = true;
                                }
                            }
                        }
                    }


                }
                //marketing mo receipt
                foreach($data_id_mo_receipt as $id_mo_receipt){
                    if(!in_array($id_mo_receipt, $finished_data_id_mo_receipt)){
                        $finished_data_id_mo_receipt[]=$id_mo_receipt;
                        $query_mo_receipt = MarketingOrderReceipt::find($id_mo_receipt);

                        if($query_mo_receipt->marketingOrderHandoverReceiptDetail->exists()){
                            foreach($query_mo_receipt->marketingOrderHandoverReceiptDetail as $row_mo_h_rd){
                                $mohr=[
                                    "name"=>$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                    "key" =>$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_h_rd->marketingOrderHandoverReceipt->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_h_rd->marketingOrderHandoverReceipt->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_handover_receipt?code=".CustomHelper::encrypt($row_mo_h_rd->marketingOrderHandoverReceipt->code),
                                ];
                                $data_go_chart[]=$mohr;
                                $data_link[]=[
                                    'from'=>$query_mo_receipt->code,
                                    'to'=>$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                    'string_link'=>$query_mo_receipt->code.$row_mo_h_rd->marketingOrderHandoverReceipt->code,
                                ];
                                
                                if(!in_array($row_mo_h_rd->marketingOrderHandoverReceipt->id, $data_id_hand_over_receipt)){
                                    $data_id_hand_over_receipt[] =$row_mo_h_rd->marketingOrderHandoverReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                        
                        foreach($query_mo_receipt->marketingOrderReceiptDetail as $row_mo_receipt_detail){
                            if($row_mo_receipt_detail->marketingOrderInvoice()){
                                $mo_invoice_tempura = [
                                    "name"=>$row_mo_receipt_detail->lookable->code,
                                    "key" => $row_mo_receipt_detail->lookable->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_receipt_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_receipt_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_mo_receipt_detail->lookable->code),
                                ];
                                $data_go_chart[]=$mo_invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_mo_receipt->code,
                                    'to'=>$row_mo_receipt_detail->lookable->code,
                                    'string_link'=>$query_mo_receipt->code.$row_mo_receipt_detail->lookable->code,
                                ];
                                if(!in_array($row_mo_receipt_detail->lookable->id, $data_id_mo_invoice)){
                                    $data_id_mo_invoice[] = $row_mo_receipt_detail->lookable->id;
                                    $added = true;
                                }
                            }
                        }
                    }

                }

                foreach($data_id_mo_delivery_process as $id_mo_delivery_process){
                    if(!in_array($id_mo_delivery_process, $finished_data_id_mo_delivery_process)){
                        $finished_data_id_mo_delivery_process[]=$id_mo_delivery_process;
                        $query_mo_delivery_process = MarketingOrderDeliveryProcess::find($id_mo_delivery_process);

                        if($query_mo_delivery_process->purchaseOrderDetail()->exists()){
                            foreach($query_mo_delivery_process->purchaseOrderDetail as $row_po_detail){
                                $po_tempura=[
                                    "name"=>$row_po_detail->purchaseOrder->code,
                                    "key" =>$row_po_detail->purchaseOrder->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_po_detail->purchaseOrder->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_po_detail->purchaseOrder->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po_detail->purchaseOrder->code),
                                ];
                                $data_go_chart[]=$po_tempura;
                                $data_link[]=[
                                    'from'=>$query_mo_delivery_process->code,
                                    'to'=>$row_po_detail->purchaseOrder->code,
                                    'string_link'=>$query_mo_delivery_process->code.$row_po_detail->purchaseOrder->code,
                                ];
                                
                                if(!in_array($row_po_detail->purchaseOrder->id, $data_id_po)){
                                    $data_id_po[] =$row_po_detail->purchaseOrder->id;
                                    $added = true;
                                }
                            }
                        
                        }
                    }
                }

                //marketing handover receipt
                foreach($data_id_hand_over_receipt as $row_handover_id){
                    if(!in_array($row_handover_id, $finished_data_id_handover)){
                        $finished_data_id_handover[]=$row_handover_id;
                        $query_handover_receipt = MarketingOrderHandoverReceipt::find($row_handover_id);
                        foreach($query_handover_receipt->marketingOrderHandoverReceiptDetail as $row_mo_h_receipt_detail){
                            if($row_mo_h_receipt_detail->marketingOrderInvoice()){
                                $mo_invoice_tempura=[
                                    "name"=>$row_mo_h_receipt_detail->lookable->code,
                                    "key" => $row_mo_h_receipt_detail->lookable->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_h_receipt_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_h_receipt_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_mo_h_receipt_detail->lookable->code),
                                ];
                                $data_go_chart[]=$mo_invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_handover_receipt->code,
                                    'to'=>$row_mo_h_receipt_detail->lookable->code,
                                    'string_link'=>$query_handover_receipt->code.$row_mo_h_receipt_detail->lookable->code,
                                ];
                                if(!in_array($row_mo_h_receipt_detail->lookable->id, $data_id_mo_invoice)){
                                    $data_id_mo_invoice[] = $row_mo_h_receipt_detail->lookable->id;
                                    $added = true;
                                }
                            }
                        }
                    }
                }
                //marketing handover invoice
                foreach($data_id_hand_over_invoice as $row_handover_invoice_id){
                    if(!in_array($row_handover_invoice_id, $finished_data_id_handover_invoice)){
                        $finished_data_id_handover_invoice[]=$row_handover_invoice_id;
                        $query_handover_invoice = MarketingOrderHandoverInvoice::find($row_handover_invoice_id);
                        foreach($query_handover_invoice->marketingOrderHandoverInvoiceDetail as $row_mo_h_invoice_detail){
                            if($row_mo_h_invoice_detail->marketingOrderInvoice->exists()){
                                $mo_invoice_tempura=[
                                    "name"=>$row_mo_h_receipt_detail->lookable->code,
                                    "key" => $row_mo_h_receipt_detail->lookable->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_h_receipt_detail->lookable->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_h_receipt_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_mo_h_receipt_detail->lookable->code),
                                ];
                                $data_go_chart[]=$mo_invoice_tempura;
                                $data_link[]=[
                                    'from'=>$query_handover_invoice->code,
                                    'to'=>$row_mo_h_receipt_detail->lookable->code,
                                    'string_link'=>$query_handover_invoice->code.$row_mo_h_receipt_detail->lookable->code,
                                ];
                                if(!in_array($row_mo_h_receipt_detail->lookable->id, $data_id_mo_invoice)){
                                    $data_id_mo_invoice[] = $row_mo_h_receipt_detail->lookable->id;
                                    $added = true;
                                }
                            }
                        }
                    }
                }

                // menacari anakan invoice
                foreach($data_id_mo_invoice as $row_id_invoice){
                    if(!in_array($row_id_invoice, $finished_data_id_invoice)){
                        $finished_data_id_invoice[]=$row_id_invoice;
                        $query_invoice = MarketingOrderInvoice::find($row_id_invoice);
                        if($query_invoice->incomingPaymentDetail()->exists()){
                            foreach($query_invoice->incomingPaymentDetail as $row_ip_detail){
                                $mo_incoming_payment=[
                                    "name"=>$row_ip_detail->incomingPayment->code,
                                    "key" => $row_ip_detail->incomingPayment->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_ip_detail->incomingPayment->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_ip_detail->incomingPayment->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_ip_detail->incomingPayment->code),
                                ];
                                $data_go_chart[]=$mo_incoming_payment;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_ip_detail->incomingPayment->code,
                                    'string_link'=>$query_invoice->code.$row_ip_detail->incomingPayment->code,
                                ];
                                if(!in_array($row_ip_detail->incomingPayment->id, $data_incoming_payment)){
                                    $data_incoming_payment[] = $row_ip_detail->incomingPayment->id;
                                    $added = true;
                                }
                            }
                        }
                        if($query_invoice->marketingOrderInvoiceDeliveryProcess()->exists()){
                            foreach($query_invoice->marketingOrderInvoiceDeliveryProcess as $row_delivery_detail){
                                
                                $mo_delivery=[
                                    "name"=> $row_delivery_detail->lookable->marketingOrderDelivery->code,
                                    "key" => $row_delivery_detail->lookable->marketingOrderDelivery->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_delivery_detail->lookable->marketingOrderDelivery->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_delivery_detail->lookable->marketingOrderDelivery->grandtotal,2,',','.')],
                                        
                                    ],
                                    'url'=>request()->root()."/admin/sales/delivery_order?code=".CustomHelper::encrypt($row_delivery_detail->lookable->marketingOrderDelivery->code),
                                ];
                                $data_go_chart[]=$mo_delivery;
                                $data_link[]=[
                                    'from'=>$row_delivery_detail->lookable->marketingOrderDelivery->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row_delivery_detail->lookable->marketingOrderDelivery->code.$query_invoice->code,
                                ];
                                $data_id_mo_delivery[]=$row_delivery_detail->lookable->marketingOrderDelivery->id;
                            }    
                            
                        }
                        if($query_invoice->marketingOrderInvoiceDownPayment()->exists()){
                            foreach($query_invoice->marketingOrderInvoiceDownPayment as $row_dp){
                                $mo_downpayment=[
                                    "name"=>$row_dp->lookable->code,
                                    "key" =>$row_dp->lookable->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_dp->lookable->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_dp->lookable->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/sales_down_payment?code=".CustomHelper::encrypt($row_dp->lookable->code),
                                ];
                                $data_go_chart[]=$mo_downpayment;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_dp->lookable->code,
                                    'string_link'=>$query_invoice->code.$row_dp->lookable->code,
                                ];
                                
                                if(!in_array($row_dp->lookable->id, $data_id_mo_dp)){
                                    $data_id_mo_dp[] =$row_dp->lookable->id;
                                    $added = true;
                                }
                            }
                            
                        }
                        if($query_invoice->marketingOrderHandoverInvoiceDetail()->exists()){
                            foreach($query_invoice->marketingOrderHandoverInvoiceDetail as $row_handover_detail){
                                $mo_handover_tempura=[
                                    "name"=>$row_handover_detail->marketingOrderHandoverInvoice->code,
                                    "key" =>$row_handover_detail->marketingOrderHandoverInvoice->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_handover_detail->marketingOrderHandoverInvoice->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_handover_detail->marketingOrderHandoverInvoice->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_handover_invoice?code=".CustomHelper::encrypt($row_handover_detail->marketingOrderHandoverInvoice->code),
                                ];
                                $data_go_chart[]=$mo_handover_tempura;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_handover_detail->marketingOrderHandoverInvoice->code,
                                    'string_link'=>$query_invoice->code.$row_handover_detail->marketingOrderHandoverInvoice->code,
                                ];
                                
                                if(!in_array($row_handover_detail->marketingOrderHandoverInvoice->id, $data_id_hand_over_invoice)){
                                    $data_id_hand_over_invoice[] =$row_handover_detail->marketingOrderHandoverInvoice->id;
                                    $added = true;
                                }
                            }
                        }
                        if($query_invoice->marketingOrderReceiptDetail()->exists()){
                            foreach($query_invoice->marketingOrderReceiptDetail as $row_mo_receipt_detail){
                                $mo_receipt_tempura=[
                                    "name"=>$row_mo_receipt_detail->marketingOrderReceipt->code,
                                    "key" =>$row_mo_receipt_detail->marketingOrderReceipt->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_receipt_detail->marketingOrderReceipt->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_receipt_detail->marketingOrderReceipt->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_receipt?code=".CustomHelper::encrypt($row_mo_receipt_detail->marketingOrderReceipt->code),
                                ];
                                $data_go_chart[]=$mo_receipt_tempura;
                                $data_link[]=[
                                    'from'=>$query_invoice->code,
                                    'to'=>$row_mo_receipt_detail->marketingOrderReceipt->code,
                                    'string_link'=>$query_invoice->code.$row_mo_receipt_detail->marketingOrderReceipt->code,
                                ];
                                
                                if(!in_array($row_mo_receipt_detail->marketingOrderReceipt->id, $data_id_mo_receipt)){
                                    $data_id_mo_receipt[] =$row_mo_receipt_detail->marketingOrderReceipt->id;
                                    $added = true;
                                }
                            }
                        }
                        foreach($query_invoice->marketingOrderInvoiceDetail as $row_invoice_detail){
                            if($row_invoice_detail->marketingOrderMemoDetail()->exists()){
                                foreach($row_invoice_detail->marketingOrderMemoDetail as $row_memo){
                                    $mo_memo=[
                                        "name"=>$row_memo->marketingOrderMemo->code,
                                        "key" => $row_memo->marketingOrderMemo->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_memo->marketingOrderMemo->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_memo->marketingOrderMemo->grandtotal,2,',','.')]
                                        ],
                                        'url'=>request()->root()."/admin/sales/marketing_order_memo?code=".CustomHelper::encrypt($row_memo->marketingOrderMemo->code),
                                    ];
                                    $data_go_chart[]=$mo_memo;
                                    $data_link[]=[
                                        'from'=>$query_invoice->code,
                                        'to'=>$row_memo->marketingOrderMemo->code,
                                        'string_link'=>$query_invoice->code.$row_memo->marketingOrderMemo->code,
                                    ];
                                    $data_id_mo_memo[] = $row_memo->marketingOrderMemo->id;
                                    // if(!in_array($row_memo->marketingOrderMemo->id, $data_id_mo_memo)){
                                    //     $data_id_mo_memo[] = $row_memo->marketingOrderMemo->id;
                                    //     $added = true;
                                    // }
                                }
                            }
                            
                        }
                    }

                }

                foreach($data_id_mo_memo as $row_id_memo){
                    if(!in_array($row_id_memo, $finished_data_id_memo)){
                        $finished_data_id_memo[]=$row_id_memo;
                        $query_mo_memo = MarketingOrderMemo::find($row_id_memo);
                        if($query_mo_memo->incomingPaymentDetail()->exists()){
                            foreach($query_mo_memo->incomingPaymentDetail as $ip_detail){
                                $ip_tempura = [
                                    "name"=>$ip_detail->incomingPayment->code,
                                    "key" => $ip_detail->incomingPayment->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$ip_detail->incomingPayment->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($ip_detail->incomingPayment->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/delivery_order/?code=".CustomHelper::encrypt($ip_detail->incomingPayment->code),
                                ];
                                
                                $data_go_chart[]=$ip_tempura;
                                $data_link[]=[
                                    'from'=>$query_mo_memo->code,
                                    'to'=>$ip_detail->incomingPayment->code,
                                    'string_link'=>$query_mo_memo->code.$ip_detail->incomingPayment->code,
                                ];
                                if(!in_array($ip_detail->incomingPayment->id, $data_incoming_payment)){
                                    $data_incoming_payment[]=$ip_detail->incomingPayment->id;
                                    $added = true;
                                }  
                            }    
                        }
                        foreach($query_mo_memo->marketingOrderMemoDetail as $row_mo_memo_detail){
                                if($row_mo_memo_detail->marketingOrderDownPayment()){
                                    $mo_downpayment=[
                                        "name"=>$row_mo_memo_detail->lookable->code,
                                        "key" => $row_mo_memo_detail->lookable->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_mo_memo_detail->lookable->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_mo_memo_detail->lookable->grandtotal,2,',','.')]
                                        ],
                                        'url'=>request()->root()."admin/sales/sales_down_payment/?code=".CustomHelper::encrypt($row_mo_memo_detail->lookable->code),
                                    ];
                                    $data_go_chart[]=$mo_downpayment;
                                    $data_link[]=[
                                        'from'=>$row_mo_memo_detail->lookable->code,
                                        'to'=>$query_mo_memo->code,
                                        'string_link'=>$row_mo_memo_detail->lookable->code.$query_mo_memo->code,
                                    ];
                                    $data_id_mo_dp[] = $row_mo_memo_detail->lookable->id;
                                    
                                    
                                }
                                if($row_mo_memo_detail->marketingOrderInvoiceDetail()){
                                    $mo_invoice_tempura=[
                                        "name"=>$row_mo_memo_detail->lookable->code,
                                        "key" => $row_mo_memo_detail->lookable->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_mo_memo_detail->lookable->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_mo_memo_detail->lookable->grandtotal,2,',','.')]
                                        ],
                                        'url'=>request()->root()."admin/sales/sales_down_payment/?code=".CustomHelper::encrypt($row_mo_memo_detail->lookable->code),
                                    ];
                                    $data_go_chart[]=$mo_invoice_tempura;
                                    $data_link[]=[
                                        'from'=>$row_mo_memo_detail->lookable->code,
                                        'to'=>$query_mo_memo->code,
                                        'string_link'=>$row_mo_memo_detail->lookable->code.$query_mo_memo->code,
                                    ];
                                    $data_id_mo_invoice[] = $row_mo_memo_detail->lookable->id;
                                    
                                }
                        }
                    }

                }
               
                foreach($data_id_mo_return as $row_id_mo_return){
                    if(!in_array($row_id_mo_return, $finished_data_id_mo_return)){
                        $finished_data_id_mo_return[]=$row_id_mo_return;
                        $query_mo_return = MarketingOrderReturn::find($row_id_mo_return);
                        foreach($query_mo_return->marketingOrderReturnDetail as $row_mo_return_detail){
                            if($row_id_mo_return->marketingOrderDeliveryDetail()->exists()){
                                $data_mo_delivery_tempura = [
                                    "name"=>$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code,
                                    "key" => $row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->post_date],
                                        ['name'=> "Nominal : Rp.:".number_format($row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->grandtotal,2,',','.')]
                                    ],
                                    'url'=>request()->root()."/admin/sales/marketing_order_delivery/?code=".CustomHelper::encrypt($row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code),
                                ];
                                $data_go_chart[]=$data_mo_delivery_tempura;
                                $data_link[]=[
                                    'from'=>$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code,
                                    'to'=>$query_mo_return->code,
                                    'string_link'=>$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->code.$query_mo_return->code,
                                ];
                                if(!in_array($row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->id, $data_id_mo_delivery)){
                                    $data_id_mo_delivery[]=$row_mo_return_detail->marketingOrderDeliveryDetail->marketingOrderDelivery->id;
                                    $added = true;
                                }
                            }
                            
                            
                            
                        }
                    }
                }
                // mencari delivery anakan
                foreach($data_id_mo_delivery as $row_id_mo_delivery){
                    if(!in_array($row_id_mo_delivery, $finished_data_id_mo_delivery)){
                        $finished_data_id_mo_delivery[]=$row_id_mo_delivery;
                        $query_mo_delivery = MarketingOrderDelivery::find($row_id_mo_delivery);
                        if($query_mo_delivery->marketingOrderDeliveryProcess()->exists()){
                            $data_mo_delivery_process = [
                                "name"=>$query_mo_delivery->marketingOrderDeliveryProcess->code,
                                "key" => $query_mo_delivery->marketingOrderDeliveryProcess->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$query_mo_delivery->marketingOrderDeliveryProcess->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($query_mo_delivery->marketingOrderDeliveryProcess->grandtotal,2,',','.')]
                                ],
                                'url'=>request()->root()."/admin/sales/delivery_order/?code=".CustomHelper::encrypt($query_mo_delivery->marketingOrderDeliveryProcess->code),
                            ];
                            
                            $data_go_chart[]=$data_mo_delivery_process;
                            $data_link[]=[
                                'from'=>$query_mo_delivery->code,
                                'to'=>$query_mo_delivery->marketingOrderDeliveryProcess->code,
                                'string_link'=>$query_mo_delivery->code.$query_mo_delivery->marketingOrderDeliveryProcess->code,
                            ];
                            if(!in_array($query_mo_delivery->marketingOrderDeliveryProcess->id, $data_id_mo_delivery_process)){
                                $data_id_mo_delivery_process[]=$query_mo_delivery->marketingOrderDeliveryProcess->id;
                                $added = true;
                            }
                            
                            
                        }//mencari process dari delivery
                        foreach($query_mo_delivery->marketingOrderDeliveryDetail as $row_delivery_detail){
                            if($row_delivery_detail->marketingOrderInvoiceDetail()->exists()){
                                $arr = [];
                                foreach($row_delivery_detail->marketingOrderInvoiceDetail as $row_invoice_detail){
                                    if($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess()->exists()){
                                        foreach($row_invoice_detail->marketingOrderInvoice->marketingOrderInvoiceDeliveryProcess as $rowmoidp){
                                            $arr[] = $rowmoidp->lookable->marketingOrderDelivery->marketingOrderDeliveryProcess->code;  
                                        }
                                    }
                                    
                                    $newArray = array_unique($arr);
                                    $string = implode(', ', $newArray);
                                    $data_invoice = [
                                        "name"=>$row_invoice_detail->marketingOrderInvoice->code,
                                        "key" => $row_invoice_detail->marketingOrderInvoice->code,
                                    
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_invoice_detail->marketingOrderInvoice->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_invoice_detail->marketingOrderInvoice->grandtotal,2,',','.')],
                                            ['name'=> "No Surat Jalan  :".$string.""]
                                        ],
                                        'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_invoice_detail->marketingOrderInvoice->code),
                                    ];
                                    
                                    $data_go_chart[]=$data_invoice;
                                    $data_link[]=[
                                        'from'=>$query_mo_delivery->code,
                                        'to'=>$row_invoice_detail->marketingOrderInvoice->code,
                                        'string_link'=>$query_mo_delivery->code.$row_invoice_detail->marketingOrderInvoice->code,
                                    ];
                                    
                                    if(!in_array($row_invoice_detail->marketingOrderInvoice->id, $data_id_mo_invoice)){
                                        $data_id_mo_invoice[] = $row_invoice_detail->marketingOrderInvoice->id;
                                        $added = true;
                                    }
                                }
                            }//mencari marketing order invoice

                            if($row_delivery_detail->marketingOrderReturnDetail()->exists()){
                                foreach($row_delivery_detail->marketingOrderReturnDetail as $row_return_detail){
                                    $data_return = [
                                        "name"=>$row_return_detail->marketingOrderReturn->code,
                                        "key" => $row_return_detail->marketingOrderReturn->code,
                                        
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_return_detail->marketingOrderReturn->post_date],
                                            ['name'=> "Nominal : Rp.:".number_format($row_return_detail->marketingOrderReturn->grandtotal,2,',','.')]
                                        ],
                                        'url'=>request()->root()."/admin/sales/marketing_order_invoice?code=".CustomHelper::encrypt($row_return_detail->marketingOrderReturn->code),
                                    ];
                                    
                                    $data_go_chart[]=$data_return;
                                    $data_link[]=[
                                        'from'=>$query_mo_delivery->code,
                                        'to'=>$row_return_detail->marketingOrderReturn->code,
                                        'string_link'=>$query_mo_delivery->code.$row_return_detail->marketingOrderReturn->code,
                                    ];
                                    
                                    $data_id_mo_return[]=$row_return_detail->marketingOrderReturn->id;
                                }
                            }//mencari marketing order return
                        }
                        if($query_mo_delivery->marketingOrder()->exists()){
                            $data_marketing_order = [
                                "name"=> $query_mo_delivery->marketingOrder->code,
                                "key" => $query_mo_delivery->marketingOrder->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$query_mo_delivery->marketingOrder->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($query_mo_delivery->marketingOrder->grandtotal,2,',','.')]
                                ],
                                'url'=>request()->root()."/admin/sales/marketing_order_delivery?code=".CustomHelper::encrypt($query_mo_delivery->marketingOrder->code),           
                            ];
                
                            $data_go_chart[]= $data_marketing_order;
                            $data_id_mo[]=$query_mo_delivery->marketingOrder->id;
                        }
                    }
                }

                foreach($data_id_mo as $row_id_mo){
                    if(!in_array($row_id_mo, $finished_data_id_mo)){
                        $finished_data_id_mo[]=$row_id_mo;
                        $query_mo= MarketingOrder::find($row_id_mo);

                        foreach($query_mo->marketingOrderDelivery as $row_mod_del){
                            $modelvery=[
                                "name"=>$row_mod_del->code,
                                "key" => $row_mod_del->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$row_mod_del->post_date],
                                    ['name'=> "Nominal : Rp.:".number_format($row_mod_del->grandtotal,2,',','.')]
                                ],
                                'url'=>request()->root()."/admin/sales/delivery_order?code=".CustomHelper::encrypt($row_mod_del->code),  
                            ];
        
                            $data_go_chart[]=$modelvery;
                            $data_link[]=[
                                'from'=>$query_mo->code,
                                'to'=>$row_mod_del->code,
                                'string_link'=>$query_mo->code.$row_mod_del->code
                            ]; 

                            if(!in_array($row_mod_del->id, $data_id_mo_delivery)){
                                $data_id_mo_delivery[] = $row_mod_del->id; 
                                $added = true;
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

        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }

        return response()->json($response);
    }

    public function done(Request $request){
        $query_done = MarketingOrderHandoverInvoice::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'    => '3'
                ]);
    
                activity()
                        ->performedOn(new MarketingOrderHandoverInvoice())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Marketing Handover Invoice data');
    
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
}
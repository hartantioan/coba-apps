<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\GoodScale;
use App\Models\InventoryTransferOut;
use App\Models\GoodIssueRequest;
use App\Models\Item;
use App\Models\Line;
use App\Models\CloseBill;
use App\Exports\ExportOutstandingDP;
use App\Models\LandedCost;
use App\Models\Machine;
use App\Models\MaterialRequest;
use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrderDetail;

use App\Models\Place;

use App\Models\PurchaseRequest;
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
use App\Models\PurchaseOrder;
use App\Models\Currency;
use App\Models\Menu;
use App\Models\PurchaseDownPaymentDetail;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Exports\ExportPurchaseDownPayment;
use App\Exports\ExportDownPaymentTransactionPage;
use App\Models\ChecklistDocumentList;
use App\Models\FundRequest;
use App\Models\FundRequestDetail;
use App\Models\MenuUser;
use App\Models\User;
use App\Helpers\TreeHelper;
use App\Models\CancelDocument;
use App\Models\Tax;

class PurchaseDownPaymentController extends Controller
{
    protected $dataplaces, $dataplacecode, $url, $menu;

    public function __construct(){
        $user = User::find(Session::get('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->url = request()->segment(3);
        $this->menu = Menu::where('url', $this->url)->first();
    }

    public function index(Request $request)
    {
       
        $menu = $this->menu;
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();

        $data = [
            'title'         => 'AP Down Payment',
            'content'       => 'admin.purchase.down_payment',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'menu'          => $menu,
            'menucode'      => $menu->document_code,
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = PurchaseDownPayment::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getPurchaseOrder(Request $request){
        $data = PurchaseOrder::where('account_id',$request->supplier)->whereIn('status',['2'])->get();
        $data2 = FundRequest::where('account_id',$request->supplier)->whereIn('status',['2'])->where('document_status','3')->where('type','1')->get();

        $details = [];

        foreach($data as $row){
            $list_items = '<ol>';

            foreach($row->purchaseOrderDetail as $key => $rowdetail){
                $item_code = $rowdetail->item()->exists() ? $rowdetail->item->code : ($rowdetail->coa()->exists() ? $rowdetail->coa->code : '');
                $item_name = $rowdetail->item()->exists() ? $rowdetail->item->name : ($rowdetail->coa()->exists() ? $rowdetail->coa->name : '');
                $item_unit = $rowdetail->item()->exists() ? $rowdetail->itemUnit->unit->code : '-';
                $list_items .= '<li>'.$item_code.' - '.$item_name.' Qty : '.CustomHelper::formatConditionalQty($rowdetail->qty).' '.$item_unit.' Total '.number_format($rowdetail->subtotal,2,',','.').' PPN '.number_format($rowdetail->tax,2,',','.').' PPh '.number_format($rowdetail->wtax,2,',','.').' Grandtotal '.number_format($rowdetail->grandtotal,2,',','.').'</li>';
            }

            $list_items .= '</ol>';

            $details[] = [
                'id'            => $row->id,
                'po_code'       => CustomHelper::encrypt($row->code),
                'po_no'         => $row->code,
                'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                'delivery_date' => date('d/m/Y',strtotime($row->delivery_date)),
                'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                'list_items'    => $list_items,
                'note'          => $row->note ? $row->note : '',
                'type'          => $row->getTable(),
                'total'         => number_format($row->total,2,',','.'),
                'tax'           => number_format($row->tax,2,',','.'),
                'wtax'          => number_format($row->wtax,2,',','.'),
            ];
        }

        foreach($data2 as $row){
            $list_items = '<ol>';

            foreach($row->fundRequestDetail as $key => $rowdetail){
                $item_unit = $rowdetail->unit->code;CustomHelper::formatConditionalQty($rowdetail->qty).' '.$item_unit.' Total '.number_format($rowdetail->total,2,',','.').' PPN '.number_format($rowdetail->tax,2,',','.').' PPh '.number_format($rowdetail->wtax,2,',','.').' Grandtotal '.number_format($rowdetail->grandtotal,2,',','.').' 1231312321321312312312312312</li>';
            }

            $list_items .= '</ol>';

            $details[] = [
                'id'            => $row->id,
                'po_code'       => CustomHelper::encrypt($row->code),
                'po_no'         => $row->code,
                'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                'delivery_date' => date('d/m/Y',strtotime($row->required_date)),
                'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                'list_items'    => $list_items,
                'note'          => $row->note ? $row->note : '',
                'type'          => $row->getTable(),
                'total'         => number_format($row->total,2,',','.'),
                'tax'           => number_format($row->tax,2,',','.'),
                'wtax'          => number_format($row->wtax,2,',','.'),
            ];
        }

        return response()->json($details);
    }

    public function getAccountData(Request $request){
        $details = [];

        if($request->arr_type){
            foreach($request->arr_type as $key => $row){
                if($row == 'purchase_orders'){
                    $data = PurchaseOrder::find($request->arr_id[$key]);

                    $arrChecklist = [];

                    $list_items = '<ol>';

                    foreach($data->purchaseOrderDetail as $key => $rowdetail){
                        $item_code = $rowdetail->item()->exists() ? $rowdetail->item->code : ($rowdetail->coa()->exists() ? $rowdetail->coa->code : '');
                        $item_name = $rowdetail->item()->exists() ? $rowdetail->item->name : ($rowdetail->coa()->exists() ? $rowdetail->coa->name : '');
                        $item_unit = $rowdetail->item()->exists() ? $rowdetail->itemUnit->unit->code : '-';
                        $list_items .= '<li>'.$item_code.' - '.$item_name.' Qty : '.CustomHelper::formatConditionalQty($rowdetail->qty).' '.$item_unit.' Total '.number_format($rowdetail->subtotal,2,',','.').' PPN '.number_format($rowdetail->tax,2,',','.').' PPh '.number_format($rowdetail->wtax,2,',','.').' Grandtotal '.number_format($rowdetail->grandtotal,2,',','.').'</li>';
                    }

                    $list_items .= '</ol>';

                    $details[] = [
                        'id'            => $data->id,
                        'po_code'       => CustomHelper::encrypt($data->code),
                        'po_no'         => $data->code,
                        'post_date'     => date('d/m/Y',strtotime($data->post_date)),
                        'delivery_date' => date('d/m/Y',strtotime($data->delivery_date)),
                        'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                        'list_items'    => $list_items,
                        'note'          => $data->note ? $data->note : '',
                        'type'          => $data->getTable(),
                        'total'         => number_format($data->total,2,',','.'),
                        'tax'           => number_format($data->tax,2,',','.'),
                        'wtax'          => number_format($data->wtax,2,',','.'),
                        'checklist'     => $arrChecklist,
                        'payment_type'  => '',
                        'currency_rate' => number_format($data->currency_rate,2,',','.'),
                        'currency_id'   => $data->currency_id,
                    ];
                }elseif($row == 'fund_requests'){
                    $data = FundRequest::find($request->arr_id[$key]);
                    $list_items = '<ol>';

                    $arrChecklist = [];

                    foreach($data->checklistDocumentList as $row){
                        $arrChecklist[] = [
                            'id'    => $row->checklist_document_id,
                            'title' => $row->checklistDocument->title,
                            'note'  => $row->note ? $row->note : '',
                        ];
                    }

                    foreach($data->fundRequestDetail as $key => $rowdetail){
                        $item_unit = $rowdetail->unit->code;
                        $details[] = [
                            'id'            => $rowdetail->id,
                            'po_code'       => CustomHelper::encrypt($data->code),
                            'po_no'         => $data->code,
                            'post_date'     => date('d/m/Y',strtotime($data->post_date)),
                            'delivery_date' => date('d/m/Y',strtotime($data->required_date)),
                            'grandtotal'    => number_format($rowdetail->grandtotal,2,',','.'),
                            'list_items'    => $rowdetail->note.' Qty : '.CustomHelper::formatConditionalQty($rowdetail->qty).' '.$item_unit,
                            'note'          => $rowdetail->note ? $rowdetail->note : '',
                            'type'          => $rowdetail->getTable(),
                            'total'         => number_format($rowdetail->total,2,',','.'),
                            'tax'           => number_format($rowdetail->tax,2,',','.'),
                            'wtax'          => number_format($rowdetail->wtax,2,',','.'),
                            'checklist'     => $arrChecklist,
                            'payment_type'  => $data->payment_type,
                            'currency_rate' => number_format($data->currency_rate,2,',','.'),
                            'currency_id'   => $data->currency_id,
                        ];
                    }
                }
            }
        }
        
        $response['details'] = $details;

        return response()->json($response);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'type',
            'document',
            'post_date',
            'top',
            'currency_id',
            'currency_rate',
            'note',
            'subtotal',
            'discount',
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

        $total_data = PurchaseDownPayment::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = PurchaseDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($search, $request){
                                $query->whereHas('purchaseOrder',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('supplier',function($query) use($search, $request){
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

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->supplier_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }
                
                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
                
                if($request->is_tax){
                    if($request->is_tax == '1'){
                        $query->whereNotNull('is_tax');
                    }else{
                        $query->whereNull('is_tax');
                    }
                }

                if($request->is_include_tax){
                    $query->where('is_include_tax',$request->is_include_tax);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->status_document == 1 &&$request->status_document){
                    $query->where('balance_status',$request->status_document);
                }

                if(!$request->modedata){
                    
                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
                    $query->where('user_id',session('bo_id'));
                    
                }
            })
            /* ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')") */
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseDownPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('subtotal', 'like', "%$search%")
                            ->orWhere('discount', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($search, $request){
                                $query->whereHas('purchaseOrder',function($query) use($search, $request){
                                    $query->where('code', 'like', "%$search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('supplier',function($query) use($search, $request){
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

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->supplier_id){
                    $query->whereIn('account_id',$request->supplier_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
                
                if($request->is_tax){
                    if($request->is_tax == '1'){
                        $query->whereNotNull('is_tax');
                    }else{
                        $query->whereNull('is_tax');
                    }
                }

                if($request->is_include_tax){
                    $query->where('is_include_tax',$request->is_include_tax);
                }
                
                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->status_document == 1 &&$request->status_document){
                    $query->where('balance_status',$request->status_document);
                }

                if(!$request->modedata){
                    
                    /*if(session('bo_position_id') == ''){
                        $query->where('user_id',session('bo_id'));
                    }else{
                        $query->whereHas('user', function ($subquery) {
                            $subquery->whereHas('position', function($subquery1) {
                                $subquery1->where('division_id',session('bo_division_id'));
                            });
                        });
                    }*/
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
                $nodis = '';
                if($val->isOpenPeriod()){
                    $dis = 'style="cursor: default;
                    pointer-events: none;
                    color: #9f9f9f !important;
                    background-color: #dfdfdf !important;
                    box-shadow: none;"';
                }else{
                    $nodis = 'style="cursor: default;
                    pointer-events: none;
                    color: #9f9f9f !important;
                    background-color: #dfdfdf !important;
                    box-shadow: none;"';
                }
                if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light grey darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $total_invoice = $val->totalInvoice();
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name ?? '',
                    $val->supplier->name ?? '',
                    $val->company->name,
                    $val->type(),
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->top,
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    $val->note,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($total_invoice,2,',','.'),
                    number_format($val->grandtotal - $total_invoice,2,',','.'),
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
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light cyan darken-4 white-tex btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" '.$dis.' onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1  btn-small btn-flat waves-effect waves-light purple darken-2 white-text" data-popup="tooltip" title="Cancel" onclick="cancelStatus(`' . CustomHelper::encrypt($val->code) . '`)" '.$nodis.'><i class="material-icons dp48">cancel</i></button>
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
           /*  'code'			            => $request->temp ? ['required', Rule::unique('purchase_down_payments', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:purchase_down_payments,code',
			 */'supplier_id' 				=> 'required',
			'type'                      => 'required',
            'top'                       => 'required',
            'company_id'                => 'required',
            'post_date'                 => 'required',
            'currency_id'               => 'required',
            'currency_rate'             => 'required',
            'subtotal'                  => 'required',
            'note'                      => 'required',
		], [
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            /* 'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.', */
            'code.unique'                       => 'Kode telah dipakai',
			'supplier_id.required' 				=> 'Supplier tidak boleh kosong.',
			'type.required'                     => 'Tipe tidak boleh kosong',
            'top.required'                      => 'TOP tidak boleh kosong',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'post_date.required'                => 'Tgl post tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'subtotal.required'                 => 'Subtotal tidak boleh kosong.',
            'note.required'                     => 'Keterangan tidak boleh kosong.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
            $total = 0;
            $tax = 0;
            $wtax = 0;
            $grandtotal = 0;

            if($request->arr_code){
                foreach($request->arr_code as $key => $row){
                    if($request->arr_type[$key] == 'purchase_orders'){
                        $po = PurchaseOrder::find($row);
                        $bobot = str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])) / $po->grandtotal;
                        $total += $bobot * $po->total;
                        $tax += $bobot * $po->tax;
                        $wtax += $bobot * $po->wtax;
                    }elseif($request->arr_type[$key] == 'fund_request_details'){
                        $fr = FundRequestDetail::find($row);
                        $bobot = str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])) / $fr->grandtotal;
                        $total += $bobot * $fr->total;
                        $tax += $bobot * $fr->tax;
                        $wtax += $bobot * $fr->wtax;
                    }
                }
            }

            $subtotal = str_replace(',','.',str_replace('.','',$request->subtotal));
            $discount = str_replace(',','.',str_replace('.','',$request->discount));

            $grandtotal = $subtotal - $discount;

            if($grandtotal <= 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Grandtotal tidak boleh kurang dari sama dengan 0.'
                ]);
            }

			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->temp))->first();

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
                    if(!CustomHelper::checkLockAcc($request->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                    if($approved && !$revised){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Purchase Down Payment telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

                        CustomHelper::removeDeposit($query->account_id,$query->grandtotal);

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/purchase_down_payments');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->supplier_id;
                        $query->type = $request->type;
                        $query->company_id = $request->company_id;
                        $query->document = $document;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->post_date = $request->post_date;
                        $query->note = $request->note;
                        $query->subtotal = round($subtotal,2);
                        $query->discount = $discount;
                        $query->total = round($total,2);
                        $query->tax = round($tax,2);
                        $query->wtax = round($wtax,2);
                        $query->grandtotal = round($grandtotal,2);
                        $query->status = '1';
                        $query->top = $request->top;

                        $query->save();

                        foreach($query->purchaseDownPaymentDetail as $row){
                            $row->delete();
                        }

                        $query->checklistDocumentList()->delete();

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status AP Down Payment sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
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
                    $newCode=PurchaseDownPayment::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = PurchaseDownPayment::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->supplier_id,
                        'type'	                    => $request->type,
                        'company_id'                => $request->company_id,
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/purchase_down_payments') : NULL,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'post_date'                 => $request->post_date,
                        'note'                      => $request->note,
                        'subtotal'                  => round($subtotal,2),
                        'discount'                  => $discount,
                        'total'                     => round($total,2),
                        'tax'                       => round($tax,2),
                        'wtax'                      => round($wtax,2),
                        'grandtotal'                => round($grandtotal,2),
                        'status'                    => '1',
                        'top'                       => $request->top,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {
                    if($request->arr_code){
                        foreach($request->arr_code as $key => $row){
                            PurchaseDownPaymentDetail::create([
                                'purchase_down_payment_id'      => $query->id,
                                'purchase_order_id'             => $request->arr_type[$key] == 'purchase_orders' ? $row : NULL,
                                'fund_request_detail_id'        => $request->arr_type[$key] == 'fund_request_details' ? $row : NULL,
                                'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                                'note'                          => $request->arr_note[$key]
                            ]);
                        }
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

                CustomHelper::sendApproval('purchase_down_payments',$query->id,$query->note);
                CustomHelper::sendNotification('purchase_down_payments',$query->id,'Pengajuan AP Down Payment No. '.$query->code,$query->note,session('bo_id'));                

                activity()
                    ->performedOn(new PurchaseDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit purchase order down payment.');

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
        $menu = $this->menu;

        $data   = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        $canceled = '';
        if ($data->cancelDocument()->exists()) {
            $canceled .= '<span style="color: red;">|| Tanggal Cancel: ' . $data->cancelDocument->post_date .  ' || Void User: ' . $data->cancelDocument->user->name.' || Note:' . $data->void_note.'</span>' ;
        }
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
           $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser.'</span>';
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="10">Daftar Order Pembelian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">PO/FR No.</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Tgl.Kirim/Tgl.Dipakai</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Total DP</th>
                            </tr>
                        </thead><tbody>';
        $totals=0;
        $totalnominal=0;
        if(count($data->purchaseDownPaymentDetail) > 0){
            foreach($data->purchaseDownPaymentDetail as $key => $row){
                if($row->purchaseOrder()->exists()){
                    $totals+=$row->purchaseOrder->grandtotal;
                    $totalnominal+=$row->nominal;
                    $string .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td class="center-align">'.$row->purchaseOrder->code.'</td>
                        <td class="center-align">'.date('d/m/Y',strtotime($row->purchaseOrder->post_date)).'</td>
                        <td class="center-align">'.date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)).'</td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="right-align">'.number_format($row->purchaseOrder->grandtotal,2,',','.').'</td>
                        <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                    </tr>';
                }
                if($row->fundRequestDetail()->exists()){
                    $totals+=$row->fundRequestDetail->grandtotal;
                    $totalnominal+=$row->nominal;
                    $string .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td class="center-align">'.$row->fundRequestDetail->fundRequest->code.'</td>
                        <td class="center-align">'.date('d/m/Y',strtotime($row->fundRequestDetail->fundRequest->post_date)).'</td>
                        <td class="center-align">'.date('d/m/Y',strtotime($row->fundRequestDetail->fundRequest->required_date)).'</td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="right-align">'.number_format($row->fundRequestDetail->grandtotal,2,',','.').'</td>
                        <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                    </tr>';
                }
            }
            $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="5"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalnominal, 2, ',', '.') . '</td>
            </tr>  
        ';
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="8">Data referensi purchase tidak ditemukan.</td>
            </tr>';
        }
        
        $string .= '</tbody></table></div><div class="col s12 mt-2"><h6>Daftar Lampiran</h6>';

        foreach($menu->checklistDocument as $row){
            $rowceklist = $row->checkDocument($data->getTable(),$data->id);
            $string .= '<label style="margin: 0 5px 0 0;">
            <input class="validate" required="" type="checkbox" value="{{ $row->id }}" '.($rowceklist ? 'checked' : '').'>
            <span>'.$row->title.' ('.$row->type().')'.'</span>
            '.($rowceklist ? $rowceklist->note : '').'
            </label>';
        }

        $string .= '</div>';

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
            $string.= '<li>'.$data->used->lookable->user->name.' - Tanggal Dipakai: '.$data->used->lookable->post_date.' Keterangan:'.$data->used->lookable->note.'</li>';
        }
        $string.='</ol><div class="col s12 mt-2" style="font-weight:bold;color:red;"> Jika ingin dihapus hubungi tim EDP dan info kode dokumen yang terpakai.</div></div>';
		
        return response()->json($string);
    }

    public function approval(Request $request,$id){
        
        $pdp = PurchaseDownPayment::where('code',CustomHelper::decrypt($id))->first();
                
        if($pdp){
            $data = [
                'title'     => 'Print Purchase Down Payment',
                'data'      => $pdp
            ];

            return view('admin.approval.purchase_down_payment', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $pdp = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $pdp['code_place_id'] = substr($pdp->code,7,2);
        $pdp['supplier_name'] = $pdp->supplier->name;
        $pdp['subtotal'] = number_format($pdp->subtotal,2,',','.');
        $pdp['discount'] = number_format($pdp->discount,2,',','.');
        $pdp['total'] = number_format($pdp->total,2,',','.');
        $pdp['tax'] = number_format($pdp->tax,2,',','.');
        $pdp['wtax'] = number_format($pdp->wtax,2,',','.');
        $pdp['grandtotal'] = number_format($pdp->grandtotal,2,',','.');
        $pdp['is_include_tax'] = $pdp->is_include_tax ? $pdp->is_include_tax : '0';
        $pdp['currency_rate'] = number_format($pdp->currency_rate,2,',','.');

        $arr = [];
        $arrChecklist = [];

        foreach($pdp->checklistDocumentList as $row){
            $arrChecklist[] = [
                'id'    => $row->checklist_document_id,
                'note'  => $row->note ? $row->note : '',
            ];
        }

        foreach($pdp->purchaseDownPaymentDetail as $row){

            $list_items = '<ol>';

            if($row->purchaseOrder()->exists()){
                foreach($row->purchaseOrder->purchaseOrderDetail as $key => $rowdetail){
                    $item_code = $rowdetail->item()->exists() ? $rowdetail->item->code : ($rowdetail->coa()->exists() ? $rowdetail->coa->code : '');
                    $item_name = $rowdetail->item()->exists() ? $rowdetail->item->name : ($rowdetail->coa()->exists() ? $rowdetail->coa->name : '');
                    $item_unit = $rowdetail->item()->exists() ? $rowdetail->itemUnit->unit->code : '-';
                    $list_items .= '<li>'.$item_code.' - '.$item_name.' Qty : '.CustomHelper::formatConditionalQty($rowdetail->qty).' '.$item_unit.' Total '.number_format($rowdetail->subtotal,2,',','.').' PPN '.number_format($rowdetail->tax,2,',','.').' PPh '.number_format($rowdetail->wtax,2,',','.').' Grandtotal '.number_format($rowdetail->grandtotal,2,',','.').'</li>';
                }
            }elseif($row->fundRequestDetail()->exists()){
                
            }

            $list_items .= '</ol>';

            $arr[] = [
                'id'                        => $row->purchase_order_id ?? $row->fund_request_detail_id,
                'type'                      => $row->purchase_order_id ? $row->purchaseOrder->getTable() : $row->fundRequestDetail->getTable(),
                'purchase_order_id'         => $row->purchase_order_id ?? $row->fund_request_detail_id,
                'purchase_order_code'       => $row->purchaseOrder()->exists() ? $row->purchaseOrder->code : $row->fundRequestDetail->fundRequest->code,
                'purchase_order_encrypt'    => $row->purchaseOrder()->exists() ? CustomHelper::encrypt($row->purchaseOrder->code) : CustomHelper::encrypt($row->fundRequestDetail->fundRequest->code),
                'post_date'                 => $row->purchaseOrder()->exists() ? date('d/m/Y',strtotime($row->purchaseOrder->post_date)) : date('d/m/Y',strtotime($row->fundRequestDetail->fundRequest->post_date)),
                'delivery_date'             => $row->purchaseOrder()->exists() ? date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)) : date('d/m/Y',strtotime($row->fundRequestDetail->fundRequest->required_date)),
                'note'                      => $row->note ? $row->note : '',
                'total'                     => $row->purchaseOrder()->exists() ? number_format($row->purchaseOrder->grandtotal,2,',','.') : number_format($row->fundRequestDetail->grandtotal,2,',','.'),
                'tax'                       => $row->purchaseOrder()->exists() ? number_format($row->purchaseOrder->tax,2,',','.') : number_format($row->fundRequestDetail->tax,2,',','.'),
                'wtax'                      => $row->purchaseOrder()->exists() ? number_format($row->purchaseOrder->wtax,2,',','.') : number_format($row->fundRequestDetail->wtax,2,',','.'),
                'grandtotal'                => $row->purchaseOrder()->exists() ? number_format($row->purchaseOrder->grandtotal,2,',','.') : number_format($row->fundRequestDetail->grandtotal,2,',','.'),
                'total_dp'                  => number_format($row->nominal,2,',','.'),
                'list_items'                => $list_items,
            ];
        }

        $pdp['details'] = $arr;
        $pdp['checklist'] = $arrChecklist;
        				
		return response()->json($pdp);
    }

    public function voidStatus(Request $request){
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    'message' => 'Data telah digunakan pada Payment Request / A/P Invoice sebagai DP.'
                ];
            }else{
                
                CustomHelper::removeDeposit($query->account_id,$query->total);
                CustomHelper::removeApproval('purchase_down_payments',$query->id);
                CustomHelper::removeJournal('purchase_down_payments',$query->id);

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new PurchaseDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the purchase order down payment data');
    
                CustomHelper::sendNotification('purchase_down_payments',$query->id,'AP Down Payment No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
    
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

    public function cancelStatus(Request $request){
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
        if($query) {

            if(!CustomHelper::checkLockAcc($request->cancel_date)){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Transaksi pada tanggal cancel void telah ditutup oleh Akunting.'
                ]);
            }

            if(in_array($query->status,['4','5','8'])){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah ditutup anda tidak bisa menutup lagi.'
                ];
            }elseif($query->hasChildDocumentExceptAdjustRate()){
                $response = [
                    'status'  => 500,
                    'message' => 'Data telah digunakan pada Payment Request / A/P Invoice sebagai DP.'
                ];
            }else{
                
                CustomHelper::removeDeposit($query->account_id,$query->grandtotal);
                CustomHelper::removeApproval($query->getTable(),$query->id);

                $query->update([
                    'status'    => '8',
                ]);

                $cd = CancelDocument::create([
                    'code'          => CancelDocument::generateCode('CAPD',substr($query->code,7,2),$request->cancel_date),
                    'user_id'       => session('bo_id'),
                    'post_date'     => $request->cancel_date,
                    'lookable_type' => $query->getTable(),
                    'lookable_id'   => $query->id,
                ]);

                CustomHelper::cancelJournal($cd,$request->cancel_date);
    
                activity()
                    ->performedOn(new PurchaseDownPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void cancel the purchase order down payment data');
    
                CustomHelper::sendNotification('purchase_down_payments',$query->id,'AP Down Payment No. '.$query->code.' telah ditutup dengan tombol cancel void.','AP Down Payment No. '.$query->code.' telah ditutup dengan tombol cancel void.',$query->user_id);
    
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
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();

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
                'message' => 'Jurnal sudah dalam progres, anda tidak bisa melakukan perubahan.'
            ]);
        }
        
        if($query->delete()) {

            $query->update([
                'delete_id'     => session('bo_id'),
                'delete_note'   => $request->msg,
            ]);

            CustomHelper::removeDeposit($query->account_id,$query->grandtotal);
            CustomHelper::removeApproval('purchase_down_payments',$query->id);

            $query->purchaseDownPaymentDetail()->delete();

            activity()
                ->performedOn(new PurchaseOrder())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the purchase order down payment data');

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

    public function printALL(Request $request){

        $data = [
            'title' => 'AP DOWN PAYMENT REPORT',
            'data' => PurchaseDownPayment::where(function ($query) use ($request) {
                if($request->search) {
                    $query->where(function($query) use ($request) {
                        $query->where('code', 'like', "%$request->search%")
                            ->orWhere('post_date', 'like', "%$request->search%")
                            ->orWhere('grandtotal', 'like', "%$request->search%")
                            ->orWhere('note', 'like', "%$request->search%")
                            ->orWhereHas('purchaseDownPaymentDetail',function($query) use($request){
                                $query->whereHas('purchaseOrder',function($query) use($request){
                                    $query->where('code', 'like', "%$request->search%");
                                });
                            })
                            ->orWhereHas('user',function($query) use($request){
                                $query->where('name','like',"%$request->search%")
                                    ->orWhere('employee_no','like',"%$request->search%");
                            });
                    });
                }

                if($request->status){
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->supplier){
                    $query->whereIn('account_id',$request->supplier);
                }
                
                if($request->company){
                    $query->where('company_id',$request->company);
                }          
                
                if($request->currency){
                    $query->whereIn('currency_id',$request->currency);
                }

                if($request->is_tax){
                    if($request->is_tax == '1'){
                        $query->whereNotNull('is_tax');
                    }else{
                        $query->whereNull('is_tax');
                    }
                }

                if($request->is_include_tax){
                    $query->where('is_include_tax',$request->is_include_tax);
                }
            })
            ->get()
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
         
        $pdf = PDF::loadView('admin.print.purchase.down_payment', $data)->setPaper('a4', 'portrait');

        $content = $pdf->download()->getOriginalContent();
        $randomString = Str::random(10); 


        $filePath = 'public/pdf/' . $randomString . '.pdf';
        

        Storage::put($filePath, $content);
        
        $document_po = asset(Storage::url($filePath));
        $var_link=$document_po;


        return $document_po;
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
                $pr = PurchaseDownPayment::where('code',$row)->first();
                
                if($pr){
                    
                    $pdf = PrintHelper::print($pr,'AP Down Payment','a4','portrait','admin.print.purchase.down_payment_individual');
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


            $document_po = PrintHelper::savePrint($result);

            $response =[
                'status'=>200,
                'message'  =>$document_po
            ];
        }
        
		
		return response()->json($response);
    }

    public function printIndividual(Request $request,$id){
        
        $pr = PurchaseDownPayment::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');       
        if($pr){
            $pdf = PrintHelper::print($pr,'AP Down Payment','a4','portrait','admin.print.purchase.down_payment_individual');
    
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
                        $query = PurchaseDownPayment::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'AP Down Payment','a4','portrait','admin.print.purchase.down_payment_individual');
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
                        $query = PurchaseDownPayment::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $pdf = PrintHelper::print($query,'AP Down Payment','a4','portrait','admin.print.purchase.down_payment_individual');
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

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';

		return Excel::download(new ExportPurchaseDownPayment($post_date,$end_date,$mode), 'purchase_down_payment_'.uniqid().'.xlsx');
    }

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
        
        
        $data_go_chart=[];
        $data_link=[];


        if($query) {
           
            $data_purchase_dp = [
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    ['name'=> "Nominal :".formatNominal($query).number_format($query->grandtotal,2,',','.')],
                 ],
                'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($query->code),           
            ];
            $data_go_chart[]=$data_purchase_dp;
            
            $result = TreeHelper::treeLoop1($data_go_chart,$data_link,'data_id_dp',$query->id);
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
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = PurchaseDownPayment::where('code',CustomHelper::decrypt($id))->first();
        if($query->journal()->exists()){
            $response = [
                'title'     => 'Journal',
                'status'    => 200,
                'message'   => $query->journal,
                'user'      => $query->user->name,
                'reference' => $query->code,
                'company'   => $query->company()->exists() ? $query->company->name : '-',
                'code'      => $query->journal->code,
                'note'      => $query->note,
                'post_date' => date('d/m/Y',strtotime($query->post_date)),
            ];
            $string='';
            foreach($query->journal->journalDetail()->where(function($query){
            $query->whereHas('coa',function($query){
                $query->orderBy('code');
            })
            ->orderBy('type');
        })->get() as $key => $row){
                if($row->type == '1'){
                    $total_debit_asli += $row->nominal_fc;
                    $total_debit_konversi += $row->nominal;
                }
                if($row->type == '2'){
                    $total_kredit_asli += $row->nominal_fc;
                    $total_kredit_konversi += $row->nominal;
                }
                
                $string .= '<tr>
                    <td class="center-align">'.($key + 1).'</td>
                    <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                    <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                    <td class="center-align">'.($row->place_id ? $row->place->code : '-').'</td>
                    <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                    <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                    <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                    <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                    <td class="center-align">'.($row->project_id ? $row->project->name : '-').'</td>
                    <td class="center-align">'.($row->note ? $row->note : '').'</td>
                    <td class="center-align">'.($row->note2 ? $row->note2 : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                    <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                </tr>';

                
            }

            if($query->cancelDocument()->exists()){
                foreach($query->cancelDocument->journal->journalDetail()->where(function($query){
                    $query->whereHas('coa',function($query){
                        $query->orderBy('code');
                    })
                    ->orderBy('type');
                })->get() as $key => $row){
                    if($row->type == '1'){
                        $total_debit_asli += $row->nominal_fc;
                        $total_debit_konversi += $row->nominal;
                    }
                    if($row->type == '2'){
                        $total_kredit_asli += $row->nominal_fc;
                        $total_kredit_konversi += $row->nominal;
                    }
                    $string .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td>'.$row->coa->code.' - '.$row->coa->name.'</td>
                        <td class="center-align">'.($row->account_id ? $row->account->name : '-').'</td>
                        <td class="center-align">'.($row->place_id ? $row->place->code : '-').'</td>
                        <td class="center-align">'.($row->line_id ? $row->line->name : '-').'</td>
                        <td class="center-align">'.($row->machine_id ? $row->machine->name : '-').'</td>
                        <td class="center-align">'.($row->department_id ? $row->department->name : '-').'</td>
                        <td class="center-align">'.($row->warehouse_id ? $row->warehouse->name : '-').'</td>
                        <td class="center-align">'.($row->project_id ? $row->project->name : '-').'</td>
                        <td class="center-align">'.($row->note ? $row->note : '').'</td>
                        <td class="center-align">'.($row->note2 ? $row->note2 : '').'</td>
                        <td class="right-align">'.($row->type == '1' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                        <td class="right-align">'.($row->type == '2' ? number_format($row->nominal_fc,2,',','.') : '').'</td>
                        <td class="right-align">'.($row->type == '1' ? number_format($row->nominal,2,',','.') : '').'</td>
                        <td class="right-align">'.($row->type == '2' ? number_format($row->nominal,2,',','.') : '').'</td>
                    </tr>';
                }
            }

            $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="11"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_asli, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_debit_konversi, 2, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($total_kredit_konversi, 2, ',', '.') . '</td>
            </tr>';
            $response["tbody"] = $string; 
        }else{
            $response = [
                'status'  => 500,
                'message' => 'Data masih belum di approve.'
            ]; 
        }
        return response()->json($response);
    }

    public function getOutstanding(Request $request){
		return Excel::download(new ExportOutstandingDP(), 'outstanding_purchase_down_payment'.uniqid().'.xlsx');
    }

    public function done(Request $request){
        $query_done = PurchaseDownPayment::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new PurchaseDownPayment())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Purchase Down Payment data');
    
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
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $status_dokumen = $request->status_dokumen? $request->status_dokumen : '';
        $company = $request->company ? $request->company : '';
        $type_pay = $request->type_pay ? $request->type_pay : '';
        $supplier = $request->supplier? $request->supplier : '';
        $currency = $request->currency ? $request->currency : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
		$modedata = $request->modedata? $request->modedata : '';
      
		return Excel::download(new ExportDownPaymentTransactionPage($search,$status,$company,$type_pay,$supplier,$currency,$end_date,$start_date,$modedata,$status_dokumen), 'purchase_down_payment'.uniqid().'.xlsx');
    }
}
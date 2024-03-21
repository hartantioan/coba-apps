<?php

namespace App\Http\Controllers\Finance;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\LandedCost;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestCross;
use App\Models\PaymentRequestDetail;
use App\Models\GoodIssueRequest;
use App\Models\CloseBill;
use App\Models\GoodScale;
use App\Models\GoodIssue;
use App\Models\InventoryTransferOut;
use App\Models\MaterialRequest;


use App\Models\Item;
use App\Models\Line;
use App\Models\Machine;

use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
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
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceDetail;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseDownPaymentDetail;
use App\Models\Currency;
use App\Models\ItemCogs;
use App\Helpers\CustomHelper;
use App\Exports\ExportPaymentRequest;
use App\Models\Place;
use App\Models\User;
use App\Models\Department;
use App\Models\Division;
use App\Models\Menu;
use App\Models\MarketingOrderMemo;
use App\Models\MenuUser;
use App\Models\OutgoingPayment;
use App\Models\PaymentRequestCost;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use App\Exports\ExportPaymentRequestTransactionPage;

class PaymentRequestController extends Controller
{

    protected $dataplaces, $dataplacecode, $datawarehouse;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouse = $user ? $user->userWarehouseArray() : [];
    }
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Payment Request',
            'content'       => 'admin.finance.payment_request',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcodePay'    => 'OPYM-'.date('y'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'line'          => Line::where('status','1')->get(),
            'machine'       => Machine::where('status','1')->get(),
            'department'    => Division::where('status','1')->get(),
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = PaymentRequest::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getCodePay(Request $request){
        $code = OutgoingPayment::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'coa_source_id',
            'payment_type',
            'payment_no',
            'post_date',
            'pay_date',
            'currency_id',
            'currency_rate',
            'admin',
            'grandtotal',
            'document',
            'account_bank',
            'account_no',
            'account_name',
            'note',
            'is_reimburse',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PaymentRequest::/* whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")-> */where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = PaymentRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('admin', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('account_bank', 'like', "%$search%")
                            ->orWhere('account_no', 'like', "%$search%")
                            ->orWhere('account_name', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('paymentRequestDetail',function($query) use($search, $request){
                                $query->whereHasMorph('lookable',
                                    [FundRequest::class, PurchaseDownPayment::class, PurchaseInvoice::class],
                                    function (Builder $query) use ($search) {
                                        $query->where('code','like',"%$search%");
                                    });
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

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
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

        $total_filtered = PaymentRequest::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('admin', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('account_bank', 'like', "%$search%")
                            ->orWhere('account_no', 'like', "%$search%")
                            ->orWhere('account_name', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('paymentRequestDetail',function($query) use($search, $request){
                                $query->whereHasMorph('lookable',
                                    [FundRequest::class, PurchaseDownPayment::class, PurchaseInvoice::class],
                                    function (Builder $query) use ($search) {
                                        $query->where('code','like',"%$search%");
                                    });
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

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->currency_id){
                    $query->whereIn('currency_id',$request->currency_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
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
                if($val->journal()->exists()){
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue darken-3 white-tex btn-small" data-popup="tooltip" title="Journal" onclick="viewJournal(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">note</i></button>';
                }else{
                    $btn_jurnal ='<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light grey darken-3 white-tex btn-small disabled" data-popup="tooltip" title="Journal" ><i class="material-icons dp48">note</i></button>';
                }
                $response['data'][] = [
                    '<button class="btn-floating green btn-small" data-popup="tooltip" title="Lihat Detail" onclick="rowDetail(`'.CustomHelper::encrypt($val->code).'`)"><i class="material-icons">speaker_notes</i></button>',
                    $val->code,
                    $val->user->name,
                    $val->account()->exists() ? $val->account->name : '',
                    $val->company->name,
                    $val->coa_source_id ? $val->coaSource->name : '-',
                    $val->paymentType(),
                    $val->payment_no,
                    date('d/m/Y',strtotime($val->post_date)),
                    date('d/m/Y',strtotime($val->pay_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->rounding,2,',','.'),
                    number_format($val->admin,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($val->payment,2,',','.'),
                    number_format($val->balance,2,',','.'),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->account_bank,
                    $val->account_no,
                    $val->account_name,
                    $val->note,
                    $val->isReimburse(),
                    $val->status(),
                    $val->balance == 0 ? 'Terbayar' : ($val->status == '2' && !$val->outgoingPayment()->exists() ?
                    '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small" data-popup="tooltip" title="Bayar" onclick="cashBankOut(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">screen_share</i></button>' : ($val->outgoingPayment()->exists() ? $val->outgoingPayment->code : $val->statusRaw() )),
                    '
                    '.$btn_jurnal.'
                        <button type="button" class="btn-floating mb-1 btn-flat purple accent-2 white-text btn-small" data-popup="tooltip" title="Selesai" onclick="done(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">gavel</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light amber accent-2 white-tex btn-small" data-popup="tooltip" title="Tutup" onclick="voidStatus(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">close</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light red accent-2 white-text btn-small" data-popup="tooltip" title="Delete" onclick="destroy(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">delete</i></button>
					',
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
        $details = [];

        $payments = [];

        if($data->type == '1'){
            $op = OutgoingPayment::where('account_id',$data->id)
            ->whereIn('status',['2','3'])
            ->whereHas('paymentRequest',function($query){
                $query->whereHas('paymentRequestDetail',function($query){
                    $query->whereHasMorph('lookable',
                    [FundRequest::class],
                    function (Builder $query){
                        $query->where('document_status','3');
                    });
                });
            })->get();
        }else{
            $op = OutgoingPayment::whereIn('status',['2','3'])->whereHas('account',function($query){
                $query->where('type','1');
            })->whereHas('paymentRequest',function($query){
                $query->whereHas('paymentRequestDetail',function($query){
                    $query->whereHasMorph('lookable',
                    [FundRequest::class],
                    function (Builder $query){
                        $query->where('document_status',"3");
                    });
                });
            })->get();
        }
        
        if(isset($op)){
            foreach($op as $row){
                $balance = $row->balancePaymentIncoming();
                if(!$row->used()->exists() && $balance > 0){
                    $payments[] = [
                        'id'                    => $row->id,
                        'code'                  => $row->code,
                        'name'                  => $row->account->name,
                        'payment_request_code'  => $row->paymentRequest->code,
                        'post_date'             => date('d/m/Y',strtotime($row->post_date)),
                        'coa_name'              => $row->coaSource->name,
                        'admin'                 => number_format($row->admin,2,',','.'),
                        'total'                 => number_format($row->total,2,',','.'),
                        'grandtotal'            => number_format($row->grandtotal,2,',','.'),
                        'used'                  => number_format($row->totalUsedCross(),2,',','.'),
                        'balance'               => number_format($balance,2,',','.'),
                    ];
                }
            }
        }

        $data['payments'] = $payments;

        if($data){
            foreach($data->userBank()->orderByDesc('is_default')->get() as $row){
                $banks[] = [
                    'id'        => $row->id,
                    'bank'      => $row->bank,
                    'name'      => $row->name,
                    'no'        => $row->no,
                ];
            }

            foreach($data->fundRequest as $row){
                if(!$row->used()->exists() && $row->balancePaymentRequest() > 0 && $row->document_status !== '1' && $row->status == '2' && $data->type == '1'){
                    $memo = 0;
                    $final = $row->balancePaymentRequest() - $memo;

                    $details[] = [
                        'id'                => $row->id,
                        'type'              => 'fund_requests',
                        'code'              => CustomHelper::encrypt($row->code),
                        'rawcode'           => $row->code,
                        'rawdate'           => $row->post_date,
                        'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                        'due_date'          => date('d/m/Y',strtotime($row->required_date)),
                        'total'             => number_format($row->total,2,',','.'),
                        'tax'               => number_format($row->tax,2,',','.'),
                        'wtax'              => number_format($row->wtax,2,',','.'),
                        'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                        'downpayment'       => number_format(0,2,',','.'),
                        'rounding'          => number_format(0,2,',','.'),
                        'balance'           => number_format($row->balancePaymentRequest(),2,',','.'),
                        'memo'              => number_format($memo,2,',','.'),
                        'final'             => $row->currency->symbol.' '.number_format($final,2,',','.'),
                        'note'              => $row->note ? $row->note : '',
                        'type_fr'           => $row->type,
                        'document_status'   => $row->document_status,
                        'status_document'   => $row->document_status == '2' ? 'LENGKAP' : 'TIDAK LENGKAP',
                    ];
                }
            }

            foreach($data->purchaseDownPayment as $row){
                if(!$row->used()->exists() && $row->balancePaymentRequest() > 0){
                    $memo = 0;
                    $final = $row->grandtotal - $memo;
                    $details[] = [
                        'id'                => $row->id,
                        'type'              => 'purchase_down_payments',
                        'code'              => CustomHelper::encrypt($row->code),
                        'rawcode'           => $row->code,
                        'rawdate'           => $row->post_date,
                        'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                        'due_date'          => date('d/m/Y',strtotime($row->due_date)),
                        'total'             => number_format($row->total,2,',','.'),
                        'tax'               => number_format($row->tax,2,',','.'),
                        'wtax'              => number_format($row->wtax,2,',','.'),
                        'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                        'downpayment'       => number_format(0,2,',','.'),
                        'rounding'          => number_format(0,2,',','.'),
                        'balance'           => number_format($row->grandtotal,2,',','.'),
                        'memo'              => number_format($memo,2,',','.'),
                        'final'             => $row->currency->symbol.' '.number_format($final,2,',','.'),
                        'note'              => $row->note ? $row->note : '',
                        'type_fr'           => '',
                        'document_status'   => '2',
                        'status_document'   => 'LENGKAP',
                    ];
                }
            }

            foreach($data->purchaseInvoice as $row){
                if(!$row->used()->exists() && $row->balancePaymentRequest() > 0){
                    $memo = $row->totalMemo();
                    $final = $row->balance - $memo;
                    $details[] = [
                        'id'                => $row->id,
                        'type'              => 'purchase_invoices',
                        'code'              => CustomHelper::encrypt($row->code),
                        'rawcode'           => $row->code,
                        'rawdate'           => $row->post_date,
                        'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                        'due_date'          => date('d/m/Y',strtotime($row->due_date)),
                        'total'             => number_format($row->total,2,',','.'),
                        'tax'               => number_format($row->tax,2,',','.'),
                        'wtax'              => number_format($row->wtax,2,',','.'),
                        'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                        'downpayment'       => number_format($row->downpayment,2,',','.'),
                        'rounding'          => number_format($row->rounding,2,',','.'),
                        'balance'           => number_format($row->balance,2,',','.'),
                        'memo'              => number_format($memo,2,',','.'),
                        'final'             => $row->currency->symbol.' '.number_format($final,2,',','.'),
                        'note'              => $row->note ? $row->note : '',
                        'type_fr'           => '',
                        'document_status'   => '2',
                        'status_document'   => 'LENGKAP',
                    ];
                }
            }

            foreach($data->marketingOrderMemo()->where('type','3')->get() as $row){
                if(!$row->used()->exists() && $row->balance() > 0){
                    $memo = $row->totalUsed();
                    $final = $row->grandtotal - $memo;
                    $details[] = [
                        'id'                => $row->id,
                        'type'              => 'marketing_order_memos',
                        'code'              => CustomHelper::encrypt($row->code),
                        'rawcode'           => $row->code,
                        'rawdate'           => $row->post_date,
                        'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                        'due_date'          => date('d/m/Y',strtotime($row->post_date)),
                        'total'             => number_format($row->total,2,',','.'),
                        'tax'               => number_format($row->tax,2,',','.'),
                        'wtax'              => number_format(0,2,',','.'),
                        'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                        'downpayment'       => number_format(0,2,',','.'),
                        'rounding'          => number_format(0,2,',','.'),
                        'balance'           => number_format($row->grandtotal,2,',','.'),
                        'memo'              => number_format($memo,2,',','.'),
                        'final'             => 'IDR '.number_format($final,2,',','.'),
                        'note'              => $row->note ? $row->note : '',
                        'type_fr'           => '',
                        'document_status'   => '2',
                        'status_document'   => 'LENGKAP',
                    ];
                }
            }
        }

        $data['banks'] = $banks;

        $collection = collect($details)->sortByDesc('rawdate')->values()->all();

        $data['details'] = $collection;

        return response()->json($data);
    }

    public function getAccountData(Request $request){
        $details = [];
        $payments = [];

        if($request->arr_op_id){
            foreach($request->arr_op_id as $key => $row){
                $op = OutgoingPayment::find(intval($row));
                if($op){
                    $balance = $op->balancePaymentIncoming();
                    if(!$op->used()->exists() && $balance > 0){
                        CustomHelper::sendUsedData($op->getTable(),$op->id,'Form Payment Request');
                        $payments[] = [
                            'id'                    => $op->id,
                            'code'                  => $op->code,
                            'rawcode'               => $op->code,
                            'type'                  => $op->getTable(),
                            'name'                  => $op->account->name,
                            'payment_request_code'  => $op->paymentRequest->code,
                            'post_date'             => date('d/m/Y',strtotime($op->post_date)),
                            'coa_name'              => $op->coaSource->name,
                            'admin'                 => number_format($op->admin,2,',','.'),
                            'total'                 => number_format($op->total,2,',','.'),
                            'grandtotal'            => number_format($op->grandtotal,2,',','.'),
                            'used'                  => number_format($op->totalUsedCross(),2,',','.'),
                            'balance'               => number_format($balance,2,',','.'),
                        ];
                    }
                }
            }
        }

        $user['payments'] = $payments;

        if($request->arr_type){
            foreach($request->arr_type as $key => $row){
                if($row == 'fund_requests'){
                    $data = null;
                    $data = FundRequest::find(intval($request->arr_id[$key]));
                    if($data){
                        if(!$data->used()->exists() && $data->balancePaymentRequest() > 0 && $data->document_status !== '1'){
                            CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Payment Request');
                            if($data->type == '1'){
                                $coa = Coa::where('code','100.01.03.03.02')->where('company_id',$data->company_id)->first();
                            }elseif($data->type == '2'){
                                $coa = Coa::where('code','100.01.03.03.01')->where('company_id',$data->company_id)->first();
                            }
                            $total = $data->balancePaymentRequest();
                            $balanceduplicate = round($total / intval($request->arr_qty_duplicate[$key]),2);

                            $listDetails = [];

                            if($data->document_status == '2'){
                                foreach($data->fundRequestDetail as $rowdetail){
                                    if($rowdetail->total > 0){
                                        $listDetails[] = [
                                            'note'          => $rowdetail->note,
                                            'nominal'       => number_format($rowdetail->total,2,',','.'),
                                            'type'          => '1',
                                            'coa_id'        => '',
                                            'coa_name'      => '',
                                            'place_id'      => $rowdetail->place_id ?? '',
                                            'line_id'       => $rowdetail->line_id ?? '',
                                            'machine_id'    => $rowdetail->machine_id ?? '',
                                            'division_id'   => $rowdetail->division_id ?? '',
                                            'project_id'    => $rowdetail->project_id ?? '',
                                            'project_name'  => $rowdetail->project()->exists() ? $rowdetail->project->name : '',
                                        ];
                                    }

                                    if($rowdetail->tax > 0){
                                        $listDetails[] = [
                                            'note'          => $rowdetail->note,
                                            'nominal'       => number_format($rowdetail->tax,2,',','.'),
                                            'type'          => '1',
                                            'coa_id'        => $rowdetail->taxMaster()->exists() ? $rowdetail->taxMaster->coa_purchase_id : '',
                                            'coa_name'      => $rowdetail->taxMaster()->exists() ? $rowdetail->taxMaster->coaPurchase->code.' - '.$rowdetail->taxMaster->coaPurchase->name : '',
                                            'place_id'      => $rowdetail->place_id ?? '',
                                            'line_id'       => '',
                                            'machine_id'    => '',
                                            'division_id'   => '',
                                            'project_id'    => '',
                                            'project_name'  => '',
                                        ];
                                    }

                                    if($rowdetail->wtax > 0){
                                        $listDetails[] = [
                                            'note'          => $rowdetail->note,
                                            'nominal'       => number_format($rowdetail->wtax,2,',','.'),
                                            'type'          => '2',
                                            'coa_id'        => $rowdetail->wtaxMaster()->exists() ? $rowdetail->wtaxMaster->coa_purchase_id : '',
                                            'coa_name'      => $rowdetail->wtaxMaster()->exists() ? $rowdetail->wtaxMaster->coaPurchase->code.' - '.$rowdetail->wtaxMaster->coaPurchase->name : '',
                                            'place_id'      => $rowdetail->place_id ?? '',
                                            'line_id'       => '',
                                            'machine_id'    => '',
                                            'division_id'   => '',
                                            'project_id'    => '',
                                            'project_name'  => '',
                                        ];
                                    }
                                }
                            }

                            $details[] = [
                                'id'            => $data->id,
                                'type'          => 'fund_requests',
                                'code'          => CustomHelper::encrypt($data->code),
                                'rawcode'       => $data->code,
                                'rawdate'       => $data->post_date,
                                'post_date'     => date('d/m/Y',strtotime($data->post_date)),
                                'due_date'      => date('d/m/Y',strtotime($data->required_date)),
                                'total'         => number_format($data->total,2,',','.'),
                                'tax'           => number_format($data->tax,2,',','.'),
                                'wtax'          => number_format($data->wtax,2,',','.'),
                                'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                                'balance'       => number_format($total,2,',','.'),
                                'balance_duplicate' => number_format($balanceduplicate,2,',','.'),
                                'coa_id'        => $data->type == '1' ? ($data->document_status == '3' ? ($coa ? $coa->id : '') : '') : $coa->id,
                                'coa_name'      => $data->type == '1' ? ($data->document_status == '3' ? ($coa ? $coa->code.' - '.$coa->name : '') : '') : $coa->code.' - '.$coa->name,
                                'memo'          => number_format(0,2,',','.'),
                                'currency_id'   => $data->currency_id,
                                'currency_rate' => number_format($data->currency_rate,2,',','.'),
                                'note'          => $data->note ? $data->note : '',
                                'name_account'  => $data->name_account ?? '',
                                'no_account'    => $data->no_account ?? '',
                                'bank_account'  => $data->bank_account ?? '',
                                'place_id'      => $data->place_id,
                                'department_id' => $data->department_id,
                                'account_code'  => $data->account->employee_no,
                                'remark'        => $data->note,
                                'list_details'  => $listDetails,
                                'document_status' => $data->document_status,
                                'is_reimburse'  => '',
                                'raw_due_date'  => '',
                            ];
                        }
                    }
                }elseif($row == 'purchase_down_payments'){
                    $data = null;
                    $data = PurchaseDownPayment::find(intval($request->arr_id[$key]));
                    if($data){
                        if(!$data->used()->exists() && $data->balancePaymentRequest() > 0){
                            CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Payment Request');
                            $coa = Coa::where('code','200.01.03.01.01')->where('company_id',$data->company_id)->first();
                            $total = $data->balancePaymentRequest();
                            $is_reimburse = '';
                            $name_account = '';
                            $bank_account = '';
                            $no_account = '';
                            $required_date = $data->post_date;
                            foreach($data->purchaseDownPaymentDetail as $row){
                                if($row->fundRequestDetail()->exists()){
                                    $is_reimburse = $row->fundRequestDetail->fundRequest->is_reimburse ?? '';
                                    $name_account = $row->fundRequestDetail->fundRequest->name_account ?? '';
                                    $bank_account = $row->fundRequestDetail->fundRequest->bank_account ?? '';
                                    $no_account = $row->fundRequestDetail->fundRequest->no_account ?? '';
                                    $required_date = $row->fundRequestDetail->fundRequest->required_date ?? '';
                                }
                            }
                            $details[] = [
                                'id'            => $data->id,
                                'type'          => 'purchase_down_payments',
                                'code'          => CustomHelper::encrypt($data->code),
                                'rawcode'       => $data->code,
                                'rawdate'       => $data->post_date,
                                'post_date'     => date('d/m/Y',strtotime($data->post_date)),
                                'due_date'      => '-',
                                'total'         => number_format($data->total,2,',','.'),
                                'tax'           => number_format($data->tax,2,',','.'),
                                'wtax'          => number_format($data->wtax,2,',','.'),
                                'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                                'balance'       => number_format($total,2,',','.'),
                                'balance_duplicate' => number_format($total,2,',','.'),
                                'coa_id'        => $coa ? $coa->id : '',
                                'coa_name'      => $coa ? $coa->code.' - '.$coa->name : '',
                                'memo'          => number_format($data->totalMemo(),2,',','.'),
                                'currency_id'   => $data->currency_id,
                                'currency_rate' => number_format($data->currency_rate,2,',','.'),
                                'note'          => $data->note ? $data->note : '',
                                'name_account'  => $name_account,
                                'no_account'    => $no_account,
                                'bank_account'  => $bank_account,
                                'place_id'      => '',
                                'department_id' => '',
                                'account_code'  => $data->supplier->employee_no,
                                'remark'        => $data->note,
                                'list_details'  => [],
                                'document_status' => '',
                                'is_reimburse'  => $is_reimburse,
                                'raw_due_date'  => $required_date,
                            ];
                        }
                    }
                }elseif($row == 'purchase_invoices'){
                    $data = null;
                    $data = PurchaseInvoice::find(intval($request->arr_id[$key]));
                    if($data){
                        if(!$data->used()->exists() && $data->balance > 0){
                            CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Payment Request');
                            $coa = Coa::where('code','200.01.03.01.01')->where('company_id',$data->company_id)->first();
                            $total = $data->balancePaymentRequest();
                            $details[] = [
                                'id'            => $data->id,
                                'type'          => 'purchase_invoices',
                                'code'          => CustomHelper::encrypt($data->code),
                                'rawcode'       => $data->code,
                                'rawdate'       => $data->post_date,
                                'post_date'     => date('d/m/Y',strtotime($data->post_date)),
                                'due_date'      => date('d/m/Y',strtotime($data->due_date)),
                                'total'         => number_format($data->total,2,',','.'),
                                'tax'           => number_format($data->tax,2,',','.'),
                                'wtax'          => number_format($data->wtax,2,',','.'),
                                'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                                'balance'       => number_format($total,2,',','.'),
                                'balance_duplicate' => number_format($total,2,',','.'),
                                'coa_id'        => $coa ? $coa->id : '',
                                'coa_name'      => $coa ? $coa->code.' - '.$coa->name : '',
                                'memo'          => number_format($data->totalMemo(),2,',','.'),
                                'currency_id'   => $data->currency_id,
                                'currency_rate' => number_format($data->currency_rate,2,',','.'),
                                'note'          => $data->note ? $data->note : '',
                                'name_account'  => '',
                                'no_account'    => '',
                                'bank_account'  => '',
                                'place_id'      => '',
                                'department_id' => '',
                                'account_code'  => $data->account->employee_no,
                                'remark'        => $data->note,
                                'list_details'  => [],
                                'document_status' => '',
                                'is_reimburse'  => '',
                                'raw_due_date'  => '',
                            ];
                        }
                    }
                }elseif($row == 'marketing_order_memos'){
                    $data = null;
                    $data = MarketingOrderMemo::find(intval($request->arr_id[$key]));
                    if($data){
                        if(!$data->used()->exists() && $data->balance() > 0){
                            CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Payment Request');
                            $coa = Coa::where('code','100.01.03.01.01')->where('company_id',$data->company_id)->first();
                            $total = $data->balancePaymentRequest();
                            $details[] = [
                                'id'            => $data->id,
                                'type'          => 'marketing_order_memos',
                                'code'          => CustomHelper::encrypt($data->code),
                                'rawcode'       => $data->code,
                                'rawdate'       => $data->post_date,
                                'post_date'     => date('d/m/Y',strtotime($data->post_date)),
                                'due_date'      => date('d/m/Y',strtotime($data->post_date)),
                                'total'         => number_format($data->total,2,',','.'),
                                'tax'           => number_format($data->tax,2,',','.'),
                                'wtax'          => number_format(0,2,',','.'),
                                'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                                'balance'       => number_format($total,2,',','.'),
                                'balance_duplicate' => number_format($total,2,',','.'),
                                'coa_id'        => $coa ? $coa->id : '',
                                'coa_name'      => $coa ? $coa->code.' - '.$coa->name : '',
                                'memo'          => number_format($data->totalUsed(),2,',','.'),
                                'currency_id'   => 1,
                                'currency_rate' => number_format(1,2,',','.'),
                                'note'          => $data->note ? $data->note : '',
                                'name_account'  => '',
                                'no_account'    => '',
                                'bank_account'  => '',
                                'place_id'      => '',
                                'department_id' => '',
                                'account_code'  => $data->account->employee_no,
                                'remark'        => $data->note,
                                'list_details'  => [],
                                'document_status' => '',
                                'is_reimburse'  => '',
                                'raw_due_date'  => '',
                            ];
                        }
                    }
                }
            }
        }
        
        $user['details'] = $details;

        return response()->json($user);
    }

    public function create(Request $request){
        
        $validation = Validator::make($request->all(), [
            'code'                      => 'required',
            'code_place_id'             => 'required',
            /* 'code'			        => $request->temp ? ['required', Rule::unique('payment_requests', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:payment_requests,code',
			 */'account_id' 			=> 'required',
            'company_id'            => 'required',
            'coa_source_id'         => $request->payment_type == '5' || ($request->payment_type == '6' && str_replace(',','.',str_replace('.','',$request->balance)) <= 0) ? '' : 'required',
            'payment_type'          => 'required',
            'post_date'             => 'required',
            'pay_date'              => $request->payment_type == '5' || ($request->payment_type == '6' && str_replace(',','.',str_replace('.','',$request->balance)) <= 0) ? '' : 'required',
            'currency_id'           => 'required',
            'currency_rate'         => 'required',
            'cost_distribution_id'  => str_replace(',','.',str_replace('.','',$request->admin)) > 0 ? 'required' : '',
            'admin'                 => 'required',
            'grandtotal'            => 'required',
            'arr_type'              => $request->arr_type ? 'required|array' : '',
            'arr_code'              => $request->arr_type ? 'required|array' : '',
            'arr_pay'               => $request->arr_type ? 'required|array' : '',
            'arr_coa'               => $request->arr_type ? 'required|array' : '',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'account_id.required' 			    => 'Supplier/Vendor tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'coa_source_id.required'            => 'Kas/Bank tidak boleh kosong.',
            'payment_type.required'             => 'Tipe pembayaran tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'pay_date.required'                 => 'Tanggal bayar tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
            'cost_distribution_id.required'     => 'Distribusi biaya admin tidak boleh kosong.',
            'admin.required'                    => 'Biaya admin tidak boleh kosong, minimal 0.',
            'grandtotal.required'               => 'Total bayar tidak boleh kosong.',
            'arr_type.required'                 => 'Tipe dokumen tidak boleh kosong.',
            'arr_type.array'                    => 'Tipe dokumen harus dalam bentuk array.',
            'arr_code.required'                 => 'Kode tidak boleh kosong.',
            'arr_code.array'                    => 'Kode harus dalam bentuk array.',
            'arr_pay.required'                  => 'Baris bayar tidak boleh kosong.',
            'arr_pay.array'                     => 'Baris bayar harus dalam bentuk array.',
            'arr_coa.required'                  => 'Baris coa tidak boleh kosong.',
            'arr_coa.array'                     => 'Baris coa harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if($request->arr_code){
                $arr = $request->arr_code;
                $passedDuplicate = true;
                
                $newArr = [];

                foreach($arr as $keymain => $row){
                    $index = -1;
                    foreach($newArr as $key => $rowcek){
                        if($rowcek['code'] == $row){
                            $index = $key;
                        }
                    }
                    if($index >= 0){
                        $newArr[$index]['total'] += str_replace(',','.',str_replace('.','',$request->arr_pay[$keymain]));
                    }else{
                        $newArr[] = [
                            'code'  => $row,
                            'type'  => $request->arr_type[$keymain],
                            'total' => floatval(str_replace(',','.',str_replace('.','',$request->arr_pay[$keymain]))),
                        ];
                    }
                }

                foreach($newArr as $row){
                    if($row['type'] == 'fund_requests'){
                        $fr = FundRequest::where('code',CustomHelper::decrypt($row['code']))->first();
                        $totalBalance = $fr->balancePaymentRequest();
                        if($row['total'] > $totalBalance){
                            $passedDuplicate = false;
                        }
                    }
                }

                if(!$passedDuplicate){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Mohon maaf terdapat total Permohonan Dana melebihi tagihan yang ada.'
                    ]);
                }
            }
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = PaymentRequest::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Payment Request telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','2','3','6'])){

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/payment_requests');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->account_id = $request->account_id;
                        $query->company_id = $request->company_id;
                        $query->coa_source_id = $request->coa_source_id ? $request->coa_source_id : NULL;
                        $query->payment_type = $request->payment_type;
                        $query->payment_no = $request->payment_no ? $request->payment_no : NULL;
                        $query->post_date = $request->post_date;
                        $query->pay_date = $request->pay_date ? $request->pay_date : NULL;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->cost_distribution_id = $request->cost_distribution_id ? $request->cost_distribution_id : NULL;
                        $query->total = str_replace(',','.',str_replace('.','',$request->total));
                        $query->rounding = str_replace(',','.',str_replace('.','',$request->rounding));
                        $query->admin = str_replace(',','.',str_replace('.','',$request->admin));
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->payment = str_replace(',','.',str_replace('.','',$request->payment));
                        $query->balance = str_replace(',','.',str_replace('.','',$request->balance));
                        $query->document = $document;
                        $query->account_bank = $request->account_bank;
                        $query->account_no = $request->account_no;
                        $query->account_name = $request->account_name;
                        $query->note = $request->note;
                        $query->status = '1';
                        $query->is_reimburse = $request->is_reimburse;

                        $query->save();

                        $query->paymentRequestDetail()->delete();
                        $query->paymentRequestCross()->delete();
                        $query->paymentRequestCost()->delete();

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status Payment Request sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
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
                    $newCode=PaymentRequest::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                    
                    $query = PaymentRequest::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'account_id'                => $request->account_id,
                        'company_id'                => $request->company_id,
                        'coa_source_id'             => $request->coa_source_id,
                        'payment_type'              => $request->payment_type,
                        'payment_no'                => $request->payment_no,
                        'post_date'                 => $request->post_date,
                        'pay_date'                  => $request->pay_date,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'cost_distribution_id'      => $request->cost_distribution_id ? $request->cost_distribution_id : NULL,
                        'total'                     => str_replace(',','.',str_replace('.','',$request->total)),
                        'rounding'                  => str_replace(',','.',str_replace('.','',$request->rounding)),
                        'admin'                     => str_replace(',','.',str_replace('.','',$request->admin)),
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'payment'                   => str_replace(',','.',str_replace('.','',$request->payment)),
                        'balance'                   => str_replace(',','.',str_replace('.','',$request->balance)),
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/payment_requests') : NULL,
                        'account_bank'              => $request->account_bank,
                        'account_no'                => $request->account_no,
                        'account_name'              => $request->account_name,
                        'note'                      => $request->note,
                        'status'                    => '1',
                        'is_reimburse'              => $request->is_reimburse,
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                /* DB::beginTransaction();
                try { */

                    if($request->arr_coa_cost){
                        foreach($request->arr_coa_cost as $key => $row){
                            PaymentRequestCost::create([
                                'payment_request_id'            => $query->id,
                                'cost_distribution_id'          => $request->arr_cost_distribution_cost[$key] ? intval($request->arr_cost_distribution_cost[$key]) : NULL,
                                'coa_id'                        => intval($row),
                                'place_id'                      => $request->arr_place[$key] ? $request->arr_place[$key] : NULL,
                                'line_id'                       => $request->arr_line[$key] ? $request->arr_line[$key] : NULL,
                                'machine_id'                    => $request->arr_machine[$key] ? $request->arr_machine[$key] : NULL,
                                'department_id'                 => $request->arr_division[$key] ? $request->arr_division[$key] : NULL,
                                'project_id'                    => $request->arr_project[$key] ? $request->arr_project[$key] : NULL,
                                'nominal_debit'                 => str_replace(',','.',str_replace('.','',$request->arr_nominal_debit[$key])),
                                'nominal_credit'                => str_replace(',','.',str_replace('.','',$request->arr_nominal_credit[$key])),
                                'nominal_debit_fc'              => str_replace(',','.',str_replace('.','',$request->arr_nominal_debit_fc[$key])),
                                'nominal_credit_fc'             => str_replace(',','.',str_replace('.','',$request->arr_nominal_credit_fc[$key])),
                                'note'                          => $request->arr_note_cost[$key],
                                'note2'                         => $request->arr_note_cost2[$key],
                            ]);
                        }
                    }
                    
                    if($request->arr_type){
                        foreach($request->arr_type as $key => $row){
                            $code = CustomHelper::decrypt($request->arr_code[$key]);

                            if($row == 'fund_requests'){
                                $idDetail = FundRequest::find($request->arr_id[$key])->id;
                            }elseif($row == 'purchase_down_payments'){
                                $idDetail = PurchaseDownPayment::find($request->arr_id[$key])->id;
                            }elseif($row == 'purchase_invoices'){
                                $idDetail = PurchaseInvoice::find($request->arr_id[$key])->id;
                            }elseif($row == 'marketing_order_memos'){
                                $idDetail = MarketingOrderMemo::find($request->arr_id[$key])->id;
                            }
                            
                            $prd = PaymentRequestDetail::create([
                                'payment_request_id'            => $query->id,
                                'lookable_type'                 => $row,
                                'lookable_id'                   => $idDetail,
                                'coa_id'                        => $request->arr_coa[$key] ? $request->arr_coa[$key] : NULL,
                                'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_pay[$key])),
                                'note'                          => $request->arr_note[$key],
                            ]);

                            if(in_array($row,['purchase_invoices','purchase_down_payments','fund_requests'])){
                                CustomHelper::updateStatus($row,$request->arr_id[$key],'7');
                            }

                            if($row == 'fund_requests'){
                                if($prd->lookable->document_status == '3'){
                                    $prd->lookable->addLimitCreditEmployee($prd->nominal);
                                }
                            }
                        }
                    }

                    if($request->arr_cd_payment){
                        foreach($request->arr_cd_payment as $key => $row){
                            $prc = PaymentRequestCross::create([
                                'payment_request_id'            => $query->id,
                                'lookable_type'                 => 'outgoing_payments',
                                'lookable_id'                   => intval($row),
                                'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_payment[$key])),
                            ]);
                            if($prc->lookable_type == 'outgoing_payments'){
                                $op = $prc->lookable;
                                if($op->balancePaymentCross() <= 0){
                                    foreach($op->paymentRequest->paymentRequestDetail as $rowdetail){
                                        if($rowdetail->lookable_type == 'fund_requests'){
                                            $rowdetail->lookable->update([
                                                'balance_status'	=> '1'
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    /* DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                } */

                CustomHelper::sendApproval('payment_requests',$query->id,$query->note);
                CustomHelper::sendNotification('payment_requests',$query->id,'Payment Request No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new PaymentRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit payment request.');

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

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->table,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function rowDetail(Request $request){
        $data   = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.'</div><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="5">Daftar Pembayaran</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Tipe</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Bayar</th>
                            </tr>
                        </thead><tbody>';
        $totalbayar=0;
        foreach($data->paymentRequestDetail as $key => $row){
            $totalbayar+=$row->nominal;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->getCode().'</td>
                <td class="center-align">'.$row->type().'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="4"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalbayar, 3, ',', '.') . '</td>
            </tr>  
        ';
        
        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12"><table>
        <thead>
            <tr>
                <th class="center-align" colspan="14">Daftar Biaya</th>
            </tr>
            <tr>
                <th class="center-align">No.</th>
                <th class="center-align">Coa</th>
                <th class="center-align">Dist.Biaya</th>
                <th class="center-align">Plant</th>
                <th class="center-align">Line</th>
                <th class="center-align">Mesin</th>
                <th class="center-align">Divisi</th>
                <th class="center-align">Proyek</th>
                <th class="center-align">Ket.1</th>
                <th class="center-align">Ket.2</th>
                <th class="center-align">Debit FC</th>
                <th class="center-align">Kredit FC</th>
                <th class="center-align">Debit Rp</th>
                <th class="center-align">Kredit Rp</th>
            </tr>
        </thead><tbody>';

        foreach($data->paymentRequestCost as $key => $row){
            
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="">'.$row->coa->code.' - '.$row->coa->name.'</td>
                <td class="center-align">'.($row->costDistribution()->exists() ? $row->costDistribution->name : '-').'</td>
                <td class="center-align">'.($row->place()->exists() ? $row->place->code : '-').'</td>
                <td class="center-align">'.($row->line()->exists() ? $row->line->code : '-').'</td>
                <td class="center-align">'.($row->machine()->exists() ? $row->machine->name : '-').'</td>
                <td class="center-align">'.($row->division()->exists() ? $row->division->name : '-').'</td>
                <td class="center-align">'.($row->project()->exists() ? $row->project->name : '-').'</td>
                <td class="">'.$row->note.'</td>
                <td class="">'.$row->note2.'</td>
                <td class="right-align">'.number_format($row->nominal_debit_fc,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_credit_fc,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_debit,2,',','.').'</td>
                <td class="right-align">'.number_format($row->nominal_credit,2,',','.').'</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

        $string .= '<div class="col s12 mt-1"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="6">Pembayaran dengan Piutang Karyawan</th>
                            </tr>
                            <tr>
                                <th class="center-align">Kode OP</th>
                                <th class="center-align">Kode PR</th>
                                <th class="center-align">Partner Bisnis</th>
                                <th class="center-align">Tgl.Post</th>
                                <th class="center-align">Coa Kas/Bank</th>
                                <th class="center-align">Nominal</th>
                            </tr>
                        </thead><tbody>';
        
        if($data->paymentRequestCross()->exists()){
            foreach($data->paymentRequestCross as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->lookable->code.'</td>
                    <td class="center-align">'.$row->lookable->paymentRequest->code.'</td>
                    <td class="center-align">'.$row->lookable->account->name.'</td>
                    <td class="center-align">'.date('d/m/Y',strtotime($row->lookable->post_date)).'</td>
                    <td class="center-align">'.$row->lookable->coaSource->name.'</td>
                    <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                </tr>';
            }
        }else{
            $string .= '<tr>
                <td class="center-align" colspan="6">Data tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div>';

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
                <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
            </tr>';
        }

        $string .= '</tbody></table></div></div>';
		
        return response()->json($string);
    }

    public function show(Request $request){
        $pr = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $pr['account_name'] = $pr->account()->exists() ? $pr->account->name : '';
        $pr['code_place_id'] = substr($pr->code,7,2);
        $pr['coa_source_name'] = $pr->coaSource()->exists() ? $pr->coaSource->code.' - '.$pr->coaSource->name.' - '.$pr->coaSource->company->name : '';
        $pr['currency_rate'] = number_format($pr->currency_rate,2,',','.');
        $pr['cost_distribution_name'] = $pr->cost_distribution_id ? $pr->costDistribution->code.' - '.$pr->costDistribution->name : '';
        $pr['total'] = number_format($pr->total,2,',','.');
        $pr['rounding'] = number_format($pr->rounding,2,',','.');
        $pr['admin'] = number_format($pr->admin,2,',','.');
        $pr['grandtotal'] = number_format($pr->grandtotal,2,',','.');
        $pr['payment'] = number_format($pr->payment,2,',','.');
        $pr['balance'] = number_format($pr->balance,2,',','.');
        $pr['top'] = $pr->account()->exists() ? $pr->account->top : 0;

        $arr = [];
        $banks = [];
        $payments = [];
        $costs = [];

        $is_cost = 0;

        if($pr->account()->exists()){
            foreach($pr->account->userBank()->orderByDesc('is_default')->get() as $row){
                $banks[] = [
                    'id'        => $row->id,
                    'name'      => $row->name,
                    'bank_name' => $row->bank,
                    'no'        => $row->no,
                ];
            }
        }

        foreach($pr->paymentRequestDetail as $row){
            $code = CustomHelper::encrypt($row->lookable->code);
            $arr[] = [
                'id'            => $row->lookable_type == 'fund_request_details' ? $row->lookable->fundRequest->id : $row->lookable_id,
                'type'          => $row->lookable_type,
                'type_document' => $row->lookable_type == 'fund_requests' ? $row->lookable->document_status : ($row->lookable_type == 'fund_request_details' ? $row->lookable->fundRequest->document_status : ''),
                'type_fr'       => $row->lookable_type == 'fund_requests' ? $row->lookable->type : ($row->lookable_type == 'fund_request_details' ? $row->lookable->fundRequest->type : ''),
                'code'          => $code,
                'rawcode'       => $row->getCode(),
                'post_date'     => $row->lookable_type == 'fund_request_details' ? $row->lookable->fundRequest->post_date : $row->lookable->post_date,
                'due_date'      => isset($row->lookable->due_date) ? $row->lookable->due_date : ($row->lookable_type == 'fund_request_details' ? $row->lookable->fundRequest->post_date : $row->lookable->post_date),
                'total'         => number_format($row->lookable->total,2,',','.'),
                'tax'           => number_format($row->lookable->tax,2,',','.'),
                'wtax'          => number_format($row->lookable->wtax,2,',','.'),
                'grandtotal'    => number_format($row->lookable->grandtotal,2,',','.'),
                'nominal'       => number_format($row->nominal,2,',','.'),
                'balance'       => number_format($row->lookable->balancePaymentRequest() + $row->nominal,2,',','.'),
                'note'          => $row->note ? $row->note : '',
                'remark'        => $row->remark ? $row->remark : '',
                'cost_distribution_id'        => $row->cost_distribution_id ? $row->cost_distribution_id : '',
                'cost_distribution_name'      => $row->cost_distribution_id ? $row->costDistribution->code.' - '.$row->costDistribution->name : '',
                'coa_id'        => $row->coa_id ?? '',
                'coa_name'      => $row->coa()->exists() ? $row->coa->code.' - '.$row->coa->name : '',
                'memo'          => number_format($row->getMemo(),2,',','.'),
                'name_account'  => $row->fundRequest() ? ($row->lookable->name_account ? $row->lookable->name_account : '') : ($row->fundRequestDetail() ? $row->lookable->fundRequest->name_account : ''),
                'no_account'    => $row->fundRequest() ? ($row->lookable->no_account ? $row->lookable->no_account : '') : ($row->fundRequestDetail() ? $row->lookable->fundRequest->no_account : ''),
                'bank_account'  => $row->fundRequest() ? ($row->lookable->bank_account ? $row->lookable->bank_account : '') : ($row->fundRequestDetail() ? $row->lookable->fundRequest->bank_account : ''),
                'place_id'      => $row->place()->exists() ? $row->place->id : '',
                'line_id'       => $row->line()->exists() ? $row->line->id : '',
                'machine_id'    => $row->machine()->exists() ? $row->machine->id : '',
                'department_id' => $row->department()->exists() ? $row->department->id : '',
                'project_id'    => $row->project()->exists() ? $row->project->id : '',
                'project_name'  => $row->project()->exists() ? $row->project->name : '',
                'account_code'  => $row->getAccountCode(),
            ];
        }

        foreach($pr->paymentRequestCost as $row){
            $costs[] = [
                'note'                  => $row->note,
                'cost_distribution_id'  => $row->cost_distribution_id ?? '',
                'cost_distribution_name'=> $row->costDistribution()->exists() ? $row->costDistribution->name : '',
                'coa_id'                => $row->coa_id,
                'coa_name'              => $row->coa->code.' - '.$row->coa->name,
                'place_id'              => $row->place_id ?? '',
                'line_id'               => $row->line_id ?? '',
                'machine_id'            => $row->machine_id ?? '',
                'division_id'           => $row->division_id ?? '',
                'project_id'            => $row->project_id ?? '',
                'project_name'          => $row->project()->exists() ? $row->project->name : '',
                'nominal_debit_fc'      => number_format($row->nominal_debit_fc,2,',','.'),
                'nominal_credit_fc'     => number_format($row->nominal_credit_fc,2,',','.'),
                'nominal_debit'         => number_format($row->nominal_debit,2,',','.'),
                'nominal_credit'        => number_format($row->nominal_credit,2,',','.'),
                'note2'                 => $row->note2,
            ];
        }

        foreach($pr->paymentRequestCross as $row){
            $balance = $row->lookable->balancePaymentCross();
            $payments[] = [
                'id'                    => $row->lookable_id,
                'code'                  => $row->lookable->code,
                'name'                  => $row->lookable->account->name,
                'payment_request_code'  => $row->lookable->paymentRequest->code,
                'post_date'             => date('d/m/Y',strtotime($row->lookable->post_date)),
                'coa_name'              => $row->lookable->coaSource->name,
                'admin'                 => number_format($row->lookable->admin,2,',','.'),
                'total'                 => number_format($row->lookable->total,2,',','.'),
                'grandtotal'            => number_format($row->lookable->grandtotal,2,',','.'),
                'used'                  => number_format($row->lookable->totalUsedCross() - $row->nominal,2,',','.'),
                'balance'               => number_format($balance + $row->nominal,2,',','.'),
                'nominal'               => number_format($row->nominal,2,',','.'),
            ];
        }

        $pr['details'] = $arr;
        $pr['banks'] = $banks;
        $pr['costs'] = $costs;
        $pr['payments'] = $payments;
        				
		return response()->json($pr);
    }

    public function voidStatus(Request $request){
        $query = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    'message' => 'Data telah digunakan pada Outgoing Payment / Kas Bank Keluar.'
                ];
            }else{

                $status = $query->status;

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                if($query->journal()->exists()){
                    CustomHelper::removeJournal($query->getTable(),$query->id);
                }

                if(in_array($status,['2','3'])){
    
                    $query->updateStatusProcess();

                    foreach($query->paymentRequestCross as $row){
                        if($row->lookable_type == 'outgoing_payments'){
                            $op = $row->lookable;
                            foreach($op->paymentRequest->paymentRequestDetail as $rowdetail){
                                if($rowdetail->lookable_type == 'fund_requests'){
                                    $rowdetail->lookable->update([
                                        'balance_status'	=> NULL
                                    ]);
                                }
                            }
                        }
                        $row->addLimitCreditEmployee();
                    }

                    foreach($query->paymentRequestDetail as $row){
                        if($row->lookable_type == 'fund_requests'){
                            if($row->lookable->type == '1' && $row->lookable->document_status == '3'){
                                $row->lookable->removeLimitCreditEmployee($row->nominal);
                            }
                        }

                        if(in_array($row->lookable_type,['purchase_invoices','purchase_down_payments','fund_requests'])){
                            CustomHelper::updateStatus($row->lookable_type,$row->lookable_id,'2');
                        }
                    }
                }
    
                activity()
                    ->performedOn(new PaymentRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the payment requests data');
    
                CustomHelper::sendNotification('payment_requests',$query->id,'Payment Request No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('payment_requests',$query->id);

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
        $query = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();

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

            foreach($query->paymentRequestDetail as $row){
                if($row->lookable_type == 'fund_requests'){
                    if($row->lookable->document_status == '3'){
                        $row->lookable->removeLimitCreditEmployee($row->nominal);
                    }
                }
                if(in_array($row->lookable_type,['purchase_invoices','purchase_down_payments','fund_requests'])){
                    CustomHelper::updateStatus($row->lookable_type,$row->lookable_id,'2');
                }
                $row->delete();
            }
            
            foreach($query->paymentRequestCross as $row){
                if($row->lookable_type == 'outgoing_payments'){
                    $op = $row->lookable;
                    if($op->balancePaymentCross() <= 0){
                        foreach($op->paymentRequest->paymentRequestDetail as $rowdetail){
                            if($rowdetail->lookable_type == 'fund_requests'){
                                $rowdetail->lookable->update([
                                    'balance_status'	=> NULL
                                ]);
                            }
                        }
                    }
                }
                $row->addLimitCreditEmployee();
                $row->delete();
            }

            CustomHelper::removeApproval('payment_requests',$query->id);

            activity()
                ->performedOn(new PaymentRequest())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the payment request data');

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
                $pr = PaymentRequest::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Payment Request',
                        'data'      => $pr
                    ];
                    CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $e_banking = 'website/payment_request_e_banking.jpeg';
                    $extencion_banking = pathinfo($e_banking, PATHINFO_EXTENSION);
                    $image_temp_banking = file_get_contents($e_banking);
                    $img_base_64_banking = base64_encode($image_temp_banking);
                    $path_img_banking = 'data:image/' . $extencion_banking . ';base64,' . $img_base_64_banking;
                    $data["e_banking"]=$path_img_banking;
                    $pdf = Pdf::loadView('admin.print.finance.payment_request_individual', $data)->setPaper('a4', 'portrait');
                    $pdf->render();
                    $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                    $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                    $pdf->getCanvas()->page_text(422, 810, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
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
                        $query = PaymentRequest::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Payment Request',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $e_banking = 'website/payment_request_e_banking.jpeg';
                            $extencion_banking = pathinfo($e_banking, PATHINFO_EXTENSION);
                            $image_temp_banking = file_get_contents($e_banking);
                            $img_base_64_banking = base64_encode($image_temp_banking);
                            $path_img_banking = 'data:image/' . $extencion_banking . ';base64,' . $img_base_64_banking;
                            $data["e_banking"]=$path_img_banking;
                            $pdf = Pdf::loadView('admin.print.finance.payment_request_individual', $data)->setPaper('a4', 'portrait');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 810, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = PaymentRequest::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Payment Request',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $e_banking = 'website/payment_request_e_banking.jpeg';
                            $extencion_banking = pathinfo($e_banking, PATHINFO_EXTENSION);
                            $image_temp_banking = file_get_contents($e_banking);
                            $img_base_64_banking = base64_encode($image_temp_banking);
                            $path_img_banking = 'data:image/' . $extencion_banking . ';base64,' . $img_base_64_banking;
                            $data["e_banking"]=$path_img_banking;
                            $pdf = Pdf::loadView('admin.print.finance.payment_request_individual', $data)->setPaper('a4', 'portrait');
                            $pdf->render();
                            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
                            $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $query->printCounter()->count(), $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
                            $pdf->getCanvas()->page_text(422, 810, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
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

    public function printIndividual(Request $request,$id){
        
        $pr = PaymentRequest::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){

            $data = [
                'title'     => 'Payment Request',
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

            $e_banking = 'website/payment_request_e_banking.jpeg';
            $extencion_banking = pathinfo($e_banking, PATHINFO_EXTENSION);
            $image_temp_banking = file_get_contents($e_banking);
            $img_base_64_banking = base64_encode($image_temp_banking);
            $path_img_banking = 'data:image/' . $extencion_banking . ';base64,' . $img_base_64_banking;
            $data["e_banking"]=$path_img_banking;
             
            $pdf = Pdf::loadView('admin.print.finance.payment_request_individual', $data)->setPaper('a4', 'portrait');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(495, 785, "Jumlah Print, ". $pr->printCounter()->count(), $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(505, 800, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 810, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
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

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		
		return Excel::download(new ExportPaymentRequest($post_date,$end_date,$mode), 'payment_request'.uniqid().'.xlsx');
    }
    
    public function approval(Request $request,$id){
        
        $pr = PaymentRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Payment Request',
                'data'      => $pr
            ];

            return view('admin.approval.payment_request', $data);
        }else{
            abort(404);
        }
    }

    public function getPaymentData(Request $request){
        $data = PaymentRequest::where('code',CustomHelper::decrypt($request->code))->first();

        if($data){
            if(!$data->used()->exists() && !$data->outgoingPayment()->exists()){
                $currency_rate = $data->currency_rate;

                $data['currency_rate'] = number_format($data->currency_rate,2,',','.');

                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Payment Request (Payment Request)');

                $html = '<div class="row pt-1 pb-1"><div class="col s12"><table>
                        <thead>
                            <tr>
                                <th class="" colspan="13"><h6>Mata Uang : '.$data->currency->code.', Konversi = '.$data->currency_rate.', Bayar dengan <b>'.$data->coaSource->name.'</b>, Sisa Tagihan <b>'.$data->currency->symbol.' <b id="real-balance">'.number_format($data->balance,2,',','.').'</b></b> Tagihan dalam Rupiah = <b id="convert-balance">'.number_format($data->balance * $currency_rate,2,',','.').'</b></h6></th>
                            </tr>
                            <tr>
                                <th class="center-align" colspan="5">Daftar Dokumen</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Tipe</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Bayar</th>
                            </tr>
                        </thead><tbody>';
        
                foreach($data->paymentRequestDetail as $key => $row){
                    
                    $html .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td class="center-align">'.$row->getCode().'</td>
                        <td class="center-align">'.$row->type().'</td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                    </tr>';
                }

                $html .= '</tbody></table></div>';

                $html .= '<div class="col s12"><table>
                <thead>
                    <tr>
                        <th class="center-align" colspan="14">Daftar Biaya</th>
                    </tr>
                    <tr>
                        <th class="center-align">No.</th>
                        <th class="center-align">Coa</th>
                        <th class="center-align">Dist.Biaya</th>
                        <th class="center-align">Plant</th>
                        <th class="center-align">Line</th>
                        <th class="center-align">Mesin</th>
                        <th class="center-align">Divisi</th>
                        <th class="center-align">Proyek</th>
                        <th class="center-align">Ket.1</th>
                        <th class="center-align">Ket.2</th>
                        <th class="center-align">Debit FC</th>
                        <th class="center-align">Kredit FC</th>
                        <th class="center-align">Debit Rp</th>
                        <th class="center-align">Kredit Rp</th>
                    </tr>
                </thead><tbody>';

                foreach($data->paymentRequestCost as $key => $row){
                    
                    $html .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td class="">'.$row->coa->code.' - '.$row->coa->name.'</td>
                        <td class="center-align">'.($row->costDistribution()->exists() ? $row->costDistribution->name : '-').'</td>
                        <td class="center-align">'.($row->place()->exists() ? $row->place->code : '-').'</td>
                        <td class="center-align">'.($row->line()->exists() ? $row->line->code : '-').'</td>
                        <td class="center-align">'.($row->machine()->exists() ? $row->machine->name : '-').'</td>
                        <td class="center-align">'.($row->division()->exists() ? $row->division->name : '-').'</td>
                        <td class="center-align">'.($row->project()->exists() ? $row->project->name : '-').'</td>
                        <td class="">'.$row->note.'</td>
                        <td class="">'.$row->note2.'</td>
                        <td class="right-align">'.number_format($row->nominal_debit_fc,2,',','.').'</td>
                        <td class="right-align">'.number_format($row->nominal_credit_fc,2,',','.').'</td>
                        <td class="right-align">'.number_format($row->nominal_debit,2,',','.').'</td>
                        <td class="right-align">'.number_format($row->nominal_credit,2,',','.').'</td>
                    </tr>';
                }

                $html .= '</tbody></table></div>';

                $html .= '<div class="col s4 right mt-1"><table class="bordered" style="right:0px;" style="min-width:50%;">
                            <thead>
                                <tr>
                                    <th>TOTAL</th>
                                    <th class="right-align">'.number_format($data->total,2,',','.').'</th>
                                </tr>
                                <tr>
                                    <th>PEMBULATAN</th>
                                    <th class="right-align">'.number_format($data->rounding,2,',','.').'</th>
                                </tr>
                                <tr>
                                    <th>BIAYA ADMIN '.($data->cost_distribution_id ? $data->costDistribution->code : '').'</th>
                                    <th class="right-align">'.number_format($data->admin,2,',','.').'</th>
                                </tr>
                                <tr>
                                    <th>GRANDTOTAL</th>
                                    <th class="right-align">'.number_format($data->grandtotal,2,',','.').'</th>
                                </tr>
                                <tr>
                                    <th>BAYAR (PIUTANG)</th>
                                    <th class="right-align">'.number_format($data->payment,2,',','.').'</th>
                                </tr>
                                <tr>
                                    <th>SISA HARUS BAYAR</th>
                                    <th class="right-align">'.number_format($data->balance,2,',','.').'</th>
                                </tr>
                            </thead></table></div>';

                $html .= '<div class="col s12 mt-1"><table style="max-width:500px;">
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
                        $html .= '<tr>
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
            
                            $html .= '<tr>
                                <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                                <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                                <td class="center-align">'.$icon.'<br></td>
                                <td class="center-align">'.$row->note.'</td>
                            </tr>';
                        }
                    }
                }else{
                    $html .= '<tr>
                        <td class="center-align" colspan="4">Approval tidak ditemukan.</td>
                    </tr>';
                }

                $html .= '</tbody></table></div></div>';

                return response()->json([
                    'status'    => 200,
                    'data'      => $data,
                    'html'      => $html,
                ]);
            }elseif($data->outgoingPayment()->exists()){
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Payment Request '.$data->outgoingPayment->code.' telah memiliki kas bank out.'
                ]);
            }else{
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Payment Request '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.'
                ]);
            }
        }else{
            return response()->json([
                'status'    => 500,
                'message'   => 'Data tidak ditemukan.',
            ]);
        }
    }

    public function createPay(Request $request){
        $validation = Validator::make($request->all(), [
            'codePay'			        => 'required|string|min:18|unique:outgoing_payments,code',
            'pay_date_pay'              => 'required',
            'currency_id_pay'           => 'required',
            'currency_rate_pay'         => 'required',
		], [
            'codePay.required' 	        => 'Kode tidak boleh kosong.',
            'codePay.string'            => 'Kode harus dalam bentuk string.',
            'codePay.min'               => 'Kode harus minimal 18 karakter.',
            'codePay.unique'            => 'Kode telah dipakai.',
            'pay_date_pay.required'     => 'Tanggal bayar tidak boleh kosong.',
            'currency_id_pay.required'  => 'Mata uang tidak boleh kosong.',
            'currency_rate_pay.required'=> 'Konversi mata uang tidak boleh kosong.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {
            
			if($request->tempPay){
                DB::beginTransaction();
                try {
                    $cek = PaymentRequest::where('code',CustomHelper::decrypt($request->tempPay))->first();

                    $query = OutgoingPayment::create([
                        'code'			            => $request->codePay,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $cek->company_id,
                        'account_id'                => $cek->account_id,
                        'payment_request_id'        => $cek->id,
                        'coa_source_id'             => $cek->coa_source_id,
                        'post_date'                 => date('Y-m-d'),
                        'pay_date'                  => $request->pay_date_pay,
                        'currency_id'               => $request->currency_id_pay,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate_pay)),
                        'cost_distribution_id'      => $cek->cost_distribution_id ? $cek->cost_distribution_id : NULL,
                        'total'                     => $cek->total,
                        'rounding'                  => $cek->rounding,
                        'admin'                     => $cek->admin,
                        'grandtotal'                => $cek->grandtotal,
                        'payment'                   => $cek->payment,
                        'balance'                   => $cek->balance,
                        'document'                  => $request->file('documentPay') ? $request->file('documentPay')->store('public/outgoing_payments') : NULL,
                        'note'                      => $request->notePay ? $request->notePay : '',
                        'status'                    => '3',
                    ]);

                    /* $cek->update([
                        'pay_date'                  => $request->pay_date_pay,
                    ]); */

                    DB::commit();
                    
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {

                CustomHelper::sendNotification('outgoing_payments',$query->id,'Kas Bank Out / Kas Keluar No. '.$query->code,$query->note,session('bo_id'));
                CustomHelper::sendJournal('outgoing_payments',$query->id,$query->account_id);

                activity()
                    ->performedOn(new OutgoingPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit payment request.');

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

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $fr = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query->post_date))],
                  ],
                'url'   =>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($query->code),
                "title" =>$query->code,
            ];
        $data_go_chart[]=$fr;
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
        

        $data_id_pyrs[]=$query->id;
        
        if($query) {

            foreach($query->paymentRequestDetail as $row_pyr_detail){
                        
                $data_pyr_tempura=[
                    'properties'=> [
                        ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                    ],
                    "key" => $row_pyr_detail->paymentRequest->code,
                    "name" => $row_pyr_detail->paymentRequest->code,
                    'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                ];
                if($row_pyr_detail->fundRequest()){
                    
                    $data_fund_tempura=[
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                            ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
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
                if($row_pyr_detail->purchaseDownPayment()){
                    $data_downp_tempura = [
                        'properties'=> [
                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                            ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                        ],
                        "key" => $row_pyr_detail->lookable->code,
                        "name" => $row_pyr_detail->lookable->code,
                        'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
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
                            ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                        ],
                        "key" => $row_pyr_detail->lookable->code,
                        "name" => $row_pyr_detail->lookable->code,
                        'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                    ];

                    $data_go_chart[]=$data_invoices_tempura;
                    $data_link[]=[
                        'from'=>$row_pyr_detail->lookable->code,
                        'to'=>$row_pyr_detail->paymentRequest->code,
                        'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                    ];
                    
                    if(!in_array($row_pyr_detail->lookable->id, $data_id_invoice)){
                        $data_id_invoice[] = $row_pyr_detail->lookable->id;
                    }
                }

                // if($row_pyr_detail->paymentRequestCross()){
                //     $data_pyrc_tempura = [
                //         'properties'=> [
                //             ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                //             ['name'=> "Nominal :".formatNominal().number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                //         ],
                //         "key" => $row_pyr_detail->lookable->code,
                //         "name" => $row_pyr_detail->lookable->code,
                //         'url'=>request()->root()."/admin/purchase/payment_request_cross?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
                //     ];

                //     $data_go_chart[]=$data_pyrc_tempura;
                //     $data_link[]=[
                //         'from'=>$row_pyr_detail->lookable->code,
                //         'to'=>$row_pyr_detail->paymentRequest->code,
                //         'string_link'=>$row_pyr_detail->lookable->code.$row_pyr_detail->paymentRequest->code,
                //     ];
                    
                //     if(!in_array($row_pyr_detail->lookable->id, $data_id_pyrcs)){
                //         $data_id_pyrcs[] = $row_pyr_detail->lookable->id;
                //     }
                // }
            }

            $added = true;
            $finished_data_id_gr=[];
            $finished_data_id_gscale=[];
            $finished_data_id_greturns=[];
            $finished_data_id_invoice=[];
            $finished_data_id_pyrs=[];
            $finished_data_id_pyrcs=[];
            $finished_data_id_dp=[];
            $finished_data_id_memo=[];
            $finished_data_id_gissue=[];
            $finished_data_id_lc=[];
            $finished_data_id_invetory_to=[];
            $finished_data_id_po=[];
            $finished_data_id_pr=[];
            $finished_data_id_mr=[];
            $finished_data_id_gir=[];
            $finished_data_id_cb=[];
            $finished_data_id_frs=[];
            $finished_data_id_cb=[];
            $finished_data_id_frs=[];
            while($added){
               
                $added=false;
                // Pengambilan foreign branch gr
                foreach($data_id_gr as $gr_id){
                    if(!in_array($gr_id, $finished_data_id_gr)){
                        $finished_data_id_gr[]= $gr_id; 
                        $query_gr = GoodReceipt::where('id',$gr_id)->first();
                        foreach($query_gr->goodReceiptDetail as $good_receipt_detail){
                            $po = [
                                'properties'=> [
                                    ['name'=> "Tanggal: ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->post_date],
                                    ['name'=> "Vendor  : ".$good_receipt_detail->purchaseOrderDetail->purchaseOrder->supplier->name],
                                    ['name'=> "Nominal :".formatNominal($good_receipt_detail->purchaseOrderDetail->purchaseOrder).number_format($good_receipt_detail->purchaseOrderDetail->purchaseOrder->grandtotal,2,',','.')]
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
                            //$data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                            if(!in_array($good_receipt_detail->purchaseOrderDetail->purchaseOrder->id, $data_id_po)){
                                $data_id_po[]= $good_receipt_detail->purchaseOrderDetail->purchaseOrder->id; 
                                $added = true; 
                            }
    
                            if($good_receipt_detail->goodReturnPODetail()->exists()){
                                foreach($good_receipt_detail->goodReturnPODetail as $goodReturnPODetail){
                                    $good_return_tempura =[
                                        "name"=> $goodReturnPODetail->goodReturnPO->code,
                                        "key" => $goodReturnPODetail->goodReturnPO->code,
                                        
                                        'properties'=> [
                                            ['name'=> "Tanggal :". $goodReturnPODetail->goodReturnPO->post_date],
                                        ],
                                        'url'=>request()->root()."/admin/inventory/good_return_po?code=".CustomHelper::encrypt( $goodReturnPODetail->goodReturnPO->code),
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
                                            ['name'=> "Nominal :".formatNominal($landed_cost_detail->landedCost).number_format($landed_cost_detail->landedCost->grandtotal,2,',','.')]
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
                                   
                                    if(!in_array($landed_cost_detail->landedCost->id, $data_id_lc)){
                                        $data_id_lc[] = $landed_cost_detail->landedCost->id;
                                        $added = true; 
                                    }
                                   
                                    
                                    
                                }
                            }
    
                            //invoice searching
                            if($good_receipt_detail->purchaseInvoiceDetail()->exists()){
                                foreach($good_receipt_detail->purchaseInvoiceDetail as $invoice_detail){
                                    $invoice_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal : ".$invoice_detail->purchaseInvoice->post_date],
                                            ['name'=> "Nominal :".formatNominal($invoice_detail->purchaseInvoice).number_format($invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                            
                                        ],
                                        'key'=>$invoice_detail->purchaseInvoice->code,
                                        'name'=>$invoice_detail->purchaseInvoice->code,
                                        'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($invoice_detail->purchaseInvoice->code)
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
    
                            if($good_receipt_detail->goodScaleDetail()->exists()){
                                $data_gscale = [
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$good_receipt_detail->goodScaleDetail->goodScale->post_date],
                                            ['name'=> "Vendor  : ".$good_receipt_detail->goodScaleDetail->goodScale->supplier->name],
                                            ['name'=> "Nominal :".formatNominal($good_receipt_detail->goodScaleDetail->goodScale).number_format($good_receipt_detail->goodScaleDetail->goodScale->grandtotal,2,',','.')]
                                        ],
                                        'key'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                        'name'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                        'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($good_receipt_detail->goodScaleDetail->goodScale->code),
                                    ];
                                    $data_go_chart[]=$data_gscale;
                                    $data_link[]=[
                                        'from'=>$good_receipt_detail->goodScaleDetail->goodScale->code,
                                        'to'=>$query_gr->code,
                                        'string_link'=>$good_receipt_detail->goodScaleDetail->goodScale->code.$query_gr->code
                                    ];
                                    $data_id_good_scale[]= $good_receipt_detail->goodScaleDetail->goodScale->id; 
                                
                            }
    
                        }
                    }
                    
                }

                foreach($data_id_cb as $cb_id){
                    if(!in_array($cb_id,$finished_data_id_cb)){
                        $finished_data_id_cb[]= $cb_id; 
                        $query_cb = CloseBill::find($cb_id);
                        foreach($query_cb->closeBillDetail as $row_bill_detail){
                            $outgoingpaymnet = [
                                'key'   => $row_bill_detail->outgoingPayment->code,
                                "name"  => $row_bill_detail->outgoingPayment->code,
                                
                                'properties'=> [
                                    ['name'=> "Tanggal: ".date('d/m/Y',strtotime($row_bill_detail->outgoingPayment->post_date))],
                                    ['name'=> "Nominal: Rp".number_format($row_bill_detail->outgoingPayment->grandtotal,2,',','.')]
                                ],
                                'url'   =>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($row_bill_detail->outgoingPayment->code),
                                "title" => $row_bill_detail->outgoingPayment->code,
                            ];
                            $data_go_chart[]=$outgoingpaymnet;
                            $data_link[]=[
                                'from'=>$row_bill_detail->outgoingPayment->code,
                                'to'=>$query->code,
                                'string_link'=>$row_bill_detail->outgoingPayment->code.$query->code,
                            ];
                            if(!in_array($row_bill_detail->outgoingPayment->id, $data_id_op)){
                                $data_id_op[]= $row_bill_detail->outgoingPayment->id; 
                                $added = true; 
                            } 
                                
                        }

                    }
                }

                foreach($data_id_good_scale as $gs_id){
                    if(!in_array($gs_id, $finished_data_id_gscale)){
                        $finished_data_id_gscale[]=$gs_id;
                        $query_gs = GoodScale::where('id',$gs_id)->first();
                        
                        foreach($query_gs->goodScaleDetail as $data_gs){
                            if($data_gs->goodReceiptDetail->exists()){
                                $gr = [
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$data_gs->goodReceiptDetail->goodReceipt->post_date],
                                        ['name'=> "Vendor  : ".$data_gs->goodReceiptDetail->goodReceipt->supplier->name],
                                       
                                    ],
                                    'key'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                    'name'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                    'url'=>request()->root()."/admin/inventory/good_scale?code=".CustomHelper::encrypt($data_gs->goodReceiptDetail->goodReceipt->code),
                                ];
        
                                $data_go_chart[]=$gr;
                                $data_link[]=[
                                    'from'=>$data_gs->goodReceiptDetail->goodReceipt->code,
                                    'to'=>$query_gs->code,
                                    'string_link'=>$data_gs->goodReceiptDetail->goodReceipt->code.$query_gs->code
                                ];
                                if(!in_array($data_gs->goodReceiptDetail->goodReceipt->id, $data_id_gr)){
                                    $data_id_gr[]= $data_gs->goodReceiptDetail->goodReceipt->id; 
                                    $added = true; 
                                }
                                // $data_id_gr[]= $data_gs->goodReceiptDetail->goodReceipt->id; 
        
                            }
                        }
                    }
                }

                //mencari goodreturn foreign
                foreach($data_id_greturns as $good_return_id){
                    if(!in_array($good_return_id, $finished_data_id_greturns)){
                        $finished_data_id_greturns[]=$good_return_id;
                        $query_return = GoodReturnPO::where('id',$good_return_id)->first();
                        foreach($query_return->goodReturnPODetail as $good_return_detail){
                            $data_good_receipt = [
                                "name"=>$good_return_detail->goodReceiptDetail->goodReceipt->code,
                                "key" => $good_return_detail->goodReceiptDetail->goodReceipt->code,
                    
                                'properties'=> [
                                    ['name'=> "Tanggal :".$good_return_detail->goodReceiptDetail->goodReceipt->post_date],
                                ],
                                'url'=>request()->root()."/admin/inventory/good_return_po?code=".CustomHelper::encrypt($good_return_detail->goodReceiptDetail->goodReceipt->code),
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
                }

                // invoice insert foreign

                foreach($data_id_invoice as $invoice_id){
                    if(!in_array($invoice_id, $finished_data_id_invoice)){
                        $finished_data_id_invoice[]=$invoice_id;
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
                                            ['name'=> "Nominal :".formatNominal($row_po).number_format($row_po->grandtotal,2,',','.')]
                                        ],
                                        'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($row_po->code),           
                                    ];

                                    $data_go_chart[]=$po;
                                    $data_link[]=[
                                        'from'=>$row_po->code,
                                        'to'=>$query_invoice->code,
                                        'string_link'=>$row_po->code.$query_invoice->code
                                    ]; 
                                    $data_id_po[]= $row_po->id;  
                                        
                                    foreach($row_po->purchaseOrderDetail as $po_detail){
                                        if($po_detail->goodReceiptDetail()->exists()){
                                            foreach($po_detail->goodReceiptDetail as $good_receipt_detail){
                                                $data_good_receipt=[
                                                    'properties'=> [
                                                        ['name'=> "Tanggal :".$good_receipt_detail->goodReceipt->post_date],
                                                        ['name'=> "Nominal :".formatNominal($good_receipt_detail->goodReceipt).number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
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
                            if($row->landedCostFeeDetail()){
                                $data_lc=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row->lookable->landedCost->post_date],
                                        ['name'=> "Nominal :".formatNominal($row->lookable->landedCost).number_format($row->lookable->landedCost->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row->lookable->landedCost->code,
                                    "name" => $row->lookable->landedCost->code,
                                    'url'=>request()->root()."/admin/inventory/landed_cost?code=".CustomHelper::encrypt($row->lookable->landedCost->code),
                                ];

                                $data_go_chart[]=$data_lc;
                                $data_link[]=[
                                    'from'=>$row->lookable->landedCost->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row->lookable->landedCost->code.$query_invoice->code,
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
                                            ['name'=> "Nominal :".formatNominal($purchase_memodetail->purchaseMemo).number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                        ],
                                        'url'=>request()->root()."/admin/finance/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
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

                            if($row->fundRequestDetail()->exists()){
                                $fr=[
                                    "name"=>$row->fundRequestDetail->fundRequest->code,
                                    "key" => $row->fundRequestDetail->fundRequest->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row->fundRequestDetail->fundRequest->post_date],
                                        ['name'=> "User :".$row->fundRequestDetail->fundRequest->account->name],
                                        ['name'=> "Nominal :".formatNominal($row->fundRequestDetail->fundRequest).number_format($row->fundRequestDetail->fundRequest->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row->fundRequestDetail->fundRequest->code),
                                ];
                            
                                $data_go_chart[]=$fr;
                                $data_link[]=[
                                    'from'=>$row->fundRequestDetail->fundRequest->code,
                                    'to'=>$query_invoice->code,
                                    'string_link'=>$row->fundRequestDetail->fundRequest->code.$query_invoice->code,
                                ];
                                if(!in_array($row->fundRequestDetail->fundRequest->id, $data_id_frs)){
                                    $data_id_frs[] = $row->fundRequestDetail->fundRequest->id;
                                    $added = true; 
                                } 
                            }
                            
                        }
                        if($query_invoice->purchaseInvoiceDp()->exists()){
                            foreach($query_invoice->purchaseInvoiceDp as $row_pi){
                                $data_down_payment=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pi->purchaseDownPayment->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_pi->purchaseDownPayment).number_format($row_pi->purchaseDownPayment->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pi->purchaseDownPayment->code,
                                    "name" => $row_pi->purchaseDownPayment->code,
                                    'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pi->purchaseDownPayment->code),
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
                                                ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
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
                                                    ['name'=> "User :".$row_pyr_detail->lookable->account->name],
                                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
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

                                            if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                                $data_id_frs[] = $row_pyr_detail->lookable->id;
                                                $added = true; 
                                            } 

                                            
                                        }
                                        if($row_pyr_detail->purchaseDownPayment()){
                                            $data_downp_tempura = [
                                                'properties'=> [
                                                    ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                                ],
                                                "key" => $row_pyr_detail->lookable->code,
                                                "name" => $row_pyr_detail->lookable->code,
                                                'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
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
                                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                                ],
                                                "key" => $row_pyr_detail->lookable->code,
                                                "name" => $row_pyr_detail->lookable->code,
                                                'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
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
                                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
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
                                // $data_id_pyrs[]= $row_pyr_detail->paymentRequest->id;  
                                if(!in_array($row_pyr_detail->paymentRequest->id, $data_id_pyrs)){
                                    $data_id_pyrs[] = $row_pyr_detail->paymentRequest->id;
                                    $added = true; 
                                
                                }    
                                
                                if($row_pyr_detail->fundRequest()){
                                    $data_fund_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                            ['name'=> "User :".$row_pyr_detail->lookable->account->name],
                                            ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
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
                                    if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                        $data_id_frs[] = $row_pyr_detail->lookable->id;
                                        $added = true; 
                                    }           
                                    
                                }
                                if($row_pyr_detail->purchaseDownPayment()){
                                    $data_downp_tempura = [
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                            ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pyr_detail->lookable->code,
                                        "name" => $row_pyr_detail->lookable->code,
                                        'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
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
                                            ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pyr_detail->lookable->code,
                                        "name" => $row_pyr_detail->lookable->code,
                                        'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
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
                }

                foreach($data_id_pyrs as $payment_request_id){
                    if(!in_array($payment_request_id, $finished_data_id_pyrs)){
                        $finished_data_id_pyrs[]=$payment_request_id;
                        $query_pyr = PaymentRequest::find($payment_request_id);
                        
                        if($query_pyr->outgoingPayment()->exists()){
                            $outgoing_payment = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$query_pyr->outgoingPayment->post_date],
                                    ['name'=> "Nominal :".formatNominal($query_pyr->outgoingPayment).number_format($query_pyr->outgoingPayment->grandtotal,2,',','.')]
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
                                    ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                ],
                                "key" => $row_pyr_detail->paymentRequest->code,
                                "name" => $row_pyr_detail->paymentRequest->code,
                                'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                            ];
                        
                            if($row_pyr_detail->fundRequest()){
                                
                                $data_fund_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->code],
                                        ['name'=> "User :".$row_pyr_detail->lookable->account->name],
                                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
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

                                if(!in_array($row_pyr_detail->lookable->id, $data_id_frs)){
                                    $data_id_frs[] = $row_pyr_detail->lookable->id;
                                    $added = true; 
                                } 
                                
                            }
                            if($row_pyr_detail->purchaseDownPayment()){
                                $data_downp_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->lookable->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
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
                                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->lookable).number_format($row_pyr_detail->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_detail->lookable->code,
                                    "name" => $row_pyr_detail->lookable->code,
                                    'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
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
                            
                            if($row_pyr_detail->paymentRequest->paymentRequestCross()->exists()){
                            
                            
                                foreach($row_pyr_detail->paymentRequest->paymentRequestCross as $row_pyr_cross){
                                    
                                    $data_pyrc_tempura = [
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_cross->lookable->post_date],
                                            ['name'=> "Nominal :".formatNominal($row_pyr_cross->lookable).number_format($row_pyr_cross->lookable->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pyr_cross->lookable->code,
                                        "name" => $row_pyr_cross->lookable->code,
                                        'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($row_pyr_cross->lookable->code),  
                                    ];
                        
                                    $data_go_chart[]=$data_pyrc_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pyr_cross->lookable->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                        'string_link'=>$row_pyr_cross->lookable->code.$row_pyr_detail->paymentRequest->code,
                                    ];
                                    if(!in_array($row_pyr_cross->id, $data_id_pyrcs)){
                                        $data_id_pyrcs[] = $row_pyr_cross->id;
                                        
                                    }
                                }

                                
                            }
                        }
                    }
                    
                }

                foreach($data_id_pyrcs as $payment_request_cross_id){
                    
                    if(!in_array($payment_request_cross_id, $finished_data_id_pyrcs)){
                        $finished_data_id_pyrcs[]=$payment_request_cross_id;
                        $query_pyrc = PaymentRequestCross::find($payment_request_cross_id);
                        if($query_pyrc->paymentRequest()->exists()){
                            $data_pyr_tempura = [
                                'key'   => $query_pyrc->paymentRequest->code,
                                "name"  => $query_pyrc->paymentRequest->code,
                                'properties'=> [
                                    ['name'=> "Tanggal: ".date('d/m/Y',strtotime($query_pyrc->paymentRequest->post_date))],
                                ],
                                'url'   =>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyrc->paymentRequest->code),
                                "title" =>$query_pyrc->paymentRequest->code,
                            ];
                            $data_go_chart[]=$data_pyr_tempura;
                            $data_link[]=[
                                'from'=>$query_pyrc->lookable->code,
                                'to'=>$query_pyrc->paymentRequest->code,
                                'string_link'=>$query_pyrc->code.$query_pyrc->paymentRequest->code,
                            ];
                            
                            if(!in_array($query_pyrc->paymentRequest->id, $data_id_pyrs)){
                                $data_id_pyrs[] = $query_pyrc->paymentRequest->id;
                                $added=true;
                            }
                        }
                        if($query_pyrc->outgoingPayment()){
                            $outgoing_tempura = [
                                'properties'=> [
                                    ['name'=> "Tanggal :".$query_pyrc->lookable->post_date],
                                    ['name'=> "Nominal :".formatNominal($query_pyrc->lookable).number_format($query_pyrc->lookable->grandtotal,2,',','.')]
                                ],
                                "key" => $query_pyrc->lookable->code,
                                "name" => $query_pyrc->lookable->code,
                                'url'=>request()->root()."/admin/finance/outgoing_payment?code=".CustomHelper::encrypt($query_pyrc->lookable->code),  
                            ];
        
                            $data_go_chart[]=$outgoing_tempura;
                            $data_link[]=[
                                'from'=>$query_pyrc->lookable->code,
                                'to'=>$query_pyrc->paymentRequest->code,
                                'string_link'=>$query_pyrc->lookable->code.$query_pyrc->paymentRequest->code,
                            ];
                        }
                    }
                }
                
                foreach($data_id_dp as $downpayment_id){
                    
                    if(!in_array($downpayment_id, $finished_data_id_dp)){
                        $finished_data_id_dp[]=$downpayment_id;
                        
                        $query_dp = PurchaseDownPayment::find($downpayment_id);
                       
                        foreach($query_dp->purchaseDownPaymentDetail as $row){
                            if($row->purchaseOrder()->exists()){
                                $po=[
                                    "name"=>$row->purchaseOrder->code,
                                    "key" => $row->purchaseOrder->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row->purchaseOrder->post_date],
                                        ['name'=> "Vendor  : ".$row->purchaseOrder->supplier->name],
                                        ['name'=> "Nominal :".formatNominal($row->purchaseOrder).number_format($row->purchaseOrder->grandtotal,2,',','.')],
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
                                                    ['name'=> "Nominal :".formatNominal($good_receipt_detail->goodReceipt).number_format($good_receipt_detail->goodReceipt->grandtotal,2,',','.')],
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
                            
                            if($row->fundRequestDetail()->exists()){
                                $fr=[
                                    "name"=>$row->fundRequestDetail->fundRequest->code,
                                    "key" => $row->fundRequestDetail->fundRequest->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row->fundRequestDetail->fundRequest->post_date],
                                        ['name'=> "User :".$row->fundRequestDetail->fundRequest->account->name],
                                        ['name'=> "Nominal :".formatNominal($row->fundRequestDetail->fundRequest).number_format($row->fundRequestDetail->fundRequest->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row->fundRequestDetail->fundRequest->code),
                                ];
                            
                                $data_go_chart[]=$fr;
                                $data_link[]=[
                                    'from'=>$row->fundRequestDetail->fundRequest->code,
                                    'to'=>$query_dp->code,
                                    'string_link'=>$row->fundRequestDetail->fundRequest->code.$query_dp->code,
                                ];
                                if(!in_array($row->fundRequestDetail->fundRequest->id, $data_id_frs)){
                                    $data_id_frs[] = $row->fundRequestDetail->fundRequest->id;
                                    $added = true; 
                                } 
                            }
                        }

                        foreach($query_dp->purchaseInvoiceDp as $purchase_invoicedp){
                            
                            $invoice_tempura = [
                                "name"=>$purchase_invoicedp->purchaseInvoice->code,
                                "key" => $purchase_invoicedp->purchaseInvoice->code,
                                'properties'=> [
                                    ['name'=> "Tanggal :".$purchase_invoicedp->purchaseInvoice->post_date],
                                    ['name'=> "Nominal :".formatNominal($purchase_invoicedp->purchaseInvoice).number_format($purchase_invoicedp->purchaseInvoice->grandtotal,2,',','.')],
                                    ],
                                'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoicedp->purchaseInvoice->code),           
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
                                    ['name'=> "Nominal :".formatNominal($purchase_memodetail->purchaseMemo).number_format($purchase_memodetail->purchaseMemo->grandtotal,2,',','.')],
                                    ],
                                'url'=>request()->root()."/admin/finance/purchase_memo?code=".CustomHelper::encrypt($purchase_memodetail->purchaseMemo->code),           
                            ];
                            $data_go_chart[]=$data_memo;
                            $data_link[]=[
                                'from'=>$query_dp->code,
                                'to'=>$purchase_memodetail->purchaseMemo->code,
                                'string_link'=>$query_dp->code.$purchase_memodetail->purchaseMemo->code,
                            ];
                            

                        }

                        if($query_dp->hasPaymentRequestDetail()->exists()){
                            
                            foreach($query_dp->hasPaymentRequestDetail as $row_pyr_detail){
                                $data_pyr_tempura=[
                                    "name"=>$row_pyr_detail->paymentRequest->code,
                                    "key" => $row_pyr_detail->paymentRequest->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')],
                                        ],
                                    'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),           
                                ];
                                $data_go_chart[]=$data_pyr_tempura;
                                $data_link[]=[
                                    'from'=>$query_dp->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$query_dp->code.$row_pyr_detail->paymentRequest->code,
                                ];

                                if(!in_array($query_dp->id, $data_id_dp)){
                                    $data_id_dp[] = $query_dp->id;
                                    $added=true;
                                }
                            }
                        }
                    }

                }

                foreach($data_id_memo as $memo_id){
                    if(!in_array($memo_id, $finished_data_id_memo)){
                        $finished_data_id_memo []= $memo_id;
                        $query = PurchaseMemo::find($memo_id);
                        foreach($query->purchaseMemoDetail as $row){
                            if($row->lookable_type == 'purchase_invoice_details'){
                                $data_invoices_tempura=[
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row->lookable->purchaseInvoice->post_date],
                                        ['name'=> "Nominal :".formatNominal($row->lookable->purchaseInvoice).number_format($row->lookable->purchaseInvoice->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row->lookable->purchaseInvoice->code,
                                    "name" => $row->lookable->purchaseInvoice->code,
                                    'url'=>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($row->lookable->purchaseInvoice->code),
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
                                        ['name'=> "Nominal :".formatNominal($row->lookable).number_format($row->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row->lookable->code,
                                    "name" => $row->lookable->code,
                                    'url'=>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row->lookable->code),
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
                }
                
                foreach($data_id_good_issue as $good_issue_id){
                    if(!in_array($good_issue_id, $finished_data_id_gissue)){
                        $finished_data_id_gissue[]=$good_issue_id;
                        $query_good_issue = GoodIssue::find($good_issue_id);
                        foreach($query_good_issue->goodIssueDetail as $data_detail_good_issue){
                            if($data_detail_good_issue->materialRequestDetail()){
                                $material_request_tempura = [
                                    "key" => $data_detail_good_issue->lookable->materialRequest->code,
                                    "name" => $data_detail_good_issue->lookable->materialRequest->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$data_detail_good_issue->lookable->materialRequest->post_date],
                                        ['name'=> "Nominal :".formatNominal($data_detail_good_issue->lookable->materialRequest).number_format($data_detail_good_issue->lookable->materialRequest->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/purchase/material_request?code=".CustomHelper::encrypt($data_detail_good_issue->lookable->materialRequest->code),
                                ];

                                $data_go_chart[]=$material_request_tempura;
                                $data_link[]=[
                                    'from'=>$data_detail_good_issue->lookable->materialRequest->code,
                                    'to'=>$query_good_issue->code,
                                    'string_link'=>$data_detail_good_issue->lookable->materialRequest->code.$query_good_issue->code,
                                ];
                                $data_id_mr[] = $data_detail_good_issue->lookable->materialRequest->id;
                            }

                            if($data_detail_good_issue->purchaseOrderDetail()->exists()){
                                foreach($data_detail_good_issue->purchaseOrderDetail as $data_purchase_order_detail){
                                    $po_tempura = [
                                        "key" => $data_purchase_order_detail->purchaseOrder->code,
                                        "name" => $data_purchase_order_detail->purchaseOrder->code,
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$data_purchase_order_detail->purchaseOrder->post_date],
                                            ['name'=> "Nominal :".formatNominal($data_purchase_order_detail->purchaseOrder).number_format($data_purchase_order_detail->purchaseOrder->grandtotal,2,',','.')],
                                        ],
                                        'url'=>request()->root()."/admin/purchase/purchase_order?code=".CustomHelper::encrypt($data_purchase_order_detail->purchaseOrder->code),
                                    ];
        
                                    $data_go_chart[]=$material_request_tempura;
                                    $data_link[]=[
                                        'from'=>$query_good_issue->code,
                                        'to'=>$data_purchase_order_detail->purchaseOrder->code,
                                        'string_link'=>$query_good_issue->code.$data_purchase_order_detail->purchaseOrder->code,
                                    ];
                                    $data_id_po[] = $data_purchase_order_detail->purchaseOrder->id;
                                }
                            }
                            
                            if($data_detail_good_issue->goodIssueRequestDetail()){
                                $good_issue_request_tempura = [
                                    "key" => $data_detail_good_issue->lookable->goodIssueRequest->code,
                                    "name" => $data_detail_good_issue->lookable->goodIssueRequest->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$data_detail_good_issue->lookable->goodIssueRequest->post_date],
                                        ['name'=> "Nominal :".formatNominal($data_detail_good_issue->lookable->goodIssueRequest).number_format($data_detail_good_issue->lookable->goodIssueRequest->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/good_issue_request?code=".CustomHelper::encrypt($data_detail_good_issue->lookable->goodIssueRequest->code),
                                ];

                                $data_go_chart[]=$good_issue_request_tempura;
                                $data_link[]=[
                                    'from'=>$data_detail_good_issue->lookable->goodIssueRequest->code,
                                    'to'=>$query_good_issue->code,
                                    'string_link'=>$data_detail_good_issue->lookable->goodIssueRequest->code.$query_good_issue->code,
                                ];
                                $data_id_gir[] = $data_detail_good_issue->lookable->goodIssueRequest->id;  
                            }
                        }
                    }
                }

                foreach($data_id_lc as $landed_cost_id){
                    if(!in_array($landed_cost_id, $finished_data_id_lc)){
                        $finished_data_id_lc[]=$landed_cost_id;
                        $query= LandedCost::find($landed_cost_id);
                        foreach($query->landedCostDetail as $lc_detail ){
                            if($lc_detail->goodReceiptDetail()){
                                $data_good_receipt = [
                                    "key" => $lc_detail->lookable->goodReceipt->code,
                                    'name'=> $lc_detail->lookable->goodReceipt->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$lc_detail->lookable->goodReceipt->post_date],
                                        
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
                                        ['name'=> "Nominal :".formatNominal($lc_detail->lookable->landedCost).number_format($lc_detail->lookable->landedCost->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/purchase/landed_cost?code=".CustomHelper::encrypt($lc_detail->lookable->landedCost->code),
                                ];

                                $data_go_chart[]=$lc_other;
                                $data_link[]=[
                                    'from'=>$query->code,
                                    'to'=>$lc_detail->lookable->landedCost->code,
                                    'string_link'=>$query->code.$lc_detail->lookable->landedCost->code,
                                ];
                                if(!in_array($lc_detail->lookable->landedCost->id,$data_id_lc)){
                                    $data_id_lc[] = $lc_detail->lookable->landedCost->id;
                                    $added = true;
                                }
                            
                                                
                            }//??
                            if($lc_detail->inventoryTransferOutDetail()){
                                $inventory_transfer_out = [
                                    "key" => $lc_detail->lookable->inventoryTransferOut->code,
                                    "name" => $lc_detail->lookable->inventoryTransferOut->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$lc_detail->lookable->inventoryTransferOut->post_date],
                                        ['name'=> "Nominal :".formatNominal($lc_detail->lookable->inventoryTransferOut).number_format($lc_detail->lookable->inventoryTransferOut->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/inventory_transfer_out?code=".CustomHelper::encrypt($lc_detail->lookable->inventoryTransferOut->code),
                                ];

                                $data_go_chart[]=$inventory_transfer_out;
                                $data_link[]=[
                                    'from'=>$query->code,
                                    'to'=>$lc_detail->lookable->inventoryTransferOut->code,
                                    'string_link'=>$query->code.$lc_detail->lookable->inventoryTransferOut->code,
                                ];
                                $data_id_inventory_transfer_out[] = $lc_detail->lookable->inventoryTransferOut->id;
                                                
                            }
                        } // inventory transferout detail apakah perlu
                        if($query->landedCostFeeDetail()->exists()){
                            foreach($query->landedCostFeeDetail as $row_landedfee_detail){
                                foreach($row_landedfee_detail->purchaseInvoiceDetail as $row_invoice_detail){
                                    $data_invoices_tempura = [
                                        'key'   => $row_invoice_detail->purchaseInvoice->code,
                                        "name"  => $row_invoice_detail->purchaseInvoice->code,
                                    
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$row_invoice_detail->purchaseInvoice->post_date],
                                            ['name'=> "Nominal :".formatNominal($row_invoice_detail->purchaseInvoice).number_format($row_invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                        ],
                                        'url'   =>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($row_invoice_detail->purchaseInvoice->code),
                                    ];
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                        'from'  =>  $query->code,
                                        'to'    =>  $row_invoice_detail->purchaseInvoice->code,
                                        'string_link'=>$query->code.$row_invoice_detail->purchaseInvoice->code
                                    ];
                                    if(!in_array($row_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                        $data_id_invoice[]=$row_invoice_detail->purchaseInvoice->id;
                                        $added = true;
                                    }
                                }
                            
                            }
                        }
                    }
                }

                foreach($data_id_inventory_transfer_out as $id_transfer_out){
                    if(!in_array($id_transfer_out, $finished_data_id_invetory_to)){
                        $finished_data_id_invetory_to[]=$id_transfer_out;
                        $query_inventory_transfer_out = InventoryTransferOut::find($id_transfer_out);
                        foreach($query_inventory_transfer_out->inventoryTransferOutDetail as $row_transfer_out_detail){
                            if($row_transfer_out_detail->landedCostDetail->exists()){
                                $lc_tempura = [
                                    "key" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                    "name" => $row_transfer_out_detail->landedCostDetail->landedCost->code,
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_transfer_out_detail->landedCostDetail->landedCost->post_date],
                                        ['name'=> "Nominal :".formatNominal($row_transfer_out_detail->landedCostDetail).number_format($row_transfer_out_detail->landedCostDetail->landedCost->grandtotal,2,',','.')],
                                    ],
                                    'url'=>request()->root()."/admin/inventory/inventory_transfer_out?code=".CustomHelper::encrypt($row_transfer_out_detail->landedCostDetail->landedCost->code),
                                ];

                                $data_go_chart[]=$lc_tempura;
                                $data_link[]=[
                                    'from'=>$query->code,
                                    'to'=>$row_transfer_out_detail->landedCostDetail->landedCost->code,
                                    'string_link'=>$query->code.$row_transfer_out_detail->landedCostDetail->landedCost->code,
                                ];
                                if(!in_array($row_transfer_out_detail->landedCostDetail->landedCost->id,$data_id_lc)){
                                    $data_id_lc[] = $row_transfer_out_detail->landedCostDetail->landedCost->id;
                                    $added = true;
                                }
                            
                                    
                            }
                        }
                    }
                }

                foreach($data_id_frs as $fr_id){
                    if(!in_array($fr_id, $finished_data_id_frs)){
                        $finished_data_id_frs[]=$fr_id;
                        $query_fr = FundRequest::find($fr_id);

                        foreach($query_fr->fundRequestDetail as $row_fr_detail){
                            if($row_fr_detail->hasPaymentRequestDetail()->exists()){
                                foreach($row_fr_detail->hasPaymentRequestDetail as $row_pyr_detail){
                                    $data_pyr_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pyr_detail->paymentRequest->post_date],
                                            ['name'=> "Nominal :".formatNominal($row_pyr_detail->paymentRequest).number_format($row_pyr_detail->paymentRequest->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pyr_detail->paymentRequest->code,
                                        "name" => $row_pyr_detail->paymentRequest->code,
                                        'url'=>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($row_pyr_detail->paymentRequest->code),
                                    ];
                                    $data_go_chart[]=$data_pyr_tempura;
                                    $data_link[]=[
                                        'from'=>$query_fr->code,
                                        'to'=>$row_pyr_detail->paymentRequest->code,
                                        'string_link'=>$query_fr->code.$row_pyr_detail->paymentRequest->code,
                                    ];
                                    if(!in_array($row_pyr_detail->paymentRequest->id,$data_id_pyrs)){
                                        $data_id_pyrs[] = $row_pyr_detail->paymentRequest->id;
                                        $added = true;
                                    } 
                                   
                                }
                            }
                            
                            if($row_fr_detail->purchaseInvoiceDetail()->exists()){
                                foreach($row_fr_detail->purchaseInvoiceDetail as $row_invoice_detail){
                                    $data_invoices_tempura = [
                                        'key'   => $row_invoice_detail->purchaseInvoice->code,
                                        "name"  => $row_invoice_detail->purchaseInvoice->code,
                                    
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$row_invoice_detail->purchaseInvoice->post_date],
                                            ['name'=> "Nominal :".formatNominal($row_invoice_detail->purchaseInvoice).number_format($row_invoice_detail->purchaseInvoice->grandtotal,2,',','.')]
                                        ],
                                        'url'   =>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($row_invoice_detail->purchaseInvoice->code),
                                    ];
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                        'from'  =>  $query_fr->code,
                                        'to'    =>  $row_invoice_detail->purchaseInvoice->code,
                                        'string_link'=>$query_fr->code.$row_invoice_detail->purchaseInvoice->code
                                    ];
                                    if(!in_array($row_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                        $data_id_invoice[]=$row_invoice_detail->purchaseInvoice->id;
                                        $added = true;
                                    }
                                }
                            }

                            if($row_fr_detail->purchaseDownPaymentDetail()->exists()){
                                foreach($row_fr_detail->purchaseDownPaymentDetail as $row_dp_detail){
                                    $data_apdp_tempura = [
                                        'key'   => $row_dp_detail->purchaseDownPayment->code,
                                        "name"  => $row_dp_detail->purchaseDownPayment->code,
                                    
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$row_dp_detail->purchaseDownPayment->post_date],
                                            ['name'=> "Vendor  : ".$row_dp_detail->purchaseDownPayment->name],
                                        ],
                                        'url'   =>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_dp_detail->purchaseDownPayment->code),
                                    ];
                                    $data_go_chart[]=$data_apdp_tempura;
                                    $data_link[]=[
                                        'from'  =>  $query_fr->code,
                                        'to'    =>  $row_dp_detail->purchaseDownPayment->code,
                                        'string_link'=>$query_fr->code.$row_dp_detail->purchaseDownPayment->code,
                                    ];
                                    if(!in_array($row_dp_detail->purchaseDownPayment->id,$data_id_dp)){
                                        $data_id_dp[]=$row_dp_detail->purchaseDownPayment->id;
                                        $added = true;
                                    } 
                                }
                            }
                        }

                    }
                }

                //Pengambilan foreign branch po
                foreach($data_id_po as $po_id){
                    if(!in_array($po_id, $finished_data_id_po)){
                        $finished_data_id_po[]=$po_id;
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
                            if($purchase_order_detail->goodIssueDetail()->exists()){
                                $good_issue_tempura=[
                                    'key'   => $purchase_order_detail->goodIssueDetail->goodIssue->code,
                                    "name"  => $purchase_order_detail->goodIssueDetail->goodIssue->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$purchase_order_detail->goodIssueDetail->goodIssue->post_date],
                                    
                                    ],
                                    'url'   =>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($purchase_order_detail->goodIssueDetail->goodIssue->code),
                                ];
                        
                                $data_go_chart[]=$good_issue_tempura;
                                $data_link[]=[
                                    'from'=>$query_po->code,
                                    'to'=>$purchase_order_detail->goodIssueDetail->goodIssue->code,
                                    'string_link'=>$query_po->code.$purchase_order_detail->goodIssueDetail->goodIssue->code,
                                ];
                                
                                if(!in_array($purchase_order_detail->goodIssueDetail->goodIssue->id,$data_id_good_issue)){
                                    $data_id_good_issue[]=$purchase_order_detail->goodIssueDetail->goodIssue->id;
                                    $added = true;
                                }
                                
                            }
                            if($purchase_order_detail->purchaseInvoiceDetail()->exists()){
                                foreach($purchase_order_detail->purchaseInvoiceDetail as $purchase_invoice_detail){
                                    $data_invoices_tempura = [
                                        'key'   => $purchase_invoice_detail->purchaseInvoice->code,
                                        "name"  => $purchase_invoice_detail->purchaseInvoice->code,
                                    
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".$purchase_invoice_detail->purchaseInvoice->post_date],
                                        
                                        ],
                                        'url'   =>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($purchase_invoice_detail->purchaseInvoice->code),
                                    ];
                                    $data_go_chart[]=$data_invoices_tempura;
                                    $data_link[]=[
                                        'from'  =>  $query_po->code,
                                        'to'    =>  $purchase_invoice_detail->purchaseInvoice->code,
                                        'string_link'=>$query_po->code.$purchase_invoice_detail->purchaseInvoice->code,
                                    ];
                                    if(!in_array($purchase_invoice_detail->purchaseInvoice->id,$data_id_invoice)){
                                        $data_id_invoice[]=$purchase_invoice_detail->purchaseInvoice->id;
                                        $added = true;
                                    }
                                
                                }
                            }
                            if($purchase_order_detail->marketingOrderDeliveryProcess()->exists()){
                                
                                $data_marketing_order_delivery_process = [
                                    'key'   => $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                    "name"  => $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$purchase_order_detail->marketingOrderDeliveryProcess->post_date],
                                    
                                    ],
                                    'url'   =>request()->root()."/admin/finance/purchase_invoice?code=".CustomHelper::encrypt($purchase_order_detail->marketingOrderDeliveryProcess->code),
                                ];
                                $data_go_chart[]=$data_marketing_order_delivery_process;
                                $data_link[]=[
                                    'from'  =>  $purchase_order_detail->marketingOrderDeliveryProcess->code,
                                    'to'    =>  $query_po->code,
                                    'string_link'=>$purchase_order_detail->marketingOrderDeliveryProcess->code.$query_po->code,
                                ];
                                if(!in_array($purchase_order_detail->marketingOrderDeliveryProcess->id,$data_id_mo_delivery_process)){
                                    $data_id_mo_delivery_process[]=$purchase_order_detail->marketingOrderDeliveryProcess->id;
                                    $added = true;
                                }
                                
                                
                            }
                            
                        }

                        if($query_po->purchaseDownPaymentDetail()->exists()){
                            
                            foreach($query_po->purchaseDownPaymentDetail as $row_dp_detail){
                                $data_apdp_tempura = [
                                    'key'   => $row_dp_detail->purchaseDownPayment->code,
                                    "name"  => $row_dp_detail->purchaseDownPayment->code,
                                
                                    'properties'=> [
                                        ['name'=> "Tanggal: ".$row_dp_detail->purchaseDownPayment->post_date],
                                        ['name'=> "Vendor  : ".$row_dp_detail->purchaseDownPayment->name],
                                    ],
                                    'url'   =>request()->root()."/admin/finance/purchase_down_payment?code=".CustomHelper::encrypt($row_dp_detail->purchaseDownPayment->code),
                                ];
                                $data_go_chart[]=$data_apdp_tempura;
                                $data_link[]=[
                                    'from'  =>  $query_po->code,
                                    'to'    =>  $row_dp_detail->purchaseDownPayment->code,
                                    'string_link'=>$query_po->code.$row_dp_detail->purchaseDownPayment->code,
                                ];
                                if(!in_array($row_dp_detail->purchaseDownPayment->id,$data_id_dp)){
                                    $data_id_dp[]=$row_dp_detail->purchaseDownPayment->id;
                                    $added = true;
                                } 
                            }
                        }
                    }

                }

                foreach($data_id_pr as $pr_id){
                    if(!in_array($pr_id, $finished_data_id_pr)){
                        $finished_data_id_pr[]=$pr_id;
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
                            if($purchase_request_detail->materialRequestDetail()){
                                $mr=[
                                    'properties'=> [
                                        ['name'=> "Tanggal : ".$purchase_request_detail->lookable->materialRequest->post_date],
                                        ['name'=> "Vendor  : ".$purchase_request_detail->lookable->materialRequest->user->name],
                                    ],
                                    'key'=>$purchase_request_detail->lookable->materialRequest->code,
                                    'name'=>$purchase_request_detail->lookable->materialRequest->code,
                                    'url'=>request()->root()."/admin/purchase/material_request?code=".CustomHelper::encrypt($purchase_request_detail->lookable->materialRequest->code),
                                ];
                                
                                $data_go_chart[]=$mr;
                                $data_link[]=[
                                    'from'=>$purchase_request_detail->lookable->materialRequest->code,
                                    'to'=>$query_pr->code,
                                    'string_link'=>$purchase_request_detail->lookable->materialRequest->code.$query_pr->code,
                                ];
                                if(!in_array($purchase_request_detail->lookable->materialRequest->id,$data_id_mr)){
                                    $data_id_mr[]= $purchase_request_detail->lookable->materialRequest->id;  
                                    $added = true;
                                }
                            
                                
                            }
                        }
                    }
                }

                foreach($data_id_gir as $gir_id){
                    if(!in_array($gir_id, $finished_data_id_gir)){
                        $finished_data_id_gir[]=$gir_id;
                        $query_good_issue_request = GoodIssueRequest::find($gir_id);
                        foreach($query_good_issue_request->goodIssueRequestDetail as $row_gird){
                            if($row_gird->goodIssueDetail()->exists()){
                                foreach($row_gird->goodIssueDetail as $good_issue_detail){
                                    $good_issue_tempura = [
                                        'properties'=> [
                                            ['name'=> "Tanggal : ".$good_issue_detail->goodIssue->post_date],
                                        ],
                                        'key'=>$good_issue_detail->goodIssue->code,
                                        'name'=>$good_issue_detail->goodIssue->code,
                                        'url'=>request()->root()."/admin/inventory/good_issue_request?code=".CustomHelper::encrypt($good_issue_detail->goodIssue->code),
                                    ];
        
                                    $data_go_chart[]=$good_issue_tempura;
                                    $data_link[]=[
                                        'from'=>$query_good_issue_request->code,
                                        'to'=>$good_issue_detail->goodIssue->code,
                                        'string_link'=>$query_good_issue_request->code.$good_issue_detail->goodIssue->code,
                                    ];
                                    if(!in_array($good_issue_detail->goodIssue->id,$data_id_good_issue)){
                                        $data_id_good_issue[] = $good_issue_detail->goodIssue->id;
                                        $added = true;
                                    }
                                }
                            }
                            
                        }

                    }
                }

                foreach($data_id_mr as $mr_id){
                    if(!in_array($mr_id, $finished_data_id_mr)){
                        $finished_data_id_mr[]=$mr_id;
                        $query_material_request = MaterialRequest::find($mr_id);
                        foreach($query_material_request->materialRequestDetail as $row_material_request_detail){
                            if($row_material_request_detail->purchaseRequestDetail()->exists()){
                            
                                foreach($row_material_request_detail->purchaseRequestDetail as $row_purchase_request_detail){
                                    $pr_tempura = [
                                        'properties'=> [
                                            ['name'=> "Tanggal : ".$row_purchase_request_detail->purchaseRequest->post_date],
                                            ['name'=> "Vendor  : ".$row_purchase_request_detail->purchaseRequest->user->name],
                                        ],
                                        'key'=>$row_purchase_request_detail->purchaseRequest->code,
                                        'name'=>$row_purchase_request_detail->purchaseRequest->code,
                                        'url'=>request()->root()."/admin/purchase/purchase_request?code=".CustomHelper::encrypt($row_purchase_request_detail->purchaseRequest->code),
                                    ];
        
                                    $data_go_chart[]=$pr_tempura;
                                    $data_link[]=[
                                        'from'=>$query_material_request->code,
                                        'to'=>$row_purchase_request_detail->purchaseRequest->code,
                                        'string_link'=>$query_material_request->code.$row_purchase_request_detail->purchaseRequest->code,
                                    ];
                                    if(!in_array($row_purchase_request_detail->purchaseRequest->id,$data_id_pr)){
                                        $data_id_pr[] = $row_purchase_request_detail->purchaseRequest->id;
                                        $added = true;
                                    }
                                }                     
                            
                            }
                            if($row_material_request_detail->goodIssueDetail()->exists()){
                            
                                foreach($row_material_request_detail->goodIssueDetail as $good_issue_detail){
                                    $good_issue_tempura = [
                                        'properties'=> [
                                            ['name'=> "Tanggal : ".$good_issue_detail->goodIssue->post_date],
                                            ['name'=> "User  : ".$good_issue_detail->goodIssue->user->name],
                                        ],
                                        'key'=>$good_issue_detail->goodIssue->code,
                                        'name'=>$good_issue_detail->goodIssue->code,
                                        'url'=>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($good_issue_detail->goodIssue->code),
                                    ];
        
                                    $data_go_chart[]=$good_issue_tempura;
                                    $data_link[]=[
                                        'from'=>$query_material_request->code,
                                        'to'=>$good_issue_detail->goodIssue->code,
                                        'string_link'=>$query_material_request->code.$good_issue_detail->goodIssue->code,
                                    ];
                                
                                    if(!in_array($good_issue_detail->goodIssue->id,$data_id_good_issue)){
                                        $data_id_good_issue[] = $good_issue_detail->goodIssue->id;
                                        $added = true;
                                    }
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

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = PaymentRequest::where('code',CustomHelper::decrypt($id))->first();
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
                    <td class="center-align">'.($row->place()->exists() ? $row->place->code : '-').'</td>
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

    public function done(Request $request){
        $query_done = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'    => '3'
                ]);
    
                activity()
                        ->performedOn(new PaymentRequest())
                        ->causedBy(session('bo_id'))
                        ->withProperties($query_done)
                        ->log('Done the Good Issue Request data');
    
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
        $status = $request->status? $request->status : '';;
        $company = $request->company ? $request->company : '';
        $type_pay = $request->type_pay ? $request->type_pay : '';
        $account = $request->account? $request->account : '';
        $currency = $request->currency ? $request->currency : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
		$modedata = $request->modedata? $request->modedata : '';
      
		return Excel::download(new ExportPaymentRequestTransactionPage($search,$status,$company,$type_pay,$account,$currency,$end_date,$start_date,$modedata), 'purchase_down_payment'.uniqid().'.xlsx');
    }
}
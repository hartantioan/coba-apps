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
use App\Models\OutgoingPayment;
use Illuminate\Database\Eloquent\Builder;

class PaymentRequestController extends Controller
{

    protected $dataplaces, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }
    public function index(Request $request)
    {

        $data = [
            'title'         => 'Permintaan Pembayaran',
            'content'       => 'admin.finance.payment_request',
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => 'PREQ-'.date('y'),
            'newcodePay'    => 'OPYM-'.date('y'),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
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
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PaymentRequest::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")->count();
        
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
            })
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
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
                    $val->account->name,
                    $val->company->name,
                    $val->coa_source_id ? $val->coaSource->name : '-',
                    $val->paymentType(),
                    $val->payment_no,
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->pay_date)),
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
                    $val->status(),
                    $val->balance == 0 ? 'Terbayar' : ($val->status == '2' && !$val->outgoingPayment()->exists() ?
                    '<button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light blue accent-2 white-text btn-small" data-popup="tooltip" title="Bayar" onclick="cashBankOut(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">screen_share</i></button>' : ($val->outgoingPayment()->exists() ? $val->outgoingPayment->code : $val->statusRaw() )),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
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
                        'post_date'             => date('d/m/y',strtotime($row->post_date)),
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
                    'bank_id'   => $row->bank_id,
                    'name'      => $row->name,
                    'bank_name' => $row->bank->name,
                    'no'        => $row->no,
                ];
            }

            foreach($data->fundRequest as $row){
                if(!$row->used()->exists() && $row->balancePaymentRequest() > 0 && $row->document_status !== '1'){
                    $memo = 0;
                    $final = $row->grandtotal - $memo;
                    $details[] = [
                        'id'            => $row->id,
                        'type'          => 'fund_requests',
                        'code'          => CustomHelper::encrypt($row->code),
                        'rawcode'       => $row->code,
                        'rawdate'       => $row->post_date,
                        'post_date'     => date('d/m/y',strtotime($row->post_date)),
                        'due_date'      => date('d/m/y',strtotime($row->required_date)),
                        'total'         => number_format($row->total,2,',','.'),
                        'tax'           => number_format($row->tax,2,',','.'),
                        'wtax'          => number_format($row->wtax,2,',','.'),
                        'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                        'downpayment'   => number_format(0,2,',','.'),
                        'rounding'      => number_format(0,2,',','.'),
                        'balance'       => number_format($row->grandtotal,2,',','.'),
                        'memo'          => number_format($memo,2,',','.'),
                        'final'         => $row->currency->symbol.' '.number_format($final,2,',','.'),
                        'note'          => $row->note,
                    ];
                }
            }

            foreach($data->purchaseDownPayment as $row){
                if(!$row->used()->exists() && $row->balancePaymentRequest() > 0){
                    $memo = 0;
                    $final = $row->grandtotal - $memo;
                    $details[] = [
                        'id'            => $row->id,
                        'type'          => 'purchase_down_payments',
                        'code'          => CustomHelper::encrypt($row->code),
                        'rawcode'       => $row->code,
                        'rawdate'       => $row->post_date,
                        'post_date'     => date('d/m/y',strtotime($row->post_date)),
                        'due_date'      => date('d/m/y',strtotime($row->due_date)),
                        'total'         => number_format($row->total,2,',','.'),
                        'tax'           => number_format($row->tax,2,',','.'),
                        'wtax'          => number_format($row->wtax,2,',','.'),
                        'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                        'downpayment'   => number_format(0,2,',','.'),
                        'rounding'      => number_format(0,2,',','.'),
                        'balance'       => number_format($row->grandtotal,2,',','.'),
                        'memo'          => number_format($memo,2,',','.'),
                        'final'         => $row->currency->symbol.' '.number_format($final,2,',','.'),
                        'note'          => $row->note,
                    ];
                }
            }

            foreach($data->purchaseInvoice as $row){
                if(!$row->used()->exists() && $row->balancePaymentRequest() > 0){
                    $memo = $row->totalMemo();
                    $final = $row->balance - $memo;
                    $details[] = [
                        'id'            => $row->id,
                        'type'          => 'purchase_invoices',
                        'code'          => CustomHelper::encrypt($row->code),
                        'rawcode'       => $row->code,
                        'rawdate'       => $row->post_date,
                        'post_date'     => date('d/m/y',strtotime($row->post_date)),
                        'due_date'      => date('d/m/y',strtotime($row->due_date)),
                        'total'         => number_format($row->total,2,',','.'),
                        'tax'           => number_format($row->tax,2,',','.'),
                        'wtax'          => number_format($row->wtax,2,',','.'),
                        'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                        'downpayment'   => number_format($row->downpayment,2,',','.'),
                        'rounding'      => number_format($row->rounding,2,',','.'),
                        'balance'       => number_format($row->balance,2,',','.'),
                        'memo'          => number_format($memo,2,',','.'),
                        'final'         => $row->currency()->symbol.' '.number_format($final,2,',','.'),
                        'note'          => $row->note,
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
                            'post_date'             => date('d/m/y',strtotime($op->post_date)),
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
                            $coa = Coa::where('code','100.01.03.03.02')->where('company_id',$data->place->company_id)->first();
                            $details[] = [
                                'id'            => $data->id,
                                'type'          => 'fund_requests',
                                'code'          => CustomHelper::encrypt($data->code),
                                'rawcode'       => $data->code,
                                'rawdate'       => $data->post_date,
                                'post_date'     => date('d/m/y',strtotime($data->post_date)),
                                'due_date'      => date('d/m/y',strtotime($data->required_date)),
                                'total'         => number_format($data->total,2,',','.'),
                                'tax'           => number_format($data->tax,2,',','.'),
                                'wtax'          => number_format($data->wtax,2,',','.'),
                                'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                                'balance'       => number_format($data->balancePaymentRequest(),2,',','.'),
                                'coa_id'        => $data->document_status == '3' ? ($coa ? $coa->id : '') : '',
                                'coa_name'      => $data->document_status == '3' ? ($coa ? $coa->code.' - '.$coa->name : '') : '',
                                'memo'          => number_format(0,2,',','.'),
                                'currency_id'   => $data->currency_id,
                                'currency_rate' => number_format($data->currency_rate,2,',','.'),
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
                            $details[] = [
                                'id'            => $data->id,
                                'type'          => 'purchase_down_payments',
                                'code'          => CustomHelper::encrypt($data->code),
                                'rawcode'       => $data->code,
                                'rawdate'       => $data->post_date,
                                'post_date'     => date('d/m/y',strtotime($data->post_date)),
                                'due_date'      => date('d/m/y',strtotime($data->due_date)),
                                'total'         => number_format($data->total,2,',','.'),
                                'tax'           => number_format($data->tax,2,',','.'),
                                'wtax'          => number_format($data->wtax,2,',','.'),
                                'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                                'balance'       => number_format($data->balancePaymentRequest(),2,',','.'),
                                'coa_id'        => $coa ? $coa->id : '',
                                'coa_name'      => $coa ? $coa->code.' - '.$coa->name : '',
                                'memo'          => number_format($data->totalMemo(),2,',','.'),
                                'currency_id'   => $data->currency_id,
                                'currency_rate' => number_format($data->currency_rate,2,',','.'),
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
                            $details[] = [
                                'id'            => $data->id,
                                'type'          => 'purchase_invoices',
                                'code'          => CustomHelper::encrypt($data->code),
                                'rawcode'       => $data->code,
                                'rawdate'       => $data->post_date,
                                'post_date'     => date('d/m/y',strtotime($data->post_date)),
                                'due_date'      => date('d/m/y',strtotime($data->due_date)),
                                'total'         => number_format($data->total,2,',','.'),
                                'tax'           => number_format($data->tax,2,',','.'),
                                'wtax'          => number_format($data->wtax,2,',','.'),
                                'grandtotal'    => number_format($data->grandtotal,2,',','.'),
                                'balance'       => number_format($data->balancePaymentRequest(),2,',','.'),
                                'coa_id'        => $coa ? $coa->id : '',
                                'coa_name'      => $coa ? $coa->code.' - '.$coa->name : '',
                                'memo'          => number_format($data->totalMemo(),2,',','.'),
                                'currency_id'   => $data->currency()->id,
                                'currency_rate' => number_format($data->currencyRate(),2,',','.'),
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
            'code'			        => $request->temp ? ['required', Rule::unique('payment_requests', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:payment_requests,code',
			'account_id' 			=> 'required',
            'company_id'            => 'required',
            'coa_source_id'         => $request->payment_type == '5' ? '' : 'required',
            'payment_type'          => 'required',
            'post_date'             => 'required',
            'pay_date'              => $request->payment_type == '5' ? '' : 'required',
            'currency_id'           => 'required',
            'currency_rate'         => 'required',
            'cost_distribution_id'  => str_replace(',','.',str_replace('.','',$request->admin)) > 0 ? 'required' : '',
            'admin'                 => 'required',
            'grandtotal'            => 'required',
            'arr_type'              => $request->arr_type ? 'required|array' : '',
            'arr_code'              => $request->arr_type ? 'required|array' : '',
            'arr_pay'               => $request->arr_type ? 'required|array' : '',
            'arr_coa'               => $request->arr_type ? 'required|array' : '',
            'arr_coa_cost'          => $request->arr_coa_cost ? 'required|array' : '',
            'arr_cost_distribution_cost' => $request->arr_coa_cost ? 'required|array' : '',
            'arr_note_cost'         => $request->arr_coa_cost ? 'required|array' : '',
		], [
            'code.required' 	                => 'Kode tidak boleh kosong.',
            'code.string'                       => 'Kode harus dalam bentuk string.',
            'code.min'                          => 'Kode harus minimal 18 karakter.',
            'code.unique'                       => 'Kode telah dipakai.',
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
            'arr_coa_cost.required'             => 'Coa rekonsiliasi tidak boleh kosong.',
            'arr_coa_cost.array'                => 'Coa rekonsiliasi harus dalam bentuk array.',
            'arr_cost_distribution_cost.required' => 'Distribusi biaya rekonsiliasi tidak boleh kosong.',
            'arr_cost_distribution_cost.array'  => 'Distribusi biaya rekonsiliasi harus dalam bentuk array.',
            'arr_note_cost.required'            => 'Keterangan rekonsiliasi tidak boleh kosong.',
            'arr_note_cost.array'               => 'Keterangan rekonsiliasi harus dalam bentuk array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            if($request->arr_coa_cost){
                $passedProfitLoss = true;
                foreach($request->arr_coa_cost as $key => $row){
                    $coa = Coa::find(intval($row));
                    if(in_array(substr($coa->code,0,1),['4','5','6','7','8'])){
                        if(!isset($request->arr_cost_distribution_cost[$key])){
                            $passedProfitLoss = false;
                        }
                    }
                }

                if(!$passedProfitLoss){
                    return response()->json([
                        'status'  => 500,
                        'message' => 'Untuk Coa Biaya harus memiliki Distribusi Biaya.'
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
                            'message' => 'Permintaan Pembayaran telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','6'])){

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

                        $query->save();

                        $query->paymentRequestDetail()->delete();
                        $query->paymentRequestCross()->delete();

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Status permintaan pembayaraan sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
                        ]);
                    }
                }catch(\Exception $e){
                    DB::rollback();
                }
			}else{
                DB::beginTransaction();
                try {
                    $query = PaymentRequest::create([
                        'code'			            => $request->code,
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
                    ]);

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }
			}
			
			if($query) {
                DB::beginTransaction();
                try {

                    if($request->arr_coa_cost){
                        foreach($request->arr_coa_cost as $key => $row){
                            PaymentRequestDetail::create([
                                'payment_request_id'            => $query->id,
                                'lookable_type'                 => 'coas',
                                'lookable_id'                   => intval($row),
                                'cost_distribution_id'          => intval($request->arr_cost_distribution_cost[$key]),
                                'coa_id'                        => intval($row),
                                'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_nominal[$key])),
                                'note'                          => $request->arr_note_cost[$key]
                            ]);
                        }
                    }
                    
                    if($request->arr_type){
                        foreach($request->arr_type as $key => $row){
                            $code = CustomHelper::decrypt($request->arr_code[$key]);

                            if($row == 'fund_requests'){
                                $idDetail = FundRequest::where('code',$code)->first()->id;
                            }elseif($row == 'purchase_down_payments'){
                                $idDetail = PurchaseDownPayment::where('code',$code)->first()->id;
                            }elseif($row == 'purchase_invoices'){
                                $idDetail = PurchaseInvoice::where('code',$code)->first()->id;
                            }
                            
                            $prd = PaymentRequestDetail::create([
                                'payment_request_id'            => $query->id,
                                'lookable_type'                 => $row,
                                'lookable_id'                   => $idDetail,
                                'cost_distribution_id'          => $request->arr_cost_distribution[$key] ? $request->arr_cost_distribution[$key] : NULL,
                                'coa_id'                        => $request->arr_coa[$key],
                                'nominal'                       => str_replace(',','.',str_replace('.','',$request->arr_pay[$key])),
                                'note'                          => $request->arr_note[$key]
                            ]);

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

                            $prc->removeLimitCreditEmployee();
                        }
                    }

                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval('payment_requests',$query->id,$query->note);
                CustomHelper::sendNotification('payment_requests',$query->id,'Permintaan Pembayaran No. '.$query->code,$query->note,session('bo_id'));

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
        
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12"><table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="7">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Tipe</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Dist.Biaya</th>
                                <th class="center-align">Coa</th>
                                <th class="center-align">Bayar</th>
                            </tr>
                        </thead><tbody>';
        
        foreach($data->paymentRequestDetail as $key => $row){
            
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->lookable->code.'</td>
                <td class="center-align">'.$row->type().'</td>
                <td class="center-align">'.$row->note.'</td>
                <td class="center-align">'.($row->cost_distribution_id ? $row->costDistribution->code.' - '.$row->costDistribution->name : '-').'</td>
                <td class="center-align">'.$row->coa->code.' - '.$row->coa->name.'</td>
                <td class="right-align">'.number_format($row->nominal,3,',','.').'</td>
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
                    <td class="center-align">'.date('d/m/y',strtotime($row->lookable->post_date)).'</td>
                    <td class="center-align">'.$row->lookable->coaSource->name.'</td>
                    <td class="right-align">'.number_format($row->nominal,3,',','.').'</td>
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

    public function show(Request $request){
        $pr = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $pr['account_name'] = $pr->account->name;
        $pr['code_place_id'] = substr($pr->code,7,2);
        $pr['coa_source_name'] = $pr->coaSource()->exists() ? $pr->coaSource->code.' - '.$pr->coaSource->name.' - '.$pr->coaSource->company->name : '';
        $pr['currency_rate'] = number_format($pr->currency_rate,3,',','.');
        $pr['cost_distribution_name'] = $pr->cost_distribution_id ? $pr->costDistribution->code.' - '.$pr->costDistribution->name : '';
        $pr['total'] = number_format($pr->total,2,',','.');
        $pr['rounding'] = number_format($pr->rounding,2,',','.');
        $pr['admin'] = number_format($pr->admin,2,',','.');
        $pr['grandtotal'] = number_format($pr->grandtotal,2,',','.');
        $pr['payment'] = number_format($pr->payment,2,',','.');
        $pr['balance'] = number_format($pr->balance,2,',','.');
        $pr['top'] = $pr->account->top;

        $arr = [];
        $banks = [];
        $payments = [];

        $is_cost = 0;

        foreach($pr->account->userBank()->orderByDesc('is_default')->get() as $row){
            $banks[] = [
                'bank_id'   => $row->bank_id,
                'name'      => $row->name,
                'bank_name' => $row->bank->name,
                'no'        => $row->no,
            ];
        }

        foreach($pr->paymentRequestDetail as $row){
            $is_cost = $row->lookable_type == 'coas' ? 1 : 0;
            $code = CustomHelper::encrypt($row->lookable->code);
            $arr[] = [
                'id'            => $row->lookable_id,
                'type'          => $row->lookable_type,
                'code'          => $code,
                'rawcode'       => $row->lookable->code,
                'post_date'     => $row->lookable->post_date,
                'due_date'      => isset($row->lookable->due_date) ? $row->lookable->due_date : $row->lookable->post_date,
                'total'         => number_format($row->lookable->total,3,',','.'),
                'tax'           => number_format($row->lookable->tax,3,',','.'),
                'wtax'          => number_format($row->lookable->wtax,3,',','.'),
                'grandtotal'    => number_format($row->lookable->grandtotal,3,',','.'),
                'nominal'       => number_format($row->nominal,3,',','.'),
                'note'          => $row->note,
                'cost_distribution_id'        => $row->cost_distribution_id ? $row->cost_distribution_id : '',
                'cost_distribution_name'      => $row->cost_distribution_id ? $row->costDistribution->code.' - '.$row->costDistribution->name : '',
                'coa_id'        => $row->coa_id,
                'coa_name'      => $row->coa->code.' - '.$row->coa->name,
                'memo'          => number_format($row->getMemo(),2,',','.'),
            ];
        }

        foreach($pr->paymentRequestCross as $row){
            $balance = $row->lookable->balancePaymentCross();
            $payments[] = [
                'id'                    => $row->lookable_id,
                'code'                  => $row->lookable->code,
                'name'                  => $row->lookable->account->name,
                'payment_request_code'  => $row->lookable->paymentRequest->code,
                'post_date'             => date('d/m/y',strtotime($row->lookable->post_date)),
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
        $pr['payments'] = $payments;
        $pr['is_cost'] =  $is_cost;
        				
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
                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);

                foreach($query->paymentRequestDetail as $row){
                    if($row->lookable_type == 'fund_requests'){
                        if($row->lookable->document_status == '3'){
                            $row->lookable->removeLimitCreditEmployee($row->nominal);
                        }
                    }
                }

                foreach($query->paymentRequestCross as $row){
                    $row->addLimitCreditEmployee();
                }
    
                activity()
                    ->performedOn(new PaymentRequest())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the payment requests data');
    
                CustomHelper::sendNotification('payment_requests',$query->id,'Permintaan Pembayaran No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
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

            foreach($query->paymentRequestDetail as $row){
                if($row->lookable_type == 'fund_requests'){
                    if($row->lookable->document_status == '3'){
                        $row->lookable->removeLimitCreditEmployee($row->nominal);
                    }
                }
                $row->delete();
            }
            
            foreach($query->paymentRequestCross as $row){
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
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.finance.payment_request_individual', $data)->setPaper('a5', 'landscape');
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = PaymentRequest::where('Code', 'LIKE', '%'.$etNumbersArray[$nomor-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Payment Request',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.payment_request_individual', $data)->setPaper('a5', 'landscape');
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = PaymentRequest::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Payment Request',
                                'data'      => $query
                            ];
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.payment_request_individual', $data)->setPaper('a5', 'landscape');
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
            $img_path = 'website/logo_web_fix.png';
            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
            $image_temp = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
            $img_base_64 = base64_encode($image_temp);
            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
            $data["image"]=$path_img;
             
            $pdf = Pdf::loadView('admin.print.finance.payment_request_individual', $data)->setPaper('a5', 'landscape');
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
		return Excel::download(new ExportPaymentRequest($request->search,$request->status,$request->company,$request->account,$request->currency,$this->dataplaces), 'payment_request'.uniqid().'.xlsx');
    }
    
    public function approval(Request $request,$id){
        
        $pr = PaymentRequest::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Permintaan Pembayaran',
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
                CustomHelper::sendUsedData($data->getTable(),$data->id,'Form Permintaan Pembayaran (Payment Request)');

                $html = '<div class="row pt-1 pb-1"><div class="col s12"><table>
                        <thead>
                            <tr>
                                <th class="" colspan="10"><h6>Mata Uang : '.$data->currency->code.', Konversi = '.number_format($data->currency_rate,2,',','.').', Bayar dengan <b>'.$data->coaSource->name.'</b></h6></th>
                            </tr>
                            <tr>
                                <th class="center-align" colspan="7">Daftar Item</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Tipe</th>
                                <th class="center-align">Keterangan</th>
                                <th class="center-align">Dist.Biaya</th>
                                <th class="center-align">Coa</th>
                                <th class="center-align">Bayar</th>
                            </tr>
                        </thead><tbody>';
        
                foreach($data->paymentRequestDetail as $key => $row){
                    
                    $html .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td class="center-align">'.$row->lookable->code.'</td>
                        <td class="center-align">'.$row->type().'</td>
                        <td class="center-align">'.$row->note.'</td>
                        <td class="center-align">'.($row->cost_distribution_id ? $row->costDistribution->code.' - '.$row->costDistribution->name : '-').'</td>
                        <td class="center-align">'.$row->coa->code.' - '.$row->coa->name.'</td>
                        <td class="right-align">'.number_format($row->nominal,2,',','.').'</td>
                    </tr>';
                }

                $html .= '<tr>
                    <td class="right-align" colspan="5">TOTAL</td>
                    <td class=""></td>
                    <td class="right-align">'.number_format($data->total,2,',','.').'</td>
                </tr>';

                $html .= '<tr>
                    <td class="right-align" colspan="5">PEMBULATAN</td>
                    <td class=""></td>
                    <td class="right-align">'.number_format($data->rounding,2,',','.').'</td>
                </tr>';

                $html .= '<tr>
                    <td class="right-align" colspan="5">BIAYA ADMIN</td>
                    <td class="">DIST.BIAYA : '.($data->cost_distribution_id ? $data->costDistribution->code.' - '.$data->costDistribution->name : '-').'</td>
                    <td class="right-align">'.number_format($data->admin,2,',','.').'</td>
                </tr>';

                $html .= '<tr>
                    <td class="right-align" colspan="5">GRANDTOTAL</td>
                    <td class=""></td>
                    <td class="right-align">'.number_format($data->grandtotal,2,',','.').'</td>
                </tr>';

                $html .= '<tr>
                    <td class="right-align" colspan="5">BAYAR (PIUTANG)</td>
                    <td class=""></td>
                    <td class="right-align">'.number_format($data->payment,2,',','.').'</td>
                </tr>';

                $html .= '<tr>
                    <td class="right-align" colspan="5">SISA HARUS BAYAR</td>
                    <td class=""></td>
                    <td class="right-align">'.number_format($data->balance,2,',','.').'</td>
                </tr>';

                $html .= '</tbody></table></div>';

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
                    'message'   => 'Permintaan Pembayaran '.$data->used->lookable->code.' telah memiliki kas bank out.'
                ]);
            }else{
                return response()->json([
                    'status'    => 500,
                    'message'   => 'Permintaan Pembayaran '.$data->used->lookable->code.' telah dipakai di '.$data->used->ref.', oleh '.$data->used->user->name.'.'
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
		], [
            'codePay.required' 	        => 'Kode tidak boleh kosong.',
            'codePay.string'            => 'Kode harus dalam bentuk string.',
            'codePay.min'               => 'Kode harus minimal 18 karakter.',
            'codePay.unique'            => 'Kode telah dipakai.',
            'pay_date_pay.required'     => 'Tanggal bayar tidak boleh kosong.',
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
                        'currency_id'               => $cek->currency_id,
                        'currency_rate'             => $cek->currency_rate,
                        'cost_distribution_id'      => $cek->cost_distribution_id ? $cek->cost_distribution_id : NULL,
                        'total'                     => $cek->total,
                        'rounding'                  => $cek->rounding,
                        'admin'                     => $cek->admin,
                        'grandtotal'                => $cek->grandtotal,
                        'payment'                   => $cek->payment,
                        'balance'                   => $cek->balance,
                        'document'                  => $request->file('documentPay') ? $request->file('documentPay')->store('public/outgoing_payments') : NULL,
                        'note'                      => $request->notePay,
                        'status'                    => '3',
                    ]);

                    $cek->update([
                        'pay_date'                  => $request->pay_date_pay,
                    ]);

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
        $query = PaymentRequest::where('code',CustomHelper::decrypt($request->id))->first();
        $data_go_chart = [];
        $data_link = [];
        $fr = [
                'key'   => $query->code,
                "name"  => $query->code,
                "color" => "lightblue",
                'properties'=> [
                     ['name'=> "Tanggal: ".date('d/m/y',strtotime($query->post_date))],
                  ],
                'url'   =>request()->root()."/admin/finance/payment_request?code=".CustomHelper::encrypt($query->code),
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

        $data_id_pyrs[]=$query->id;
        
        if($query) {

            foreach($query->paymentRequestDetail as $row_pyr_detail){
                        
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
                    $data_id_frs[]= $row_pyr_detail->lookable->id;  
   
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
                        'url'=>request()->root()."/admin/purchase/purchase_invoice?code=".CustomHelper::encrypt($row_pyr_detail->lookable->code),  
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
                                $data_id_po[]= $row_po->id;  
                                      
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
                        
                        if($row_pyr_detail->paymentRequest->paymentRequestCross()->exists()){
           
                           
                            foreach($row_pyr_detail->paymentRequest->paymentRequestCross as $row_pyr_cross){
                                
                                $data_pyrc_tempura = [
                                    'properties'=> [
                                        ['name'=> "Tanggal :".$row_pyr_cross->lookable->post_date],
                                        ['name'=> "Nominal : Rp.".number_format($row_pyr_cross->lookable->grandtotal,2,',','.')]
                                    ],
                                    "key" => $row_pyr_cross->lookable->code,
                                    "name" => $row_pyr_cross->lookable->code,
                                    'url'=>request()->root()."/admin/purchase/payment_request_cross?code=".CustomHelper::encrypt($row_pyr_cross->lookable->code),  
                                ];
                       
                                $data_go_chart[]=$data_pyrc_tempura;
                                $data_link[]=[
                                    'from'=>$row_pyr_cross->lookable->code,
                                    'to'=>$row_pyr_detail->paymentRequest->code,
                                    'string_link'=>$row_pyr_cross->lookable->code.$row_pyr_detail->paymentRequest->code,
                                ];
                                if(!in_array($row_pyr_cross->lookable->id, $data_id_pyrcs)){
                                    $data_id_pyrcs[] = $row_pyr_cross->lookable->id;
                                }
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
                            'from'=>$query_pyrc->lookable->code,
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
}
<?php

namespace App\Http\Controllers\Finance;
use App\Exports\ExportIncomingPayment;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\GoodIssueRequest;
use App\Models\GoodScale;
use App\Models\GoodIssue;
use App\Models\InventoryTransferOut;
use App\Models\MaterialRequest;
use App\Models\Company;
use App\Models\CloseBill;
use App\Models\CostDistribution;
use App\Models\Currency;
use App\Models\GoodReceipt;
use App\Models\GoodReturnPO;
use App\Models\IncomingPayment;
use App\Models\IncomingPaymentDetail;
use App\Models\LandedCost;
use App\Models\MarketingOrderReceipt;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderHandoverReceipt;
use App\Models\MarketingOrderHandoverInvoice;
use App\Models\MarketingOrderReturn;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderMemo;
use App\Models\OutgoingPayment;
use App\Models\PaymentRequest;
use App\Models\PersonalCloseBill;
use App\Models\PaymentRequestCross;
use App\Models\Place;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Tax;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use App\Helpers\CustomHelper;
use App\Exports\ExportIncomingPaymentTransactionPage;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Menu;
use App\Models\FundRequest;
use App\Models\MenuUser;

class IncomingPaymentController extends Controller
{

    protected $dataplaces, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }
    public function index(Request $request)
    {
        $lastSegment = request()->segment(count(request()->segments()));
        $menu = Menu::where('url', $lastSegment)->first();
        $menuUser = MenuUser::where('menu_id',$menu->id)->where('user_id',session('bo_id'))->where('type','view')->first();
        $data = [
            'title'         => 'Incoming Payment',
            'content'       => 'admin.finance.incoming_payment',
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'wtax'          => Tax::where('status','1')->where('type','-')->orderByDesc('is_default_pph')->get(),
            'distribution'  => CostDistribution::where('status','1')->get(),
            'currency'      => Currency::where('status','1')->get(),
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'newcode'       => $menu->document_code.date('y'),
            'menucode'      => $menu->document_code,
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'modedata'      => $menuUser->mode ? $menuUser->mode : '',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function getCode(Request $request){
        $code = IncomingPayment::generateCode($request->val);
        				
		return response()->json($code);
    }

    public function getAccountInfo(Request $request){
        $data = User::find($request->id);

        $details = [];

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
            
            if(isset($op)){
                foreach($op as $row){
                    $balance = $row->balancePaymentIncoming();
                    if(!$row->used()->exists() && $balance > 0){
                        $details[] = [
                            'id'                    => $row->id,
                            'type'                  => $row->getTable(),
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
        }elseif($data->type == '2'){
            foreach($data->marketingOrderDownPayment as $row){
                if(!$row->used()->exists() && $row->balancePaymentIncoming() > 0){
                    $details[] = [
                        'id'                    => $row->id,
                        'type'                  => $row->getTable(),
                        'code'                  => $row->code,
                        'post_date'             => date('d/m/Y',strtotime($row->post_date)),
                        'grandtotal'            => number_format($row->grandtotal,2,',','.'),
                        'memo'                  => number_format($row->totalPayMemo(),2,',','.'),
                        'balance'               => number_format($row->balancePaymentIncoming(),2,',','.'),
                    ];
                }
            }

            foreach($data->marketingOrderInvoice as $row){
                if(!$row->used()->exists() && $row->balancePaymentIncoming() > 0){
                    $details[] = [
                        'id'                    => $row->id,
                        'type'                  => $row->getTable(),
                        'code'                  => $row->code,
                        'post_date'             => date('d/m/Y',strtotime($row->post_date)),
                        'grandtotal'            => number_format($row->balance,2,',','.'),
                        'memo'                  => number_format($row->totalPayMemo(),2,',','.'),
                        'balance'               => number_format($row->balancePaymentIncoming(),2,',','.'),
                    ];
                }
            }

            foreach($data->marketingOrderMemo()->whereHas('marketingOrderMemoDetail',function($query){
                $query->where('lookable_type','coas');
            })->get() as $row){
                if(!$row->used()->exists() && $row->balance() > 0){
                    $details[] = [
                        'id'                    => $row->id,
                        'type'                  => $row->getTable(),
                        'code'                  => $row->code,
                        'post_date'             => date('d/m/Y',strtotime($row->post_date)),
                        'grandtotal'            => number_format($row->grandtotal,2,',','.'),
                        'memo'                  => number_format($row->totalUsed(),2,',','.'),
                        'balance'               => number_format($row->balance(),2,',','.'),
                    ];
                }
            }

            foreach($data->marketingOrderMemo()->where('type','3')->get() as $row){
                if(!$row->used()->exists() && $row->balance() > 0){
                    $details[] = [
                        'id'                    => $row->id,
                        'type'                  => $row->getTable(),
                        'code'                  => $row->code,
                        'post_date'             => date('d/m/Y',strtotime($row->post_date)),
                        'grandtotal'            => number_format($row->grandtotal,2,',','.'),
                        'memo'                  => number_format($row->totalUsed(),2,',','.'),
                        'balance'               => number_format($row->balance(),2,',','.'),
                    ];
                }
            }
        }

        $data['details'] = $details;

        return response()->json($data);
    }

    public function getAccountData(Request $request){
        $details = [];

        if($request->arr_id){
            foreach($request->arr_id as $key => $row){
                if($request->arr_type[$key] == 'outgoing_payments'){
                    $op = OutgoingPayment::find(intval($row));
                    if($op){
                        $balance = $op->balancePaymentIncoming();
                        if(!$op->used()->exists() && $balance > 0){
                            CustomHelper::sendUsedData($op->getTable(),$op->id,'Form Incoming Payment');
                            $coa = Coa::where('code','100.01.03.03.02')->where('company_id',$op->company_id)->first();
                            $details[] = [
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
                                'coa_id'                => $coa->id,
                            ];
                        }
                    }
                }elseif($request->arr_type[$key] == 'marketing_order_down_payments'){
                    $dp = MarketingOrderDownPayment::find(intval($row));
                    if($dp){
                        $balance = $dp->balancePaymentIncoming();
                        if(!$dp->used()->exists() && $balance > 0){
                            CustomHelper::sendUsedData($dp->getTable(),$dp->id,'Form Incoming Payment');
                            $coapiutang = Coa::where('code','100.01.03.01.01')->where('company_id',$dp->company_id)->first();
                            $details[] = [
                                'id'                    => $dp->id,
                                'code'                  => $dp->code,
                                'rawcode'               => $dp->code,
                                'type'                  => $dp->getTable(),
                                'name'                  => $dp->account->name,
                                'payment_request_code'  => '-',
                                'post_date'             => date('d/m/Y',strtotime($dp->post_date)),
                                'coa_name'              => $coapiutang->name,
                                'admin'                 => number_format(0,2,',','.'),
                                'total'                 => number_format($dp->grandtotal,2,',','.'),
                                'grandtotal'            => number_format($dp->grandtotal,2,',','.'),
                                'used'                  => number_format($dp->totalPayMemo(),2,',','.'),
                                'balance'               => number_format($balance,2,',','.'),
                                'coa_id'                => $coapiutang->id,
                            ];
                        }
                    }
                }elseif($request->arr_type[$key] == 'marketing_order_invoices'){
                    $moi = MarketingOrderInvoice::find(intval($row));
                    if($moi){
                        $balance = $moi->balancePaymentIncoming();
                        if(!$moi->used()->exists() && $balance > 0){
                            CustomHelper::sendUsedData($moi->getTable(),$moi->id,'Form Incoming Payment');
                            $coapiutang = Coa::where('code','100.01.03.01.01')->where('company_id',$moi->company_id)->first();
                            $details[] = [
                                'id'                    => $moi->id,
                                'code'                  => $moi->code,
                                'rawcode'               => $moi->code,
                                'type'                  => $moi->getTable(),
                                'name'                  => $moi->account->name,
                                'payment_request_code'  => '-',
                                'post_date'             => date('d/m/Y',strtotime($moi->post_date)),
                                'coa_name'              => $coapiutang->name,
                                'admin'                 => number_format(0,2,',','.'),
                                'total'                 => number_format($moi->balance,2,',','.'),
                                'grandtotal'            => number_format($moi->balance,2,',','.'),
                                'used'                  => number_format($moi->totalPayMemo(),2,',','.'),
                                'balance'               => number_format($balance,2,',','.'),
                                'coa_id'                => $coapiutang->id,
                            ];
                        }
                    }
                }elseif($request->arr_type[$key] == 'marketing_order_memos'){
                    $moi = MarketingOrderMemo::find(intval($row));
                    if($moi){
                        $balance = $moi->balance();
                        if(!$moi->used()->exists() && $balance > 0){
                            CustomHelper::sendUsedData($moi->getTable(),$moi->id,'Form Incoming Payment');
                            $details[] = [
                                'id'                    => $moi->id,
                                'code'                  => $moi->code,
                                'rawcode'               => $moi->code,
                                'type'                  => $moi->getTable(),
                                'name'                  => $moi->account->name,
                                'payment_request_code'  => '-',
                                'post_date'             => date('d/m/Y',strtotime($moi->post_date)),
                                'coa_name'              => '-',
                                'admin'                 => number_format(0,2,',','.'),
                                'total'                 => '-'.number_format($moi->grandtotal,2,',','.'),
                                'grandtotal'            => '-'.number_format($moi->grandtotal,2,',','.'),
                                'used'                  => '-'.number_format($moi->totalUsed(),2,',','.'),
                                'balance'               => '-'.number_format($balance,2,',','.'),
                                'coa_id'                => 0,
                            ];
                        }
                    }
                }
            }
        }

        $user['details'] = $details;

        return response()->json($user);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'coa_id',
            'post_date',
            'currency_id',
            'currency_rate',
            'total',
            'wtax_id',
            'percent_wtax',
            'wtax',
            'grandtotal',
            'document',
            'note',
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = IncomingPayment::whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        ->where(function($query)use($request){
            if(!$request->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })->count();
        
        $query_data = IncomingPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('incomingPaymentDetail',function($query) use($search, $request){
                                $query->where('note','like',"%$search%");
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
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = IncomingPayment::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('post_date', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('wtax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('incomingPaymentDetail',function($query) use($search, $request){
                                $query->where('note','like',"%$search%");
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
            ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
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
                    $val->account_id ? $val->account->name : '-',
                    $val->company->name,
                    $val->coa->name,
                    date('d/m/Y',strtotime($val->post_date)),
                    $val->currency->code,
                    number_format($val->currency_rate,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                      $val->document ? '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>' : 'file tidak ditemukan',
                    $val->note,
                    $val->status(),
                    (
                        ($val->status == 3 && is_null($val->done_id)) ? 'sistem' :
                        (
                            ($val->status == 3 && !is_null($val->done_id)) ? $val->doneUser->name :
                            (
                                ($val->status != 3 && !is_null($val->void_id) && !is_null($val->void_date)) ? $val->voidUser->name :
                                (
                                    ($val->status != 3 && is_null($val->void_id) && !is_null($val->void_date)) ? 'sistem' :
                                    (
                                        ($val->status != 3 && is_null($val->void_id) && is_null($val->void_date)) ? null : null
                                    )
                                )
                            )
                        )
                    ),
                    '
                        <button type="button" class="btn-floating mb-1 btn-flat  grey white-text btn-small" data-popup="tooltip" title="Preview Print" onclick="whatPrinting(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">visibility</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat green accent-2 white-text btn-small" data-popup="tooltip" title="Cetak" onclick="printPreview(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">local_printshop</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat waves-effect waves-light orange accent-2 white-text btn-small" data-popup="tooltip" title="Edit" onclick="show(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">create</i></button>
                        <button type="button" class="btn-floating mb-1 btn-flat cyan darken-4 white-text btn-small" data-popup="tooltip" title="Lihat Relasi" onclick="viewStructureTree(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">timeline</i></button>
                        '.$btn_jurnal.'
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
            /* 'code'			        => $request->temp ? ['required', Rule::unique('incoming_payments', 'code')->ignore(CustomHelper::decrypt($request->temp),'code')] : 'required|string|min:18|unique:incoming_payments,code',
             */'company_id'            => 'required',
            'coa_id'                => 'required',
            'post_date'             => 'required',
            'currency_rate'         => 'required',
            'currency_id'           => 'required',
            'grandtotal'            => 'required',
            'arr_coa'               => 'required|array',
            'arr_total'             => 'required|array',
            'arr_rounding'          => 'required|array',
            'arr_subtotal'          => 'required|array',
		], [
            'code_place_id.required'            => 'Plant Tidak boleh kosong',
            'code.required' 				    => 'Kode/No tidak boleh kosong.',
            'company_id.required'               => 'Perusahaan tidak boleh kosong.',
            'coa_id.required'                   => 'Coa Kas / Bank masuk tidak boleh kosong.',
            'post_date.required'                => 'Tanggal posting tidak boleh kosong.',
            'currency_rate.required'            => 'Konversi mata uang tidak boleh kosong.',
            'currency_id.required'              => 'Mata uang tidak boleh kosong.',
            'grandtotal.required'               => 'Grandtotal tidak boleh kosong.',
            'arr_coa.required'                  => 'Coa tidak boleh kosong.',
            'arr_coa.array'                     => 'Coa harus array.',
            'arr_total.required'                => 'Total tidak boleh kosong.',
            'arr_total.array'                   => 'Total harus array.',
            'arr_rounding.required'             => 'Pembulatan tidak boleh kosong.',
            'arr_rounding.array'                => 'Pembulatan harus array.',
            'arr_subtotal.required'             => 'Subtotal tidak boleh kosong.',
            'arr_subtotal.array'                => 'Subtotal harus array.',
		]);

        if($validation->fails()) {
            $response = [
                'status' => 422,
                'error'  => $validation->errors()
            ];
        } else {

            $grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));

            if($grandtotal <= 0){
                return response()->json([
                    'status'  => 500,
                    'message' => 'Nominal tidak boleh dibawah sama dengan 0.'
                ]);
            }
            
			if($request->temp){
                DB::beginTransaction();
                try {
                    $query = IncomingPayment::where('code',CustomHelper::decrypt($request->temp))->first();

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
                            'message' => 'Kas / Bank Masuk telah diapprove, anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(!CustomHelper::checkLockAcc($query->post_date)){
                        return response()->json([
                            'status'  => 500,
                            'message' => 'Transaksi pada periode dokumen telah ditutup oleh Akunting. Anda tidak bisa melakukan perubahan.'
                        ]);
                    }

                    if(in_array($query->status,['1','2','6'])){

                        if($query->journal()->exists()){
                            CustomHelper::removeJournal($query->getTable(),$query->id);
                        }

                        if($request->has('document')) {
                            if($query->document){
                                if(Storage::exists($query->document)){
                                    Storage::delete($query->document);
                                }
                            }
                            $document = $request->file('document')->store('public/incoming_payments');
                        } else {
                            $document = $query->document;
                        }

                        $query->code = $request->code;
                        $query->user_id = session('bo_id');
                        $query->company_id = $request->company_id;
                        $query->account_id = $request->account_id ? $request->account_id : NULL;
                        $query->coa_id = $request->coa_id;
                        $query->post_date = $request->post_date;
                        $query->currency_id = $request->currency_id;
                        $query->currency_rate = str_replace(',','.',str_replace('.','',$request->currency_rate));
                        $query->total = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->wtax = 0;
                        $query->grandtotal = str_replace(',','.',str_replace('.','',$request->grandtotal));
                        $query->document = $document;
                        $query->note = $request->note;
                        $query->status = '1';

                        $query->save();

                        $query->incomingPaymentDetail()->delete();

                        DB::commit();
                    }else{
                        return response()->json([
                            'status'  => 500,
					        'message' => 'Kas / Bank Masuk sudah diupdate dari menunggu, anda tidak bisa melakukan perubahan.'
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
                    $newCode=IncomingPayment::generateCode($menu->document_code.date('y',strtotime($request->post_date)).$request->code_place_id);
                   
                    $query = IncomingPayment::create([
                        'code'			            => $newCode,
                        'user_id'		            => session('bo_id'),
                        'company_id'                => $request->company_id,
                        'account_id'                => $request->account_id ? $request->account_id : NULL,
                        'coa_id'                    => $request->coa_id,
                        'post_date'                 => $request->post_date,
                        'currency_id'               => $request->currency_id,
                        'currency_rate'             => str_replace(',','.',str_replace('.','',$request->currency_rate)),
                        'total'                     => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'wtax'                      => 0,
                        'grandtotal'                => str_replace(',','.',str_replace('.','',$request->grandtotal)),
                        'document'                  => $request->file('document') ? $request->file('document')->store('public/incoming_payments') : NULL,
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
                    foreach($request->arr_coa as $key => $row){
                        $total = str_replace(',','.',str_replace('.','',$request->arr_total[$key]));
                        $rounding = str_replace(',','.',str_replace('.','',$request->arr_rounding[$key]));
                        $subtotal = str_replace(',','.',str_replace('.','',$request->arr_subtotal[$key]));
                        IncomingPaymentDetail::create([
                            'incoming_payment_id'   => $query->id,
                            'lookable_type'         => $request->arr_type[$key],
                            'lookable_id'           => $request->arr_type[$key] == 'coas' ? $request->arr_coa[$key] : $request->arr_id[$key],
                            'cost_distribution_id'  => $request->arr_cost_distribution[$key] ? $request->arr_cost_distribution[$key] : NULL,
                            'total'                 => $total,
                            'rounding'              => $rounding,
                            'subtotal'              => $subtotal,
                            'note'                  => $request->arr_note[$key],
                        ]);
                    }
                    DB::commit();
                }catch(\Exception $e){
                    DB::rollback();
                }

                CustomHelper::sendApproval($query->getTable(),$query->id,$query->note);
                CustomHelper::sendNotification($query->getTable(),$query->id,'Kas / Bank Masuk No. '.$query->code,$query->note,session('bo_id'));

                activity()
                    ->performedOn(new IncomingPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Add / edit incoming payment.');

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
        $data   = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $x="";
        if (isset($data->void_date)) {
            $voidUser = $data->voidUser ? $data->voidUser->employee_no . '-' . $data->voidUser->name : 'Sistem';
            $x .= '<span style="color: red;">|| Tanggal Void: ' . $data->void_date .  ' || Void User: ' . $voidUser.' || Note:' . $data->void_note.'</span>' ;
        }if($data->status == 3){
            $doneUser = $data->done_id ? $data->doneUser->employee_no . '-' . $data->doneUser->name : 'Sistem';
            $x .= '<span style="color: blue;">|| Tanggal Done: ' . $data->done_date .  ' || Done User: ' . $doneUser;
        }
        $string = '<div class="row pt-1 pb-1 lighten-4"><div class="col s12">'.$data->code.$x.'</div><div class="col s12">
                    <table style="min-width:100%;max-width:100%;">
                        <thead>
                            <tr>
                                <th class="center-align" colspan="8">Detail Rincian</th>
                            </tr>
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Referensi</th>
                                <th class="center-align">Tipe</th>
                                <th class="center-align">Dist.Biaya</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Pembulatan</th>
                                <th class="center-align">Subtotal</th>
                                <th class="center-align">Keterangan</th>
                            </tr>
                        </thead><tbody>';
        $totals=0;
        $totalpembulatan=0;
        $totalsubtotal=0;
        foreach($data->incomingPaymentDetail as $key => $row){
            $totals+=$row->total;
            $totalpembulatan+=$row->rounding;
            $totalsubtotal+=$row->subtotal;
            $string .= '<tr>
                <td class="center-align">'.($key + 1).'</td>
                <td class="center-align">'.$row->lookable->code.'</td>
                <td class="center-align">'.class_basename($row->lookable).'</td>
                <td class="center-align">'.($row->cost_distribution_id ? $row->costDistribution->code : '-').'</td>
                <td class="right-align">'.number_format($row->total,2,',','.').'</td>
                <td class="right-align">'.number_format($row->rounding,2,',','.').'</td>
                <td class="right-align">'.number_format($row->subtotal,2,',','.').'</td>
                <td class="">'.$row->note.'</td>
            </tr>';
        }
        $string .= '<tr>
                <td class="center-align" style="font-weight: bold; font-size: 16px;" colspan="4"> Total </td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totals, 3, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalpembulatan, 3, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;">' . number_format($totalsubtotal, 3, ',', '.') . '</td>
                <td class="right-align" style="font-weight: bold; font-size: 16px;"></td> 
            </tr>  
        ';
        
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
        
        if($data->approval() && $data->approval()->approvalMatrix()->exists()){                
            foreach($data->approval()->approvalMatrix as $key => $row){
                $string .= '<tr>
                    <td class="center-align">'.$row->approvalTemplateStage->approvalStage->level.'</td>
                    <td class="center-align">'.$row->user->profilePicture().'<br>'.$row->user->name.'</td>
                    <td class="center-align">'.($row->status == '1' ? '<i class="material-icons">hourglass_empty</i>' : ($row->approved ? '<i class="material-icons">thumb_up</i>' : ($row->rejected ? '<i class="material-icons">thumb_down</i>' : '<i class="material-icons">hourglass_empty</i>'))).'<br></td>
                    <td class="center-align">'.$row->note.'</td>
                </tr>';
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
        
        $pr = IncomingPayment::where('code',CustomHelper::decrypt($id))->first();
                
        if($pr){
            $data = [
                'title'     => 'Print Kas / Bank Masuk',
                'data'      => $pr
            ];

            return view('admin.approval.incoming_payment', $data);
        }else{
            abort(404);
        }
    }

    public function show(Request $request){
        $ip = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        $ip['code_place_id'] = substr($ip->code,7,2);
        $ip['grandtotal'] = number_format($ip->grandtotal,2,',','.');
        $ip['account_name'] = $ip->account_id ? $ip->account->name : '';
        $ip['coa_name'] = $ip->coa->code.' - '.$ip->coa->name;
        $ip['currency_rate'] = number_format($ip->currency_rate,2,',','.');
        $coareceivable = Coa::where('code','100.01.03.03.02')->where('company_id',$ip->company_id)->first()->id;

        $arr = [];

        foreach($ip->incomingPaymentDetail as $row){
            $arr[] = [
                'type'                  => $row->lookable_type,
                'coa_id'                => $row->lookable_type == 'coas' ? $row->lookable_id : $coareceivable,
                'id'                    => $row->lookable_id,
                'post_date'             => $row->lookable_type == 'coas' ? '-' : date('d/m/Y',strtotime($row->lookable->post_date)),
                'name'                  => $row->getCode(),
                'cost_distribution_id'  => $row->cost_distribution_id ? $row->cost_distribution_id : '',
                'cost_distribution_name'=> $row->cost_distribution_id ? $row->costDistribution->code.' - '.$row->costDistribution->name : '',
                'total'                 => number_format($row->total,'2',',','.'),
                'rounding'              => number_format($row->rounding,'2',',','.'),
                'subtotal'              => number_format($row->subtotal,'2',',','.'),
                'note'                  => $row->note ? $row->note : '',
            ];
        }

        $ip['details'] = $arr;
        				
		return response()->json($ip);
    }

    public function voidStatus(Request $request){
        $query = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        
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
                    foreach($query->incomingPaymentDetail as $row){
                        if($row->lookable_type == 'outgoing_payments'){
                            CustomHelper::addCountLimitCredit($query->account_id,$row->total);
                        }
                        if($row->lookable_type == 'marketing_order_down_payments'){
                            CustomHelper::removeDeposit($row->lookable->account_id,$row->total * $query->currency_rate);
                            CustomHelper::addCountLimitCredit($row->lookable->account_id,$row->total * $query->currency_rate);
                        }
                    }
                }

                $query->update([
                    'status'    => '5',
                    'void_id'   => session('bo_id'),
                    'void_note' => $request->msg,
                    'void_date' => date('Y-m-d H:i:s')
                ]);
    
                activity()
                    ->performedOn(new IncomingPayment())
                    ->causedBy(session('bo_id'))
                    ->withProperties($query)
                    ->log('Void the incoming payment data');
    
                CustomHelper::sendNotification('incoming_payments',$query->id,'Kas / Bank Masuk No. '.$query->code.' telah ditutup dengan alasan '.$request->msg.'.',$request->msg,$query->user_id);
                CustomHelper::removeApproval('incoming_payments',$query->id);
                CustomHelper::removeJournal('incoming_payments',$query->id);

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

    public function removeUsedData(Request $request){
        CustomHelper::removeUsedData($request->table,$request->id);
        return response()->json([
            'status'    => 200,
            'message'   => ''
        ]);
    }

    public function destroy(Request $request){
        $query = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();

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

            foreach($query->incomingPaymentDetail as $row){
                if($row->lookable_type == 'outgoing_payments'){
                    CustomHelper::addCountLimitCredit($query->account_id,$row->total);
                }
                $row->delete();
            }

            CustomHelper::removeApproval('incoming_payments',$query->id);

            activity()
                ->performedOn(new IncomingPayment())
                ->causedBy(session('bo_id'))
                ->withProperties($query)
                ->log('Delete the incoming payment data');

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

    public function export(Request $request){
        $post_date = $request->start_date? $request->start_date : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $mode = $request->mode ? $request->mode : '';
		return Excel::download(new ExportIncomingPayment($post_date,$end_date,$mode), 'incoming_payment_'.uniqid().'.xlsx');
    }

    public function viewJournal(Request $request,$id){
        $total_debit_asli = 0;
        $total_debit_konversi = 0;
        $total_kredit_asli = 0;
        $total_kredit_konversi = 0;
        $query = IncomingPayment::where('code',CustomHelper::decrypt($id))->first();
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
                        $query = IncomingPayment::where('Code', 'LIKE', '%'.$x)->first();
                        if($query){
                            $data = [
                                'title'     => 'Good Issue',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.incoming_payment_individual', $data)->setPaper('a5', 'landscape');
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
                        $etNumbersArray = explode(',', $request->tabledata);
                        $query = IncomingPayment::where('code', 'LIKE', '%'.$etNumbersArray[$code-1])->first();
                        if($query){
                            $data = [
                                'title'     => 'Good Issue',
                                    'data'      => $query
                            ];
                            CustomHelper::addNewPrinterCounter($query->getTable(),$query->id);
                            $img_path = 'website/logo_web_fix.png';
                            $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                            $image_temp = file_get_contents($img_path);
                            $img_base_64 = base64_encode($image_temp);
                            $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                            $data["image"]=$path_img;
                            $pdf = Pdf::loadView('admin.print.finance.incoming_payment_individual', $data)->setPaper('a5', 'landscape');
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
                        'status'    =>200,
                        'message'   =>$var_link
                    ];
                }
            }
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
                $pr = IncomingPayment::where('code',$row)->first();
                
                if($pr){
                    $data = [
                        'title'     => 'Incoming Payment',
                        'data'      => $pr
                    ];
                    CustomHelper::addNewPrinterCounter($pr->getTable(),$pr->id);
                    $img_path = 'website/logo_web_fix.png';
                    $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
                    $image_temp = file_get_contents($img_path);
                    $img_base_64 = base64_encode($image_temp);
                    $path_img = 'data:image/' . $extencion . ';base64,' . $img_base_64;
                    $data["image"]=$path_img;
                    $pdf = Pdf::loadView('admin.print.finance.incoming_payment_individual', $data)->setPaper('a5', 'landscape');
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

    public function printIndividual(Request $request,$id){
        
        $pr = IncomingPayment::where('code',CustomHelper::decrypt($id))->first();
        $currentDateTime = Date::now();
        $formattedDate = $currentDateTime->format('d/m/Y H:i:s');        
        if($pr){
            $data = [
                'title'     => 'Incoming Payment',
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
             
            $pdf = Pdf::loadView('admin.print.finance.incoming_payment_individual', $data)->setPaper('a5', 'landscape');
            $pdf->render();
    
            $font = $pdf->getFontMetrics()->get_font("helvetica", "bold");
            $pdf->getCanvas()->page_text(505, 350, "PAGE: {PAGE_NUM} of {PAGE_COUNT}", $font, 10, array(0,0,0));
            $pdf->getCanvas()->page_text(422, 360, "Print Date ". $formattedDate, $font, 10, array(0,0,0));
            
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

    public function viewStructureTree(Request $request){
        function formatNominal($model) {
            if ($model->currency) {
                return $model->currency->symbol;
            } else {
                return "Rp.";
            }
        }
        $query = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();
        //utk yang berada di marketing order
        $data_id_mo=[];
        $data_id_mo_delivery = [];
        $data_id_mo_dp=[];
        $data_id_mo_return=[];
        $data_id_mo_invoice=[];
        $data_id_mo_memo=[];
        $data_incoming_payment=[];
        
        // utk yang berada di purcahsing menu
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
        $data_id_outgoing_payment=[];
        
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
        $data_id_pcb=[];

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

        $isTreeOP = false;
        $isTreeARInvoice = false;
        if($query){
            $data_ip=[
                "name"=>$query->code,
                "key" => $query->code,
                "color"=>"lightblue",
                'properties'=> [
                    ['name'=> "Tanggal :".$query->post_date],
                    
                 ],
                'url'=>request()->root()."/admin/finance/incoming_payment?code=".CustomHelper::encrypt($query->code),
            ];

            $data_go_chart[]= $data_ip;
            $data_incoming_payment[]=$query->id;
            foreach($query->incomingPaymentDetail as $rowipd){
                if($rowipd->outgoingPayment()->exists()){
                    $isTreeOP = true;
                   
                }
                if($rowipd->marketingOrderInvoice()->exists()){
                    $isTreeARInvoice = true;
                   
                }
                if($rowipd->marketingOrderDownPayment()->exists()){
                    $isTreeARInvoice = true;
                    
                }
            }

            if($isTreeOP){
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
                $finished_data_id_pcb=[];
                $added = true;
           
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
                                if($row_bill_detail->outgoingPayment()->exists()){
                                  
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
                                        'to'=>$query_cb->code,
                                        'string_link'=>$row_bill_detail->outgoingPayment->code.$query_cb->code,
                                    ];
                                    if(!in_array($row_bill_detail->outgoingPayment->id, $data_id_op)){
                                        $data_id_op[]= $row_bill_detail->outgoingPayment->id; 
                                        $added = true; 
                                    }
                                }
                                
                                
                                if($row_bill_detail->personalCloseBill()->exists()){
                                    $data_pcb = [
                                        'key'   => $row_bill_detail->personalCloseBill->code,
                                        "name"  => $row_bill_detail->personalCloseBill->code,
                                        
                                        'properties'=> [
                                            ['name'=> "Tanggal: ".date('d/m/Y',strtotime($row_bill_detail->personalCloseBill->post_date))],
                                            ['name'=> "Nominal: Rp".number_format($row_bill_detail->personalCloseBill->grandtotal,2,',','.')]
                                        ],
                                        'url'   =>request()->root()."/admin/finance/close_bill_personal?code=".CustomHelper::encrypt($row_bill_detail->personalCloseBill->code),
                                        "title" => $row_bill_detail->personalCloseBill->code,
                                    ];
                                    $data_go_chart[]=$data_pcb;
    
                                    $data_link[]=[
                                        'from'=>$row_bill_detail->personalCloseBill->code,
                                        'to'=>$query_cb->code,
                                        'string_link'=>$row_bill_detail->personalCloseBill->code.$query_cb->code,
                                    ];
                                    if(!in_array($row_bill_detail->personalCloseBill->id, $data_id_pcb)){
                                        $data_id_pcb[]= $row_bill_detail->personalCloseBill->id; 
                                        $added = true; 
                                    }
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
    
                                    if(!in_array($row_pyr_detail->paymentRequest->id, $data_id_pyrs)){
                                        $data_id_pyrs[] = $row_pyr_detail->paymentRequest->id;
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
                                        'from'=>$query_inventory_transfer_out->code,
                                        'to'=>$row_transfer_out_detail->landedCostDetail->landedCost->code,
                                        'string_link'=>$query_inventory_transfer_out->code.$row_transfer_out_detail->landedCostDetail->landedCost->code,
                                    ];
                                    if(!in_array($row_transfer_out_detail->landedCostDetail->landedCost->id,$data_id_lc)){
                                        $data_id_lc[] = $row_transfer_out_detail->landedCostDetail->landedCost->id;
                                        $added = true;
                                    }
                                
                                        
                                }
                            }
                        }
                    }
    
                    foreach($data_id_pcb as $pcb_id){
                        if(!in_array($pcb_id, $finished_data_id_pcb)){
                            $finished_data_id_pcb[]=$pcb_id;
                            $query_pcb = PersonalCloseBill::find($pcb_id);
    
                            foreach($query_pcb->personalCloseBillDetail as $row_pcbd){
                                if($row_pcbd->fundRequest()->exists()){
                                    $data_fund_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pcbd->fundRequest->code],
                                            ['name'=> "User :".$row_pcbd->fundRequest->account->name],
                                            ['name'=> "Nominal :".formatNominal($row_pcbd->fundRequest).number_format($row_pcbd->fundRequest->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pcbd->fundRequest->code,
                                        "name" => $row_pcbd->fundRequest->code,
                                        'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_pcbd->fundRequest->code), 
                                    ];
                                
                                    $data_go_chart[]=$data_fund_tempura;
                                    $data_link[]=[
                                        'from'=>$row_pcbd->fundRequest->code,
                                        'to'=>$query_pcb->code,
                                        'string_link'=>$row_pcbd->fundRequest->code.$query_pcb->code,
                                    ];
    
                                    if(!in_array($row_pcbd->fundRequest->id, $data_id_frs)){
                                        $data_id_frs[] = $row_pcbd->fundRequest->id;
                                        $added = true; 
                                    } 
                                }
                            }
    
                            if($query_pcb->closebillDetail()->exists()){
                                foreach($query_pcb->closebillDetail as $row_cbd){
                                    $data_cb_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_cbd->closeBill->code],
                                            ['name'=> "Nominal :".formatNominal($row_cbd->closeBill).number_format($row_cbd->closeBill->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_cbd->closeBill->code,
                                        "name" => $row_cbd->closeBill->code,
                                        'url'=>request()->root()."/admin/finance/fund_request?code=".CustomHelper::encrypt($row_cbd->closeBill->code), 
                                    ];
                                
                                    $data_go_chart[]=$data_cb_tempura;
                                    $data_link[]=[
                                        'from'=>$query_pcb->code,
                                        'to'=>$row_cbd->closeBill->code,
                                        'string_link'=>$query_pcb->code.$row_cbd->closeBill->code,
                                    ];
    
                                    if(!in_array($row_cbd->closeBill->id, $data_id_cb)){
                                        $data_id_cb[] = $row_cbd->closeBill->id;
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
                            if($query_fr->hasPaymentRequestDetail()->exists()){
                                foreach($query_fr->hasPaymentRequestDetail as $row_pyr_detail){
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
                            if($query_fr->personalCloseBillDetail()->exists()){
    
                                foreach($query_fr->personalCloseBillDetail as $row_pcbd){
                                    $data_pcb_tempura=[
                                        'properties'=> [
                                            ['name'=> "Tanggal :".$row_pcbd->personalCloseBill->post_date],
                                            ['name'=> "Nominal :".formatNominal($row_pcbd->personalCloseBill).number_format($row_pcbd->personalCloseBill->grandtotal,2,',','.')]
                                        ],
                                        "key" => $row_pcbd->personalCloseBill->code,
                                        "name" => $row_pcbd->personalCloseBill->code,
                                        'url'=>request()->root()."/admin/finance/close_bill_personal?code=".CustomHelper::encrypt($row_pcbd->personalCloseBill->code),
                                    ];
                                    $data_go_chart[]=$data_pcb_tempura;
                                    $data_link[]=[
                                        'from'=>$query_fr->code,
                                        'to'=>$row_pcbd->personalCloseBill->code,
                                        'string_link'=>$query_fr->code.$row_pcbd->personalCloseBill->code,
                                    ];
                                    if(!in_array($row_pcbd->personalCloseBill->id,$data_id_pcb)){
                                        $data_id_pcb[] = $row_pcbd->personalCloseBill->id;
                                        $added = true;
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
                                            'url'=>request()->root()."/admin/inventory/good_issue?code=".CustomHelper::encrypt($good_issue_detail->goodIssue->code),
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
            }
            
            if($isTreeARInvoice){
                
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
        }else {
            $response = [
                'status'  => 500,
                'message' => 'Data failed to delete.'
            ];
        }
        return response()->json($response);
    }

    public function done(Request $request){
        $query_done = IncomingPayment::where('code',CustomHelper::decrypt($request->id))->first();

        if($query_done){

            if(in_array($query_done->status,['1','2'])){
                $query_done->update([
                    'status'     => '3',
                    'done_id'    => session('bo_id'),
                    'done_date'  => date('Y-m-d H:i:s'),
                ]);
    
                activity()
                        ->performedOn(new IncomingPayment())
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
        $account = $request->account? $request->account : '';
        $currency = $request->currency ? $request->currency : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
		$modedata = $request->modedata? $request->modedata : '';
      
		return Excel::download(new ExportIncomingPaymentTransactionPage($search,$status,$company,$account,$currency,$end_date,$start_date,$modedata), 'purchase_down_payment'.uniqid().'.xlsx');
    }
}
<?php

namespace App\Http\Controllers\Purchase;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\OutgoingPayment;
use App\Models\PurchaseInvoice;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;

class PurchasePaymentHistoryController extends Controller
{
    protected $dataplaces;

    public function __construct(){
        $user = User::find(session('bo_id'));

    }

    public function index(Request $request)
    {
        $data = [
            'title'         => 'Laporan Histori Pembayaran',
            'content'       => 'admin.purchase.purchase_payment_history',
            'company'       => Company::where('status','1')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function datatable(Request $request){
        $column = [
            'id',
            'code',
            'user_id',
            'account_id',
            'company_id',
            'post_date',
            'received_date',
            'due_date',
            'document_date',
            'type',
            'document',
            'note',
            'tax_no',
            'tax_cut_no',
            'cut_date',
            'spk_no',
            'invoice_no',
            'subtotal',
            'percent_discount',
            'nominal_discount',
            'total',
            'tax',
            'grandtotal',
            'downpayment',
            'balance'
        ];

        $start  = $request->start;
        $length = $request->length;
        $order  = $column[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');
        $search = $request->input('search.value');

        $total_data = PurchaseInvoice::count();
        
        $query_data = PurchaseInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhere('tax_cut_no', 'like', "%$search%")
                            ->orWhere('spk_no', 'like', "%$search%")
                            ->orWhere('invoice_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('purchaseInvoiceDetail',function($query) use($search, $request){
                                $query->whereHasMorph('lookable',[PurchaseOrder::class,PurchaseInvoice::class,LandedCost::class,GoodReceipt::class,Coa::class],function (Builder $query) use ($search) {
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
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->offset($start)
            ->limit($length)
            ->orderBy($order, $dir)
            ->get();

        $total_filtered = PurchaseInvoice::where(function($query) use ($search, $request) {
                if($search) {
                    $query->where(function($query) use ($search, $request) {
                        $query->where('code', 'like', "%$search%")
                            ->orWhere('total', 'like', "%$search%")
                            ->orWhere('tax', 'like', "%$search%")
                            ->orWhere('grandtotal', 'like', "%$search%")
                            ->orWhere('downpayment', 'like', "%$search%")
                            ->orWhere('balance', 'like', "%$search%")
                            ->orWhere('note', 'like', "%$search%")
                            ->orWhere('tax_no', 'like', "%$search%")
                            ->orWhere('tax_cut_no', 'like', "%$search%")
                            ->orWhere('spk_no', 'like', "%$search%")
                            ->orWhere('invoice_no', 'like', "%$search%")
                            ->orWhereHas('user',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('account',function($query) use($search, $request){
                                $query->where('name','like',"%$search%")
                                    ->orWhere('employee_no','like',"%$search%");
                            })
                            ->orWhereHas('purchaseInvoiceDetail',function($query) use($search, $request){
                                $query->whereHasMorph('lookable',[PurchaseOrder::class,PurchaseInvoice::class,LandedCost::class,GoodReceipt::class,Coa::class],function (Builder $query) use ($search) {
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
                    $query->where('status', $request->status);
                }

                if($request->type){
                    $query->where('type',$request->type);
                }

                if($request->account_id){
                    $query->whereIn('account_id',$request->account_id);
                }

                if($request->company_id){
                    $query->where('company_id',$request->company_id);
                }
            })
            ->count();

        $response['data'] = [];
        if($query_data <> FALSE) {
            $nomor = $start + 1;
            foreach($query_data as $val) {
                if($val->hasPaymentRequestDetail()->exists()){
                    $color='btn-floating mb-1 btn-flat waves-effect waves-light purple darken-4 white-text btn-small';
                }else{
                    $color='btn-floating mb-1 btn-flat waves-effect waves-light pink darken-4 white-text btn-small';
                }
                $response['data'][] = [
                    $nomor,
                    $val->code,
                    $val->user->name,
                    $val->account->name,
                    $val->company->name,
                    date('d/m/y',strtotime($val->post_date)),
                    date('d/m/y',strtotime($val->received_date)),
                    date('d/m/y',strtotime($val->due_date)),
                    date('d/m/y',strtotime($val->document_date)),
                    $val->type(),
                    '<a href="'.$val->attachment().'" target="_blank"><i class="material-icons">attachment</i></a>',
                    $val->note,
                    $val->tax_no,
                    $val->tax_cut_no,
                    date('d/m/y',strtotime($val->cut_date)),
                    $val->spk_no,
                    $val->invoice_no,
                    number_format($val->subtotal,2,',','.'),
                    number_format($val->percent_discount,2,',','.'),
                    number_format($val->nominal_discount,2,',','.'),
                    number_format($val->total,2,',','.'),
                    number_format($val->tax,2,',','.'),
                    number_format($val->wtax,2,',','.'),
                    number_format($val->grandtotal,2,',','.'),
                    number_format($val->downpayment,2,',','.'),
                    number_format($val->balance,2,',','.'),
                    $val->status(),
                    ' 
                    <button type="button" class="'.$color.'" data-popup="tooltip" title="Lihat History" onclick="viewHistory(`' . CustomHelper::encrypt($val->code) . '`)"><i class="material-icons dp48">unarchive</i></button>
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

    public function viewHistoryPayment(Request $request){
        $query_invoice = PurchaseInvoice::where('code',CustomHelper::decrypt($request->code))->first();
        info($query_invoice);
        $data_temp = [];
        $grandtotal=$query_invoice->grandtotal;
        $downpayment=$query_invoice->downpayment;
        $tagihan=$query_invoice->balance;
        $memo=$query_invoice->totalMemo();
        $dibayar=0;
        $kurangbayar=0;
        if($query_invoice->hasPaymentRequestDetail()->exists()){
            foreach($query_invoice->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
                $query->whereHas('outgoingPayment');
            })->get() as $rowpayment){
                $data_temp[]=[
                    'post_date' => date('d M Y',strtotime($rowpayment->paymentRequest->outgoingPayment->post_date)),
                    'code_op' => $rowpayment->paymentRequest->outgoingPayment->code,
                    'code_pyr' => $rowpayment->paymentRequest->code,
                    'nominal' => number_format($rowpayment->nominal,2,',','.'),
                ];
            }
            if($query_invoice){
                $string = '';
                $string1 = '';
                foreach ($data_temp as $key => $row) {
                    $string .='<li>
                            <div class="timeline-badge blue">
                            <a class="tooltipped" data-position="top" data-tooltip="' . $row['post_date'] . '"><i class="material-icons white-text">trending_flat</i></a>
                            </div>
                            <div class="timeline-panel">
                            <div class="card m-0 hoverable" id="profile-card" style="overflow: visible;">
                                <div class="card-content">
                                <div style="display:-webkit-box;">
                                    <h5 class="card-title activator grey-text text-darken-4 mt-1 ml-3">' . $row['code_op'] . '</h5>
                                </div>
                                <p><i class="material-icons profile-card-i">copyright</i>' . $row['code_pyr'] . '</p>
                                <p><i class="material-icons profile-card-i">insert_invitation</i>' . $row['post_date'] . '</p>
                                <p><i class="material-icons profile-card-i">eject</i> ' . $row['nominal'] . '</p>
                                </div>
                            </div>
                            </div>
                        </li>';
                    $dibayar+=$rowpayment->nominal;
                    
                    $string1 .= '<tr>
                        <td class="center-align">' . ($key + 1) . '</td>
                        <td>' . $row['post_date']  . '</td>
                        <td class="center-align">' .$row['code_op']. '</td>
                        <td class="center-align">' . $row['code_pyr'] . '</td>
                        <td class="center-align">' . $row['nominal'] . '</td>
                    </tr>';
                }
                $string1 .='<tr>
                <td class="right-align" colspan="5">Total ='.number_format($dibayar,2,',','.').' </td>
                </tr>
                ';
                $string.='<li class="clearfix" style="float: none;"></li>';
                $response["tbody"] = $string;
                $response["tbody1"] = $string1;
                $response["title"] = CustomHelper::decrypt($request->code);
                $response["grandtotal"]=number_format($grandtotal,2,',','.');
                $response["downpayment"]=number_format($downpayment,2,',','.');
                $response["tagihan"]=number_format($tagihan,2,',','.');
                $response["dibayar"]=number_format($dibayar,2,',','.');
                $response["memo"]=number_format($memo,2,',','.');
                $response["kurangbayar"]=number_format($tagihan-$memo-$dibayar,2,',','.');
            }else{
                $response = [
                    'status'  => 500,
                    'message' => 'Data Tidak Dapat Diambil.'
                ]; 
            }
        }else{
            $response["title"] = CustomHelper::decrypt($request->code);
            $response["grandtotal"]=number_format($grandtotal,2,',','.');
            $response["downpayment"]=number_format($downpayment,2,',','.');
            $response["tagihan"]=number_format($tagihan,2,',','.');
            $response["dibayar"]=number_format($dibayar,2,',','.');
            $response["memo"]=number_format($memo,2,',','.');
            $response["kurangbayar"]=number_format($tagihan-$memo-$dibayar,2,',','.');
            $string="";
            $string1='';
            $string .='Belum Ada pembayaran';
            $string1 .='<tr>
            <td class="center-align" colspan="5">BELUM ADA PEMBAYARAN</td>
            </tr>
            ';
            $response["tbody"] = $string;
            $response["tbody1"] = $string1;
        }
            
        
        return response()->json($response);
    }


}

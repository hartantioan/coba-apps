<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportOutstandingAP;
use App\Exports\ExportOutstandingAR;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MarketingOrderOutstandingController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Outstanding AR',
            'content'   => 'admin.sales.outstanding_ar',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filterByDate(Request $request){
        $start_time = microtime(true);

        $array_filter = [];
        $query_data = MarketingOrderInvoice::where(function($query) use ( $request) {
                if($request->date) {
                    $query->whereDate('post_date', '<=', $request->date);
                }
            })
            ->whereIn('status',['2','3'])
            ->get();
        $query_data2 = MarketingOrderDownPayment::where(function($query) use ( $request) {
                if($request->date) {
                    $query->whereDate('post_date', '<=', $request->date);
                }
            })
            ->whereIn('status',['2','3'])
            ->get();
        if($query_data && $query_data2){
            $grandtotalAll = 0;
            foreach($query_data as $row){
                if($row->balancePaymentIncoming() > 0){
                    foreach($row->marketingOrderInvoiceDeliveryProcess as $rowdetail){
                        $price = $rowdetail->getPrice();
                        $rounding = $rowdetail->getRounding();
                        $grandtotal = $rowdetail->getGrandtotal();
                        $memo = $rowdetail->getMemo();
                        $payment = $rowdetail->getDownPayment() + $rowdetail->getPayment();
                        $balance = $grandtotal - $memo - $payment;
                        $array_filter[] = [
                            'code'              => $row->code,
                            'customer'          => $row->account->name,
                            'post_date'         => date('d/m/y',strtotime($row->post_date)),
                            'top'               => $row->account->top,
                            'item_name'         => $rowdetail->lookable->item->name,
                            'qty_order'         => number_format($rowdetail->lookable->marketingOrderDetail->qty,3,',','.'),
                            'qty'               => number_format($rowdetail->qty,3,',','.'),
                            'unit'              => $rowdetail->lookable->item->sellUnit->code,
                            'price'             => number_format($price,2,',','.'),
                            'total'             => number_format($rowdetail->total,2,',','.'),
                            'tax'               => number_format($rowdetail->tax,2,',','.'),
                            'total_after_tax'   => number_format($rowdetail->grandtotal,2,',','.'),
                            'rounding'          => number_format($rounding,2,',','.'),
                            'grandtotal'        => number_format($grandtotal,2,',','.'),
                            'memo'              => number_format($memo,2,',','.'),
                            'payment'           => number_format($payment,2,',','.'),
                            'balance'           => number_format($balance,2,',','.'),
                            'note'              => $rowdetail->note,
                        ];
                    }
                    $grandtotalAll += $balance;
                }
            }

            foreach($query_data2 as $row){
                if($row->balancePaymentIncoming() > 0){
                    $rounding = 0;
                    $memo = $row->totalMemo();
                    $payment = $row->totalPay();
                    $balance = $row->grandtotal - $memo - $payment;
                    $array_filter[] = [
                        'code'              => $row->code,
                        'customer'          => $row->account->name,
                        'post_date'         => date('d/m/y',strtotime($row->post_date)),
                        'top'               => $row->account->top,
                        'item_name'         => '-',
                        'qty_order'         => 1,
                        'qty'               => 1,
                        'unit'              => '-',
                        'price'             => number_format($row->total,2,',','.'),
                        'total'             => number_format($row->total,2,',','.'),
                        'tax'               => number_format($row->tax,2,',','.'),
                        'total_after_tax'   => number_format($row->grandtotal,2,',','.'),
                        'rounding'          => number_format(0,2,',','.'),
                        'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                        'memo'              => number_format($memo,2,',','.'),
                        'payment'           => number_format($payment,2,',','.'),
                        'balance'           => number_format($balance,2,',','.'),
                        'note'              => $row->note,
                    ];
                    $grandtotalAll += $balance;
                }
            }

            $end_time = microtime(true);
        
            $execution_time = ($end_time - $start_time);

            $response =[
                'status'            => 200,
                'data'              => $array_filter,
                'grandtotal'        => number_format($grandtotalAll,2,',','.'),
                'execution_time'    => round($execution_time,5),
            ];
        }else{
            $response =[
                'status'  => 500,
                'message' =>'Data error tidak ditemukan'
            ];
        }
        return response()->json($response);
    }

    public function export(Request $request){
		return Excel::download(new ExportOutstandingAR($request->date), 'outstanding_ar_'.uniqid().'.xlsx');
    }
}

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
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;

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
        if($query_data){
            $grandtotalAll = 0;
            foreach($query_data as $row){
                $payment = round($row->totalPayByDate($request->date),2);
                $balance = round($row->grandtotal - $payment,2);
                if($balance > 0){
                    $array_filter[] = [
                        'code'              => $row->code,
                        'customer'          => $row->account->name,
                        'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                        'due_date'          => date('d/m/Y',strtotime($row->due_date)),
                        'note'              => $row->note,
                        'top'               => $row->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderDeliveryProcess->marketingOrderDelivery->top_internal : '-',
                        'type'              => $row->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderDeliveryProcess->marketingOrderDelivery->soType() : '-',
                        'total'             => CustomHelper::formatConditionalQty($row->grandtotal),
                        'payment'           => CustomHelper::formatConditionalQty($payment),
                        'balance'           => CustomHelper::formatConditionalQty($balance),
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

<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportAgingAR;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MarketingOrderAgingController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Aging AP',
            'content'   => 'admin.sales.aging_ar',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        
        $start_time = microtime(true);
        
        $date = $request->date;

        $results = DB::select("
            SELECT 
                *,
                IFNULL((SELECT 
                    SUM(ipd.total) 
                    FROM incoming_payment_details ipd 
                    JOIN incoming_payments ip
                        ON ip.id = ipd.incoming_payment_id
                    WHERE 
                        ipd.lookable_id = moi.id 
                        AND ipd.lookable_type = 'marketing_order_invoices'
                        AND ip.post_date <= :date1
                        AND ip.status IN ('2','3')
                ),0) AS total_payment,
                IFNULL((
                    SELECT
                        SUM(momd.balance)
                        FROM marketing_order_memo_details momd
                        JOIN marketing_order_memos mom
                            ON mom.id = momd.marketing_order_memo_id
                        JOIN marketing_order_invoice_details midd
                            ON midd.marketing_order_invoice_id = moi.id
                            AND midd.id = momd.lookable_id
                        WHERE momd.lookable_type = 'marketing_order_invoice_details'
                        AND mom.post_date <= :date2
                        AND mom.status IN ('2','3')
                ),0) AS total_memo,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM marketing_order_invoices moi
                LEFT JOIN users u
                    ON u.id = moi.account_id
                WHERE 
                    moi.post_date <= :date3
                    AND moi.balance > 0
                    AND moi.status IN ('2','3')
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
        ));

        $results2 = DB::select("
            SELECT 
                *,
                IFNULL((SELECT 
                    SUM(ipd.total)
                    FROM incoming_payment_details ipd 
                    JOIN incoming_payments ip
                        ON ip.id = ipd.incoming_payment_id
                    WHERE 
                        ipd.lookable_id = modp.id 
                        AND ipd.lookable_type = 'marketing_order_down_payments'
                        AND ip.post_date <= :date1
                        AND ip.status IN ('2','3')
                ),0) AS total_payment,
                IFNULL((
                    SELECT
                        SUM(momd.balance)
                        FROM marketing_order_memo_details momd
                        JOIN marketing_order_memos mom
                            ON mom.id = momd.marketing_order_memo_id
                        WHERE momd.lookable_type = 'marketing_order_down_payments'
                        AND momd.lookable_id = modp.id
                        AND mom.post_date <= :date2
                        AND mom.status IN ('2','3')
                ),0) AS total_memo,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM marketing_order_down_payments modp
                LEFT JOIN users u
                    ON u.id = modp.account_id
                WHERE 
                    modp.post_date <= :date3
                    AND modp.grandtotal > 0
                    AND modp.status IN ('2','3')
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
        ));

        $newData = []; 

        foreach($results as $row){
            $balance = $row->balance - $row->total_payment - $row->total_memo;
            if($balance > 0){
                $daysDiff = $this->dateDiffInDays($row->due_date,$date);
                $index = $this->findDuplicate($row->account_code,$newData);
                if($index >= 0){
                    $newData[$index]['balance0'] = $daysDiff <= 0 ? $newData[$index]['balance0'] + $balance : $newData[$index]['balance0'];
                    $newData[$index]['balance30'] = $daysDiff <= 30 && $daysDiff > 0 ? $newData[$index]['balance30'] + $balance : $newData[$index]['balance30'];
                    $newData[$index]['balance60'] = $daysDiff <= 60 && $daysDiff > 30 ? $newData[$index]['balance60'] + $balance : $newData[$index]['balance60'];
                    $newData[$index]['balance90'] = $daysDiff <= 90 && $daysDiff > 60 ? $newData[$index]['balance90'] + $balance : $newData[$index]['balance90'];
                    $newData[$index]['balanceOver'] = $daysDiff > 90 ? $newData[$index]['balanceOver'] + $balance : $newData[$index]['balanceOver'];
                    $newData[$index]['total'] = $newData[$index]['total'] + $balance;
                    $newData[$index]['arrInvoiceBalance0'][] = $daysDiff <= 0 ? $row->code : null;
                    $newData[$index]['arrInvoiceBalance30'][] = $daysDiff <= 30 && $daysDiff > 0 ? $row->code : null;
                    $newData[$index]['arrInvoiceBalance60'][] = $daysDiff <= 60 && $daysDiff > 30 ? $row->code : null;
                    $newData[$index]['arrInvoiceBalance90'][] = $daysDiff <= 90 && $daysDiff > 60 ? $row->code : null;
                    $newData[$index]['arrInvoiceBalanceOver'][] = $daysDiff > 90 ? $row->code : null;
                }else{
                    $newData[] = [
                        'customer_code'         => $row->account_code,
                        'customer_name'         => $row->account_name,
                        'balance0'              => $daysDiff <= 0 ? $balance : 0,
                        'balance30'             => $daysDiff <= 30 && $daysDiff > 0 ? $balance : 0,
                        'balance60'             => $daysDiff <= 60 && $daysDiff > 30 ? $balance : 0,
                        'balance90'             => $daysDiff <= 90 && $daysDiff > 60 ? $balance : 0,
                        'balanceOver'           => $daysDiff > 90 ? $balance : 0,
                        'total'                 => $balance,
                        'arrInvoiceBalance0'    => $daysDiff <= 0 ? array($row->code) : [],
                        'arrInvoiceBalance30'   => $daysDiff <= 30 && $daysDiff > 0 ? array($row->code) : [],
                        'arrInvoiceBalance60'   => $daysDiff <= 60 && $daysDiff > 30 ? array($row->code) : [],
                        'arrInvoiceBalance90'   => $daysDiff <= 90 && $daysDiff > 60 ? array($row->code) : [],
                        'arrInvoiceBalanceOver' => $daysDiff > 90 ? array($row->code) : [],
                    ];
                }
            }
        }

        foreach($results2 as $row){
            $balance = $row->grandtotal - $row->total_payment - $row->total_memo;
            if($balance > 0){
                $daysDiff = $this->dateDiffInDays($row->due_date,$date);
                $index = $this->findDuplicate($row->account_code,$newData);
                if($index >= 0){
                    $newData[$index]['balance0'] = $daysDiff <= 0 ? $newData[$index]['balance0'] + $balance : $newData[$index]['balance0'];
                    $newData[$index]['balance30'] = $daysDiff <= 30 && $daysDiff > 0 ? $newData[$index]['balance30'] + $balance : $newData[$index]['balance30'];
                    $newData[$index]['balance60'] = $daysDiff <= 60 && $daysDiff > 30 ? $newData[$index]['balance60'] + $balance : $newData[$index]['balance60'];
                    $newData[$index]['balance90'] = $daysDiff <= 90 && $daysDiff > 60 ? $newData[$index]['balance90'] + $balance : $newData[$index]['balance90'];
                    $newData[$index]['balanceOver'] = $daysDiff > 90 ? $newData[$index]['balanceOver'] + $balance : $newData[$index]['balanceOver'];
                    $newData[$index]['total'] = $newData[$index]['total'] + $balance;
                    $newData[$index]['arrInvoiceBalance0'][] = $daysDiff <= 0 ? $daysDiff : null;
                    $newData[$index]['arrInvoiceBalance30'][] = $daysDiff <= 30 && $daysDiff > 0 ? $row->code : null;
                    $newData[$index]['arrInvoiceBalance60'][] = $daysDiff <= 60 && $daysDiff > 30 ? $row->code : null;
                    $newData[$index]['arrInvoiceBalance90'][] = $daysDiff <= 90 && $daysDiff > 60 ? $row->code : null;
                    $newData[$index]['arrInvoiceBalanceOver'][] = $daysDiff > 90 ? $row->code : null;
                }else{
                    $newData[] = [
                        'customer_code'         => $row->account_code,
                        'customer_name'         => $row->account_name,
                        'balance0'              => $daysDiff <= 0 ? $balance : 0,
                        'balance30'             => $daysDiff <= 30 && $daysDiff > 0 ? $balance : 0,
                        'balance60'             => $daysDiff <= 60 && $daysDiff > 30 ? $balance : 0,
                        'balance90'             => $daysDiff <= 90 && $daysDiff > 60 ? $balance : 0,
                        'balanceOver'           => $daysDiff > 90 ? $balance : 0,
                        'total'                 => $balance,
                        'arrInvoiceBalance0'    => $daysDiff <= 0 ? array($row->code) : [],
                        'arrInvoiceBalance30'   => $daysDiff <= 30 && $daysDiff > 0 ? array($row->code) : [],
                        'arrInvoiceBalance60'   => $daysDiff <= 60 && $daysDiff > 30 ? array($row->code) : [],
                        'arrInvoiceBalance90'   => $daysDiff <= 90 && $daysDiff > 60 ? array($row->code) : [],
                        'arrInvoiceBalanceOver' => $daysDiff > 90 ? array($row->code) : [],
                    ];
                }
            }
        }

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);
        
        $response =[
            'status'            => 200,
            'content'           => $newData,
            'execution_time'    => $execution_time,
        ];

        return response()->json($response);
    }

    public function showDetail(Request $request){
        
        $arrInvoice = explode(',',$request->invoice);
        $date = $request->date;
        $results = [];
        $grandtotal = 0;

        foreach($arrInvoice as $row){
            $prefix = substr($row,0,4);
            if($prefix == 'PODP'){
                $dp = PurchaseDownPayment::where('code',$row)->first();
                if($dp){
                    $memo = $dp->totalMemoByDate($date);
                    $paid = $dp->totalPaidByDate($date);
                    $balance = $dp->grandtotal - $memo - $paid;
                    $results[] = [
                        'code'          => $dp->code,
                        'vendor'        => $dp->supplier->name,
                        'post_date'     => date('d/m/y',strtotime($dp->post_date)),
                        'rec_date'      => '-',
                        'due_date'      => date('d/m/y',strtotime($dp->due_date)),
                        'due_days'      => $this->dateDiffInDays($dp->due_date,$date),
                        'grandtotal'    => number_format($dp->grandtotal,2,',','.'),
                        'memo'          => number_format($memo,2,',','.'),
                        'paid'          => number_format($paid,2,',','.'),
                        'balance'       => number_format($balance,2,',','.'),
                    ];
                    $grandtotal += $balance;
                }
            }else{
                $pi = PurchaseInvoice::where('code',$row)->first();
                if($pi){
                    $memo = $pi->totalMemoByDate($date);
                    $paid = $pi->getTotalPaidDate($date);
                    $balance = $pi->balance - $memo - $paid;
                    $results[] = [
                        'code'          => $pi->code,
                        'vendor'        => $pi->account->name,
                        'post_date'     => date('d/m/y',strtotime($pi->post_date)),
                        'rec_date'      => date('d/m/y',strtotime($pi->received_date)),
                        'due_date'      => date('d/m/y',strtotime($pi->due_date)),
                        'due_days'      => $this->dateDiffInDays($pi->due_date,$date),
                        'grandtotal'    => number_format($pi->balance,2,',','.'),
                        'memo'          => number_format($memo,2,',','.'),
                        'paid'          => number_format($paid,2,',','.'),
                        'balance'       => number_format($balance,2,',','.'),
                    ];
                    $grandtotal += $balance;
                }
            }
        }
        
        $response = [
            'status'    => 200,
            'result'    => $results,
            'grandtotal'=> number_format($grandtotal,2,',','.'),
        ];

        return response()->json($response);
    }

    public function export(Request $request){
		return Excel::download(new ExportAgingAP($request->date), 'aging_ap_'.uniqid().'.xlsx');
    }

    function findDuplicate($value,$array){
        $index = -1;
        foreach($array as $key => $row){
            if($row['customer_code'] == $value){
                $index = $key;
            }
        }
        return $index;
    }

    function dateDiffInDays($date1, $date2) {
    
        // Calculating the difference in timestamps
        $diff = strtotime($date2) - strtotime($date1);
      
        // 1 day = 24 hours
        // 24 * 60 * 60 = 86400 seconds
        return round($diff / 86400);
    }
}
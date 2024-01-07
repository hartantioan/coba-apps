<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportAgingAP;
use App\Http\Controllers\Controller;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AgingAPController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Aging AP',
            'content'   => 'admin.purchase.aging_ap',
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
                    SUM(nominal) 
                    FROM payment_request_details prd 
                    JOIN outgoing_payments op
                        ON op.payment_request_id = prd.payment_request_id
                    WHERE 
                        prd.lookable_id = pi.id 
                        AND prd.lookable_type = 'purchase_invoices'
                        AND op.post_date <= :date1
                        AND op.status IN ('2','3')
                ),0) AS total_payment,
                IFNULL((
                    SELECT
                        SUM(pmd.grandtotal)
                        FROM purchase_memo_details pmd
                        JOIN purchase_memos pm
                            ON pm.id = pmd.purchase_memo_id
                        JOIN purchase_invoice_details pid
                            ON pid.purchase_invoice_id = pi.id
                            AND pid.id = pmd.lookable_id
                        WHERE pmd.lookable_type = 'purchase_invoice_details'
                        AND pm.post_date <= :date2
                        AND pm.status IN ('2','3')
                ),0) AS total_memo,
                IFNULL((
                    SELECT
                        SUM(prd.nominal)
                        FROM payment_request_details prd
                        JOIN payment_requests pr
                            ON pr.id = prd.payment_request_id
                        WHERE prd.lookable_type = 'purchase_invoices'
                        AND prd.lookable_id = pi.id
                        AND pr.post_date <= :date3
                        AND pr.status IN ('2','3')
                        AND pr.payment_type = '5'
                ),0) AS total_reconcile,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM purchase_invoices pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date4
                    AND pi.balance > 0
                    AND pi.status IN ('2','3')
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
        ));

        $results2 = DB::select("
            SELECT 
                *,
                pi.top AS topdp,
                IFNULL((SELECT 
                    SUM(nominal) 
                    FROM payment_request_details prd 
                    JOIN outgoing_payments op
                        ON op.payment_request_id = prd.payment_request_id
                    WHERE 
                        prd.lookable_id = pi.id 
                        AND prd.lookable_type = 'purchase_down_payments'
                        AND op.post_date <= :date1
                        AND op.status IN ('2','3')
                ),0) AS total_payment,
                IFNULL((
                    SELECT
                        SUM(pmd.grandtotal)
                        FROM purchase_memo_details pmd
                        JOIN purchase_memos pm
                            ON pm.id = pmd.purchase_memo_id
                        WHERE pmd.lookable_type = 'purchase_down_payments'
                        AND pmd.lookable_id = pi.id
                        AND pm.post_date <= :date2
                        AND pm.status IN ('2','3')
                ),0) AS total_memo,
                IFNULL((
                    SELECT
                        SUM(prd.nominal)
                        FROM payment_request_details prd
                        JOIN payment_requests pr
                            ON pr.id = prd.payment_request_id
                        WHERE prd.lookable_type = 'purchase_down_payments'
                        AND prd.lookable_id = pi.id
                        AND pr.post_date <= :date3
                        AND pr.status IN ('2','3')
                        AND pr.payment_type = '5'
                ),0) AS total_reconcile,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM purchase_down_payments pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date4
                    AND pi.grandtotal > 0
                    AND pi.status IN ('2','3')
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
        ));

        $countPeriod = 1;
        $column = intval($request->column);
        $countPeriod += $column + 1;
        $interval = intval($request->interval);
        $totalDays = $column * $interval;
        $arrColumn = [];
        $arrColumn[] = [
            'name'                          => 'Belum jatuh tempo',
            'start'                         => -999999999999999999,
            'end'                           => 0,
            'total'                         => 0,
        ];
        for($i=1;$i<=$column;$i++){
            $end = $i * $interval;
            $start = ($end - $interval) + 1;
            $arrColumn[] = [
                'name'                          => ''.$start.'-'.$end.' hari',
                'start'                         => $start,
                'end'                           => $end,
                'total'                         => 0,
            ];
        }
        $arrColumn[] = [
            'name'                          => 'Diatas '.$totalDays.' hari',
            'start'                         => $totalDays + 1,
            'end'                           => 999999999999999999,
            'total'                         => 0,
        ];

        $newData = []; 

        foreach($results as $row){
            $balance = $row->balance - $row->total_payment - $row->total_memo - $row->total_reconcile;
            if($balance > 0){
                $daysDiff = $this->dateDiffInDays($row->due_date,$date);
                $index = $this->findDuplicate($row->account_code,$newData);
                if($index >= 0){
                    foreach($newData[$index]['data'] as $key => $rowdata){
                        if($daysDiff <= $rowdata['end'] && $daysDiff >= $rowdata['start']){
                            $newData[$index]['data'][$key]['balance'] += $balance;
                            $newData[$index]['total'] += $balance;
                            $arrColumn[$key]['total'] += $balance;
                            $newData[$index]['data'][$key]['list_invoice'][] = $row->code;
                        }
                    }
                }else{
                    $arrDetail = [];
                    foreach($arrColumn as $key => $rowcolumn){
                        if($daysDiff <= $rowcolumn['end'] && $daysDiff >= $rowcolumn['start']){
                            $arrDetail[] = [
                                'name'          => $rowcolumn['name'],
                                'start'         => $rowcolumn['start'],
                                'end'           => $rowcolumn['end'],
                                'balance'       => $balance,
                                'list_invoice'  => array($row->code),
                            ];
                            $arrColumn[$key]['total'] += $balance;
                        }else{
                            $arrDetail[] = [
                                'name'          => $rowcolumn['name'],
                                'start'         => $rowcolumn['start'],
                                'end'           => $rowcolumn['end'],
                                'balance'       => 0,
                                'list_invoice'  => [],
                            ];
                        }
                    }
                    $newData[] = [
                        'supplier_code'         => $row->account_code,
                        'supplier_name'         => $row->account_name,
                        'data'                  => $arrDetail,
                        'total'                 => $balance,
                    ];
                }
            }
        }

        foreach($results2 as $row){
            $balance = $row->grandtotal - $row->total_payment - $row->total_memo - $row->total_reconcile;
            $due_date = $row->due_date ? $row->due_date : date('Y-m-d', strtotime($row->post_date. ' + '.$row->topdp.' day'));
            if($balance > 0){
                $daysDiff = $this->dateDiffInDays($due_date,$date);
                $index = $this->findDuplicate($row->account_code,$newData);
                if($index >= 0){
                    foreach($newData[$index]['data'] as $key => $rowdata){
                        if($daysDiff <= $rowdata['end'] && $daysDiff >= $rowdata['start']){
                            $newData[$index]['data'][$key]['balance'] += $balance;
                            $newData[$index]['total'] += $balance;
                            $arrColumn[$key]['total'] += $balance;
                            $newData[$index]['data'][$key]['list_invoice'][] = $row->code;
                        }
                    }
                }else{
                    $arrDetail = [];
                    foreach($arrColumn as $key => $rowcolumn){
                        if($daysDiff <= $rowcolumn['end'] && $daysDiff >= $rowcolumn['start']){
                            $arrDetail[] = [
                                'name'          => $rowcolumn['name'],
                                'start'         => $rowcolumn['start'],
                                'end'           => $rowcolumn['end'],
                                'balance'       => $balance,
                                'list_invoice'  => array($row->code),
                            ];
                            $arrColumn[$key]['total'] += $balance;
                        }else{
                            $arrDetail[] = [
                                'name'          => $rowcolumn['name'],
                                'start'         => $rowcolumn['start'],
                                'end'           => $rowcolumn['end'],
                                'balance'       => 0,
                                'list_invoice'  => [],
                            ];
                        }
                    }
                    $newData[] = [
                        'supplier_code'         => $row->account_code,
                        'supplier_name'         => $row->account_name,
                        'data'                  => $arrDetail,
                        'total'                 => $balance,
                    ];
                }
            }
        }

        $html = '<table class="bordered" style="font-size:10px;min-width:100% !important;">
        <thead id="head_detail">
            <tr>
                <th rowspan="2" class="center-align">No.</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Supplier</th>
                <th rowspan="2" class="center-align" style="min-width:175px !important;">Total Hutang</th>
                <th colspan="'.$countPeriod.'">Nominal Jatuh Tempo (Dari Tgl. Posting dan Tgl. Jatuh Tempo)</th>
            </tr>
            <tr>';
                
        foreach($arrColumn as $row){
            $html .= '<th class="center-align" style="min-width:175px !important;">'.$row['name'].'</th>';
        }
            
        $html .= '</tr>
        </thead>
        <tbody>';

        foreach($newData as $key => $row){
            $html .= '<tr class="row_detail"><td class="center-align">'.($key + 1).'</td><td>'.$row['supplier_name'].'</td>';

            $html .= '<td class="right-align">'.number_format($row['total'],2,',','.').'</td>';

            foreach($row['data'] as $rowdetail){
                $html .= '<td class="right-align '.($rowdetail['balance'] > 0 ? 'gradient-45deg-yellow-teal blue-text text-darken-2' : '').'" onclick="detailShow(this)" data-invoice="'.implode(',',$rowdetail['list_invoice']).'">'.number_format($rowdetail['balance'],2,',','.').'</td>';
            }

            $html .= '</tr>';
        }

        $grandtotal = 0;

        foreach($arrColumn as $row){
            $grandtotal += $row['total'];
        }

        $html .= '<tr id="text-grandtotal">
                    <td class="right-align" colspan="2">Total</td>
                    <td class="right-align">'.number_format($grandtotal,2,',','.').'</td>';

        foreach($arrColumn as $row){
            $html .= '<td class="right-align">'.number_format($row['total'],2,',','.').'</td>';
            $grandtotal += $row['total'];
        }

        $html .= '</tr>';

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);

        $html .= '<tr id="text-grandtotal">
                    <td colspan="'.($countPeriod + 3).'">Waktu proses : '.$execution_time.' detik</td>
                </tr>';

        $html .= '</tbody></table>';
        
        $response =[
            'status'            => 200,
            'content'           => count($newData) > 0 ? $html : '',
        ];

        return response()->json($response);
    }

    public function filterDetail(Request $request){
        
        $start_time = microtime(true);
        
        $date = $request->date;

        $results = DB::select("
            SELECT 
                *,
                IFNULL((SELECT 
                    SUM(nominal) 
                    FROM payment_request_details prd 
                    JOIN outgoing_payments op
                        ON op.payment_request_id = prd.payment_request_id
                    WHERE 
                        prd.lookable_id = pi.id 
                        AND prd.lookable_type = 'purchase_invoices'
                        AND op.post_date <= :date1
                        AND op.status IN ('2','3')
                ),0) AS total_payment,
                IFNULL((
                    SELECT
                        SUM(pmd.grandtotal)
                        FROM purchase_memo_details pmd
                        JOIN purchase_memos pm
                            ON pm.id = pmd.purchase_memo_id
                        JOIN purchase_invoice_details pid
                            ON pid.purchase_invoice_id = pi.id
                            AND pid.id = pmd.lookable_id
                        WHERE pmd.lookable_type = 'purchase_invoice_details'
                        AND pm.post_date <= :date2
                        AND pm.status IN ('2','3')
                ),0) AS total_memo,
                IFNULL((
                    SELECT
                        SUM(prd.nominal)
                        FROM payment_request_details prd
                        JOIN payment_requests pr
                            ON pr.id = prd.payment_request_id
                        WHERE prd.lookable_type = 'purchase_invoices'
                        AND prd.lookable_id = pi.id
                        AND pr.post_date <= :date3
                        AND pr.status IN ('2','3')
                        AND pr.payment_type = '5'
                ),0) AS total_reconcile,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM purchase_invoices pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date4
                    AND pi.balance > 0
                    AND pi.status IN ('2','3')
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
        ));

        $results2 = DB::select("
            SELECT 
                *,
                pi.top AS topdp,
                IFNULL((SELECT 
                    SUM(nominal) 
                    FROM payment_request_details prd 
                    JOIN outgoing_payments op
                        ON op.payment_request_id = prd.payment_request_id
                    WHERE 
                        prd.lookable_id = pi.id 
                        AND prd.lookable_type = 'purchase_down_payments'
                        AND op.post_date <= :date1
                        AND op.status IN ('2','3')
                ),0) AS total_payment,
                IFNULL((
                    SELECT
                        SUM(pmd.grandtotal)
                        FROM purchase_memo_details pmd
                        JOIN purchase_memos pm
                            ON pm.id = pmd.purchase_memo_id
                        WHERE pmd.lookable_type = 'purchase_down_payments'
                        AND pmd.lookable_id = pi.id
                        AND pm.post_date <= :date2
                        AND pm.status IN ('2','3')
                ),0) AS total_memo,
                IFNULL((
                    SELECT
                        SUM(prd.nominal)
                        FROM payment_request_details prd
                        JOIN payment_requests pr
                            ON pr.id = prd.payment_request_id
                        WHERE prd.lookable_type = 'purchase_down_payments'
                        AND prd.lookable_id = pi.id
                        AND pr.post_date <= :date3
                        AND pr.status IN ('2','3')
                        AND pr.payment_type = '5'
                ),0) AS total_reconcile,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM purchase_down_payments pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date4
                    AND pi.grandtotal > 0
                    AND pi.status IN ('2','3')
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
        ));

        $countPeriod = 1;
        $column = intval($request->column);
        $countPeriod += $column + 1;
        $interval = intval($request->interval);
        $totalDays = $column * $interval;
        $arrColumn = [];
        $arrColumn[] = [
            'name'                          => 'Belum jatuh tempo',
            'start'                         => -999999999999999999,
            'end'                           => 0,
            'total'                         => 0,
        ];
        for($i=1;$i<=$column;$i++){
            $end = $i * $interval;
            $start = ($end - $interval) + 1;
            $arrColumn[] = [
                'name'                          => ''.$start.'-'.$end.' hari',
                'start'                         => $start,
                'end'                           => $end,
                'total'                         => 0,
            ];
        }
        $arrColumn[] = [
            'name'                          => 'Diatas '.$totalDays.' hari',
            'start'                         => $totalDays + 1,
            'end'                           => 999999999999999999,
            'total'                         => 0,
        ];

        $newData = []; 

        foreach($results as $row){
            $balance = $row->balance - $row->total_payment - $row->total_memo - $row->total_reconcile;
            if($balance > 0){
                $daysDiff = $this->dateDiffInDays($row->due_date,$date);
                $arrDetail = [];
                foreach($arrColumn as $key => $rowcolumn){
                    if($daysDiff <= $rowcolumn['end'] && $daysDiff >= $rowcolumn['start']){
                        $arrDetail[] = [
                            'name'          => $rowcolumn['name'],
                            'start'         => $rowcolumn['start'],
                            'end'           => $rowcolumn['end'],
                            'balance'       => $balance,
                        ];
                        $arrColumn[$key]['total'] += $balance;
                    }else{
                        $arrDetail[] = [
                            'name'          => $rowcolumn['name'],
                            'start'         => $rowcolumn['start'],
                            'end'           => $rowcolumn['end'],
                            'balance'       => 0,
                        ];
                    }
                }
                $newData[] = [
                    'supplier_code'         => $row->account_code,
                    'supplier_name'         => $row->account_name,
                    'invoice'               => $row->code,
                    'data'                  => $arrDetail,
                    'total'                 => $balance,
                ];
            }
        }

        foreach($results2 as $row){
            $balance = $row->grandtotal - $row->total_payment - $row->total_memo - $row->total_reconcile;
            $due_date = $row->due_date ? $row->due_date : date('Y-m-d', strtotime($row->post_date. ' + '.$row->topdp.' day'));
            if($balance > 0){
                $daysDiff = $this->dateDiffInDays($due_date,$date);
                $arrDetail = [];
                foreach($arrColumn as $key => $rowcolumn){
                    if($daysDiff <= $rowcolumn['end'] && $daysDiff >= $rowcolumn['start']){
                        $arrDetail[] = [
                            'name'          => $rowcolumn['name'],
                            'start'         => $rowcolumn['start'],
                            'end'           => $rowcolumn['end'],
                            'balance'       => $balance,
                        ];
                        $arrColumn[$key]['total'] += $balance;
                    }else{
                        $arrDetail[] = [
                            'name'          => $rowcolumn['name'],
                            'start'         => $rowcolumn['start'],
                            'end'           => $rowcolumn['end'],
                            'balance'       => 0,
                        ];
                    }
                }
                $newData[] = [
                    'supplier_code'         => $row->account_code,
                    'supplier_name'         => $row->account_name,
                    'invoice'               => $row->code,
                    'data'                  => $arrDetail,
                    'total'                 => $balance,
                ];
            }
        }

        $html = '<table class="bordered" style="font-size:10px;min-width:100% !important;">
        <thead id="head_detail">
            <tr>
                <th rowspan="2" class="center-align">No.</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Supplier</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Invoice</th>
                <th rowspan="2" class="center-align" style="min-width:175px !important;">Nominal</th>
                <th colspan="'.$countPeriod.'">Nominal Jatuh Tempo (Dari Tgl. Posting dan Tgl. Jatuh Tempo)</th>
                
            </tr>
            <tr>';
                
        foreach($arrColumn as $row){
            $html .= '<th class="center-align" style="min-width:175px !important;">'.$row['name'].'</th>';
        }
            
        $html .= '</tr>
        </thead>
        <tbody>';

        foreach($newData as $key => $row){
            $html .= '<tr class="row_detail"><td class="center-align">'.($key + 1).'</td><td>'.$row['supplier_name'].'</td><td>'.$row['invoice'].'</td>';

            $html .= '<td class="right-align">'.number_format($row['total'],2,',','.').'</td>';

            foreach($row['data'] as $rowdetail){
                $html .= '<td class="right-align '.($rowdetail['balance'] > 0 ? 'gradient-45deg-yellow-teal blue-text text-darken-2' : '').'">'.number_format($rowdetail['balance'],2,',','.').'</td>';
            }

            $html .= '</tr>';
        }

        $grandtotal = 0;

        foreach($arrColumn as $row){
            $grandtotal += $row['total'];
        }

        $html .= '<tr id="text-grandtotal">
                    <td class="right-align" colspan="3">Total</td>
                    <td class="right-align">'.number_format($grandtotal,2,',','.').'</td>';

        foreach($arrColumn as $row){
            $html .= '<td class="right-align">'.number_format($row['total'],2,',','.').'</td>';
        }

        $html .= '</tr>';

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);

        $html .= '<tr id="text-grandtotal">
                    <td colspan="'.($countPeriod + 3).'">Waktu proses : '.$execution_time.' detik</td>
                </tr>';

        $html .= '</tbody></table>';
        
        $response =[
            'status'            => 200,
            'content'           => count($newData) > 0 ? $html : '',
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
            if($prefix == 'APDP'){
                $dp = PurchaseDownPayment::where('code',$row)->first();
                if($dp){
                    $memo = $dp->totalMemoByDate($date);
                    $paid = $dp->totalPaidByDate($date);
                    $balance = $dp->grandtotal - $memo - $paid;
                    $due_date = $dp->due_date ? $dp->due_date : date('Y-m-d', strtotime($dp->post_date. ' + '.$dp->top.' day'));
                    $results[] = [
                        'code'          => $dp->code,
                        'vendor'        => $dp->supplier->name,
                        'post_date'     => date('d/m/y',strtotime($dp->post_date)),
                        'rec_date'      => '-',
                        'due_date'      => $due_date,
                        'due_days'      => $this->dateDiffInDays($due_date,$date),
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
		return Excel::download(new ExportAgingAP($request->date,$request->interval,$request->column,$request->type), 'aging_ap_'.uniqid().'.xlsx');
    }

    function findDuplicate($value,$array){
        $index = -1;
        foreach($array as $key => $row){
            if($row['supplier_code'] == $value){
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
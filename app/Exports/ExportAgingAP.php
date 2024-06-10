<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportAgingAP implements FromView, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $date, $interval, $column, $type;

    public function __construct(string $date, int $interval, int $column, int $type)
    {
        $this->date = $date ? $date : '';
        $this->interval = $interval ? $interval : 0;
		$this->column = $column ? $column : 0;
        $this->type = $type ? $type : 1;
    }
    public function view(): View
    {
        $totalAll=0;
        $array_filter = [];
        $results = DB::select("
            SELECT 
                *,
                IFNULL((SELECT 
                    SUM(prd.nominal) 
                    FROM payment_request_details prd 
                    JOIN outgoing_payments op
                        ON op.payment_request_id = prd.payment_request_id
                    WHERE 
                        prd.lookable_id = pi.id 
                        AND prd.lookable_type = 'purchase_invoices'
                        AND op.pay_date <= :date1
                        AND op.status IN ('2','3')
                        AND prd.deleted_at IS NULL
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
                        AND pmd.deleted_at IS NULL
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
                        AND prd.deleted_at IS NULL
                ),0) AS total_reconcile,
                IFNULL((
                    SELECT
                        SUM(jd.nominal)
                        FROM journal_details jd
                        JOIN journals j
                            ON j.id = jd.journal_id
                        JOIN coas c
                            ON jd.coa_id = c.id
                        WHERE c.code = '200.01.03.01.01'
                        AND jd.note = CONCAT('VOID*',pi.code)
                        AND j.post_date <= :date4
                        AND j.status IN ('2','3')
                        AND jd.deleted_at IS NULL
                ),0) AS total_journal,
                IFNULL((SELECT
                    SUM(ROUND(ard.nominal,2))
                    FROM adjust_rate_details ard
                    JOIN adjust_rates ar
                        ON ar.id = ard.adjust_rate_id
                    WHERE 
                        ar.post_date <= :date5
                        AND ar.status IN ('2','3')
                        AND ard.lookable_type = 'purchase_invoices'
                        AND ard.lookable_id = pi.id
                ),0) AS adjust_nominal,
                u.name AS account_name,
                u.employee_no AS account_code,
                pi.code,
                pi.post_date,
                pi.received_date,
                pi.due_date,
                pi.total,
                pi.tax,
                pi.wtax,
                pi.balance,
                pi.currency_rate
                FROM purchase_invoices pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date6
                    AND pi.balance > 0
                    AND pi.status IN ('2','3','7')
                    AND pi.deleted_at IS NULL
        ", array(
            'date1' => $this->date,
            'date2' => $this->date,
            'date3' => $this->date,
            'date4' => $this->date,
            'date5' => $this->date,
            'date6' => $this->date,
        ));

        $results2 = DB::select("
            SELECT 
                *,
                pi.top AS topdp,
                IFNULL((SELECT 
                    SUM(prd.nominal) 
                    FROM payment_request_details prd 
                    JOIN outgoing_payments op
                        ON op.payment_request_id = prd.payment_request_id
                    WHERE 
                        prd.lookable_id = pi.id 
                        AND prd.lookable_type = 'purchase_down_payments'
                        AND op.pay_date <= :date1
                        AND op.status IN ('2','3')
                        AND prd.deleted_at IS NULL
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
                        AND pmd.deleted_at IS NULL
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
                        AND prd.deleted_at IS NULL
                ),0) AS total_reconcile,
                IFNULL((SELECT
                    SUM(ROUND(ard.nominal,2))
                    FROM adjust_rate_details ard
                    JOIN adjust_rates ar
                        ON ar.id = ard.adjust_rate_id
                    WHERE 
                        ar.post_date <= :date4
                        AND ar.status IN ('2','3')
                        AND ard.lookable_type = 'purchase_down_payments'
                        AND ard.lookable_id = pi.id
                ),0) AS adjust_nominal,
                u.name AS account_name,
                u.employee_no AS account_code,
                pi.code,
                pi.post_date,
                pi.document_date,
                pi.due_date,
                pi.total,
                pi.tax,
                pi.wtax,
                pi.grandtotal,
                pi.currency_rate
                FROM purchase_down_payments pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date5
                    AND pi.grandtotal > 0
                    AND pi.status IN ('2','3','7')
                    AND pi.deleted_at IS NULL
        ", array(
            'date1' => $this->date,
            'date2' => $this->date,
            'date3' => $this->date,
            'date4' => $this->date,
            'date5' => $this->date,
        ));

        $newData = [];

        $countPeriod = 1;
        $column = intval($this->column);
        $countPeriod += $column + 1;
        $interval = intval($this->interval);
        $totalDays = $column * $interval;
        $arrColumn = [];
        $arrColumn[] = [
            'name'      => 'Belum jatuh tempo',
            'start'     => -999999999999999999,
            'end'       => 0,
            'total'     => 0,
        ];
        for($i=1;$i<=$column;$i++){
            $end = $i * $interval;
            $start = ($end - $interval) + 1;
            $arrColumn[] = [
                'name'   => ''.$start.'-'.$end.' hari',
                'start'  => $start,
                'end'    => $end,
                'total'  => 0,
            ];
        }
        $arrColumn[] = [
            'name'      => 'Diatas '.$totalDays.' hari',
            'start'     => $totalDays + 1,
            'end'       => 999999999999999999,
            'total'     => 0,
        ];

        $newData = [];

        if($this->type == 1){
            foreach($results as $row){
                $totalPayed = $row->total_payment + $row->total_memo + $row->total_reconcile + $row->total_journal;
                $balance = $row->balance - $totalPayed;
                $currency_rate = $row->currency_rate;
                $total_received_after_adjust = ($row->balance * $currency_rate) + $row->adjust_nominal;
                $total_invoice_after_adjust = ($row->total_payment + $row->total_memo + $row->total_reconcile + $row->total_journal) * $currency_rate;
                $balance_after_adjust = $total_received_after_adjust - $total_invoice_after_adjust;
                if($balance > 0){
                    $totalAll += $balance_after_adjust;
                    $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
                    $index = $this->findDuplicate($row->account_code,$newData);
                    if($index >= 0){
                        foreach($newData[$index]['data'] as $key => $rowdata){
                            if($daysDiff <= $rowdata['end'] && $daysDiff >= $rowdata['start']){
                                $newData[$index]['data'][$key]['balance'] += $balance_after_adjust;
                                $newData[$index]['total'] += $balance_after_adjust;
                                $arrColumn[$key]['total'] += $balance_after_adjust;
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
                                    'balance'       => $balance_after_adjust,
                                    'list_invoice'  => array($row->code),
                                ];
                                $arrColumn[$key]['total'] += $balance_after_adjust;
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
                            'total'                 => $balance_after_adjust,
                        ];
                    }
                }
            }
    
            foreach($results2 as $row){
                $totalPayed = $row->total_payment + $row->total_memo + $row->total_reconcile;
                $balance = $row->grandtotal - $totalPayed;
                $currency_rate = $row->currency_rate;
                $total_received_after_adjust = ($row->grandtotal * $currency_rate) + $row->adjust_nominal;
                $total_invoice_after_adjust = ($row->total_payment + $row->total_memo + $row->total_reconcile) * $currency_rate;
                $balance_after_adjust = $total_received_after_adjust - $total_invoice_after_adjust;
                if($balance > 0){
                    $totalAll += $balance_after_adjust;
                    $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
                    $index = $this->findDuplicate($row->account_code,$newData);
                    if($index >= 0){
                        foreach($newData[$index]['data'] as $key => $rowdata){
                            if($daysDiff <= $rowdata['end'] && $daysDiff >= $rowdata['start']){
                                $newData[$index]['data'][$key]['balance'] += $balance_after_adjust;
                                $newData[$index]['total'] += $balance_after_adjust;
                                $arrColumn[$key]['total'] += $balance_after_adjust;
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
                                    'balance'       => $balance_after_adjust,
                                    'list_invoice'  => array($row->code),
                                ];
                                $arrColumn[$key]['total'] += $balance_after_adjust;
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
                            'total'                 => $balance_after_adjust,
                        ];
                    }
                }
            }
    
            return view('admin.exports.aging_ap', [
                'data'          => $newData,
                'column'        => $arrColumn,
                'countPeriod'   => $countPeriod,
                'totalall'      => $totalAll
            ]);
        }else{
            foreach($results as $row){
                $totalPayed = $row->total_payment + $row->total_memo + $row->total_reconcile + $row->total_journal;
                $balance = $row->balance - $totalPayed;
                $currency_rate = $row->currency_rate;
                $total_received_after_adjust = ($row->balance * $currency_rate) + $row->adjust_nominal;
                $total_invoice_after_adjust = ($row->total_payment + $row->total_memo + $row->total_reconcile + $row->total_journal) * $currency_rate;
                $balance_after_adjust = $total_received_after_adjust - $total_invoice_after_adjust;
                if($balance > 0){
                    $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
                    $arrDetail = [];
                    $totalAll += $balance_after_adjust;
                    foreach($arrColumn as $key => $rowcolumn){
                        if($daysDiff <= $rowcolumn['end'] && $daysDiff >= $rowcolumn['start']){
                            $arrDetail[] = [
                                'name'          => $rowcolumn['name'],
                                'start'         => $rowcolumn['start'],
                                'end'           => $rowcolumn['end'],
                                'balance'       => $balance_after_adjust,
                            ];
                            $arrColumn[$key]['total'] += $balance_after_adjust;
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
                        'supplier_name'         => $row->account_code,
                        'supplier_code'         => $row->account_name,
                        'invoice'               => $row->code,
                        'data'                  => $arrDetail,
                        'total'                 => $balance_after_adjust,
                    ];
                }
            }
    
            foreach($results2 as $row){
                $totalPayed = $row->total_payment + $row->total_memo + $row->total_reconcile;
                $balance = $row->grandtotal - $totalPayed;
                $currency_rate = $row->currency_rate;
                $total_received_after_adjust = ($row->grandtotal * $currency_rate) + $row->adjust_nominal;
                $total_invoice_after_adjust = ($row->total_payment + $row->total_memo + $row->total_reconcile) * $currency_rate;
                $balance_after_adjust = $total_received_after_adjust - $total_invoice_after_adjust;
                if($balance > 0){
                    $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
                    $arrDetail = [];
                    $totalAll += $balance_after_adjust;
                    foreach($arrColumn as $key => $rowcolumn){
                        if($daysDiff <= $rowcolumn['end'] && $daysDiff >= $rowcolumn['start']){
                            $arrDetail[] = [
                                'name'          => $rowcolumn['name'],
                                'start'         => $rowcolumn['start'],
                                'end'           => $rowcolumn['end'],
                                'balance'       => $balance_after_adjust,
                            ];
                            $arrColumn[$key]['total'] += $balance_after_adjust;
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
                        'total'                 => $balance_after_adjust,
                    ];
                }
            }
    
            return view('admin.exports.aging_ap_detail', [
                'data'          => $newData,
                'column'        => $arrColumn,
                'countPeriod'   => $countPeriod,
                'totalall'      => $totalAll
            ]);
        }
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

<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportAgingAP implements FromView , WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
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
        $query_data = DB::select("
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
                    u.name AS account_name,
                    u.employee_no AS account_code
                    FROM purchase_invoices pi
                    LEFT JOIN users u
                        ON u.id = pi.account_id
                    WHERE 
                        pi.post_date <= :date3
                        AND pi.balance > 0
                        AND pi.status IN ('2','3')
            ", array(
                'date1' => $this->date,
                'date2' => $this->date,
                'date3' => $this->date,
            ));

        $newData = [];

        $query_data2 = DB::select("
                SELECT 
                *,
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
                        JOIN purchase_invoice_details pid
                            ON pid.purchase_invoice_id = pi.id
                            AND pid.id = pmd.lookable_id
                        WHERE pmd.lookable_type = 'purchase_down_payments'
                        AND pm.post_date <= :date2
                        AND pm.status IN ('2','3')
                ),0) AS total_memo,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM purchase_down_payments pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date3
                    AND pi.grandtotal > 0
                    AND pi.status IN ('2','3')
            ", array(
                'date1' => $this->date,
                'date2' => $this->date,
                'date3' => $this->date,
            ));

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
            foreach($query_data as $row){
                $balance = $row->balance - $row->total_payment - $row->total_memo;
                if($balance > 0){
                    $totalAll += $balance;
                    $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
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
    
            foreach($query_data2 as $row){
                $balance = $row->grandtotal - $row->total_payment - $row->total_memo;
                if($balance > 0){
                    $totalAll += $balance;
                    $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
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
    
            return view('admin.exports.aging_ap', [
                'data'          => $newData,
                'column'        => $arrColumn,
                'countPeriod'   => $countPeriod,
                'totalall'      => $totalAll
            ]);
        }else{
            foreach($query_data as $row){
                $balance = $row->balance - $row->total_payment - $row->total_memo;
                if($balance > 0){
                    $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
                    $arrDetail = [];
                    $totalAll += $balance;
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
                        'supplier_name'         => $row->account_code,
                        'supplier_code'         => $row->account_name,
                        'invoice'               => $row->code,
                        'data'                  => $arrDetail,
                        'total'                 => $balance,
                    ];
                }
            }
    
            foreach($query_data2 as $row){
                $balance = $row->grandtotal - $row->total_payment - $row->total_memo;
                if($balance > 0){
                    $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
                    $arrDetail = [];
                    $totalAll += $balance;
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
    
            return view('admin.exports.aging_ap_detail', [
                'data'          => $newData,
                'column'        => $arrColumn,
                'countPeriod'   => $countPeriod,
                'totalall'      => $totalAll
            ]);
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Auto-fit columns A to Z
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->autoSize();
                $event->sheet->freezePane("A1");
            }
        ];
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

<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportAgingAR implements FromView , WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(string $date)
    {
        $this->date = $date ? $date : '';
		
    }
    public function view(): View
    {
        $totalAll=0;
        $array_filter = [];
        $query_data = DB::select("
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
                    SUM(momd.grandtotal)
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
                'date1' => $this->date,
                'date2' => $this->date,
                'date3' => $this->date,
            ));

        $newData = [];

        $query_data2 = DB::select("
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
                        SUM(momd.grandtotal)
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
                'date1' => $this->date,
                'date2' => $this->date,
                'date3' => $this->date,
            ));

        foreach($query_data as $row){
            $balance = $row->balance - $row->total_payment - $row->total_memo;
            if($balance > 0){
                $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
                $index = $this->findDuplicate($row->account_code,$newData);
                if($index >= 0){
                    $newData[$index]['balance0'] = $daysDiff <= 0 ? $newData[$index]['balance0'] + $balance : $newData[$index]['balance0'];
                    $newData[$index]['balance30'] = $daysDiff <= 30 && $daysDiff > 0 ? $newData[$index]['balance30'] + $balance : $newData[$index]['balance30'];
                    $newData[$index]['balance60'] = $daysDiff <= 60 && $daysDiff > 30 ? $newData[$index]['balance60'] + $balance : $newData[$index]['balance60'];
                    $newData[$index]['balance90'] = $daysDiff <= 90 && $daysDiff > 60 ? $newData[$index]['balance90'] + $balance : $newData[$index]['balance90'];
                    $newData[$index]['balanceOver'] = $daysDiff > 90 ? $newData[$index]['balanceOver'] + $balance : $newData[$index]['balanceOver'];
                    $newData[$index]['total'] = $newData[$index]['total'] + $balance;
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
                    ];
                }
                $totalAll += $balance;
            }
        }
        
        foreach($query_data2 as $row){
            $balance = $row->grandtotal - $row->total_payment - $row->total_memo;
            if($balance > 0){
                $daysDiff = $this->dateDiffInDays($row->due_date,$this->date);
                $index = $this->findDuplicate($row->account_code,$newData);
                if($index >= 0){
                    $newData[$index]['balance0'] = $daysDiff <= 0 ? $newData[$index]['balance0'] + $balance : $newData[$index]['balance0'];
                    $newData[$index]['balance30'] = $daysDiff <= 30 && $daysDiff > 0 ? $newData[$index]['balance30'] + $balance : $newData[$index]['balance30'];
                    $newData[$index]['balance60'] = $daysDiff <= 60 && $daysDiff > 30 ? $newData[$index]['balance60'] + $balance : $newData[$index]['balance60'];
                    $newData[$index]['balance90'] = $daysDiff <= 90 && $daysDiff > 60 ? $newData[$index]['balance90'] + $balance : $newData[$index]['balance90'];
                    $newData[$index]['balanceOver'] = $daysDiff > 90 ? $newData[$index]['balanceOver'] + $balance : $newData[$index]['balanceOver'];
                    $newData[$index]['total'] = $newData[$index]['total'] + $balance;
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
                    ];
                }
                $totalAll += $balance;
            }
        }

        return view('admin.exports.aging_ar', [
            'data'      => $newData,
            'totalall'  => $totalAll
        ]);
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

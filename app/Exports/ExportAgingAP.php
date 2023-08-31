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
                    ),0) AS total_memo,
                    u.name AS account_name,
                    u.employee_no AS account_code
                    FROM purchase_invoices pi
                    LEFT JOIN users u
                        ON u.id = pi.account_id
                    WHERE 
                        pi.post_date <= :date3
                        AND pi.balance > 0
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
                ),0) AS total_memo,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM purchase_down_payments pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date3
                    AND pi.grandtotal > 0
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
                        'supplier_code'         => $row->account_code,
                        'supplier_name'         => $row->account_name,
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
                        'supplier_code'         => $row->account_code,
                        'supplier_name'         => $row->account_name,
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

        return view('admin.exports.aging_ap', [
            'data'      => $newData,
            'totalall'  => number_format($totalAll,2,',','.')
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

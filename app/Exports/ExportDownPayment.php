<?php

namespace App\Exports;

use App\Models\PurchaseDownPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportDownPayment implements FromView , WithEvents
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
        $totalbalance=0;
        $array_filter = [];
        $query_data = DB::select("
                    SELECT 
                    *,
                    pdp.type as typepdp,
                    IFNULL((SELECT 
                        SUM(nominal) 
                        FROM purchase_invoice_dps pid 
                        JOIN purchase_invoices pi
                            ON pid.purchase_invoice_id = pi.id
                        WHERE 
                            pid.purchase_down_payment_id = pdp.id
                            AND pi.post_date <= :date1
                            AND pi.status IN ('2','3')
                    ),0) AS total_used,
                    IFNULL((
                        SELECT
                            SUM(pmd.grandtotal)
                            FROM purchase_memo_details pmd
                            JOIN purchase_memos pm
                                ON pm.id = pmd.purchase_memo_id
                            WHERE pmd.lookable_type = 'purchase_down_payments'
                            AND pmd.lookable_id = pdp.id
                            AND pm.post_date <= :date2
                    ),0) AS total_memo,
                    u.name AS account_name,
                    u.employee_no AS account_code
                    FROM purchase_down_payments pdp
                    LEFT JOIN users u
                        ON u.id = pdp.account_id
                    WHERE 
                        pdp.post_date <= :date3
                        AND pdp.grandtotal > 0
                ", array(
                    'date1' => $this->date,
                    'date2' => $this->date,
                    'date3' => $this->date,
                ));

            foreach($query_data as $row_invoice){
                $balance = $row_invoice->grandtotal - $row_invoice->total_used - $row_invoice->total_memo;
                if($balance > 0){
                    $array_filter[] = [
                        'code'          => $row_invoice->code,
                        'supplier_name' => $row_invoice->name,
                        'type'          => PurchaseDownPayment::typeStatic($row_invoice->typepdp),
                        'post_date'     => date('d/m/y',strtotime($row_invoice->post_date)),
                        'due_date'      => date('d/m/y',strtotime($row_invoice->due_date)),
                        'note'          => $row_invoice->note,
                        'subtotal'      => round($row_invoice->subtotal,2),
                        'discount'      => round($row_invoice->discount,2),
                        'total'         => round($row_invoice->total,2),
                        'used'          => round($row_invoice->total_used,2),
                        'memo'          => round($row_invoice->total_memo,2),
                        'balance'       => round($balance,2),
                    ];
                    $totalbalance += $balance;
                }
            }  
        return view('admin.exports.down_payment', [
            'data'      => $array_filter,
            'totalall'  => round($totalbalance,2)
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
}

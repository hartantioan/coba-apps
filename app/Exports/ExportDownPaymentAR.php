<?php

namespace App\Exports;

use App\Models\MarketingOrderDownPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportDownPaymentAR implements FromView , WithEvents
{
    protected $date;

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
                    modp.type as typepdp,
                    IFNULL((SELECT 
                        SUM(moid.total)
                        FROM marketing_order_invoice_details moid 
                        JOIN marketing_order_invoices moi
                            ON moi.id = moid.marketing_order_invoice_id
                        WHERE 
                            moid.lookable_id = modp.id 
                            AND moid.lookable_type = 'marketing_order_down_payments'
                            AND moi.post_date <= :date1
                            AND moi.status IN ('2','3')
                    ),0) AS total_used,
                    IFNULL((
                        SELECT
                            SUM(momd.total)
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

            foreach($query_data as $row_invoice){
                $balance = $row_invoice->total - $row_invoice->total_used - $row_invoice->total_memo;
                if($balance > 0){
                    $array_filter[] = [
                        'code'          => $row_invoice->code,
                        'customer_name' => $row_invoice->account_name,
                        'type'          => MarketingOrderDownPayment::typeStatic($row_invoice->typepdp),
                        'post_date'     => date('d/m/Y',strtotime($row_invoice->post_date)),
                        'due_date'      => date('d/m/Y',strtotime($row_invoice->due_date)),
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
            activity()
                ->performedOn(new MarketingOrderDownPayment())
                ->causedBy(session('bo_id'))
                ->withProperties($query_data)
                ->log('Export Downpayment AR data.');
        
            return view('admin.exports.down_payment_ar', [
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

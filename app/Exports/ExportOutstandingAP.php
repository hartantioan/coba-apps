<?php

namespace App\Exports;

use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

class ExportOutstandingAP implements FromView , WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $date;

    public function __construct(string $date)
    {
        $this->date = $date ? $date : '';
		
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
                u.name AS account_name,
                u.employee_no AS account_code,
                pi.code,
                pi.post_date,
                pi.received_date,
                pi.due_date,
                pi.total,
                pi.tax,
                pi.wtax,
                pi.balance
                FROM purchase_invoices pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date5
                    AND pi.balance > 0
                    AND pi.status IN ('2','3','7')
                    AND pi.deleted_at IS NULL
        ", array(
            'date1' => $this->date,
            'date2' => $this->date,
            'date3' => $this->date,
            'date4' => $this->date,
            'date5' => $this->date,
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
                    pi.post_date <= :date4
                    AND pi.grandtotal > 0
                    AND pi.status IN ('2','3','7')
                    AND pi.deleted_at IS NULL
        ", array(
            'date1' => $this->date,
            'date2' => $this->date,
            'date3' => $this->date,
            'date4' => $this->date,
        ));

        foreach($results as $row){
            $totalPayed = $row->total_payment + $row->total_memo + $row->total_reconcile + $row->total_journal;
            $balance = $row->balance - $totalPayed;
            $data_tempura = [
                'code'      => $row->code,
                'vendor'    => $row->account_name,
                'post_date' => date('d/m/Y',strtotime($row->post_date)),
                'rec_date'  => date('d/m/Y',strtotime($row->received_date)),
                'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                'top'       => '-',
                'total'     => number_format($row->total,2,',','.'),
                'tax'       => number_format($row->tax,2,',','.'),
                'wtax'      => number_format($row->wtax,2,',','.'),
                'grandtotal'=> number_format($row->balance,2,',','.'),
                'payed'     => number_format($totalPayed,2,',','.'),
                'sisa'      => number_format($balance,2,',','.'),
            ];
            
            if($balance > 0){
                $totalAll += $balance;
                $array_filter[] = $data_tempura;
            }
        }

        foreach($results2 as $row){
            $totalPayed = $row->total_payment + $row->total_memo + $row->total_reconcile;
            $balance = ($row->grandtotal * $row->currency_rate) - $totalPayed;
            $data_tempura = [
                'code'      => $row->code,
                'vendor'    => $row->account_name,
                'post_date' => date('d/m/Y',strtotime($row->post_date)),
                'rec_date'  => date('d/m/Y',strtotime($row->document_date)),
                'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                'top'       => '-',
                'total'     => number_format($row->total * $row->currency_rate,2,',','.'),
                'tax'       => number_format($row->tax * $row->currency_rate,2,',','.'),
                'wtax'      => number_format($row->wtax * $row->currency_rate,2,',','.'),
                'grandtotal'=> number_format($row->grandtotal * $row->currency_rate,2,',','.'),
                'payed'     => number_format($totalPayed,2,',','.'),
                'sisa'      => number_format($balance,2,',','.'),
            ];
            
            if($balance > 0){
                $totalAll += $balance;
                $array_filter[] = $data_tempura;
            }
        }

        return view('admin.exports.outstanding_ap', [
            'data' => $array_filter,
            'totalall' =>number_format($totalAll,2,',','.')
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

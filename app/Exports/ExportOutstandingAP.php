<?php

namespace App\Exports;

use App\Models\OutstandingAP;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportOutstandingAP implements FromView ,ShouldAutoSize
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
                        AND (
                            CASE 
                                WHEN ar.post_date >= '2024-06-01' THEN ard.type = '2'
                                WHEN ar.post_date < '2024-06-01' THEN ard.type IS NOT NULL
                            END
                        )
                ),0) AS adjust_nominal,
                IFNULL((SELECT
                    '1'
                    FROM cancel_documents cd
                    WHERE 
                        cd.post_date <= :date6
                        AND cd.lookable_type = 'purchase_invoices'
                        AND cd.lookable_id = pi.id
                        AND cd.deleted_at IS NULL
                ),0) AS status_cancel,
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
                pi.currency_rate,
                pi.note
                FROM purchase_invoices pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date7
                    AND pi.balance > 0
                    AND pi.status IN ('2','3','7','8')
                    AND pi.deleted_at IS NULL
        ", array(
            'date1' => $this->date,
            'date2' => $this->date,
            'date3' => $this->date,
            'date4' => $this->date,
            'date5' => $this->date,
            'date6' => $this->date,
            'date7' => $this->date,
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
                        AND (
                            CASE 
                                WHEN ar.post_date >= '2024-06-01' THEN ard.type = '2'
                                WHEN ar.post_date < '2024-06-01' THEN ard.type IS NOT NULL
                            END
                        )
                ),0) AS adjust_nominal,
                IFNULL((SELECT
                    '1'
                    FROM cancel_documents cd
                    WHERE 
                        cd.post_date <= :date5
                        AND cd.lookable_type = 'purchase_down_payments'
                        AND cd.lookable_id = pi.id
                        AND cd.deleted_at IS NULL
                ),0) AS status_cancel,
                IFNULL((
                    SELECT
                        SUM(jd.nominal)
                        FROM journal_details jd
                        JOIN journals j
                            ON j.id = jd.journal_id
                        JOIN coas c
                            ON jd.coa_id = c.id
                        WHERE c.code = '200.01.03.01.01'
                        AND jd.note = CONCAT('REVERSE*',pi.code)
                        AND j.post_date <= :date6
                        AND j.status IN ('2','3')
                        AND jd.deleted_at IS NULL
                ),0) AS total_journal,
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
                pi.currency_rate,
                pi.note
                FROM purchase_down_payments pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date7
                    AND pi.grandtotal > 0
                    AND pi.status IN ('2','3','7','8')
                    AND pi.deleted_at IS NULL
                    
        ", array(
            'date1' => $this->date,
            'date2' => $this->date,
            'date3' => $this->date,
            'date4' => $this->date,
            'date5' => $this->date,
            'date6' => $this->date,
            'date7' => $this->date,
        ));

        foreach($results as $row){
            $totalPayed = round($row->total_payment + $row->total_memo + $row->total_reconcile + $row->total_journal,2);
            $balance = $row->balance - $totalPayed;
            $currency_rate = $row->currency_rate;
            $total_received_after_adjust = round(($row->balance * $currency_rate) + $row->adjust_nominal,2);
            $total_invoice_after_adjust = round(($row->total_payment + $row->total_memo + $row->total_reconcile + $row->total_journal) * $currency_rate,2);
            $balance_after_adjust = round($total_received_after_adjust - $total_invoice_after_adjust,2);
            if($balance > 0 && $row->status_cancel == '0'){
                $data_tempura = [
                    'code'      => $row->code,
                    'vendor'    => $row->account_name,
                    'post_date' => date('d/m/Y',strtotime($row->post_date)),
                    'rec_date'  => date('d/m/Y',strtotime($row->received_date)),
                    'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                    'top'       => '-',
                    'grandtotal'=> number_format($total_received_after_adjust,2,',','.'),
                    'payed'     => number_format($total_invoice_after_adjust,2,',','.'),
                    'sisa'      => number_format($balance_after_adjust,2,',','.'),
                    'kurs'      => number_format($balance_after_adjust / $balance,2,',','.'),
                    'real'      => number_format($balance,2,',','.'),
                    'note'      => $row->note
                ];
                $totalAll += $balance_after_adjust;
                $array_filter[] = $data_tempura;
            }
        }

        foreach($results2 as $row){
            $totalPayed = round($row->total_payment + $row->total_memo + $row->total_reconcile,2);
            $balance = $row->grandtotal - $totalPayed;
            $currency_rate = $row->currency_rate;
            $total_received_after_adjust = round(($row->grandtotal * $currency_rate) + $row->adjust_nominal,2);
            $total_invoice_after_adjust = round(($row->total_payment + $row->total_memo + $row->total_reconcile) * $currency_rate,2) + $row->total_journal;
            $balance_after_adjust = round($total_received_after_adjust - $total_invoice_after_adjust,2);
            if($balance > 0 && $row->status_cancel == '0'){
                $data_tempura = [
                    'code'      => $row->code,
                    'vendor'    => $row->account_name,
                    'post_date' => date('d/m/Y',strtotime($row->post_date)),
                    'rec_date'  => date('d/m/Y',strtotime($row->document_date)),
                    'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                    'top'       => '-',
                    'grandtotal'=> number_format($total_received_after_adjust,2,',','.'),
                    'payed'     => number_format($total_invoice_after_adjust,2,',','.'),
                    'sisa'      => number_format($balance_after_adjust,2,',','.'),
                    'kurs'      => number_format($balance_after_adjust / $balance,2,',','.'),
                    'real'      => number_format($balance,2,',','.'),
                    'note'      => $row->note
                ];
                $totalAll += $balance_after_adjust;
                $array_filter[] = $data_tempura;
            }
        }

        return view('admin.exports.outstanding_ap', [
            'data' => $array_filter,
            'totalall' =>number_format($totalAll,2,',','.')
        ]);

        /* $date = $this->date;

        $array_filter = [];

        $results = OutstandingAP::where('post_date',$date)->first();

        if($results){
            if($results->status){
                foreach($results->outstandingApDetail as $row){
                    $data_tempura = [
                        'code'      => $row->code,
                        'vendor'    => $row->account,
                        'post_date' => date('d/m/Y',strtotime($row->post_date)),
                        'rec_date'  => date('d/m/Y',strtotime($row->received_date)),
                        'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                        'top'       => $row->top,
                        'grandtotal'=> number_format($row->total,2,',','.'),
                        'payed'     => number_format($row->paid,2,',','.'),
                        'sisa'      => number_format($row->balance,2,',','.'),
                        'kurs'      => number_format($row->currency_rate,2,',','.'),
                        'real'      => number_format($row->balance_fc,2,',','.'),
                        'note'      => $row->note
                    ];
                    $array_filter[] = $data_tempura;
                }
                return view('admin.exports.outstanding_ap', [
                    'data'      => $array_filter,
                    'totalall'  => number_format($results->total,2,',','.')
                ]);
            }
        } */
    }

    
}

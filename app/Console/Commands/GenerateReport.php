<?php

namespace App\Console\Commands;

use App\Models\OutstandingAP;
use App\Models\OutstandingAPDetail;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate report into its respective table.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        #outstanding ap
        $date = date('Y-m-d');

        $querycek = OutstandingAP::where('post_date', $date)->first();
        
        if($querycek){
            $querycek->outstandingApDetail()->delete();
            $querycek->delete();
        }
        $results = DB::select("
            SELECT 
                rs.account_name,
                rs.account_code,
                rs.code,
                rs.post_date,
                rs.received_date,
                rs.due_date,
                rs.total,
                rs.tax,
                rs.wtax,
                rs.balance,
                rs.currency_rate,
                rs.adjust_nominal,
                rs.total_payment,
                rs.total_memo,
                rs.total_reconcile,
                rs.total_journal
            FROM
                (SELECT 
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
                        SUM(ard.nominal)
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
                ) AS rs
            WHERE (rs.balance - rs.total_payment - rs.total_memo - rs.total_reconcile - rs.total_journal) > 0
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
            'date5' => $date,
            'date6' => $date,
        ));

        $results2 = DB::select("
            SELECT 
                rs.code,
                rs.total_payment,
                rs.total_memo,
                rs.total_reconcile,
                rs.adjust_nominal,
                rs.account_name,
                rs.account_code,
                rs.post_date,
                rs.document_date,
                rs.due_date,
                rs.total,
                rs.tax,
                rs.wtax,
                rs.grandtotal,
                rs.currency_rate,
                rs.adjust_nominal,
                rs.total_payment,
                rs.total_memo,
                rs.total_reconcile
            FROM
                (SELECT
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
                        SUM(ard.nominal)
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
                ) AS rs
                WHERE (rs.grandtotal - rs.total_payment - rs.total_memo - rs.total_reconcile) > 0
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
            'date5' => $date,
        ));

        $totalAll = 0;

        if($results || $results2){
            $query = OutstandingAP::create([
                'post_date' => $date,
                'total'     => 0,
            ]);

            foreach($results as $row){
                $total_received_after_adjust = ($row->balance * $row->currency_rate) + $row->adjust_nominal;
                $total_invoice_after_adjust = ($row->total_payment + $row->total_memo + $row->total_reconcile + $row->total_journal) * $row->currency_rate;
                $balance_after_adjust = $total_received_after_adjust - $total_invoice_after_adjust;
                OutstandingAPDetail::create([
                    'outstanding_ap_id'     => $query->id,
                    'code'                  => $row->code,
                    'account'               => $row->account_name,
                    'post_date'             => $row->post_date,
                    'received_date'         => $row->received_date,
                    'top'                   => 0,
                    'due_date'              => $row->due_date,
                    'total'                 => $total_received_after_adjust,
                    'paid'                  => $total_invoice_after_adjust,
                    'balance'               => $balance_after_adjust,
                ]);
                $totalAll += $balance_after_adjust;
            }

            foreach($results2 as $row){
                $total_received_after_adjust = ($row->grandtotal * $row->currency_rate) + $row->adjust_nominal;
                $total_invoice_after_adjust = ($row->total_payment + $row->total_memo + $row->total_reconcile) * $row->currency_rate;
                $balance_after_adjust = $total_received_after_adjust - $total_invoice_after_adjust;
                OutstandingAPDetail::create([
                    'outstanding_ap_id'     => $query->id,
                    'code'                  => $row->code,
                    'account'               => $row->account_name,
                    'post_date'             => $row->post_date,
                    'received_date'         => $row->document_date,
                    'top'                   => 0,
                    'due_date'              => $row->due_date,
                    'total'                 => $total_received_after_adjust,
                    'paid'                  => $total_invoice_after_adjust,
                    'balance'               => $balance_after_adjust,
                ]);
                
                $totalAll += $balance_after_adjust;
            }

            $query->update([
                'total'     => $totalAll,
            ]);
        }
    }
}

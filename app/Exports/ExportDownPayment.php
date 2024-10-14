<?php

namespace App\Exports;

use App\Models\PurchaseDownPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportDownPayment implements FromView,ShouldAutoSize
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
                            AND pi.status IN ('2','3','7')
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
                            AND pm.status IN ('2','3','7')
                    ),0) AS total_memo,
                    IFNULL((SELECT
                        SUM(ROUND(ard.nominal,2))
                        FROM adjust_rate_details ard
                        JOIN adjust_rates ar
                            ON ar.id = ard.adjust_rate_id
                        WHERE
                            ar.post_date <= :date3
                            AND ar.status IN ('2','3')
                            AND ard.lookable_type = 'purchase_down_payments'
                            AND ard.lookable_id = pdp.id
                            AND (
                                CASE
                                    WHEN ar.post_date >= '2024-06-01' THEN ard.type = '1'
                                    WHEN ar.post_date < '2024-06-01' THEN ard.type IS NOT NULL
                                END
                            )
                    ),0) AS adjust_nominal,
                    IFNULL((SELECT
                        ar.currency_rate
                        FROM adjust_rate_details ard
                        JOIN adjust_rates ar
                            ON ar.id = ard.adjust_rate_id
                        WHERE
                            ar.post_date <= :date4
                            AND ar.status IN ('2','3')
                            AND ard.lookable_type = 'purchase_down_payments'
                            AND ard.lookable_id = pdp.id
                        ORDER BY ar.id DESC
                        LIMIT 1
                    ),0) AS latest_currency,
                    IFNULL((
                        SELECT
                            SUM(jd.nominal)
                            FROM journal_details jd
                            JOIN journals j
                                ON j.id = jd.journal_id
                            JOIN coas c
                                ON jd.coa_id = c.id
                            WHERE c.code = '100.01.07.01.01'
                            AND jd.note = CONCAT('REVERSE*',pdp.code)
                            AND j.post_date <= :date5
                            AND j.status IN ('2','3')
                            AND jd.deleted_at IS NULL
                    ),0) AS total_journal,
                    u.name AS account_name,
                    u.employee_no AS account_code,
                    uvoid.name AS void_name,
                    udelete.name AS delete_name
                    FROM purchase_down_payments pdp
                    LEFT JOIN users u
                        ON u.id = pdp.account_id
                    LEFT JOIN users uvoid
                        ON uvoid.id = pdp.void_id
                    LEFT JOIN users udelete
                        ON udelete.id = pdp.void_id
                    WHERE
                        pdp.post_date <= :date6
                        AND pdp.grandtotal > 0
                        AND pdp.status IN ('2','3','7','8')
                        AND IFNULL((SELECT
                        '1'
                        FROM cancel_documents cd
                        WHERE
                            cd.post_date <= :date7
                            AND cd.lookable_type = 'purchase_down_payments'
                            AND cd.lookable_id = pdp.id
                            AND cd.deleted_at IS NULL
                    ),'0') = '0'
                ", array(
                    'date1' => $this->date,
                    'date2' => $this->date,
                    'date3' => $this->date,
                    'date4' => $this->date,
                    'date5' => $this->date,
                    'date6' => $this->date,
                    'date7' => $this->date,
                ));

            foreach($query_data as $row_invoice){
                $currency_rate = $row_invoice->latest_currency > 0 ? $row_invoice->latest_currency : $row_invoice->currency_rate;
                $total_received_after_adjust = round(round($row_invoice->grandtotal * $currency_rate,3),2);
                $total_invoice_after_adjust = round(($row_invoice->total_used + $row_invoice->total_memo) * $currency_rate,2);
                $balance_after_adjust = round($total_received_after_adjust - $total_invoice_after_adjust,2);
                $balance = round($row_invoice->grandtotal - $row_invoice->total_used - $row_invoice->total_memo,2);
                $currency_rate = $row_invoice->latest_currency;
                if($balance > 0){
                    $pdp = PurchaseDownPayment::where('code',$row_invoice->code)->first();
                    $array_filter[] = [
                        'code'          => $row_invoice->code,
                        'supplier_code' => $row_invoice->account_code,
                        'supplier_name' => $row_invoice->account_name,
                        'type'          => PurchaseDownPayment::typeStatic($row_invoice->typepdp),
                        'post_date'     => date('d/m/Y',strtotime($row_invoice->post_date)),
                        'due_date'      => date('d/m/Y',strtotime($row_invoice->due_date)),
                        'note'          => $row_invoice->note,
                        'subtotal'      => number_format($row_invoice->subtotal * $currency_rate,2,',','.'),
                        'discount'      => number_format($row_invoice->discount * $currency_rate,2,',','.'),
                        'total_fc'         => number_format($row_invoice->total * $currency_rate,2,',','.'),
                        'total'      => number_format($row_invoice->total,2,',','.'),
                        'used'          => number_format($row_invoice->total_used * $currency_rate,2,',','.'),
                        'memo'          => number_format($row_invoice->total_memo * $currency_rate,2,',','.'),
                        'balance'       => number_format($balance_after_adjust,2,',','.'),
                        'balance_fc'    => number_format($balance,2,',','.'),
                        'status'        => $this->getStatus($row_invoice->status),
                        'void_name'     => $row_invoice->void_name,
                        'void_date'     => date('d/m/Y',strtotime($row_invoice->void_date)),
                        'void_note'     => $row_invoice->void_note,
                        'delete_name'   => $row_invoice->delete_name,
                        'delete_note'   => $row_invoice->delete_note,
                        'delete_date'   => date('d/m/Y',strtotime($row_invoice->deleted_at)),
                        'references'    => PurchaseDownPayment::getReference($row_invoice->code),
                        'preq_code'     => $pdp->listPaymentRequest(),
                        'opym_code'     => $pdp->listOutgoingPayment(),
                        'pay_date'      => $pdp->listPayDate(),
                    ];
                    $totalbalance += round($balance_after_adjust,2);
                }
            }

            activity()
                ->performedOn(new PurchaseDownPayment())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export puchase downpayment data.');

            return view('admin.exports.down_payment', [
            'data'      => $array_filter,
            'totalall'  => round($totalbalance,2)
        ]);
    }

    public function getStatus($status){
        $status = match ($status) {
            '1' => 'Menunggu',
            '2' => 'Proses',
            '3' => 'Selesai',
            '4' => 'Ditolak',
            '5' => 'Ditutup',
            '6' => 'Revisi',
            default => 'Invalid',
          };

          return $status;
    }

}

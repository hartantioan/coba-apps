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
                        ar.currency_rate
                        FROM adjust_rate_details ard
                        JOIN adjust_rates ar
                            ON ar.id = ard.adjust_rate_id
                        WHERE 
                            ar.post_date <= :date3
                            AND ar.status IN ('2','3')
                            AND ard.lookable_type = 'purchase_down_payments'
                            AND ard.lookable_id = pdp.id
                        ORDER BY ar.post_date DESC LIMIT 1
                    ),0) AS currency_rate_adjust,
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
                        pdp.post_date <= :date4
                        AND pdp.grandtotal > 0
                        AND pdp.status IN ('2','3','7')
                ", array(
                    'date1' => $this->date,
                    'date2' => $this->date,
                    'date3' => $this->date,
                    'date4' => $this->date,
                ));

            foreach($query_data as $row_invoice){
                $balance = $row_invoice->grandtotal - $row_invoice->total_used - $row_invoice->total_memo;
                $currency_rate = $row_invoice->currency_rate_adjust > 0 ? $row_invoice->currency_rate_adjust : $row_invoice->currency_rate;
                if($balance > 0){
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
                        'total'         => number_format($row_invoice->total * $currency_rate,2,',','.'),
                        'total_fc'      => number_format($row_invoice->total,2,',','.'),
                        'used'          => number_format($row_invoice->total_used * $currency_rate,2,',','.'),
                        'memo'          => number_format($row_invoice->total_memo * $currency_rate,2,',','.'),
                        'balance'       => number_format($balance * $currency_rate,2,',','.'),
                        'balance_fc'    => number_format($balance,2,',','.'),
                        'status'        => $this->getStatus($row_invoice->status),
                        'void_name'     => $row_invoice->void_name,
                        'void_date'     => date('d/m/Y',strtotime($row_invoice->void_date)),
                        'void_note'     => $row_invoice->void_note,
                        'delete_name'   => $row_invoice->delete_name,
                        'delete_note'   => $row_invoice->delete_note,
                        'delete_date'   => date('d/m/Y',strtotime($row_invoice->deleted_at)),
                        'references'    => PurchaseDownPayment::getReference($row_invoice->code),
                    ];
                    $totalbalance += round($balance * $currency_rate,2);
                }
            }  
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

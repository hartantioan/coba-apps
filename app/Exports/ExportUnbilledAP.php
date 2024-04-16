<?php

namespace App\Exports;

use App\Models\JournalDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;

class ExportUnbilledAP implements FromView , WithEvents
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
        $array_filter = [];
        $date = $this->date;

        $results = DB::select("
        SELECT 
            gr.*,
            u.name AS account_name,
            IFNULL((SELECT 
                SUM(pid.total)
                FROM purchase_invoice_details pid
                JOIN purchase_invoices pi
                    ON pi.id = pid.purchase_invoice_id
                WHERE pid.lookable_type = 'good_receipt_details' 
                AND pid.lookable_id 
                    IN (
                        SELECT 
                            grd.id 
                            FROM good_receipt_details grd
                            WHERE grd.good_receipt_id = gr.id 
                            AND grd.deleted_at IS NULL
                        )
                AND pid.deleted_at IS NULL 
                AND pi.status IN ('2','3','7') 
                AND pi.post_date <= :date1
            ),0) AS total_invoice,
            IFNULL((
                SELECT 
                    GROUP_CONCAT(DISTINCT pi.code)
                    FROM purchase_invoice_details pid
                    JOIN purchase_invoices pi
                        ON pi.id = pid.purchase_invoice_id
                    WHERE pid.lookable_type = 'good_receipt_details' 
                    AND pid.lookable_id 
                        IN (
                            SELECT 
                                grd.id 
                                FROM good_receipt_details grd
                                WHERE grd.good_receipt_id = gr.id 
                                AND grd.deleted_at IS NULL
                            )
                    AND pid.deleted_at IS NULL 
                    AND pi.status IN ('2','3','7') 
                    AND pi.post_date <= :date2
            ),'') AS data_reconcile,
            IFNULL((SELECT 
                SUM(grtd.total) 
                FROM good_return_details grtd 
                WHERE grtd.good_receipt_detail_id 
                    IN (
                        SELECT 
                            grd.id 
                            FROM good_receipt_details grd
                            WHERE grd.good_receipt_id = gr.id 
                            AND grd.deleted_at IS NULL
                        )
                AND grtd.deleted_at IS NULL 
                AND grtd.good_return_id 
                    IN (
                        SELECT 
                            grt.id 
                            FROM good_returns grt 
                            WHERE grt.status IN ('2','3') 
                            AND grt.post_date <= :date3
                        )
            ),0) AS total_return,
            (SELECT 
                j.currency_rate
                FROM journals j 
                WHERE 
                    j.lookable_id = gr.id
                    AND j.lookable_type = 'good_receipts'
                    AND j.deleted_at IS NULL
            ) AS currency_rate,
            IFNULL((SELECT
                SUM(ard.nominal)
                FROM adjust_rate_details ard
                JOIN adjust_rates ar
                    ON ar.id = ard.adjust_rate_id
                WHERE 
                    ar.post_date <= :date4
                    AND ar.status IN ('2','3')
                    AND ard.lookable_type = 'good_receipts'
                    AND ard.lookable_id = gr.id
            ),0) AS adjust_nominal
            FROM good_receipts gr
            LEFT JOIN users u
                ON u.id = gr.account_id
            WHERE 
                gr.post_date <= :date5
                AND gr.status IN ('2','3')
                AND gr.deleted_at IS NULL;
        ", array(
            'date1'     => $date,
            'date2'     => $date,
            'date3'     => $date,
            'date4'     => $date,
            'date5'     => $date,
        ));

        $totalUnbilled = 0;

        foreach($results as $key => $row){
            $invoices = explode(',',$row->data_reconcile);
            $total_reconcile = 0;
            if(count($invoices) > 0){
                foreach($invoices as $rowinvoice){
                    $total_reconcile += JournalDetail::where('note','VOID*'.$rowinvoice)
                    ->whereHas('coa',function($query){
                        $query->where('code','200.01.03.01.02');
                    })->whereHas('journal',function($query)use($date){
                        $query->where('post_date','<=',$date)->whereIn('status',['2','3']);
                    })->sum('nominal_fc');
                }
            }
            $balance = $row->total - ($row->total_invoice - $total_reconcile) - $row->total_return;
            if($balance > 0){
                $currency_rate = $row->currency_rate;
                $total_received_after_adjust = ($row->total * $currency_rate) + $row->adjust_nominal;
                $total_invoice_after_adjust = ($row->total_invoice - $total_reconcile + $row->total_return) * $currency_rate;
                $balance_after_adjust = $total_received_after_adjust - $total_invoice_after_adjust;
                $array_filter[] = [
                    'no'            => ($key + 1),
                    'code'          => $row->code,
                    'vendor'        => $row->account_name,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'delivery_no'   => $row->delivery_no,
                    'note'          => $row->note,
                    'total_received'=> number_format($total_received_after_adjust,2,',','.'),
                    'total_invoice' => number_format($total_invoice_after_adjust,2,',','.'),
                    'total_balance' => number_format($balance_after_adjust,2,',','.'),
                    'kurs'          => number_format($balance_after_adjust / $balance,2,',','.'),
                    'real'          => number_format($balance,2,',','.'),
                ];
                $totalUnbilled += round($balance_after_adjust,2);
            }
        }

        return view('admin.exports.unbilled_ap', [
            'data'      => $array_filter,
            'total'     => number_format($totalUnbilled,2,',','.')
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

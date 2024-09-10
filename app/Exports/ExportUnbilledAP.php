<?php

namespace App\Exports;

use App\Models\JournalDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Support\Facades\DB;

class ExportUnbilledAP implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $date;

    public function __construct(string $date)
    {
        $this->date = $date ? $date : '';
		
    }

    private $headings = [
        'NO',
        'NO.GRPO',
        'SUPPLIER/VENDOR',
        'TGL.POST',
        'NO.SURAT JALAN',
        'KETERANGAN',
        'KURS',
        'TOTAL SISA FC',
        'TOTAL DITERIMA',
        'TOTAL INVOICE',
        'TOTAL SISA RP',
    ];

    public function collection()
    {
        $array_filter = [];
        $date = $this->date;

        $results = DB::select("
            SELECT
                rs.data_reconcile,
                rs.total,
                rs.total_invoice,
                rs.total_return,
                rs.currency_rate,
                rs.total_detail,
                rs.adjust_nominal,
                rs.code,
                rs.account_name,
                rs.post_date,
                rs.delivery_no,
                rs.note
                FROM
                    (SELECT 
                        gr.*,
                        u.name AS account_name,
                        IFNULL((SELECT 
                            SUM(ROUND(grd.total * (SELECT j.currency_rate FROM journals j WHERE j.lookable_type = 'good_receipts' AND j.lookable_id = gr.id),2))
                            FROM good_receipt_details grd
                            WHERE grd.good_receipt_id = gr.id 
                            AND grd.deleted_at IS NULL
                        ),0) AS total_detail,
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
                            SUM(ROUND(ard.nominal,2))
                            FROM adjust_rate_details ard
                            JOIN adjust_rates ar
                                ON ar.id = ard.adjust_rate_id
                            WHERE 
                                ar.post_date <= :date4
                                AND ar.status IN ('2','3')
                                AND ard.lookable_type = 'good_receipts'
                                AND ard.lookable_id = gr.id
                                AND (
                                    CASE 
                                        WHEN ar.post_date >= '2024-06-01' THEN ard.type = '2'
                                        WHEN ar.post_date < '2024-06-01' THEN ard.type IS NOT NULL
                                    END
                                )
                        ),0) AS adjust_nominal
                        FROM good_receipts gr
                        LEFT JOIN users u
                            ON u.id = gr.account_id
                        WHERE 
                            gr.post_date <= :date5
                            AND gr.status IN ('2','3')
                            AND gr.deleted_at IS NULL
                    ) AS rs
                WHERE (rs.total - rs.total_invoice - rs.total_return) > 0
        ", array(
            'date1'     => $date,
            'date2'     => $date,
            'date3'     => $date,
            'date4'     => $date,
            'date5'     => $date,
        ));

        $totalUnbilled = 0;

        $arr = [];
        $no = 1;

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
            $currency_rate = $row->currency_rate;
            $total_received_after_adjust = round($row->total_detail + $row->adjust_nominal,2);
            $total_invoice_after_adjust = ($row->total_invoice - $total_reconcile + $row->total_return) * $currency_rate;
            $balance_after_adjust = $total_received_after_adjust - $total_invoice_after_adjust;
            if(round($balance,2) > 0){
                $arr[] = [
                    'no'            => $no,
                    'no_grpo'       => $row->code,
                    'vendor'        => $row->account_name,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'delivery_no'   => $row->delivery_no,
                    'note'          => $row->note,
                    'kurs'          => $currency_rate > 1 ? round($balance_after_adjust / $balance,2) : $currency_rate,
                    'real'          => number_format($balance,2,',','.'),
                    'total_received'=> number_format($total_received_after_adjust,2,',','.'),
                    'total_invoice' => number_format($total_invoice_after_adjust,2,',','.'),
                    'total_balance' => number_format($balance_after_adjust,2,',','.'),
                ];

                $totalUnbilled += round($balance_after_adjust,2);
                $no++;
            }
        }
        $arr[] = [
            'no'            => '',
            'no_grpo'       => '',
            'vendor'        => '',
            'post_date'     => '',
            'delivery_no'   => '',
            'note'          => '',
            'total_received'=> '',
            'total_invoice' => '',
            'total_balance' => '',
            'kurs'          => 'TOTAL',
            'real'          => number_format($totalUnbilled,2,',','.'),
        ];
        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Hutang Usaha Belum Ditagihkan.';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	/**
	 * @return array
	 */
	public function headings() : array
	{
		return $this->headings;
	}
}

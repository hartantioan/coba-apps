<?php

namespace App\Exports;

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
                *,
                u.name AS account_name,
                (SELECT 
                    SUM(pid.total) 
                    FROM purchase_invoice_details pid 
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
                    AND pid.purchase_invoice_id 
                        IN (
                            SELECT 
                                pi.id 
                                FROM purchase_invoices pi 
                                WHERE pi.status IN ('2','3','7') 
                                AND pi.post_date <= :date1
                            )
                ) AS total_invoice,
                (SELECT 
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
                                AND grt.post_date <= :date2
                            )
                ) AS total_return
                FROM good_receipts gr
                LEFT JOIN users u
                    ON u.id = gr.account_id
                WHERE 
                    gr.post_date <= :date3
                    AND gr.status IN ('2','3')
                    AND gr.deleted_at IS NULL
        ", array(
            'date1'     => $date,
            'date2'     => $date,
            'date3'     => $date,
        ));

        $totalUnbilled = 0;

        foreach($results as $key => $row){
            $balance = $row->total - $row->total_invoice - $row->total_return;
            if($balance > 0){
                $array_filter[] = [
                    'no'            => ($key + 1),
                    'code'          => $row->code,
                    'vendor'        => $row->account_name,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'delivery_no'   => $row->delivery_no,
                    'note'          => $row->note,
                    'total_received'=> number_format($row->total,2,',','.'),
                    'total_invoice' => number_format($row->total_invoice,2,',','.'),
                    'total_balance' => number_format($balance,2,',','.'),
                ];
                $totalUnbilled += $balance;
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

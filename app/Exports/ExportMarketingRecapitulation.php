<?php

namespace App\Exports;

use App\Models\MarketingOrder;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportMarketingRecapitulation implements FromView , WithEvents
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
        $totalAll=0;
        $array_filter = [];
        $mo = MarketingOrder::whereIn('status',['2','3'])->whereDate('post_date','<=',$this->date)->get();
            
        foreach($mo as $row){
            $totalInvoice = $row->totalInvoice();
            $totalMemo = $row->totalMemo();
            $totalPayment = $row->totalPayment();
            $balance = $totalInvoice - $totalMemo - $totalPayment;
            $array_filter[] = [
                'code'              => $row->code,
                'customer'          => $row->account->name,
                'post_date'         => date('d/m/y',strtotime($row->post_date)),
                'top'               => $row->top_customer,
                'note'              => $row->note,
                'subtotal'          => number_format($row->subtotal,2,',','.'),
                'discount'          => number_format($row->discount,2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'total_after_tax'   => number_format($row->total_after_tax,2,',','.'),
                'rounding'          => number_format($row->rounding,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                'schedule'          => number_format($row->totalMod(),2,',','.'),
                'sent'              => number_format($row->totalModProcess(),2,',','.'),
                'return'            => number_format($row->totalReturn(),2,',','.'),
                'invoice'           => number_format($totalInvoice,2,',','.'),
                'memo'              => number_format($totalMemo,2,',','.'),
                'payment'           => number_format($totalPayment,2,',','.'),
                'balance'           => number_format($balance,2,',','.'),
            ];            
        }

        return view('admin.exports.sales_recapitulation', [
            'data'      => $array_filter,
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

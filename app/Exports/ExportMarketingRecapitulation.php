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
    
    protected $start_date,$end_date;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
    }
    public function view(): View
    {
        $totalAll=0;
        $array_filter = [];
        $mo = MarketingOrder::whereIn('status',['2','3'])
                ->whereDate('post_date', '>=', $this->start_date)
                ->whereDate('post_date', '<=', $this->end_date)->get();
            
        foreach($mo as $row){
            $totalInvoice = $row->totalInvoice();
            $totalMemo = $row->totalMemo();
            $totalPayment = $row->totalPayment();
            $balance = $totalInvoice - $totalMemo - $totalPayment;
            $array_filter[] = [
                'code'              => $row->code,
                'customer'          => $row->account->name,
                'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                'top'               => $row->top_customer,
                'note'              => $row->note,
                'total'             => round($row->total,2),
                'tax'               => round($row->tax,2),
                'grandtotal'        => round($row->grandtotal,2),
                'schedule'          => round($row->totalMod(),2),
                'sent'              => round($row->totalModProcess(),2),
                'return'            => round($row->totalReturn(),2),
                'invoice'           => round($totalInvoice,2),
                'memo'              => round($totalMemo,2),
                'payment'           => round($totalPayment,2),
                'balance'           => round($balance,2),
            ];            
        }

        activity()
                ->performedOn(new MarketingOrder())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export market recapitulation marketing data.');

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

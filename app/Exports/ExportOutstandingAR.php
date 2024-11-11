<?php

namespace App\Exports;

use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderDownPayment;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportOutstandingAR implements FromView , WithEvents
{
    protected $date;

    public function __construct(string $date)
    {
        $this->date = $date ? $date : '';
		
    }
    public function view(): View
    {
        $array_filter = [];
        $grandtotalAll = 0;

        $query_data = MarketingOrderInvoice::where(function($query){
            if($this->date) {
                $query->whereDate('post_date', '<=', $this->date);
            }
        })
        ->whereIn('status',['2','3'])
        ->get();
        
        foreach($query_data as $row){
            $payment = round($row->totalPayByDate($this->date),2);
            $balance = round($row->grandtotal - $payment,2);
            if($balance > 0){
                $array_filter[] = [
                    'code'              => $row->code,
                    'customer'          => $row->account->name,
                    'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                    'due_date'          => date('d/m/Y',strtotime($row->due_date)),
                    'top'               => $row->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderDeliveryProcess->marketingOrderDelivery->top_internal : '-',
                    'type'              => $row->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderDeliveryProcess->marketingOrderDelivery->soType() : '-',
                    'note'              => $row->note,
                    'total'             => $row->grandtotal,
                    'payment'           => $payment,
                    'balance'           => $balance,
                ];
                $grandtotalAll += $balance;
            }
        }

        activity()
                ->performedOn(new MarketingOrderInvoice())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export marketing order invoice.');

        return view('admin.exports.outstanding_ar', [
            'data'          => $array_filter,
            'grandtotal'    => number_format($grandtotalAll,2,',','.'),
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

<?php

namespace App\Exports;

use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\MarketingOrderInvoiceDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderInvoice;

class ExportMarketingInvoiceDetailRecap implements FromView, WithEvents
{

    protected $start_date, $end_date;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
    }
    public function view(): View
    {
        $totalAll = 0;
        $array_filter = [];
        $data = MarketingOrderInvoiceDetail::whereHas('MarketingOrderInvoice',function($query){
            $query->whereIn('status',['2','3'])->where('post_date', '>=', $this->start_date)
            ->where('post_date', '<=', $this->end_date);
        })->get();


        foreach ($data as $row) {

            $array_filter[] = [
                'code'  => $row->MarketingOrderInvoice->code,
                'tglinvoice' => date('d/m/Y', strtotime($row->MarketingOrderInvoice->post_date)),
                'tglduedate' => date('d/m/Y', strtotime($row->MarketingOrderInvoice->due_date)),
                'grandtotal' => $row->grandtotal,
                'nosj' => $row->MarketingOrderInvoice->marketingOrderDeliveryProcess->code,
                'nomod' => $row->MarketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->code,
                'pocust' => $row->MarketingOrderInvoice->marketingOrderDeliveryProcess->getPoCustomer(),
                'customer' => $row->MarketingOrderInvoice->account->name,
                'item'=>$row->lookable->itemStock->item->name,
                'qty'=>$row->lookable->qty * $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,
                'uom'=>$row->lookable->itemStock->item->uomUnit->code,
                'type'=>$row->MarketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->deliveryType(),
            ];
        }

        activity()
            ->performedOn(new MarketingOrderInvoice())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export ARInvoice Detail Recap.');

        return view('admin.exports.marketing_invoice_detail_recap', [
            'data'      => $array_filter,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Auto-fit columns A to Z
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->autoSize();
                $event->sheet->freezePane("A1");
            }
        ];
    }
}

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
use App\Models\MarketingOrderDeliveryProcessDetail;
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
        $data = MarketingOrderInvoiceDetail::whereHas('marketingOrderInvoice', function ($query) {
            $query->whereIn('status', ['2', '3'])->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->end_date);
        })->where('lookable_type', '=', 'marketing_order_delivery_process_details')->get();


        foreach ($data as $row) {

            $array_filter[] = [
                'code'  => $row->marketingOrderInvoice->code,
                'tglinvoice' => date('d/m/Y', strtotime($row->marketingOrderInvoice->post_date)),
                'tglduedate' => date('d/m/Y', strtotime($row->marketingOrderInvoice->due_date)),
                'grandtotal' => $row->grandtotal,
                'nosj' => $row->marketingOrderInvoice->marketingOrderDeliveryProcess->code,
                'nomod' => $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->code,
                'pocust' => $row->marketingOrderInvoice->marketingOrderDeliveryProcess->getPoCustomer(),
                'customer' => $row->marketingOrderInvoice->account->name,
                'item' => $row->lookable->itemStock->item->name,
                'qty' => $row->lookable->qty * $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,
                'uom' => $row->lookable->itemStock->item->uomUnit->code,
                'type' => $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->deliveryType(),
                'tglsj' => date('d/m/Y', strtotime($row->marketingOrderInvoice->marketingOrderDeliveryProcess->post_date)),
            ];
        }

        $data = MarketingOrderDeliveryProcessDetail::whereHas('marketingOrderDeliveryProcess', function ($query) {
            $query->whereIn('status', ['2'])->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->end_date);
        })->get();

        foreach ($data as $row) {

            $array_filter[] = [
                'code'  => '',
                'tglinvoice' => '',
                'tglduedate' => '',
                'grandtotal' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->price_after_discount * $row->qty * $row->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,
                'nosj' => $row->marketingOrderDeliveryProcess->code,
                'tglsj' => date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->post_date)),
                'nomod' => $row->marketingOrderDeliveryProcess->marketingOrderDelivery->code,
                'pocust' => $row->marketingOrderDeliveryProcess->getPoCustomer(),
                'customer' => $row->marketingOrderDeliveryProcess->marketingOrderDelivery->customer->name,
                'item' => $row->marketingOrderDeliveryDetail->item->name,
                'qty' => $row->qty * $row->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion,
                'uom' => $row->marketingOrderDeliveryDetail->item->uomUnit->code,
                'type' => $row->marketingOrderDeliveryProcess->marketingOrderDelivery->deliveryType(),
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

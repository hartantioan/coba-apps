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
                ->where('post_date', '<=', $this->end_date)->where('lookable_type','<>','marketing_order_down_payments');
        })->get();


        $code = [];


        foreach ($data as $row) {
            $code[] = array_push($code, $row->marketingOrderInvoice->code);
        }
        $counts = array_count_values($code);
        $checkdata = '1';
        $ceksama = '';
        $pricefinal = 0;

        foreach ($data as $row) {

            if ($ceksama == $row->marketingOrderInvoice->code) {
                $checkdata = 2;
            } else {
                $checkdata = 1;
            }

            $item = '';
            $qty = 0;
            $uom = '';
            $disc1 = 0;
            $disc2 = 0;
            $disc3 = 0;
            $typesell = '-';

            if ($row->lookable_type == 'marketing_order_delivery_process_details') {
                if ($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->include_tax == "0") {
                    $pricefinal = $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->price;
                } else {
                    $pricefinal = Round($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->price / (($row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->percent_tax + 100) / 100), 2);
                }
                $item = $row->lookable->itemStock->item->name;
                $qty = $row->lookable->qty * $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion;
                $uom = $row->lookable->itemStock->item->uomUnit->code;
                $disc1 = $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->percent_discount_1;
                $disc2 = $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->percent_discount_2;
                $disc3 = $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->discount_3;
                $typesell = $row->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->type();
            } elseif ($row->lookable_type == 'marketing_order_delivery_details') {
                if ($row->lookable->marketingOrderDetail->include_tax == "0") {
                    $pricefinal = $row->lookable->marketingOrderDetail->price;
                } else {
                    $pricefinal = Round($row->lookable->marketingOrderDetail->price / (($row->lookable->marketingOrderDetail->percent_tax + 100) / 100), 2);
                }
                $item = $row->lookable->item->name;
                $qty = $row->lookable->qty * $row->lookable->marketingOrderDetail->qty_conversion;
                $uom = $row->lookable->item->uomUnit->code;
                $disc1 = $row->lookable->marketingOrderDetail->percent_discount_1;
                $disc2 = $row->lookable->marketingOrderDetail->percent_discount_2;
                $disc3 = $row->lookable->marketingOrderDetail->discount_3;
                $typesell = $row->lookable->marketingOrderDetail->marketingOrder->type();
            } 

            $array_filter[] = [
                'code'  => $row->marketingOrderInvoice->code,
                'tglinvoice' => date('d/m/Y', strtotime($row->marketingOrderInvoice->post_date)),
                'tglduedate' => date('d/m/Y', strtotime($row->marketingOrderInvoice->due_date)),
                'grandtotal' => $row->total,
                'nosj' => $row->marketingOrderInvoice->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderInvoice->marketingOrderDeliveryProcess->code : '-',
                'nomod' => $row->marketingOrderInvoice->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->code : '-',
                'pocust' => $row->marketingOrderInvoice->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderInvoice->marketingOrderDeliveryProcess->getPoCustomer() : '-',
                'customer' => $row->marketingOrderInvoice->account->name,
                'item' => $item,
                'qty' => $qty,
                'uom' => $uom,
                'price' => $pricefinal,
                'disc1' => $disc1,
                'disc2' => $disc2,
                'disc3' => $disc3,
                'type' => $row->marketingOrderInvoice->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->deliveryType() : '-',
                'tglsj' => $row->marketingOrderInvoice->marketingOrderDeliveryProcess()->exists() ? date('d/m/Y', strtotime($row->marketingOrderInvoice->marketingOrderDeliveryProcess->post_date)) : '-',
                'typesell' => $typesell,
                'totalbayar' => $row->marketingOrderInvoice->totalPay(),
                'row' => $counts[$row->marketingOrderInvoice->code],
                'checkdata' => $checkdata,
                'totalinvoice' => $row->marketingOrderInvoice->total,
                'tax' => $row->marketingOrderInvoice->tax,
                'grandtotalinvoice' => $row->marketingOrderInvoice->grandtotal,
                'taxno' => $row->marketingOrderInvoice->tax_no,

            ];

            $ceksama = $row->marketingOrderInvoice->code;
        }

        $data = MarketingOrderDeliveryProcessDetail::whereHas('marketingOrderDeliveryProcess', function ($query) {
            $query->whereIn('status', ['2'])->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->end_date);
        })->get();

        foreach ($data as $row) {

            if ($row->marketingOrderDeliveryDetail->marketingOrderDetail->include_tax == "0") {
                $pricefinal = $row->marketingOrderDeliveryDetail->marketingOrderDetail->price;
            } else {
                $pricefinal = Round($row->marketingOrderDeliveryDetail->marketingOrderDetail->price / (($row->marketingOrderDeliveryDetail->marketingOrderDetail->percent_tax + 100) / 100), 2);
            }

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
                'price' => $pricefinal,
                'disc1' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->percent_discount_1,
                'disc2' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->percent_discount_2,
                'disc3' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->discount_3,
                'type' => $row->marketingOrderDeliveryProcess->marketingOrderDelivery->deliveryType(),
                'typesell' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->Type() ?? '',
                'totalbayar' => 0,
                'row' => 1,
                'checkdata' => 1,
                'totalinvoice' => 0,
                'tax' => 0,
                'grandtotalinvoice' => 0,
                'taxno' => '',
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

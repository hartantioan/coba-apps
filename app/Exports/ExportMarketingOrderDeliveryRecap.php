<?php

namespace App\Exports;

use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\MarketingOrderDeliveryDetail;

class ExportMarketingOrderDeliveryRecap implements FromView, WithEvents
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
        $mo = MarketingOrderDeliveryDetail::whereHas('marketingOrderDelivery', function ($query) {
            $query->whereIn('status', ['2', '3'])->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->end_date);
        })->get();


        foreach ($mo as $row) {

            $array_filter[] = [
                'code'              => $row->marketingOrderDelivery->code,
                'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDelivery->post_date)),
                'customer' => $row->marketingOrderDelivery->customer->name,
                'expedisi'              => $row->marketingOrderDelivery->costDeliveryType(),
                'pengiriman'                => $row->marketingOrderDelivery->deliveryType(),
                'alamatkirim'                => $row->marketingOrderDelivery->destination_address,
                'kota' => $row->marketingOrderDelivery->city->name,
                'kecamatan' => $row->marketingOrderDelivery->district->name,
                'truk' => $row->marketingOrderDelivery->transportation->name,
                'statuskirim' => $row->marketingOrderDelivery->sendStatus(),
                'noteinternal' => $row->marketingOrderDelivery->note_internal,
                'noteexternal' => $row->marketingOrderDelivery->note_external,
                'itemcode' => $row->item->code,
                'itemname' => $row->item->name,
                'qty' => $row->qty,
                'konversi' => $row->getQtyM2(),
                'noteitem' => $row->note,
                'so'=> $row->marketingOrderDetail->marketingOrder->code,
            ];
        }

        activity()
            ->performedOn(new MarketingOrderDelivery())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export MOD Recap.');

        return view('admin.exports.marketing_order_delivery_recap', [
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

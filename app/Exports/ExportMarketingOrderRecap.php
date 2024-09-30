<?php

namespace App\Exports;

use App\Models\MarketingOrder;
use App\Models\MarketingOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportMarketingOrderRecap implements FromView, WithEvents
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
        $mo = MarketingOrderDetail::whereHas('marketingOrder', function ($query) {
            $query->whereIn('status', ['2', '3'])->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->end_date);
        })->get();


        foreach ($mo as $row) {

            $array_filter[] = [
                'code'              => $row->marketingOrder->code,
                'customer'          => $row->marketingOrder->account->name,
                'post_date'         => date('d/m/Y', strtotime($row->marketingOrder->post_date)),
                'top'               => $row->marketingOrder->top_customer,
                'tipe'              => $row->marketingOrder->type(),
                'po'                => $row->marketingOrder->document_no,
                'pengiriman'                => $row->marketingOrder->deliveryType(),
                'alamatkirim'                => $row->marketingOrder->destination_address,
                'provinsi' => $row->marketingOrder->province->name,
                'kota' => $row->marketingOrder->city->name,
                'kecamatan' => $row->marketingOrder->district->name,
                'noteinternal' => $row->marketingOrder->note_internal,
                'noteexternal' => $row->marketingOrder->note_external,
                'itemcode' => $row->item->code,
                'itemname' => $row->item->name,
                'qty' => $row->qty_uom,
                'price' => $row->price,
                'disc1' => $row->percent_discount_1,
                'disc2' => $row->percent_discount_2,
                'disc3' => $row->discount_3,
                'truck' => $row->marketingOrder->transportation->name,
                
                'pembayaran' => $row->marketingOrder->paymentType(),




            ];
        }

        activity()
            ->performedOn(new MarketingOrder())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export SO Recap.');

        return view('admin.exports.marketing_order_recap', [
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

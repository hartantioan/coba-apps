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
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDetail;

class ExportOutstandingSO implements FromView, WithEvents
{


    public function __construct() {}
    public function view(): View
    {
        $array_filter = [];



        $query_data = MarketingOrderDetail::whereHas('marketingOrder', function ($query) {
            $query->whereIn('status', ['2']);
        })->get();

        foreach ($query_data as $row) {

            $array_filter[] = [
                'code'              => $row->marketingOrder->code,
                'customer'          => $row->marketingOrder->account->name,
                'post_date'         => date('d/m/Y', strtotime($row->marketingOrder->post_date)),
                'top'               => $row->marketingOrder->account->top,
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
                'qty'=>$row->qty_uom,
                'price'=>$row->price,
                'disc1'=>$row->percent_discount_1,
                'disc2'=>$row->percent_discount_2,
                'disc3'=>$row->discount_3,
                'truck'=>$row->marketingOrder->transportation->name,
                'qtymod'=>$row->balanceQtyModM2(),
                'pembayaran'=>$row->marketingOrder->paymentType(),
                
            ];
        }




        activity()
            ->performedOn(new MarketingOrder())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Outstanding SO.');

        return view('admin.exports.outstanding_so', [
            'data'          => $array_filter,

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

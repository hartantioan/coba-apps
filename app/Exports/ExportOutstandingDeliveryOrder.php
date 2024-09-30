<?php

namespace App\Exports;

use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderDownPayment;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;

class ExportOutstandingDeliveryOrder implements FromView, WithEvents
{


    public function __construct() {}
    public function view(): View
    {
        $array_filter = [];



        $query_data = MarketingOrderDeliveryProcessDetail::whereHas('marketingOrderDeliveryProcess', function ($query) {
            $query->whereIn('status', ['2']);
        })->get();

        foreach ($query_data as $row) {

            $array_filter[] = [
                'code'              => $row->marketingOrderDeliveryProcess->code,
                'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->post_date)),
                'customer' =>$row->marketingOrderDeliveryProcess->account->name,
                'sopir'                => $row->marketingOrderDeliveryProcess->driver_name,
                'truk'=>$row->marketingOrderDeliveryProcess->vehicle_name,
                'nopol' => $row->marketingOrderDeliveryProcess->vehicle_no,
               
                'itemcode' => $row->marketingOrderDeliveryDetail->item->code,
                'itemname' => $row->marketingOrderDeliveryDetail->item->name,
                'qty' => $row->qty * $row->marketingOrderDeliveryDetail->getQtyM2(),
                'konversi' => $row->marketingOrderDeliveryDetail->getQtyM2(),
           
            ];
        }




        activity()
            ->performedOn(new MarketingOrderDeliveryProcess())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Outstanding Delivery.');

        return view('admin.exports.outstanding_delivery_order', [
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

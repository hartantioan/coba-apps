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
use App\Models\MarketingOrderInvoiceDetail;

class ExportOutstandingMarketingInvoice implements FromView, WithEvents
{


    public function __construct() {}
    public function view(): View
    {
        $array_filter = [];



        $query_data = MarketingOrderInvoice::whereIn('status', ['2'])->get();

        foreach ($query_data as $row) {

            $array_filter[] = [
                'code'              => $row->code,
                'post_date'         => date('d/m/Y', strtotime($row->post_date)),
                'customer' =>$row->account->name,
                'deliveraddress'=>$row->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderDeliveryProcess->marketingOrderDelivery->destination_address : '-',
                'total' => $row->grandtotal,
                'taxno' => $row->tax_no,
                'payment'=>$row->type(),
                'duedateinternal'=>date('d/m/Y', strtotime($row->due_date_internal)),
               'aging' => $row->getAge(),
           
            ];
        }




        activity()
            ->performedOn(new MarketingOrderInvoice())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Outstanding AR Invoice.');

        return view('admin.exports.outstanding_marketing_invoice', [
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

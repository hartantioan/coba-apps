<?php

namespace App\Exports;

use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderDownPayment;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryDetail;


class ExportOutstandingMOD implements FromView, WithEvents
{


    public function __construct() {}
    public function view(): View
    {
        $query_data = MarketingOrderDelivery::whereIn('status', ['2','3'])->whereDoesntHave('marketingOrderDeliveryProcess')->get();

        activity()
            ->performedOn(new MarketingOrderDelivery())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Outstanding MOD.');

        return view('admin.exports.outstanding_mod', [
            'data'          => $query_data,

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

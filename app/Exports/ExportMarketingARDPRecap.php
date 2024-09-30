<?php

namespace App\Exports;

use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ExportMarketingARDPRecap implements FromView, WithEvents
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
        $mo = MarketingOrderDownPayment::whereIn('status', ['2', '3'])->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->end_date)->get();


        foreach ($mo as $row) {

            $array_filter[] = [
                'code'              => $row->code,
                'customer'          => $row->account->name,
                'post_date'         => date('d/m/Y', strtotime($row->post_date)),
                'total'               => $row->total,
                'tax'              => $row->tax,
                'grandtotal'                => $row->grandtotal,
                'taxno'                => $row->tax_no,
                'note'                => $row->note,
                'status' => $row->statusRaw(),
                'noincoming' => $row->getCodeIncomingPayments()[0],
                'tglincoming' => $row->getCodeIncomingPayments()[1],
                //'tglincoming' => date('d/m/Y', strtotime($row->incomingPaymentDetail2->incomingPayment->post_date)),
            ];
        }

        activity()
            ->performedOn(new MarketingOrderDownPayment())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export SO Recap.');

        return view('admin.exports.marketing_ardp_recap', [
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

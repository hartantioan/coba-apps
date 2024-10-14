<?php

namespace App\Exports;

use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderInvoice;

class ExportMarketingInvoiceRecap implements FromView, WithEvents
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
        $mo = MarketingOrderInvoice::where('post_date', '>=', $this->start_date)
            ->where('post_date', '<=', $this->end_date)
            ->get();


        foreach ($mo as $row) {

            $array_filter[] = [
                'status'              => $row->statusRaw(),
                'code'              => $row->code,
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'void_date'         => $row->voidUser()->exists() ? $row->void_date : '',
                'void_note'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'delete_date'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'delete_note'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                'done_date'         => $row->doneUser()->exists() ? $row->done_date : '',
                'done_note'         => $row->doneUser()->exists() ? $row->done_note : '',

                'post_date'         => date('d/m/Y', strtotime($row->post_date)),
                'customer' => $row->account->name,
                'deliveraddress' => $row->marketingOrderDeliveryProcess->marketingOrderDelivery->destination_address,
                'subtotal' => $row->subtotal,
                'dp' => $row->downpayment,
                'tax' => $row->tax,
                'total' => $row->total,
                'grandtotal' => $row->grandtotal,
                'taxno' => $row->tax_no,
                'payment' => $row->type(),
                'duedateinternal' => date('d/m/Y', strtotime($row->due_date_internal)),
                'nonpwp' => $row->userData->npwp,
                'namanpwp' => $row->userData->title,
                'alamatnpwp' => $row->userData->address,
                'tipepenjualan'=>$row->marketingOrderDeliveryProcess->marketingOrderDelivery->soType(),
                'percentage'=>$row->taxMaster->percentage,
            ];
        }

        activity()
            ->performedOn(new MarketingOrderInvoice())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export ARInvoice Recap.');

        return view('admin.exports.marketing_invoice_recap', [
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

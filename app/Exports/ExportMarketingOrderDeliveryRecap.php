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
            $query->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->end_date);
        })->get();


        foreach ($mo as $row) {

            $array_filter[] = [
                'code'              => $row->marketingOrderDelivery->code,
                'customer' => $row->marketingOrderDelivery->customer->name,
                'voider'            => $row->marketingOrderDelivery->voidUser()->exists() ? $row->marketingOrderDelivery->voidUser->name : '',
                'void_date'         => $row->marketingOrderDelivery->voidUser()->exists() ? $row->marketingOrderDelivery->void_date : '',
                'void_note'         => $row->marketingOrderDelivery->voidUser()->exists() ? $row->marketingOrderDelivery->void_note : '',
                'deleter'           => $row->marketingOrderDelivery->deleteUser()->exists() ? $row->marketingOrderDelivery->deleteUser->name : '',
                'delete_date'       => $row->marketingOrderDelivery->deleteUser()->exists() ? $row->marketingOrderDelivery->deleted_at : '',
                'delete_note'       => $row->marketingOrderDelivery->deleteUser()->exists() ? $row->marketingOrderDelivery->delete_note : '',
                'doner'             => ($row->marketingOrderDelivery->status == 3 && is_null($row->marketingOrderDelivery->done_id)) ? 'sistem' : (($row->marketingOrderDelivery->status == 3 && !is_null($row->marketingOrderDelivery->done_id)) ? $row->marketingOrderDelivery->doneUser->name : null),
                'done_date'         => $row->marketingOrderDelivery->doneUser()->exists() ? $row->marketingOrderDelivery->done_date : '',
                'done_note'         => $row->marketingOrderDelivery->doneUser()->exists() ? $row->marketingOrderDelivery->done_note : '',
                'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDelivery->post_date)),
                'expedisi'              => $row->marketingOrderDelivery->costDeliveryType(),
                'pengiriman'                => $row->marketingOrderDelivery->deliveryType(),
                'alamatkirim'                => $row->marketingOrderDelivery->destination_address,
                'kota' => $row->marketingOrderDelivery->city->name,
                'kecamatan' => $row->marketingOrderDelivery->district->name,
                'truk' => $row->marketingOrderDelivery->transportation->name,
                'status'=> $row->marketingOrderDelivery->statusRaw(),
                'statuskirim' => $row->marketingOrderDelivery->sendStatus(),
                'noteinternal' => $row->marketingOrderDelivery->note_internal,
                'noteexternal' => $row->marketingOrderDelivery->note_external,
                'itemcode' => $row->item->code,
                'itemname' => $row->item->name,
                'nik' => $row->marketingOrderDelivery->user->employee_no,
                'user' => $row->marketingOrderDelivery->user->name,
                'qty' => $row->qty,
                'konversi' => $row->getQtyM2(),
                'noteitem' => $row->note,
                'so'=> $row->marketingOrderDetail->marketingOrder->code ?? '-',
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

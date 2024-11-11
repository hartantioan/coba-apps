<?php

namespace App\Exports;

use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\MarketingOrderDeliveryProcess;

class ExportMarketingDeliveryRecap implements FromView, WithEvents
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
            $query->whereHas('marketingOrderDeliveryProcess', function ($query) {
                $query->where('post_date', '>=', $this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            });
        })->get();

        // $array_sudah = [];
        // $array_shading = [];
        // if(!in_array($row->code,$array_sudah)){

        // }
        $key = 0;
        foreach ($mo as $key => $row) {

            $array_filter[] = [
                'no'                => ($key+1),
                'code'              => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->code,
                'status'            => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->statusRaw(),
                'voider'            => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->voidUser()->exists() ? $row->marketingOrderDelivery->marketingOrderDeliveryProcess->voidUser->name : '',
                'tgl_void'         => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->voidUser()->exists() ? date('d/m/Y',strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcess->void_date)) : '' ,
                'ket_void'               => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->voidUser()->exists() ? $row->marketingOrderDelivery->marketingOrderDeliveryProcess->void_note : '' ,
                'deleter'              =>$row->marketingOrderDelivery->marketingOrderDeliveryProcess->deleteUser()->exists() ? $row->marketingOrderDelivery->marketingOrderDeliveryProcess->deleteUser->name : '',
                'tgl_delete'             => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->deleteUser()->exists() ? date('d/m/Y',strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcess->deleted_at)) : '',
                'ket_delete'               => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->deleteUser()->exists() ? $row->marketingOrderDelivery->marketingOrderDeliveryProcess->delete_note : '',
                'doner'        => ($row->marketingOrderDelivery->marketingOrderDeliveryProcess->status == 3 && is_null($row->marketingOrderDelivery->marketingOrderDeliveryProcess->done_id)) ? 'sistem' : (($row->marketingOrderDelivery->marketingOrderDeliveryProcess->status == 3 && !is_null($row->marketingOrderDelivery->marketingOrderDeliveryProcess->done_id)) ? $row->marketingOrderDelivery->marketingOrderDeliveryProcess->doneUser->name : null),
                'tgl_done'          => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->doneUser ? $row->marketingOrderDelivery->marketingOrderDeliveryProcess->done_date : '',
                'ket_done'              => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->doneUser ? $row->marketingOrderDelivery->marketingOrderDeliveryProcess->done_note : '' ,

                'nik' =>$row->marketingOrderDelivery->marketingOrderDeliveryProcess->user->employee_no,
                'user' =>$row->marketingOrderDelivery->marketingOrderDeliveryProcess->user->name,

                'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcess->post_date)),
                'customer' =>$row->marketingOrderDelivery->customer->name,
                'itemcode' => $row->item->code,
                'itemname' => $row->item->name,

                'plant' => $row->place->name??'-',
                'qtysj' => $row->qty,
                // 'qty_konversi' => $row->getQtyM2(),
                'satuan_konversi' => $row->marketingOrderDetail->itemUnit->unit->code,
                'qty' => $row->qty * $row->getQtyM2(),
                'satuan' => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->getUnit(),
                'gudang' => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->getWarehouse(),
                'area' => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->getArea(),
                'shading' => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->getShading(),
                'batch' => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->getBatch(),
                'delivery_type' => $row->marketingOrderDelivery->deliveryType(),
                'list_invoice' =>$row->marketingOrderDelivery->marketingOrderDeliveryProcess->marketingOrderInvoice->code ?? '',
                'expedisi' =>$row->marketingOrderDelivery->marketingOrderDeliveryProcess->account->name,
                'sopir'                => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->driver_name,
                'no_wa_supir'                => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->driver_hp,
                'truk'=>$row->marketingOrderDelivery->marketingOrderDeliveryProcess->vehicle_name,
                'nopol' => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->vehicle_no,
                'no_kontainer'          => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->no_container,
                'outlet' => $row->marketingOrderDetail->marketingOrder->outlet->name ?? '-',
                'alamat_tujuan'=> $row->marketingOrderDelivery->destination_address,
                'catatan_internal'=>$row->marketingOrderDelivery->note_internal,
                'catatan_eksternal'=>$row->marketingOrderDelivery->note_external,
                'tracking'=>$row->marketingOrderDelivery->marketingOrderDeliveryProcess->statusTrackingRaw(),
                'status_item_sent'=>$row->marketingOrderDelivery->marketingOrderDeliveryProcess->isItemSent() ? date('d/m/Y', strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcess->post_date)) : '',
                'status_received_by_customer'=>$row->marketingOrderDelivery->marketingOrderDeliveryProcess->isDelivered() ? date('d/m/Y', strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcess->receive_date)) : '',
                'status_returned_document' =>
                $row->marketingOrderDelivery->marketingOrderDeliveryProcess && $row->marketingOrderDelivery->marketingOrderDeliveryProcess->return_date
                    ? date('d/m/Y', strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcess->return_date))
                    : '',
                'based_on'=>$row->marketingOrderDelivery->code,
                'so' => $row->marketingOrderDetail->marketingOrder->code,
                'no_timbangan'=> $row->marketingOrderDelivery->goodScaleDetail->goodScale->code ?? '-',
                'po_customer' => $row->marketingOrderDetail->marketingOrder->document_no,
                'brand' => $row->marketingOrderDelivery->marketingOrderDeliveryProcess->getBrand(),
                'so_type' => $row->marketingOrderDelivery->soType(),
            ];
        }

        activity()
            ->performedOn(new MarketingOrderDeliveryProcess())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Delivery Recap.');

        return view('admin.exports.marketing_delivery_recap', [
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

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
            $query->whereHas('marketingOrderDeliveryProcessAll', function ($query) {
                $query->where('post_date', '>=', $this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            });
        })->with(['marketingOrderDelivery.marketingOrderDeliveryProcessAll']) // Include the related process for sorting
        ->get()
        ->sortBy(function ($item) {
            return $item->marketingOrderDelivery->marketingOrderDeliveryProcessAll->code ?? '';
        })->values();

        // $array_sudah = [];
        // $array_shading = [];
        // if(!in_array($row->code,$array_sudah)){

        // }
        $key = 0;
        foreach ($mo as $key => $row) {

            $array_filter[] = [
                'no'                => ($key+1),
                'code'              => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->code,
                'status'            => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->statusRaw(),
                'voider'            => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->voidUser()->exists() ? $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->voidUser->name : '',
                'tgl_void'         => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->voidUser()->exists() ? date('d/m/Y',strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->void_date)) : '' ,
                'ket_void'               => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->voidUser()->exists() ? $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->void_note : '' ,
                'deleter'              =>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->deleteUser()->exists() ? $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->deleteUser->name : '',
                'tgl_delete'             => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->deleteUser()->exists() ? date('d/m/Y',strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->deleted_at)) : '',
                'ket_delete'               => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->deleteUser()->exists() ? $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->delete_note : '',
                'doner'        => ($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->status == 3 && is_null($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->done_id)) ? 'sistem' : (($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->status == 3 && !is_null($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->done_id)) ? $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->doneUser->name : null),
                'tgl_done'          => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->doneUser ? $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->done_date : '',
                'ket_done'              => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->doneUser ? $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->done_note : '' ,

                'nik' =>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->user->employee_no,
                'user' =>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->user->name,

                'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->post_date)),
                'customer' =>$row->marketingOrderDelivery->customer->name,
                'itemcode' => $row->item->code,
                'itemname' => $row->item->name,

                'plant' => $row->place->name??'-',
                'qtysj' => $row->qty,
                // 'qty_konversi' => $row->getQtyM2(),
                'satuan_konversi' => $row->marketingOrderDetail->itemUnit->unit->code,
                'qty' => $row->qty * $row->getQtyM2(),
                'satuan' => 'M2',
                'gudang' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->getWarehouse(),
                'area' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->getArea(),
                'shading' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->getShading(),
                'batch' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->getBatch(),
                'delivery_type' => $row->marketingOrderDelivery->deliveryType(),
                'list_invoice' =>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->marketingOrderInvoice->code ?? '',
                'expedisi' =>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->account->name,
                'sopir'                => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->driver_name,
                'no_wa_supir'                => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->driver_hp,
                'truk'=>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->vehicle_name,
                'nopol' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->vehicle_no,
                'no_kontainer'          => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->no_container,
                'outlet' => $row->marketingOrderDetail->marketingOrder->outlet->name ?? '-',
                'alamat_tujuan'=> $row->marketingOrderDelivery->destination_address,
                'catatan_internal'=>$row->marketingOrderDelivery->note_internal,
                'catatan_eksternal'=>$row->marketingOrderDelivery->note_external,
                'tracking'=>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->statusTrackingRaw(),
                'status_item_sent'=>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->isItemSent() ? date('d/m/Y', strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->post_date)) : '',
                'status_received_by_customer'=>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->isDelivered() ? date('d/m/Y', strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->receive_date)) : '',
                'status_returned_document' =>$row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->return_date
                    ? date('d/m/Y', strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->return_date))
                    : '',
                'based_on'=>$row->marketingOrderDelivery->code,
                'so' => $row->marketingOrderDetail->marketingOrder->code,
                'no_timbangan'=> $row->marketingOrderDelivery->goodScaleDetail->goodScale->code ?? '-',
                'po_customer' => $row->marketingOrderDetail->marketingOrder->document_no,
                'brand' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->getBrand(),
                'so_type' => $row->marketingOrderDelivery->soType(),
            ];
        }

        activity()
            ->performedOn(new marketingOrderDeliveryProcess())
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

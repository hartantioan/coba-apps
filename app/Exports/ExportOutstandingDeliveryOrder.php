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

        foreach ($query_data as $key=>$row) {

            $array_filter[] = [
                'no'                => ($key+1),
                'code'              => $row->marketingOrderDeliveryProcess->code,
                'status'              => $row->marketingOrderDeliveryProcess->statusRaw(),
                'voider'          => $row->marketingOrderDeliveryProcess->voidUser()->exists() ? $row->marketingOrderDeliveryProcess->voidUser->name : '',
                'tgl_void'         => $row->marketingOrderDeliveryProcess->voidUser()->exists() ? date('d/m/Y',strtotime($row->marketingOrderDeliveryProcess->void_date)) : '' ,
                'ket_void'               => $row->marketingOrderDeliveryProcess->voidUser()->exists() ? $row->marketingOrderDeliveryProcess->void_note : '' ,
                'deleter'              =>$row->marketingOrderDeliveryProcess->deleteUser()->exists() ? $row->marketingOrderDeliveryProcess->deleteUser->name : '',
                'tgl_delete'             => $row->marketingOrderDeliveryProcess->deleteUser()->exists() ? date('d/m/Y',strtotime($row->marketingOrderDeliveryProcess->deleted_at)) : '',
                'ket_delete'               => $row->marketingOrderDeliveryProcess->deleteUser()->exists() ? $row->marketingOrderDeliveryProcess->delete_note : '',
                'doner'        => ($row->marketingOrderDeliveryProcess->status == 3 && is_null($row->marketingOrderDeliveryProcess->done_id)) ? 'sistem' : (($row->marketingOrderDeliveryProcess->status == 3 && !is_null($row->marketingOrderDeliveryProcess->done_id)) ? $row->marketingOrderDeliveryProcess->doneUser->name : null),
                'tgl_done'          => $row->marketingOrderDeliveryProcess->doneUser ? $row->marketingOrderDeliveryProcess->done_date : '',
                'ket_done'              => $row->marketingOrderDeliveryProcess->doneUser ? $row->marketingOrderDeliveryProcess->done_note : '' ,
                
                'nik' =>$row->marketingOrderDeliveryProcess->user->employee_no,
                'user' =>$row->marketingOrderDeliveryProcess->user->name,

                'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->post_date)),
                'customer' =>$row->marketingOrderDeliveryDetail->marketingOrderDelivery->customer->name,
                'itemcode' => $row->marketingOrderDeliveryDetail->item->code,
                'itemname' => $row->marketingOrderDeliveryDetail->item->name,

                'plant' => $row->marketingOrderDeliveryDetail->place->name??'-',
                'qtysj' => $row->marketingOrderDeliveryDetail->qty,
                // 'qty_konversi' => $row->marketingOrderDeliveryDetail->getQtyM2(),
                'satuan_konversi' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->itemUnit->unit->code,
                'qty' => $row->qty * $row->marketingOrderDeliveryDetail->getQtyM2(),
                'satuan' => $row->itemStock->item->uomUnit->code,
                'gudang' => $row->itemStock->warehouse->name,
                'area' => $row->itemStock->area->name,
                'shading' => $row->itemStock->itemShading->code,
                'batch' => $row->itemStock->productionBatch->code,
                'so' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->code,
                

                'expedisi' =>$row->marketingOrderDeliveryProcess->account->name,
                'sopir'                => $row->marketingOrderDeliveryProcess->driver_name,
                'no_wa_supir'                => $row->marketingOrderDeliveryProcess->driver_hp,
                'truk'=>$row->marketingOrderDeliveryProcess->vehicle_name,
                'nopol' => $row->marketingOrderDeliveryProcess->vehicle_no,
                'outlet' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->outlet->name ?? '-',
                'alamat_tujuan'=> $row->marketingOrderDeliveryDetail->marketingOrderDelivery->destination_address,
                'catatan_internal'=>$row->marketingOrderDeliveryDetail->marketingOrderDelivery->note_internal,
                'catatan_eksternal'=>$row->marketingOrderDeliveryDetail->marketingOrderDelivery->note_external,
                'tracking'=>$row->marketingOrderDeliveryProcess->statusTrackingRaw(),
                'tgl_kembali_sj'=>date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->statusTrackingDate())),
                'based_on'=>$row->marketingOrderDeliveryDetail->marketingOrderDelivery->code,  
               
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

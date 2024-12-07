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
        $mo = MarketingOrderDeliveryProcess::where('post_date', '>=', $this->start_date)->where('post_date', '<=', $this->end_date)->orderBy('code')->get();

        // $array_sudah = [];
        // $array_shading = [];
        // if(!in_array($row->code,$array_sudah)){

        // }
        $key = 0;
        foreach ($mo as $key => $row) {
            foreach($row->marketingOrderDelivery->marketingOrderDeliveryDetail as $rowdetail){
                $array_filter[] = [
                    'no'                => ($key+1),
                    'code'              => $row->code,
                    'status'            => $row->statusRaw(),
                    'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'tgl_void'         => $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' ,
                    'ket_void'               => $row->voidUser()->exists() ? $row->void_note : '' ,
                    'deleter'              =>$row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'tgl_delete'             => $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '',
                    'ket_delete'               => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'doner'        => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                    'tgl_done'          => $row->doneUser ? $row->done_date : '',
                    'ket_done'              => $row->doneUser ? $row->done_note : '' ,
    
                    'nik' =>$row->user->employee_no,
                    'user' =>$row->user->name,
    
                    'post_date'         => date('d/m/Y', strtotime($row->post_date)),
                    'customer' =>$rowdetail->marketingOrderDelivery->customer->name,
                    'itemcode' => $rowdetail->item->code,
                    'itemname' => $rowdetail->item->name,
    
                    'plant' => $rowdetail->place->name??'-',
                    'qtysj' => $rowdetail->qty,
                    // 'qty_konversi' => $row->getQtyM2(),
                    'satuan_konversi' => $rowdetail->marketingOrderDetail->itemUnit->unit->code,
                    'qty' => $rowdetail->qty * $rowdetail->getQtyM2(),
                    'satuan' => 'M2',
                    'gudang' => $row->getWarehouse(),
                    'area' => $row->getArea(),
                    'shading' => $row->getShading(),
                    'batch' => $row->getBatch(),
                    'delivery_type' => $rowdetail->marketingOrderDelivery->deliveryType(),
                    'list_invoice' =>$row->marketingOrderInvoice->code ?? '',
                    'expedisi' =>$row->account->name,
                    'sopir'                => $row->driver_name,
                    'no_wa_supir'                => $row->driver_hp,
                    'truk'=>$row->vehicle_name,
                    'nopol' => $row->vehicle_no,
                    'no_kontainer'          => $row->no_container,
                    'outlet' => $rowdetail->marketingOrderDetail->marketingOrder->outlet->name ?? '-',
                    'alamat_tujuan'=> $rowdetail->marketingOrderDelivery->destination_address,
                    'catatan_internal'=>$rowdetail->marketingOrderDelivery->note_internal,
                    'catatan_eksternal'=>$rowdetail->marketingOrderDelivery->note_external,
                    'tracking'=>$row->statusTrackingRaw(),
                    'status_item_sent'=>$row->isItemSent() ? date('d/m/Y', strtotime($row->post_date)) : '',
                    'status_received_by_customer'=>$row->isDelivered() ? date('d/m/Y', strtotime($row->receive_date)) : '',
                    'status_returned_document' =>$row->return_date
                        ? date('d/m/Y', strtotime($row->return_date))
                        : '',
                    'based_on'=>$rowdetail->marketingOrderDelivery->code,
                    'so' => $rowdetail->marketingOrderDetail->marketingOrder->code,
                    'no_timbangan'=> $rowdetail->marketingOrderDelivery->goodScaleDetail->goodScale->code ?? '-',
                    'po_customer' => $rowdetail->marketingOrderDetail->marketingOrder->document_no,
                    'brand' => $row->getBrand(),
                    'so_type' => $rowdetail->marketingOrderDelivery->soType(),
                ];
            }
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

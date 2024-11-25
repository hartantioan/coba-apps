<?php

namespace App\Exports;

use App\Models\MarketingOrderDeliveryProcessDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportDeliveryOrderProcessAccountingRecap implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $end_date;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
    }

    private $headings = [
        'No',
        'Dokumen',
        'Status',
        'Voider',
        'Tgl Void',
        'Ket Void',
        'Deleter',
        'Tgl Delete',
        'Ket Delete',
        'Doner',
        'Tgl Done',
        'Ket Done',
        'NIK',
        'User',
        'Tgl.Post',
        'Customer',
        'Item Code',
        'Item Name',
        'Brand',
        'Plant',
        'Qty Delivery',
        'Satuan',
        'Qty (M2)',
        'Satuan',
        'Gudang',
        'Area',
        'Shading',
        'Batch',
        'Tipe Pengiriman',
        'Expedisi',
        'Sopir',
        'No WA Sopir',
        'Truk',
        'Nopol',
        'No Kontainer',
        'Outlet',
        'Alamat Tujuan',
        'Catatan Internal',
        'Catatan Eksternal',
        'Barang dikirimkan',
        'Barang diterima customer',
        'SJ Kembali',
        'No Invoice',
        'Based On',
        'No Timbangan',
        'Po.Customer',
        'SO',
        'Tipe SO',
    ];

    public function collection()
    {
        $array_filter = [];
        $mo = MarketingOrderDeliveryProcessDetail::whereHas('marketingOrderDeliveryProcess', function ($query) {
            $query->where('post_date', '>=', $this->start_date)
                ->where('post_date', '<=', $this->end_date);
        })->get();


        foreach ($mo as $key=>$row) {

            $array_filter[] = [
                'no'                => ($key+1),
                'code'              => $row->marketingOrderDeliveryProcess->code,
                'status'            => $row->marketingOrderDeliveryProcess->statusRaw(),
                'voider'            => $row->marketingOrderDeliveryProcess->voidUser()->exists() ? $row->marketingOrderDeliveryProcess->voidUser->name : '',
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
                'brand' => $row->marketingOrderDeliveryProcess->getBrand(),
                'plant' => $row->place->name??'-',
                'qtysj' => $row->qty,
                // 'qty_konversi' => $row->getQtyM2(),
                'satuan_konversi' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->itemUnit->unit->code,
                'qty' => $row->marketingOrderDeliveryDetail->qty * $row->marketingOrderDeliveryDetail->getQtyM2(),
                'satuan' => 'M2',
                'gudang' => $row->itemStock->warehouse->name,
                'area' => $row->itemStock->area->name,
                'shading' => $row->itemStock->itemShading->code,
                'batch' => $row->itemStock->productionBatch->code,
                'delivery_type' => $row->marketingOrderDeliveryDetail->marketingOrderDelivery->deliveryType(),

                'expedisi' =>$row->marketingOrderDeliveryProcess->account->name,
                'sopir'                => $row->marketingOrderDeliveryProcess->driver_name,
                'no_wa_supir'                => $row->marketingOrderDeliveryProcess->driver_hp,
                'truk'=>$row->marketingOrderDeliveryProcess->vehicle_name,
                'nopol' => $row->marketingOrderDeliveryProcess->vehicle_no,
                'no_kontainer'          => $row->marketingOrderDeliveryProcess->no_container,
                'outlet' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->outlet->name ?? '-',
                'alamat_tujuan'=> $row->marketingOrderDeliveryDetail->marketingOrderDelivery->destination_address,
                'catatan_internal'=>$row->marketingOrderDeliveryDetail->marketingOrderDelivery->note_internal,
                'catatan_eksternal'=>$row->marketingOrderDeliveryDetail->marketingOrderDelivery->note_external,
                'status_item_sent'=>$row->marketingOrderDeliveryProcess->isItemSent() ? date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->post_date)) : '',
                'status_received_by_customer'=>$row->marketingOrderDeliveryProcess->isDelivered() ? date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->receive_date)) : '',
                'status_returned_document' =>
                $row->marketingOrderDeliveryProcess && $row->marketingOrderDeliveryProcess->return_date
                    ? date('d/m/Y', strtotime($row->marketingOrderDeliveryProcess->return_date))
                    : '',
                'list_invoice' =>$row->marketingOrderDeliveryProcess->marketingOrderInvoice->code ?? '',
                'based_on'=>$row->marketingOrderDeliveryDetail->marketingOrderDelivery->code,

                'no_timbangan'=> $row->marketingOrderDeliveryDetail->marketingOrderDelivery->goodScaleDetail->goodScale->code ?? '-',
                'po_customer' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->document_no,

                'so' => $row->marketingOrderDeliveryDetail->marketingOrderDetail->marketingOrder->code,
                'so_type' => $row->marketingOrderDeliveryDetail->marketingOrderDelivery->soType(),

            ];
        }
        return collect($array_filter);
    }

    public function title(): string
    {
        return 'SJ RECAP ACCOUNTING';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	public function headings() : array
	{
		return $this->headings;
	}
}

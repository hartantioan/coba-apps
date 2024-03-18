<?php

namespace App\Exports;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use App\Helpers\CustomHelper;
class ExportPurchaseOrder implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $mode;

    public function __construct(string $start_date, string $end_date, string $mode)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
    }

    private $headings = [
        'No',
        'No. PO',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Tgl.Posting',
        'Kode Supplier',
        'Nama Supplier',
        'Keterangan',
        'Nomor Dokumen',
        'Tipe Pembelian',
        'Tgl.Kirim',
        'Tgl.Terima',
        'Kode Item',
        'Nama Item',
        'Plant',
        'Ket.1',
        'Ket.2',
        'Qty',
        'Satuan',
        'Qty.Konversi',
        'Satuan',
        'Line',
        'Mesin',
        'Divisi',
        'Gudang',
        'Requester',
        'Proyek',
        'Harga',
        'Konversi',
        'Disc1',
        'Disc2',
        'Disc3',
        'Subtotal',
        'Diskon PO',
        'Total',
        'Based On'
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = PurchaseOrderDetail::whereHas('purchaseOrder', function($query) {
                $query->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            })->get();
        }elseif($this->mode == '2'){
            $data = PurchaseOrderDetail::withTrashed()->whereHas('purchaseOrder', function($query) {
                $query->withTrashed()->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
            })->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            $subtotal = $row->subtotal * $row->purchaseOrder->currency_rate;
            $discount = $row->discountHeader() * $row->purchaseOrder->currency_rate;
            $total = $subtotal - $discount;
            if($row->item()->exists()){
                $arr[] = [
                    'no'                => ($key + 1),
                    'code'              => $row->purchaseOrder->code,
                    'status'            => $row->purchaseOrder->statusRaw(),
                    'voider'            => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->voidUser->name : '',
                    'void_date'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_date : '',
                    'void_note'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_note : '',
                    'deleter'           => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleteUser->name : '',
                    'delete_date'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleted_at : '',
                    'delete_note'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->delete_note : '',
                    'post_date'         => date('d/m/Y',strtotime($row->purchaseOrder->post_date)),
                    'supplier_code'     => $row->purchaseOrder->supplier->employee_no,
                    'supplier_name'     => $row->purchaseOrder->supplier->name,
                    'main_note'         => $row->purchaseOrder->note,
                    'document_no'       => $row->purchaseOrder->document_no,
                    'type'              => $row->purchaseOrder->inventoryType(),
                    'delivery_date'     => date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)),
                    'received_date'     => $row->purchaseOrder->received_date ? date('d/m/Y',strtotime($row->purchaseOrder->received_date)) : '',
                    'item_code'         => $row->item->code,
                    'item_name'         => $row->item->name,
                    'plant'             => $row->place()->exists() ? $row->place->code : '',
                    'note'              => $row->note,
                    'note2'             => $row->note2,
                    'qty'               => CustomHelper::formatConditionalQty($row->qty,3,',','.'),
                    'unit'              => $row->itemUnit->unit->code,
                    'qty_stock'         => CustomHelper::formatConditionalQty($row->qty * $row->qty_conversion,3,',','.'),
                    'unit_stock'        => $row->item->uomUnit->code,
                    'line'              => $row->line()->exists() ? $row->line->code : '',
                    'machine'           => $row->machine()->exists() ? $row->machine->name : '',
                    'department'        => $row->department()->exists() ? $row->department->name : '',
                    'warehouse'         => $row->warehouse()->exists() ? $row->warehouse->name : '',
                    'requester'         => $row->requester,
                    'project'           => $row->project()->exists() ? $row->project->name : '',
                    'price'             => $row->price,
                    'conversion'        => number_format($row->purchaseOrder->currency_rate,2,',','.'),
                    'disc1'             => number_format($row->percent_discount_1,2,',','.'),
                    'disc2'             => number_format($row->percent_discount_2,2,',','.'),
                    'disc3'             => number_format($row->discount_3 * $row->purchaseOrder->currency_rate,2,',','.'),
                    'subtotal'          => number_format($subtotal,2,',','.'),
                    'discount'          => number_format($discount,2,',','.'),
                    'total'             => number_format($total,2,',','.'),
                    'based_on'          => $row->getReference(),
                ];
            }else{
                $arr[] = [
                    'no'                => ($key + 1),
                    'code'              => $row->purchaseOrder->code,
                    'status'            => $row->purchaseOrder->statusRaw(),
                    'voider'            => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->voidUser->name : '',
                    'void_date'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_date : '',
                    'void_note'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_note : '',
                    'deleter'           => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleteUser->name : '',
                    'delete_date'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleted_at : '',
                    'delete_note'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->delete_note : '',
                    'post_date'         => date('d/m/Y',strtotime($row->purchaseOrder->post_date)),
                    'supplier_code'     => $row->purchaseOrder->supplier->employee_no,
                    'supplier_name'     => $row->purchaseOrder->supplier->name,
                    'main_note'         => $row->purchaseOrder->note,
                    'document_no'       => $row->purchaseOrder->document_no,
                    'type'              => $row->purchaseOrder->inventoryType(),
                    'delivery_date'     => date('d/m/Y',strtotime($row->purchaseOrder->delivery_date)),
                    'received_date'     => $row->purchaseOrder->received_date ? date('d/m/Y',strtotime($row->purchaseOrder->received_date)) : '',
                    'item_code'         => $row->coa->code,
                    'item_name'         => $row->coa->name,
                    'plant'             => $row->place()->exists() ? $row->place->code : '',
                    'note'              => $row->note,
                    'note2'             => $row->note2,
                    'qty'               => 1,
                    'unit'              => $row->itemUnit()->exists() ? $row->itemUnit->unit->code : ($row->coaUnit()->exists() ? $row->coaUnit->code : '-'),
                    'qty_stock'         => 1,
                    'unit_stock'        => '-',
                    'line'              => $row->line()->exists() ? $row->line->code : '',
                    'machine'           => $row->machine()->exists() ? $row->machine->name : '',
                    'department'        => $row->department()->exists() ? $row->department->name : '',
                    'warehouse'         => $row->warehouse()->exists() ? $row->warehouse->name : '',
                    'requester'         => $row->requester,
                    'project'           => $row->project()->exists() ? $row->project->name : '',
                    'price'             => $row->price,
                    'conversion'        => number_format($row->purchaseOrder->currency_rate,2,',','.'),
                    'disc1'             => number_format($row->percent_discount_1,2,',','.'),
                    'disc2'             => number_format($row->percent_discount_2,2,',','.'),
                    'disc3'             => number_format($row->discount_3 * $row->purchaseOrder->currency_rate,2,',','.'),
                    'subtotal'          => number_format($subtotal,2,',','.'),
                    'discount'          => number_format($discount,2,',','.'),
                    'total'             => number_format($total,2,',','.'),
                    'based_on'          => $row->getReference(),
                ];
            }
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Rekap Purchase Order';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	/**
	 * @return array
	 */
	public function headings() : array
	{
		return $this->headings;
	}
}



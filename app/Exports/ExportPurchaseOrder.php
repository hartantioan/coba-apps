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
use App\Helpers\PrintHelper;
class ExportPurchaseOrder implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $mode, $modedata, $nominal, $warehouses;

    public function __construct(string $start_date, string $end_date, string $mode, string $modedata, string $nominal, array $warehouses)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
        $this->modedata = $modedata ?? '';
        $this->nominal = $nominal ?? '';
        $this->warehouses = $warehouses;
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
        'Doner',
        'Tgl.Done',
        'Ket.Done',
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
        'Ket.3',
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
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));

                    }
            })
            ->where(function($query){
                $query->whereNotNull('coa_id')
                    ->orWhere(function($query){
                        $query->whereNotNull('item_id')
                            ->whereIn('warehouse_id',$this->warehouses);
                    });
            })
            ->get();
        }elseif($this->mode == '2'){
            $data = PurchaseOrderDetail::withTrashed()->whereHas('purchaseOrder', function($query) {
                $query->withTrashed()->where('post_date', '>=',$this->start_date)
                    ->where('post_date', '<=', $this->end_date);
                    if(!$this->modedata){
                        $query->where('user_id',session('bo_id'));

                    }
            })
            ->where(function($query){
                $query->whereNotNull('coa_id')
                    ->orWhere(function($query){
                        $query->whereNotNull('item_id')
                            ->whereIn('warehouse_id',$this->warehouses);
                    });
            })
            ->get();
        }

        $arr = [];
        $no = 0;
        foreach($data as $key => $row){
            $subtotal = $row->subtotal * $row->purchaseOrder->currency_rate;
            $discount = $row->discountHeader() * $row->purchaseOrder->currency_rate;
            $total = $subtotal - $discount;
            if($row->item()->exists()){

                if(!$row->deleted_at ||($row->deleted_at&&$row->purchaseOrder->deleted_at)){
                    $no++;
                    $arr[] = [
                        'no'                => ($no),
                        'code'              => $row->purchaseOrder->code,
                        'status'            => $row->purchaseOrder->statusRaw(),
                        'voider'            => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->voidUser->name : '',
                        'void_date'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_date : '',
                        'void_note'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_note : '',
                        'deleter'           => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleteUser->name : '',
                        'delete_date'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleted_at : '',
                        'delete_note'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->delete_note : '',
                        'doner'             => ($row->purchaseOrder->status == 3 && is_null($row->purchaseOrder->done_id)) ? 'sistem' : (($row->purchaseOrder->status == 3 && !is_null($row->purchaseOrder->done_id)) ? $row->purchaseOrder->doneUser->name : null),
                        'done_date'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_date : '',
                        'done_note'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_note : '',
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
                        'note3'             => $row->purchaseOrder->goodScale->code ?? '-',
                        'qty'               => $row->qty,
                        'unit'              => $row->itemUnit->unit->code,
                        'qty_stock'         => $row->qty * $row->qty_conversion,
                        'unit_stock'        => $row->item->uomUnit->code,
                        'line'              => $row->line()->exists() ? $row->line->code : '',
                        'machine'           => $row->machine()->exists() ? $row->machine->name : '',
                        'department'        => $row->department()->exists() ? $row->department->name : '',
                        'warehouse'         => $row->warehouse()->exists() ? $row->warehouse->name : '',
                        'requester'         => $row->requester,
                        'project'           => $row->project()->exists() ? $row->project->name : '',
                        'price'             => $this->nominal ? $row->price : '-',
                        'conversion'        => $this->nominal ? number_format($row->purchaseOrder->currency_rate,2,',','.') : '',
                        'disc1'             => $this->nominal ? number_format($row->percent_discount_1,2,',','.') : '',
                        'disc2'             => $this->nominal ? number_format($row->percent_discount_2,2,',','.') : '',
                        'disc3'             => $this->nominal ? number_format($row->discount_3 * $row->purchaseOrder->currency_rate,2,',','.') : '',
                        'subtotal'          => $this->nominal ? number_format($subtotal,2,',','.') : '',
                        'discount'          => $this->nominal ? number_format($discount,2,',','.') : '',
                        'total'             => $this->nominal ? number_format($total,2,',','.') : '',
                        'based_on'          => $row->getReference(),
                    ];
                }
            }else{
                if(!$row->deleted_at ||($row->deleted_at&&$row->purchaseOrder->deleted_at)){
                    $no++;
                    $arr[] = [
                        'no'                => ($no),
                        'code'              => $row->purchaseOrder->code,
                        'status'            => $row->purchaseOrder->statusRaw(),
                        'voider'            => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->voidUser->name : '',
                        'void_date'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_date : '',
                        'void_note'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_note : '',
                        'deleter'           => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleteUser->name : '',
                        'delete_date'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleted_at : '',
                        'delete_note'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->delete_note : '',
                        'doner'             => ($row->purchaseOrder->status == 3 && is_null($row->purchaseOrder->done_id)) ? 'sistem' : (($row->purchaseOrder->status == 3 && !is_null($row->purchaseOrder->done_id)) ? $row->purchaseOrder->doneUser->name : null),
                        'done_date'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_date : '',
                        'done_note'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_note : '',
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
                        'note3'             => $row->purchaseOrder->goodScale->code ?? '-',
                        'qty'               => $row->qty,
                        'unit'              => $row->itemUnit()->exists() ? $row->itemUnit->unit->code : ($row->coaUnit()->exists() ? $row->coaUnit->code : '-'),
                        'qty_stock'         => $row->qty * $row->qty_conversion,
                        'unit_stock'        => '-',
                        'line'              => $row->line()->exists() ? $row->line->code : '',
                        'machine'           => $row->machine()->exists() ? $row->machine->name : '',
                        'department'        => $row->department()->exists() ? $row->department->name : '',
                        'warehouse'         => $row->warehouse()->exists() ? $row->warehouse->name : '',
                        'requester'         => $row->requester,
                        'project'           => $row->project()->exists() ? $row->project->name : '',
                        'price'             => $this->nominal ? $row->price : '-',
                        'conversion'        => $this->nominal ? number_format($row->purchaseOrder->currency_rate,2,',','.') : '',
                        'disc1'             => $this->nominal ? number_format($row->percent_discount_1,2,',','.') : '',
                        'disc2'             => $this->nominal ? number_format($row->percent_discount_2,2,',','.') : '',
                        'disc3'             => $this->nominal ? number_format($row->discount_3 * $row->purchaseOrder->currency_rate,2,',','.') : '',
                        'subtotal'          => $this->nominal ? number_format($subtotal,2,',','.') : '',
                        'discount'          => $this->nominal ? number_format($discount,2,',','.') : '',
                        'total'             => $this->nominal ? number_format($total,2,',','.') : '',
                        'based_on'          => $row->getReference(),
                    ];
                }
            }

        }

        activity()
            ->performedOn(new PurchaseOrder())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export purchase Order .');

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



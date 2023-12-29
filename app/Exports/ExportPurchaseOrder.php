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
        'Document Number',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'WareHouse Code',
        'Posting Date',
        'Item Code',
        'Item Description',
        'Quantity',
        'Unit',
        'Note 1',
        'Note 2',
        'Tipe Dokumen'
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
                    'warehouse_code'    => $row->place->code,
                    'post_date'         => date('d/m/y',strtotime($row->purchaseOrder->post_date)),
                    'item_code'         => $row->item->code,
                    'item'              => $row->item->name,
                    'qty'               => $row->qty,
                    'unit'              => $row->item->buyUnit->code,
                    'remarks'           => $row->note,
                    'free_text'         => $row->note2,
                    'type'              => $row->purchaseOrder->inventoryType(),
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
                    'warehouse_code'    => $row->place->code,
                    'post_date'         => date('d/m/y',strtotime($row->purchaseOrder->post_date)),
                    'item_code'         => $row->coa->code,
                    'item'              => $row->coa->name,
                    'qty'               => $row->qty,
                    'unit'              => '',
                    'remarks'           => $row->note,
                    'free_text'         => $row->note2,
                    'type'              => $row->purchaseOrder->inventoryType()
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



<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\PurchaseOrderDetail;

class ExportProcurementOutstandPO implements FromCollection,WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    

    private $headings = [
        'Code',
        'Tanggal',
        'Supplier',
        'Note',
        'Status',
        'User',
        'Grup Item',
        'Item Code',
        'Item Name',
        'Note 1',
        'Note 2',
        'Shipping Type',
        'Satuan',
        'Qty PO',
        'Qty GR',
        'Plant',
        'Warehouse',
        'Sisa'
       
    ];

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings() : array
	{
		return $this->headings;
	}

    public function title(): string
    {
        return 'Outstand PO';
    }

    public function collection()
    {

        $data = PurchaseOrderDetail::whereHas('purchaseOrder',function($query){
            $query->whereIn('status',['2'])->where('inventory_type','1');
        })->whereIn('warehouse_id',[2,3])->whereNull('status')->get();
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->purchaseOrder->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->purchaseOrder->post_date));
            $entry["nama_supp"]=$row->purchaseOrder->supplier->name;
            $entry["note"] = $row->purchaseOrder->note;
            $entry["status"] = $row->purchaseOrder->statusRaw();
            $entry["user_name"] = $row->purchaseOrder->user->name ?? '';
            $entry["group_item"] = $row->item->itemGroup->name;
            $entry["item_code"] = $row->item->code;
            $entry["item_name"] = $row->item->name;
            $entry["note1"] = $row->note;
            $entry["note2"] = $row->note2;
            $entry["shipping_type"] = $row->purchaseOrder->shippingType();
            $entry["satuan"] =$row->itemUnit->unit->code;
            $entry["qty"] = $row->qty;
            $entry["qty_gr"] = $row->qtyGR();
            $entry["plant"] =$row->place->code;
            $entry["warehouse"] =$row->warehouse->name;
            $entry["qty_balance"] = $row->getBalanceReceipt();
            if($row->getBalanceReceipt()> 0){
                $array[] = $entry;
            }
            
            
        }

        return collect($array);
    }
}

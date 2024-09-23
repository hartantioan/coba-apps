<?php

namespace App\Exports;

use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProcurementOutstandPR implements FromCollection,WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    

    private $headings = [
        'Code',
        'User', 
        'Tanggal',
        'Note',
        'Status',
        'Grup Item',
        'Item Code',
        'Item Name',
        'Note 1',
        'Note 2',
        'Satuan',
        'Plant',
        'Gudang',
        'Qty PR',
        'Qty PO',
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
        return 'Outstand PR';
    }

    public function collection()
    {

        $data = PurchaseRequestDetail::whereHas('purchaseRequest',function($query){
            $query->whereIn('status',['2']);
        })->whereIn('warehouse_id',[2,3])->whereNull('status')->get();
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->purchaseRequest->code;
            $entry["user"]=$row->purchaseRequest->user->name;
            $entry["post_date"] = date('d/m/Y',strtotime($row->purchaseRequest->post_date));
            $entry["note"] = $row->purchaseRequest->note;
            $entry["status"] = $row->purchaseRequest->statusRaw();
            $entry["group_item"] = $row->item->itemGroup->name;
            $entry["item_code"] = $row->item->code;
            $entry["item_name"] = $row->item->name;
            $entry["note1"] = $row->note;
            $entry["note2"] = $row->note2;
            $entry["satuan"] =$row->itemUnit->unit->code;
            $entry["plant"] =$row->place->code;
            $entry["warehouse"] =$row->warehouse->name;
            $entry["qty"] = $row->qty;
            $entry["qty_po"] = $row->qtyPO();
            $entry["qty_balance"] = $row->qtyBalance();
            if($row->qtyBalance()> 0){
                $array[] = $entry;
            }
        }

        return collect($array);
    }
}

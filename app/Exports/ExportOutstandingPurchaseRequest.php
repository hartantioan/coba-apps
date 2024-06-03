<?php

namespace App\Exports;
use App\Models\PurchaseRequestDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportOutstandingPurchaseRequest implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $warehouses;

    public function __construct(array $warehouses)
    {
        $this->warehouses = $warehouses;
    }

    public function view(): View
    {
        $data = PurchaseRequestDetail::whereHas('purchaseRequest',function($query){
            $query->whereIn('status',['2']);
        })->whereIn('warehouse_id',$this->warehouses)->whereNull('status')->get();
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
        
        
        return view('admin.exports.outstanding_pr', [
            'data' => $array,
            
        ]);
    }
}

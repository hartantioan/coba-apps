<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\PurchaseOrderDetail;
use App\Helpers\CustomHelper;
class ExportOutstandingPO implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $data = PurchaseOrderDetail::whereHas('purchaseOrder',function($query){
            $query->whereIn('status',['2','3']);
        })->whereNull('status')->get();
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->purchaseOrder->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->purchaseOrder->post_date));
            $entry["nama_supp"]=$row->purchaseOrder->supplier->name;
            $entry["note"] = $row->purchaseOrder->note;
            $entry["status"] = $row->purchaseOrder->statusRaw();
            $entry["item_code"] = $row->item->code;
            $entry["item_name"] = $row->item->name;
            $entry["satuan"] =$row->itemUnit->unit->code;
            $entry["qty"] = CustomHelper::formatConditionalQty($row->qty,3,',','.');
            $entry["qty_gr"] = CustomHelper::formatConditionalQty($row->qtyGR(),3,',','.');
            $entry["qty_balance"] = CustomHelper::formatConditionalQty($row->getBalanceReceipt(),3,',','.');
            if($row->getBalanceReceipt()> 0){
                $array[] = $entry;
            }
            
            
        }
        
        
        return view('admin.exports.outstanding_po', [
            'data' => $array,
            
        ]);
    }
}

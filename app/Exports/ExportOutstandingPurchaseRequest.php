<?php

namespace App\Exports;
use App\Models\PurchaseRequestDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportOutstandingPurchaseRequest implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $data = PurchaseRequestDetail::whereHas('purchaseRequest',function($query){
            $query->whereIn('status',['2','3']);
        })->whereNull('status')->get();
        info($data);
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->purchaseRequest->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->purchaseRequest->post_date));
            $entry["note"] = $row->purchaseRequest->note;
            $entry["status"] = $row->purchaseRequest->statusRaw();
            $entry["item_code"] = $row->item->code;
            $entry["item_name"] = $row->item->name;
            $entry["satuan"] =$row->itemUnit->unit->code;
            $entry["qty"] = number_format($row->qty,3,',','.');
            $entry["qty_po"] = number_format($row->qtyPO(),3,',','.');
            $entry["qty_balance"] = number_format($row->qtyBalance(),3,',','.');
            if($row->qtyBalance()> 0){
                $array[] = $entry;
            }
            
            
        }
        
        
        return view('admin.exports.outstanding_pr', [
            'data' => $array,
            
        ]);
    }
}

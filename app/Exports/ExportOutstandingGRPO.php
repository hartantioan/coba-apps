<?php

namespace App\Exports;

use App\Models\GoodReceiptDetail;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
class ExportOutstandingGRPO implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $data = GoodReceiptDetail::whereHas('goodReceipt',function($query){
            $query->whereIn('status',['2','3']);
        })->get();
        $array=[];
        foreach($data as $row){
            $entry = [];
            $entry["code"]=$row->goodReceipt->code;
            $entry["post_date"] = date('d/m/Y',strtotime($row->goodReceipt->post_date));
            $entry["note"] = $row->goodReceipt->note;
            $entry["status"] = $row->goodReceipt->statusRaw();
            $entry["item_code"] = $row->item->code;
            $entry["item_name"] = $row->item->name;
            $entry["satuan"] =$row->itemUnit->unit->code;
            $entry["qty"] = $row->qty;
            $entry["qty_gr"] = $row->qtyInvoice();
            $entry["qty_balance"] = $row->balanceQtyInvoice();
            if($row->balanceQtyInvoice()> 0){
                $array[] = $entry;
            }
            
            
        }
        
        
        return view('admin.exports.outstanding_po', [
            'data' => $array,
            
        ]);
    }
}

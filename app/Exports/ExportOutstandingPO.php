<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\PurchaseOrderDetail;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportOutstandingPO implements FromView,ShouldAutoSize
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
        $data = PurchaseOrderDetail::whereHas('purchaseOrder',function($query){
            $query->whereIn('status',['2'])->where('inventory_type','1');
        })->whereIn('warehouse_id',$this->warehouses)->whereNull('status')->get();
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
        activity()
            ->performedOn(new PurchaseOrderDetail())
            ->causedBy(session('bo_id'))
            ->withProperties($data)
            ->log('Export outstanding purchase order.');
        
        return view('admin.exports.outstanding_po', [
            'data' => $array,
            
        ]);
    }
}

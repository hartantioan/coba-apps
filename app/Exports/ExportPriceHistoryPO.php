<?php

namespace App\Exports;

use App\Models\PurchaseOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class ExportPriceHistoryPO implements FromView
{
    protected $search,$item,$inventory_type;
    public function __construct(string $search, string $item,string $inventory_type)
    {
        $this->search = $search ? $search : '';
		$this->item = $item ? $item : '';
        $this->inventory_type = $inventory_type ? $inventory_type : '';

    }
    public function view(): View
    {
        $query_data= PurchaseOrderDetail::where(function($query) {
            if($this->search) {
                $query->whereHas('purchaseOrder',function($query){
                    $query->where('code', 'like', "%$this->search%");
                });
            }
            if($this->item){
                $query->where('item_id',$this->item);
            }
            if($this->inventory_type !== 'all'){
                $query->whereHas('purchaseOrder',function($query){
                    $query->where('inventory_type', $this->inventory_type);
                });
            }
        })
        ->get();
        
        $array_filter = [];
        foreach($query_data as $val) {
            
            $disc1 = $val->price * ($val->percent_discount_1 / 100);
            $disc2 = $val->price * ($val->percent_discount_2 / 100);
            $total_final= $val->price-$disc1-$disc2-$val->discount_3;
            $isi='';
            if($val->item()->exists()){
                $isi = $val->item->code.' - '.$val->item->name;
            }else{
                $isi = $val->coa->code.' - '.$val->coa->name;
            }
            $array_filter[]=[
                'supplier'=> $val->purchaseOrder->supplier->name,
                'code'=> $val->purchaseOrder->code,
                'item'=> $isi,
                'date'=>date('d/m/Y',strtotime($val->purchaseOrder->post_date)),
                'price'=> number_format($val->price,2,',','.'),
                'disc1'=> number_format($disc1,2,',','.'),
                'disc2'=> number_format($disc2,2,',','.'),
                'disc3'=> number_format($val->discount_3,2,',','.'),
                'totalfinal'=> number_format($total_final,2,',','.'),
            ];
            
        }

        activity()
            ->performedOn(new PurchaseOrderDetail())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export price history po.');
      
        return view('admin.exports.price_history_po', [
            'data' => $array_filter,
        ]);
    }


}

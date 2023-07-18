<?php

namespace App\Exports;

use App\Models\ItemStock;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportStockInQty implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(string $plant, string $item,string $warehouse)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';

    }
    public function view(): View
    {
        
        $query_data = ItemStock::where(function($query) {
            if($this->item){
                $query->where('item_id',$this->item);
            }
            if($this->plant !== 'all'){
        
       
                $query->whereHas('place',function($query){
                    $query->where('id',$this->plant);
                });
            }
            if($this->warehouse !== 'all'){
                $query->whereHas('warehouse',function($query){
                    $query->where('id',$this->warehouse);
                });
            }
        })->get();
        
        $array_filter = [];
        $array_filter=[];
        foreach($query_data as $row){
            $data_tempura = [
                'item' => $row->item->code.'-'.$row->item->name,
                'final'=>number_format($row->qty,3,',','.'),
                'satuan'=>$row->item->uomUnit->code
            ];
            $array_filter[]=$data_tempura;
        }
      
        return view('admin.exports.stock_in_qty', [
            'data' => $array_filter,
        ]);
    }
}

<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\ItemStock;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportMinimumStock implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct(string $item_id)
    {
        $this->item_id = $item_id ? $item_id : '';

    }

    public function view(): View
    {
        
        $query_data = Item::where(function($querys){
            if($this->item_id ){
        
       
                $querys->where('id',$this->item_id);
            }
    
        })
        ->get();
        
        $array_filter = [];
        $array_filter=[];
        foreach($query_data as $row){
            if($row->itemStock()->exists()){
                
                $qty = $row->getStockAll();
            }
            else{
                $qty = 0;
            }
            $data_tempura = [
                'item' => $row->code.'-'.$row->name,
                'minimum'=>number_format($row->min_stock),
                'needed'=>number_format($row->min_stock-$qty),
                'maximum'=>number_format($row->max_stock),
                'final'=>number_format($qty,3,',','.'),
                'satuan'=>$row->uomUnit->code
            ];
            if($qty < $row->min_stock){
                $array_filter[]=$data_tempura;
            }
        }
      
        return view('admin.exports.minimum_stock', [
            'data' => $array_filter,
        ]);
    }
}

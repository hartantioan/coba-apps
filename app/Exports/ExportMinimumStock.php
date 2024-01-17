<?php

namespace App\Exports;

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
    public function __construct(string $plant,string $warehouse)
    {
        $this->plant = $plant ? $plant : '';
		
        $this->warehouse = $warehouse ? $warehouse : '';

    }

    public function view(): View
    {
        
        $query_data = ItemStock::where(function($query) {
            $query->whereHas('item',function($query){
                $query->whereRaw('items.min_stock > item_stocks.qty');;
            });
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
                'minimum'=>number_format($row->item->min_stock),
                'needed'=>number_format($row->item->min_stock-$row->qty),
                'final'=>number_format($row->qty,3,',','.'),
                'satuan'=>$row->item->uomUnit->code
            ];
            $array_filter[]=$data_tempura;
        }
      
        return view('admin.exports.minimum_stock', [
            'data' => $array_filter,
        ]);
    }
}

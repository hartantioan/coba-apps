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

    protected $item_id,$warehouse,$plant,$item_group_id;

    public function __construct(string $item_id,string $warehouse,string $plant,string $item_group_id)
    {
        $this->item_id = $item_id ? $item_id : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->plant = $plant ? $plant : '';
        $this->item_group_id = $item_group_id ? $item_group_id : '';

    }

    public function view(): View
    {
        info($this->warehouse);
        $query_data = ItemStock::where(function($querys){
            if($this->item_id != 'null'){

                $querys->where('id',$this->item_id);
            }
            if($this->warehouse!='all'){
                $querys->where('warehouse_id',$this->warehouse);
            }
            if($this->plant !='all'){
                $querys->where('place_id',$this->plant);
            }
            
            if($this->item_group_id){
                $groupIds = explode(',', $this->item_group_id);
                $querys->whereHas('item', function ($query) use ($groupIds) {
                    $query->whereIn('item_group_id', $groupIds);
                });
            }
    
        })
        ->get();
        info($query_data);
        $array_filter = [];
       
        foreach($query_data as $row){
            $data_tempura = [
                'plant' => $row->place->code,
                'gudang' => $row->warehouse->code,
                'kode' => $row->item->code,
                'item' => $row->item->name,
                'minimum'=>number_format($row->item->min_stock),
                'needed'=>number_format($row->item->min_stock-$row->qty),
                'maximum'=>number_format($row->item->max_stock),
                'final'=>number_format($row->qty,3,',','.'),
                'satuan'=>$row->item->uomUnit->code,
 
            ];
            if($row->qty < $row->item->min_stock){
                $array_filter[]=$data_tempura;
            }
        }
      
        return view('admin.exports.minimum_stock', [
            'data' => $array_filter,
        ]);
    }
}

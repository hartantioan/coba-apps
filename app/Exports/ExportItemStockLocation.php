<?php

namespace App\Exports;

use App\Models\ItemStock;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\View\View;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportItemStockLocation implements FromView,ShouldAutoSize
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
            if($this->plant){
                $query->where('place_id',$this->plant);
            }
            if($this->warehouse){
                $query->where('warehouse_id',$this->warehouse);
            }
        })->get();
        
        $array_filter = [];
        foreach($query_data as $row){
            $data_tempura = [
                'item' => $row->item->code.'-'.$row->item->name,
                'stock'=>CustomHelper::formatConditionalQty($row->qty),
                'plant'=>$row->place->code,
                'gudang'=>$row->warehouse->code . ' - ' . $row->warehouse->name,
                'area' => $row->area->name??'-',
                'shading' => $row->itemShading->code??'-',
                'satuan'=>$row->item->uomUnit->code,
                'location'=>$row->location ?? '',
            ];
            $array_filter[]=$data_tempura;
        }
        activity()
            ->performedOn(new ItemStock())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export lokasi item stock data.');
      
        return view('admin.exports.item_stock_location', [
            'data' => $array_filter,
        ]);
    }
}

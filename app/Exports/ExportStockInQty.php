<?php

namespace App\Exports;

use App\Models\Item;
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
        $query_data = ItemStock::join('items', 'item_stocks.item_id', '=', 'items.id')
        ->where(function ($query) {
            if ($this->item) {
                $query->where('item_stocks.item_id', $this->item);
            }
            if ($this->warehouse != 'all') {
                $query->where('item_stocks.warehouse_id', $this->warehouse);
            }
            if ($this->plant != 'all') {
                $query->where('item_stocks.place_id', $this->plant);
            }
        })
        ->orderBy('items.code')->get();

        if ($query_data->isEmpty()) {
            $query_data = ItemStock::where(function($query){
                // Your additional conditions for the second query, if needed
            })->get();
        }
        
        $array_filter = [];
       
        foreach($query_data as $row){
            
            $data_tempura = [
                'plant' => $row->place->code,
                'gudang' => $row->warehouse->name ?? '',
                'kode' => $row->item->code,
                'item' => $row->item->name,
                'final'=>number_format($row->qty,3,',','.'),
                'satuan'=>$row->item->uomUnit->code,
                'perlu' =>1,
            ];
        
            $array_filter[]=$data_tempura;
            
            
        }
      
        return view('admin.exports.stock_in_qty', [
            'data' => $array_filter,
        ]);
    }
}

<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\ItemStock;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportStockInQty implements FromView,ShouldAutoSize
{
    protected $plant, $item, $warehouse,$group;
    public function __construct(string $plant, string $item,string $warehouse,string $group)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->group = $group ? $group : '';

    }
    public function view(): View
    {
        $query_data = ItemStock::join('items', 'item_stocks.item_id', '=', 'items.id')
        ->where(function ($query) {
            $query->whereIn('items.status', ['1','2']);
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
        ->when($this->group, function ($query) {
            $groupIds = explode(',', $this->group);
            $query->whereHas('item', function ($itemQuery) use($groupIds) {
                $itemQuery->whereIn('item_group_id', $groupIds);
            });
        })
        ->orderBy('items.code')->get();

        if ($query_data->isEmpty()) {
            $query_data = [];
        }
        
        $array_filter = [];
       
        foreach($query_data as $row){
            if($row->qty > 0){
                $data_tempura = [
                    'plant' => $row->place->code,
                    'gudang' => $row->warehouse->name ?? '',
                    'kode' => $row->item->code,
                    'item' => $row->item->name,
                    'area'         => $row->area->code ?? '-',
                    'production_batch' => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                    'shading'      => $row->itemShading->code ?? '-',
                    'final'=> $row->qty,
                    'satuan'=>$row->item->uomUnit->code,
                    'perlu' =>1,
                ];
            
                $array_filter[]=$data_tempura;
            }
            
        }
        activity()
            ->performedOn(new ItemStock())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export stock in qty data  .');
        return view('admin.exports.stock_in_qty', [
            'data' => $array_filter,
        ]);
    }
}

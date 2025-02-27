<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\ItemCogs;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class ExportDeadStock implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $plant,$warehouse,$group,$date;
    public function __construct(string $plant, string $warehouse,string $date,string $group)
    {
        $this->plant = $plant ? $plant : '';
		$this->warehouse = $warehouse ? $warehouse : '';
        $this->group = $group ? $group : '';
        $this->date = $date ? $date : '';
    }
    public function view(): View
    {
        $item = Item::where(function($query){
            if($this->group){
                $query->whereIn('item_group_id', explode(',',$this->group));
            }
            if($this->warehouse != 'all'){
                $query->whereHas('itemGroup',function($query){
                    $query->whereHas('itemGroupWarehouse',function($query){
                        $query->where('warehouse_id',$this->warehouse);
                    });
                });
            }
        })->pluck('id');
        $arr = [];
        foreach($item as $row){
            $data = DB::table('item_cogs')->select('item_cogs.id AS id','item_cogs.qty_final AS qty_final','item_cogs.total_final AS total_final','places.code AS place_code','warehouses.name AS warehouse_name','items.code AS item_code','items.name AS item_name','units.code AS uom_unit','areas.code AS area_code','item_shadings.code AS item_shading','item_cogs.date AS date','production_batches.code AS batch_code')
                ->where('item_cogs.date','<=',$this->date)->where('item_cogs.item_id',$row)->where(function($query){
                    if($this->plant != 'all'){
                        $query->where('item_cogs.place_id',$this->plant);
                    }
                })
                ->whereNull('item_cogs.deleted_at')
                ->leftJoin('places', 'places.id', '=', 'item_cogs.place_id')
                ->leftJoin('warehouses', 'warehouses.id', '=', 'item_cogs.warehouse_id')
                ->leftJoin('items', 'items.id', '=', 'item_cogs.item_id')
                ->leftJoin('units', 'units.id', '=', 'items.uom_unit')
                ->leftJoin('areas', 'areas.id', '=', 'item_cogs.area_id')
                ->leftJoin('item_shadings', 'item_shadings.id', '=', 'item_cogs.item_shading_id')
                ->leftJoin('production_batches', 'production_batches.id', '=', 'item_cogs.production_batch_id')
                ->orderByDesc('item_cogs.date')->orderByDesc('item_cogs.id')->first();
            if($data){
                if($data->qty_final > 0){
                    $date = Carbon::parse($data->date);
                    $dateDifference = $date->diffInDays($this->date);
                    $arr[]=[
                        'plant'=>$data->place_code,
                        'gudang'=>$data->warehouse_name,
                        'kode'=>$data->item_code,
                        'item'=>$data->item_name,
                        'satuan' => $data->uom_unit,
                        'area'         => $data->area_code,
                        'production_batch' => $data->batch_code,
                        'shading'      => $data->item_shading,
                        'keterangan'=>'',
                        'date'=>$data->date,
                        'lamahari'=>$dateDifference,
                    ];
                }
            }
        }

        activity()
            ->performedOn(new ItemCogs())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Dead stock.');

        return view('admin.exports.dead_stock', [
            'data' => $arr,
        ]);
    }
}

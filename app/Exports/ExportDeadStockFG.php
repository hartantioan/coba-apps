<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\ItemShading;
use App\Models\ProductionBatch;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportDeadStockFG implements FromView,ShouldAutoSize
{
    protected $plant,$item_id,$date;
    public function __construct(string $plant, string $item_id,string $date)
    {
        $this->plant = $plant ? $plant : '';
        $this->item_id = $item_id ? $item_id : '';
        $this->date = $date ? $date : '';
    }
    public function view(): View
    {
        $item = Item::where(function($query){
            $query->where('is_sales_item','1');
            if($this->item_id != 'null'){
                $query->where('id', $this->item_id);
            }
        })->pluck('id');
        $arr = [];
        $arr_batch_id = [];
        foreach($item as $row){
            $shading = ItemShading::where('item_id',$row)->get();
            foreach($shading as $row_shading){
                $mbeng = ProductionBatch::where('item_shading_id',$row_shading->id)->where('post_date','<=',$this->date)->pluck('id')->toArray();
                $arr_batch_id = array_merge($arr_batch_id, $mbeng);
            }

        }

        foreach($arr_batch_id as $row_batch_id){
            $data = ItemCogs::where('production_batch_id',$row_batch_id)->where(function($query){
                if($this->plant != 'all'){
                    $query->whereHas('place',function($query) {
                        $query->where('id',$this->plant);
                    });
                }
            })->orderByDesc('date')->orderByDesc('id')->first();
            if($data){
                $infoFg = $data->infoFg();
                $qty = $infoFg['qty'];
                if( $qty > 0){
                    $date = Carbon::parse($data->date);
                    $dateDifference = $date->diffInDays($this->date);
                    $arr[]=[
                        'plant'=>$data->place->code,
                        'gudang'=>$data->warehouse->name,
                        'kode'=>$data->item->code,
                        'item'=>$data->item->name,
                        'satuan' => $data->item->uomUnit->code,
                        'area'         => $data->area->code ?? '-',
                        'production_batch' => $data->productionBatch()->exists() ? $data->productionBatch->code : '-',
                        'shading'      => $data->itemShading->code ?? '-',
                        'keterangan'=>$data->lookable->code.'-'.$data->lookable->name,
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

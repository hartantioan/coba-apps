<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\ItemCogs;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportDeadStockProduction implements FromView,ShouldAutoSize
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
            $query->whereHas('productionBatch');
            if($this->item_id){
                $query->where('id', explode(',',$this->item_id));
            }
        })->pluck('id');
        $arr = [];
        foreach($item as $row){
            $data = ItemCogs::where('date','<=',$this->date)->where('item_id',$row)->where(function($query){
                if($this->plant != 'all'){
                    $query->whereHas('place',function($query){
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
                        'shading'      => $data->shading->code ?? '-',
                        'keterangan'=>$data->lookable->code.'-'.$data->lookable->name,
                        'date'=>date('d/m/Y',strtotime($data->date)),
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

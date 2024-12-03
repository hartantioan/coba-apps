<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\ItemCogs;
use Carbon\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

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
                $query->whereIn('item_group_id', $this->group);
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
                if($this->warehouse != 'all'){
                    $query->whereHas('warehouse',function($query){
                        $query->where('id',$this->warehouse);
                    });
                }
            })->orderByDesc('date')->orderByDesc('id')->first();
            if($data){
                if($data->qty_final > 0){
                    $arr[] = $data;
                }
            }
        }
        $array_filter = [];
        foreach($arr as $row){
           
            $date = Carbon::parse($row->date);
            $dateDifference = $date->diffInDays($this->date);
               
            
                $array_filter[]=[
                    'plant'=>$row->place->code,
                    'gudang'=>$row->warehouse->name,
                    'kode'=>$row->item->code,
                    'item'=>$row->item->name,
                    'satuan' => $row->item->uomUnit->code,
                    'area'         => $row->area->code ?? '-',
                    'production_batch' => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                    'shading'      => $row->shading->code ?? '-',
                    'keterangan'=>$row->lookable->code.'-'.$row->lookable->name,
                    'date'=>date('d/m/Y',strtotime($row->date)),
                    'lamahari'=>$dateDifference,
                ];
                
                      
        }

        activity()
            ->performedOn(new ItemCogs())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Dead stock.');
      
        return view('admin.exports.dead_stock', [
            'data' => $array_filter,
        ]);
    }
}

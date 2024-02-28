<?php

namespace App\Exports;

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
        info($this->date);
        $query_data = ItemCogs::whereIn('id', function ($query) {            
            $query->selectRaw('MAX(id)')
                ->from('item_cogs')
                ->where('date', '<=', $this->date)
                ->groupBy('item_id');
        })
        ->where(function($query){
            $query->whereHas('item',function($query){
                $query->where('status',1);
            });
            if($this->plant != 'all'){
                $query->where('place_id',$this->plant);
            }
            if($this->warehouse != 'all'){
                $query->where('warehouse_id',$this->warehouse);
            }
            if($this->group){
                $groupIds = explode(',', $this->group);
                $query->whereHas('item',function($query) use($groupIds){
                    $query->whereIn('item_group_id', $groupIds);
                });
            }
        })
        ->get();
        info($query_data);
        $array_filter = [];
        foreach($query_data as $row){
           
            $date = Carbon::parse($row->date);
            $dateDifference = $date->diffInDays($this->date);
               
            
                $array_filter[]=[
                    'plant'=>$row->place->code,
                    'gudang'=>$row->warehouse->name,
                    'kode'=>$row->item->code,
                    'item'=>$row->item->name,
                    'keterangan'=>$row->lookable->code.'-'.$row->lookable->name,
                    'date'=>date('d/m/Y',strtotime($row->date)),
                    'lamahari'=>$dateDifference,
                ];
                
                      
        }
      
        return view('admin.exports.dead_stock', [
            'data' => $array_filter,
        ]);
    }
}

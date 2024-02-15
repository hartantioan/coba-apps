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
    protected $plant,$warehouse,$hari,$date;
    public function __construct(string $plant, string $warehouse,string $hari,string $date)
    {
        $this->plant = $plant ? $plant : '';
		$this->warehouse = $warehouse ? $warehouse : '';
        $this->hari = $hari ? $hari : '';
        $this->date = $date ? $date : '';
    }
    public function view(): View
    {
        $query_data = ItemCogs::whereIn('id', function ($query){
            $query->selectRaw('MAX(id)')
                ->from('item_cogs')
                ->where('date', '<=', $this->date)
                ->groupBy('item_id');
            
            if($this->plant != 'all'){
                $query->where('plant_id',$this->plant);
            }
            if($this->warehouse != 'all'){
                $query->where('warehouse_id',$this->warehouse);
            }
        })
        ->get();
        $array_filter = [];
        foreach($query_data as $row){
           
            $date = Carbon::parse($row->date);
            $dateDifference = $date->diffInDays($this->date);
               
                if ($dateDifference >= intval($this->hari)) {
                    $array_filter[]=[
                        'plant'=>$row->place->code,
                        'gudang'=>$row->warehouse->code,
                        'kode'=>$row->item->code,
                        'item'=>$row->item->name,
                        'keterangan'=>$row->lookable->code.'-'.$row->lookable->name,
                        'date'=>date('d/m/Y',strtotime($row->date)),
                        'lamahari'=>$dateDifference,
                    ];
                }
                      
        }
      
        return view('admin.exports.dead_stock', [
            'data' => $array_filter,
        ]);
    }
}

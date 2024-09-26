<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use App\Models\ItemCogs;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;

class ExportProductionBatchStock implements FromView,ShouldAutoSize
{
    protected $plant, $item, $warehouse, $start_date, $finish_date,$type,$group;

    public function __construct(string $plant, string $item,string $warehouse, string $finish_date)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    
    }

    public function view(): View
    {
        DB::statement("SET SQL_MODE=''");
   
        $perlu = 0 ;
           
        $query_data = ItemCogs::whereRaw("id IN (SELECT MAX(id) FROM item_cogs WHERE deleted_at IS NULL AND date <= '".$this->finish_date."' GROUP BY item_id, production_batch_id, item_shading_id, area_id)")
        ->where(function($query) {
            $query->whereHas('item',function($query){
                $query->whereIn('status',['1','2']);
            });
            if($this->finish_date) {
                $query->whereDate('date','<=', $this->finish_date);
            }
            if($this->item) {
                $query->whereHas('item',function($query) {
                    $query->where('id',$this->item);
                });
            }
            if($this->plant != 'all'){
                $query->whereHas('place',function($query) {
                    $query->where('id',$this->plant);
                });
            }
            if($this->warehouse != 'all'){
                $query->whereHas('warehouse',function($query) {
                    $query->where('id',$this->warehouse);
                });
            }
        })
        ->whereHas('productionBatch')
        ->orderBy('date', 'desc')
        ->get();      
        
        $cum_val = 0 ;
        $array_last_item = [];
        $array_first_item = [];
        $all_total = 0;

        foreach($query_data as $row){
            $arr = $row->infoFg();
            if(round($arr['qty'],3) > 0){
                $priceNow = $arr['total'] / $arr['qty'];
            
                $all_total += round($arr['total'],2);
                
                $data_tempura = [
                    'item_id'      => $row->item->id,
                    'perlu'        => 0,
                    'plant' => $row->place->code,
                    'warehouse' => $row->warehouse->name,
                    'item' => $row->item->name,
                    'satuan' => $row->item->uomUnit->code,
                    'kode' => $row->item->code,
                    'area' => $row->area->name ?? '-',
                    'shading' => $row->itemShading->code ?? '-',
                    'production_batch' => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                    'final'=>number_format($priceNow,2,',','.'),
                    'total'=>$perlu == 0 ? '-' : round($cum_val,3),
                    'qty' => $perlu == 0 ? '-' : round($arr['qty'],3),
                    'date' =>  date('d/m/Y',strtotime($row->date)),
                    'document' => $row->lookable->code,
                    'cum_qty' => round($arr['qty'],3),
                    'cum_val' => round($arr['total'],2),
                ];

                $array_filter[]=$data_tempura;
            }
        }

        activity()
            ->performedOn(new ItemCogs())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export stock in rupiah data  .');

        return view('admin.exports.production_batch_stock', [
            'data'          => $array_filter,
            'latest'        => $array_last_item,
            'first'         => $array_first_item,
            'perlu'         => $perlu,
        ]);
    }
}

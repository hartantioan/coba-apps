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
        ->where('qty_final','>',0)
        ->whereHas('productionBatch')
        ->orderBy('date', 'desc')
        ->get();
        
      
        
        $previousId = null; // Initialize the previous ID variable
        $cum_qty = 0;
        $cum_val = 0 ;
        $array_filter=[];
        $uom_unit = null;
        $previousId = null;
        $array_last_item = [];
        $array_first_item = [];
        $all_total = 0;
        foreach($query_data as $row){
            $arr = $row->infoFg();

            $priceNow = $arr['qty'] > 0 ? $arr['total'] / $arr['qty'] : 0;
        
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
                'total'=>$perlu == 0 ? '-' : number_format($cum_val,2,',','.'),
                'qty' => $perlu == 0 ? '-' : CustomHelper::formatConditionalQty($arr['qty']),
                'date' =>  date('d/m/Y',strtotime($row->date)),
                'document' => $row->lookable->code,
                'cum_qty' => CustomHelper::formatConditionalQty($arr['qty']),
                'cum_val' => number_format($arr['total'],2,',','.'),
            ];
            $array_filter[]=$data_tempura;
            if ($row->item_id !== $previousId) {
              
                $query_first =
                ItemCogs::where(function($query) use ( $row) {
                    $query->where('item_id',$row->item_id)
                    ->where('date', '<', $row->date);
                    
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
                })
                ->orderBy('date', 'desc') // Order by 'date' column in descending order
                ->orderBy('id','desc')
                ->first();

                if($query_first){
                    $arrFirst = $query_first->infoFg();
                    $array_last_item[] = [
                        'perlu'        => 1,
                        'item_id'      => $row->item->id,
                        'id'           => $query_first->id ?? null, 
                        'date'         => $query_first ? date('d/m/Y', strtotime($query_first->date)) : null,
                        'last_nominal' => $query_first ? number_format($arrFirst['total'], 2, ',', '.') : 0,
                        'item'         => $row->item->name,
                        'satuan'       => $row->item->uomUnit->code,
                        'area'         => $row->area->name ?? '-',
                        'production_batch' => '-',
                        'shading' => $row->shading->code ?? '-',
                        'kode'         => $row->item->code,
                        'last_qty'     => $query_first ? CustomHelper::formatConditionalQty($arrFirst['qty']) : 0,
                    ];
                }
            }
            $previousId = $row->item_id;
            
            if($uom_unit ===null){
                $uom_unit = $row->item->uomUnit->code;
            }
            
            
        }
      
        
        $combinedArray = [];

        // Merge $array_filter into $combinedArray
        foreach ($array_filter as $item) {
            $combinedArray[] = $item;
        }

        // Merge $array_last_item into $combinedArray
        foreach ($array_last_item as $item) {
            $combinedArray[] = $item;
        }

        // Merge $array_first_item into $combinedArray
        foreach ($array_first_item as $item) {
            $combinedArray[] = $item;
        }
        usort($combinedArray, function ($a, $b) {
            // First, sort by 'kode' in ascending order
            $kodeComparison = strcmp($a['kode'], $b['kode']);
            
            if ($kodeComparison !== 0) {
                return $kodeComparison;
            }
        
            // If 'kode' is the same, prioritize 'perlu' in descending order
            return $b['perlu'] - $a['perlu'];
        });
  
        $combinedArray=$array_filter;
     

        activity()
            ->performedOn(new ItemCogs())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export stock in rupiah data  .');

        return view('admin.exports.production_batch_stock', [
            'data'          => $combinedArray,
            'latest'        => $array_last_item,
            'first'         => $array_first_item,
            'perlu'         =>  $perlu,
        ]);
    }
}

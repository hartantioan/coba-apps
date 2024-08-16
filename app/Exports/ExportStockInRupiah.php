<?php

namespace App\Exports;
use Illuminate\Support\Facades\DB;
use App\Models\ItemCogs;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportStockInRupiah implements FromView,ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $plant, $item, $warehouse, $start_date, $finish_date,$type,$group;

    public function __construct(string $plant, string $item,string $warehouse, string $start_date, string $finish_date , string $type , string $group)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->type = $type ? $type : '';
        $this->group = $group ? $group : '';
    }

    public function view(): View
    {
        DB::statement("SET SQL_MODE=''");
        if($this->type == 'final'){
            $perlu = 0 ;
           
            $query_data = ItemCogs::whereRaw("id IN (SELECT MAX(id) FROM item_cogs WHERE deleted_at IS NULL AND date <= '".$this->finish_date."' GROUP BY item_id)")
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
    
                if($this->group){
                    $query->whereHas('item',function($query) {
                        $query->whereIn('item_group_id', explode(',',$this->group));
                    });
                }
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        }else{
            $perlu = 1;
            $query_data = ItemCogs::where(function($query) {
                $query->whereHas('item',function($query){
                    $query->whereIn('status',['1','2']);
                });
                if($this->start_date && $this->finish_date) {
                    $query->whereDate('date', '>=', $this->start_date)
                        ->whereDate('date', '<=', $this->finish_date);
                } else if($this->start_date) {
                    $query->whereDate('date','>=', $this->start_date);
                } else if($this->finish_date) {
                    $query->whereDate('date','<=', $this->finish_date);
                }
                if($this->item) {
                    $query->whereHas('item',function($query){
                        $query->where('id',$this->item);
                    });
                }
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
                if($this->group){
                    $groupIds = explode(',', $this->group);
    
                    $query->whereHas('item',function($query) use($groupIds){
                        $query->whereIn('item_group_id', $groupIds);
                    });
                }
            })
            ->orderBy('item_id')
            ->orderBy('date')
            ->orderBy('id')
            ->get();
        }
      
        
        $previousId = null; // Initialize the previous ID variable
        $cum_qty = 0;
        $cum_val = 0 ;
        $array_filter=[];
        $uom_unit = null;
        $previousId = null;
        $array_last_item = [];
        $array_first_item = [];
        foreach($query_data as $row){
           
            if($row->type=='IN'){
                $priceNow = $row->price_in;
                $cum_qty=$row->qty_in;
                $cum_val=round($row->total_in,2);
            }else{
                $priceNow = $row->price_out;
                $cum_qty=$row->qty_out * -1;
                $cum_val=round($row->total_out,2) * -1;
            }
            
            $data_tempura = [
                'item_id'      => $row->item->id,
                'perlu'        => 0,
                'plant' => $row->place->code,
                'warehouse' => $row->warehouse->name,
                'item' => $row->item->name,
                'satuan' => $row->item->uomUnit->code,
                'kode' => $row->item->code,
                'area' => $row->area->code ?? '-',
                'shading' => $row->shading->code ?? '-',
                'production_batch' => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                'final'=>number_format($priceNow,2,',','.'),
                'total'=>$perlu == 0 ? '-' : number_format($cum_val,2,',','.'),
                'qty' => $perlu == 0 ? '-' : $cum_qty,
                'date' =>  date('d/m/Y',strtotime($row->date)),
                'document' => $row->lookable->code,
                'cum_qty' => $row->qty_final,
                'cum_val' => number_format($row->total_final,2,',','.'),
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

                $array_last_item[] = [
                    'perlu'        => 1,
                    'item_id'      => $row->item->id,
                    'id'           => $query_first->id ?? null,
                    'date'         => $query_first ? date('d/m/Y', strtotime($query_first->date)) : null,
                    'area'         => $row->area->code ?? '-',
                    'production_batch' => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                    'shading'      => $row->shading->code ?? '-',
                    'last_nominal' => $query_first ? number_format($query_first->total_final, 2, ',', '.') : 0,
                    'item'         => $row->item->name,
                    'satuan'       => $row->item->uomUnit->code,
                    'kode'         => $row->item->code,
                    'last_qty'     => $query_first ? $query_first->qty_final : 0,
                ];


            }
            $previousId = $row->item_id;
            
            if($uom_unit ===null){
                $uom_unit = $row->item->uomUnit->code;
            }
            
            
        }
        if( $this->type != 'final'){
            if(!$this->item){
                $query_no = ItemCogs::whereIn('id', function ($query) {            
                    $query->selectRaw('MAX(id)')
                        ->from('item_cogs')
                        ->where('date', '<=', $this->finish_date)
                        ->groupBy('item_id');
                })
                ->where(function($query) use ($array_last_item) {
                    $query->whereHas('item',function($query) {
                        $query->whereIn('status',['1','2']);
                    });
                    if($this->finish_date) {
                        $query->whereDate('date','<=', $this->finish_date);
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
        
                    if($this->group){
                       
                        $query->whereHas('item',function($query) {
                            $query->whereIn('item_group_id', explode(',',$this->group));
                        });
                    }
                    $array_last_item = collect($array_last_item);
                    $excludeIds = $array_last_item->pluck('item_id')->filter()->toArray();
                 
                    if (!empty($excludeIds)) {
                        
                        $query->whereNotIn('item_id', $excludeIds);
                    }
                })
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();
            }else{
                $query_no = [];
                $first = ItemCogs::where(function($query) use ($array_last_item) {
                    $query->whereHas('item',function($query) {
                        $query->whereIn('status',['1','2'])
                        ->where('id',$this->item);
                    });
                    if($this->finish_date) {
                        $query->whereDate('date','<=', $this->finish_date);
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
        
                    if($this->group){
                       
                        $query->whereHas('item',function($query) {
                            $query->whereIn('item_group_id', explode(',',$this->group));
                        });
                    }
                    $array_last_item = collect($array_last_item);
                    $excludeIds = $array_last_item->pluck('item_id')->filter()->toArray();
                 
                    if (!empty($excludeIds)) {
                        
                        $query->whereNotIn('item_id', $excludeIds);
                    }
                })
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();
                if($first){
                    $query_no[]=$first;
                }
            }
            
            if(count($query_no) > 0 ){
                foreach($query_no as $row_tidak_ada){
                    info($row_tidak_ada);
                    if($row_tidak_ada->qty_final > 0){
                        $array_first_item[] = [
                            'perlu'        => 1,
                            'item_id'      => $row_tidak_ada->item->id,
                            'id'           => $row_tidak_ada->id, 
                            'date'         => $row_tidak_ada ? date('d/m/Y', strtotime($row_tidak_ada->date)) : null,
                            'last_nominal' => $row_tidak_ada ? number_format($row_tidak_ada->total_final, 2, ',', '.') : 0,
                            'item'         => $row_tidak_ada->item->name,
                            'area'         => $row_tidak_ada->area->code ?? '-',
                            'production_batch' => $row_tidak_ada->productionBatch()->exists() ? $row_tidak_ada->productionBatch->code : '-',
                            'shading'      => $row_tidak_ada->shading->code ?? '-',
                            'satuan'       => $row_tidak_ada->item->uomUnit->code,
                            'kode'         => $row_tidak_ada->item->code,
                            'last_qty'     => $row_tidak_ada ? $row_tidak_ada->qty_final : 0,
                        ]; 
                    }
                    
                }
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
        if($this->type == 'final'){
            $combinedArray=$array_filter;
        }

        activity()
            ->performedOn(new ItemCogs())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export stock in rupiah data  .');

        return view('admin.exports.stock_in_rupiah', [
            'data'          => $combinedArray,
            'latest'        => $array_last_item,
            'first'         => $array_first_item,
            'perlu'         =>  $perlu,
        ]);
    }
}

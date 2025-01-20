<?php


namespace App\Exports;
use Illuminate\Support\Facades\DB;
use App\Models\ItemCogs;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\ItemShading;

class ExportStockMovementShading implements FromView,ShouldAutoSize
{
    protected $plant, $item, $warehouse, $start_date, $finish_date,$type,$group,$batch_id,$shading_id;
    public function __construct(string $plant, string $item,string $warehouse, string $start_date, string $finish_date , string $type , string $group, string $batch_id , string $shading_id)
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->type = $type ? $type : '';
        $this->group = $group ? $group : '';
        $this->batch_id = $batch_id ? $batch_id : '';
        $this->shading_id = $shading_id ? $shading_id : '';
    }
    public function view(): View
    {
        DB::statement("SET SQL_MODE=''");
        $item_shading_ids = ItemShading::join('items', 'item_shadings.item_id', '=', 'items.id')
        ->whereHas('item', function ($query) {
            $query->whereNull('deleted_at');
        })
        ->orderBy('items.code')
        ->orderBy('items.id')
        ->pluck('item_shadings.id');
        if($this->type == 'final'){
            $perlu = 0 ;
            $query_data = ItemCogs::whereRaw("id IN (SELECT MAX(id) FROM item_cogs WHERE deleted_at IS NULL AND date <= '".$this->finish_date."' GROUP BY item_id)")
            ->where(function($query) use ($item_shading_ids) {
                $query->whereHas('item',function($query){
                    $query->whereIn('status',['1','2']);
                });

               if($this->finish_date) {
                    $query->whereDate('date','<=', $this->finish_date);
                }
                if($this->item) {
                    $query->whereHas('item',function($query){

                        $query->where('id',$this->item);
                        // $query->whereHas('parentFg',function($query) {
                        //     $query->where('parent_id',$this->item);
                        // });
                    });
                }else{
                    $query->whereIn('item_shading_id', $item_shading_ids);
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

                if($this->shading_id) {
                    $query->where('item_shading_id',$this->shading_id);
                }

                if($this->batch_id) {
                    $query->where('production_batch_id',$this->batch_id);
                }

                if($this->group){
                    $groupIds = explode(',', $this->group);
                    $query->whereHas('item',function($query)use($groupIds){
                        $query->whereIn('item_group_id', $groupIds);
                    });
                }
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        }else{
            $perlu = 1;
            $query_data = ItemCogs::where(function($query) use ($item_shading_ids) {
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
                        // $query->whereHas('parentFg',function($query) {
                        //     $query->where('parent_id',$this->item);
                        // });
                    });
                }else{
                    $query->whereIn('item_shading_id', $item_shading_ids);
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
                if($this->shading_id) {
                    $query->where('item_shading_id',$this->shading_id);
                }

                if($this->batch_id) {
                    $query->where('production_batch_id',$this->batch_id);
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
        $first_qty = 0;
        $qty_final = 0;
        if($this->shading_id ||$this->batch_id ){
            $query_for_shading = ItemCogs::where(function($query) {
                $query->where('date', '<', $this->start_date);

                if($this->plant != 'all'){
                    $query->whereHas('place',function($query) {
                        $query->where('id',$this->plant);
                    });
                }
                if($this->shading_id) {
                    $query->where('item_shading_id',$this->shading_id);
                }
                if($this->batch_id) {
                    $query->where('production_batch_id',$this->batch_id);
                }
                if($this->warehouse != 'all'){
                    $query->whereHas('warehouse',function($query) {
                        $query->where('id',$this->warehouse);
                    });
                }
            })
            ->orderBy('date', 'desc') // Order by 'date' column in descending order
            ->orderBy('id', 'desc')
            ->get();
            $qty_total_shading = 0;
            foreach($query_for_shading as $row_cum_bef){
                $qty_total_shading += round($row_cum_bef->qty_in,3);
                $qty_total_shading -= round($row_cum_bef->qty_out,3);
            }
            $first_qty = $query_for_shading ? round($qty_total_shading,3) : 0;
            $cum_qty += round($first_qty,3);
        }
        foreach($query_data as $key=>$row){

            if($row->type=='IN'){
                $cum_qty=round($row->qty_in,3);
                $cum_val=round($row->total_in,3);
            }else{
                $cum_qty=round($row->qty_out,3) * -1;
                $cum_val=round($row->total_out,3) * -1;
            }

            if($this->shading_id || $this->batch_id) {
                if($key == 0){

                    $qty_final += round($cum_qty,3)+round($first_qty,3);
                }else{
                    $qty_final += round($cum_qty,3);
                }
            }else{
                $qty_final =round($row->qty_final,3);
            }

            $data_tempura = [
                'item_id'      => $row->item->id,
                'perlu'        => 0,
                'requester'    => $this->type == 'final' ? '-' : $row->getRequester(),
                'plant' => $row->place->code,
                'warehouse' => $row->warehouse->name,
                'item' => $row->item->name,
                'satuan' => $row->item->uomUnit->code,
                'area'         => $row->area->name ?? '-',
                'production_batch' => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                'shading'      => $row->itemShading->code ?? '-',
                'kode' => $row->item->code,
                'final'=>number_format($row->price_final,2,',','.'),
                'total'=>number_format($cum_val,2,',','.'),
                'qty' => $perlu == 0 ? '-' : $cum_qty,
                'date' =>  date('d/m/Y',strtotime($row->date)),
                'document' => $row->lookable->code,
                'cum_qty' => round($qty_final,3),
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

                if($this->shading_id|| $this->batch_id) {
                    $query_for_shading = ItemCogs::where(function($query) use ($row) {
                        $query->where('item_id',$row->item_id)
                        ->where('date', '<', $row->date);

                        if($this->plant != 'all'){
                            $query->whereHas('place',function($query) {
                                $query->where('id',$this->plant);
                            });
                        }

                        if($this->batch_id) {
                            $query->where('production_batch_id',$this->batch_id);
                        }
                        if($this->shading_id) {
                            $query->where('item_shading_id',$this->shading_id);
                        }
                        if($this->warehouse != 'all'){
                            $query->whereHas('warehouse',function($query) {
                                $query->where('id',$this->warehouse);
                            });
                        }
                    })
                    ->orderBy('date', 'desc') // Order by 'date' column in descending order
                    ->orderBy('id', 'desc')
                    ->get();
                    $qty_total_shading = 0;
                    foreach($query_for_shading as $row_cum_bef){
                        $qty_total_shading += round($row_cum_bef->qty_in,3);
                        $qty_total_shading -= round($row_cum_bef->qty_out,3);
                    }
                    $last_qty = $query_first ? CustomHelper::formatConditionalQty(round($qty_total_shading,3)) : 0;
                }else{
                    $last_qty = $query_first ? CustomHelper::formatConditionalQty(round($query_first->qty_final,3)) : 0;
                }

                $array_last_item[] = [
                    'perlu'        => 1,
                    'item_id'      => $row->item->id,
                    'requester'    => '-',
                    'id'           => $query_first->id ?? null,
                    'date'         => $query_first ? date('d/m/Y', strtotime($query_first->date)) : null,
                    'last_nominal' => $query_first ? number_format($query_first->total_final, 2, ',', '.') : 0,
                    'item'         => $row->item->name,
                    'satuan'       => $row->item->uomUnit->code,
                    'area'         => $row->area->name ?? '-',
                    'production_batch' => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                    'shading'      => $row->itemShading->code ?? '-',
                    'kode'         => $row->item->code,
                    'last_qty'     => $last_qty,
                ];


            }
            $previousId = $row->item_id;
            if($uom_unit ===null){
                $uom_unit = $row->item->uomUnit->code;
            }
        }
        if($this->type != 'final'){
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
                    if($this->shading_id) {
                        $query->where('item_shading_id',$this->shading_id);
                    }

                    if($this->batch_id) {
                        $query->where('production_batch_id',$this->batch_id);
                    }
                    if($this->group){
                        $groupIds = explode(',', $this->group);

                        $query->whereHas('item',function($query) use($groupIds){
                            $query->whereIn('item_group_id', $groupIds);
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
                    if($this->shading_id) {
                        $query->where('item_shading_id',$this->shading_id);
                    }

                    if($this->batch_id) {
                        $query->where('production_batch_id',$this->batch_id);
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
                ->first();
                if($first){
                    $query_no[]=$first;
                }
            }


            foreach($query_no as $row_tidak_ada){
                if($row_tidak_ada->qty_final > 0){
                    $array_first_item[] = [
                        'perlu'        => 1,
                        'item_id'      => $row_tidak_ada->item->id,
                        'requester'    => '-',
                        'id'           => $row_tidak_ada->id,
                        'date'         => $row_tidak_ada ? date('d/m/Y', strtotime($row_tidak_ada->date)) : null,
                        'last_nominal' => $row_tidak_ada ? number_format($row_tidak_ada->total_final, 2, ',', '.') : 0,
                        'item'         => $row_tidak_ada->item->name,
                        'satuan'       => $row_tidak_ada->item->uomUnit->code,
                        'area'         => $row_tidak_ada->area->name ?? '-',
                        'production_batch' => $row_tidak_ada->productionBatch()->exists() ? $row_tidak_ada->productionBatch->code : '-',
                        'shading'      => $row_tidak_ada->itemShading->code ?? '-',
                        'kode'         => $row_tidak_ada->item->code,
                        'last_qty'     => $row_tidak_ada ? round($row_tidak_ada->qty_final,3) : 0,
                    ];
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

        info($combinedArray);

        activity()
            ->performedOn(new ItemCogs())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export stock movement data  .');
        return view('admin.exports.stock_movement', [
            'data' => $combinedArray,
            'latest' => $array_last_item,
            'first'         => $array_first_item,
            'perlu'         =>  $perlu,
        ]);
    }
}

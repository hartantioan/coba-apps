<?php

namespace App\Exports;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use App\Models\ItemCogs;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportStockInRupiah extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements FromView,ShouldAutoSize,WithCustomValueBinder
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $plant, $item, $warehouse, $start_date, $finish_date,$type,$group;

    public function __construct(string $plant, string $item,string $warehouse, string $start_date, string $finish_date , string $type , array $group = [])
    {
        $this->plant = $plant ? $plant : '';
		$this->item = $item ? $item : '';
        $this->warehouse = $warehouse ? $warehouse : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
        $this->type = $type ? $type : '';
        $this->group = $group;

    }

    public function view(): View
    {
        DB::statement("SET SQL_MODE=''");
        if($this->type == 'final'){
            $perlu = 0 ;
            $combinedArray = [];
            $item = Item::where(function($query){
                if($this->item) {
                    $query->where('id',$this->item);
                }
                if(count($this->group) > 0){
                    $query->whereIn('item_group_id',$this->group);
                }
                if($this->warehouse != 'all'){
                    $query->whereHas('itemGroup',function($query){
                        $query->whereHas('itemGroupWarehouse',function($query){
                            $query->where('warehouse_id',$this->warehouse);
                        });
                    });
                }
            })->pluck('id');

            foreach($item as $row){
                $start_time = microtime(true);
                
                $data = DB::table('item_cogs')->select('item_cogs.qty_final AS qty_final','item_cogs.total_final AS total_final','places.code AS place_code','warehouses.name AS warehouse_name','items.code AS item_code','items.name AS item_name','units.code AS uom_unit','areas.code AS area_code','item_shadings.code AS item_shading')
                ->where('item_cogs.date','<=',$this->finish_date)->where('item_cogs.item_id',$row)->where(function($query){
                    if($this->plant != 'all'){
                        $query->where('item_cogs.place_id',$this->plant);
                    }
                })
                ->leftJoin('places', 'places.id', '=', 'item_cogs.place_id')
                ->leftJoin('warehouses', 'warehouses.id', '=', 'item_cogs.warehouse_id')
                ->leftJoin('items', 'items.id', '=', 'item_cogs.item_id')
                ->leftJoin('units', 'units.id', '=', 'items.uom_unit')
                ->leftJoin('areas', 'areas.id', '=', 'item_cogs.area_id')
                ->leftJoin('item_shadings', 'item_shadings.id', '=', 'item_cogs.item_shading_id')
                ->orderByDesc('item_cogs.date')->orderByDesc('item_cogs.id')->first();
                
                $end_time = microtime(true);

                $execution_time = ($end_time - $start_time);
                
                if($data){
                    info($execution_time.' - '.$data->item_code.' - '.$data->item_name);
                    $combinedArray[] = [
                        'plant'     => $data->place_code,
                        'warehouse' => $data->warehouse_name,
                        'kode'      => $data->item_code,
                        'item'      => $data->item_name,
                        'satuan'    => $data->uom_unit,
                        'requester' => '-',
                        'area'      => $data->area_code,
                        'shading'   => $data->item_shading,
                        'cum_qty'   => $data->qty_final,
                        'cum_val'   => $data->total_final,
                    ];
                }
            }

            activity()
                ->performedOn(new ItemCogs())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export stock movement data  .');
            return view('admin.exports.stock_in_rupiah', [
                'data'      => $combinedArray,
                'perlu'     =>  $perlu,
            ]);
        }else{
            $perlu = 1;
            $query_data = ItemCogs::where(function($query) {
                $query->whereHas('item',function($query){
                    $query->whereIn('status',['1','2']);
                    if(count($this->group) > 0){
                        $query->whereIn('item_group_id', $this->group);
                    }
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
                if($this->plant !== 'all'){
                    $query->whereHas('place',function($query){
                        $query->where('id',$this->plant);
                    });
                }
                if($this->warehouse !== 'all'){
                    $query->whereHas('warehouse',function($query){
                        $query->where('id',$this->warehouse);
                    });
                }
            })
            ->orderBy('item_id')
            ->orderBy('date')
            ->orderBy('id')
            ->get();
            
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
                    'area' => $this->type == 'final' ? '' : ($row->area->name ?? '-'),
                    'shading' => $this->type == 'final' ? '' : ($row->itemShading->code ?? '-'),
                    'production_batch' => $this->type == 'final' ? '' : ($row->productionBatch()->exists() ? $row->productionBatch->code : '-'),
                    'final'=>$priceNow,
                    'total'=>$perlu == 0 ? 0 : $cum_val,
                    'qty' => $perlu == 0 ? 0 : $cum_qty,
                    'date' =>  date('d/m/Y',strtotime($row->date)),
                    'document' => $row->lookable->code,
                    'cum_qty' => $row->qty_final,
                    'cum_val' => $row->total_final,
                ];
                $array_filter[]=$data_tempura;

                if($this->type !== 'final'){
                    if ($row->item_id !== $previousId) {

                        $query_first =
                        ItemCogs::where(function($query) use ( $row) {
                            $query->where('item_id',$row->item_id)
                            ->where('date', '<', $row->date);

                            if($this->plant !== 'all'){
                                $query->whereHas('place',function($query){
                                    $query->where('id',$this->plant);
                                });
                            }
                            if($this->warehouse !== 'all'){
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
                            'area'         => $row->area->name ?? '-',
                            'production_batch' => '-',
                            'shading'      => $row->itemShading->code ?? '-',
                            'last_nominal' => $query_first ? $query_first->total_final : 0,
                            'item'         => $row->item->name,
                            'satuan'       => $row->item->uomUnit->code,
                            'kode'         => $row->item->code,
                            'last_qty'     => $query_first ? $query_first->qty_final : 0,
                        ];


                    }
                }
                $previousId = $row->item_id;

                if($uom_unit ===null){
                    $uom_unit = $row->item->uomUnit->code;
                }


            }
            if( $this->type !== 'final'){
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

                        if($this->plant !== 'all'){
                            $query->whereHas('place',function($query) {
                                $query->where('id',$this->plant);
                            });
                        }
                        if($this->warehouse !== 'all'){
                            $query->whereHas('warehouse',function($query) {
                                $query->where('id',$this->warehouse);
                            });
                        }

                        if(count($this->group) > 0){
                            $query->whereHas('item',function($query) {
                                $query->whereIn('item_group_id', $this->group);
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

                        if($this->plant !== 'all'){
                            $query->whereHas('place',function($query) {
                                $query->where('id',$this->plant);
                            });
                        }
                        if($this->warehouse !== 'all'){
                            $query->whereHas('warehouse',function($query) {
                                $query->where('id',$this->warehouse);
                            });
                        }

                        if(count($this->group) > 0){
                            $query->whereHas('item',function($query) {
                                $query->whereIn('item_group_id', $this->group);
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

                if(count($query_no) > 0 ){

                    foreach($query_no as $row_tidak_ada){

                        if($row_tidak_ada->qty_final > 0){
                            $array_first_item[] = [
                                'perlu'        => 1,
                                'item_id'      => $row_tidak_ada->item->id,
                                'id'           => $row_tidak_ada->id,
                                'date'         => $row_tidak_ada ? date('d/m/Y', strtotime($row_tidak_ada->date)) : null,
                                'last_nominal' => $row_tidak_ada ? $row_tidak_ada->total_final : 0,
                                'item'         => $row_tidak_ada->item->name,
                                'area'         => $row_tidak_ada->area->name ?? '-',
                                'production_batch' => $row_tidak_ada->productionBatch()->exists() ? $row_tidak_ada->productionBatch->code : '-',
                                'shading'      => $row_tidak_ada->itemShading->code ?? '-',
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
}

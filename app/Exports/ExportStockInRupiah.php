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
                $data = DB::table('item_cogs')->select('item_cogs.qty_final AS qty_final','item_cogs.total_final AS total_final','places.code AS place_code','warehouses.name AS warehouse_name','items.code AS item_code','items.name AS item_name','units.code AS uom_unit','areas.code AS area_code','item_shadings.code AS item_shading')
                ->where('item_cogs.date','<=',$this->finish_date)->where('item_cogs.item_id',$row)->where(function($query){
                    if($this->plant != 'all'){
                        $query->where('item_cogs.place_id',$this->plant);
                    }
                    /* if($this->warehouse != 'all'){
                        $query->where('item_cogs.warehouse_id',$this->warehouse);
                    } */
                })
                ->whereNull('item_cogs.deleted_at')
                ->leftJoin('places', 'places.id', '=', 'item_cogs.place_id')
                ->leftJoin('warehouses', 'warehouses.id', '=', 'item_cogs.warehouse_id')
                ->leftJoin('items', 'items.id', '=', 'item_cogs.item_id')
                ->leftJoin('units', 'units.id', '=', 'items.uom_unit')
                ->leftJoin('areas', 'areas.id', '=', 'item_cogs.area_id')
                ->leftJoin('item_shadings', 'item_shadings.id', '=', 'item_cogs.item_shading_id')
                ->orderByDesc('item_cogs.date')->orderByDesc('item_cogs.id')->first();
                
                if($data){
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

            /* $where_group = count($this->group) > 0 ? " AND i.item_group_id IN (".implode(",",$this->group).")" : "";
            $where_warehouse = $this->warehouse != 'all' ? " AND ic.warehouse_id = ".$this->warehouse : "";
            $where_warehouse_main = $this->warehouse != 'all' ? " AND igw.warehouse_id = ".$this->warehouse : "";
            $where_place = $this->plant != 'all' ? " AND ic.place_id = ".$this->plant : "";
            $where_item = $this->item ? " AND i.id = ".$this->item : "";

            $data = DB::select("
                    SELECT 
                        i.code AS item_code,
                        i.name AS item_name,
                        p.code AS place_code,
                        u.code AS unit_code,
                        w.name AS warehouse_name,
                        COALESCE((
                            SELECT 
                                ic.qty_final
                            FROM 
                                item_cogs ic
                            WHERE 
                                ic.item_id = i.id
                                AND ic.date <= :date1
                                AND ic.deleted_at IS NULL
                            	".$where_warehouse."
                                ".$where_place."
                            ORDER BY ic.date DESC, ic.id DESC LIMIT 1
                        ),0) AS qty_final,
                        COALESCE((
                            SELECT 
                                ic.total_final
                            FROM 
                                item_cogs ic
                            WHERE 
                                ic.item_id = i.id
                                AND ic.date <= :date2
                                AND ic.deleted_at IS NULL
                            	".$where_warehouse."
                                ".$where_place."
                            ORDER BY ic.date DESC, ic.id DESC LIMIT 1
                        ),0) AS total_final
                    FROM
                        items i
                    LEFT JOIN item_group_warehouses igw ON igw.item_group_id = i.item_group_id
                    LEFT JOIN units u ON u.id = i.uom_unit
                    LEFT JOIN places p ON p.id = :place
                    LEFT JOIN warehouses w ON w.id = :warehouse
                    WHERE i.deleted_at IS NULL
                    ".$where_warehouse_main."
                    ".$where_group."
                    ".$where_item."
                ", array(
                    'date1'     => $this->finish_date,
                    'date2'     => $this->finish_date,
                    'place'     => $this->plant,
                    'warehouse' => $this->warehouse,
                ));

            foreach($data as $row){
                $combinedArray[] = [
                    'plant'     => $row->place_code,
                    'warehouse' => $row->warehouse_name,
                    'kode'      => $row->item_code,
                    'item'      => $row->item_name,
                    'satuan'    => $row->unit_code,
                    'requester' => '-',
                    'area'      => '-',
                    'shading'   => '-',
                    'cum_qty'   => $row->qty_final,
                    'cum_val'   => $row->total_final,
                ];
            } */

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
                $totalQty = 0;
                $totalNominal = 0;
                $old_data = DB::table('item_cogs')->select('item_cogs.qty_final AS qty_final','item_cogs.total_final AS total_final','places.code AS place_code','warehouses.name AS warehouse_name','items.code AS item_code','items.name AS item_name','units.code AS uom_unit','areas.code AS area_code','item_shadings.code AS item_shading','production_batches.code AS batch_code')
                ->where('item_cogs.date','<=',$this->finish_date)->where('item_cogs.item_id',$row)->where(function($query){
                    if($this->plant != 'all'){
                        $query->where('item_cogs.place_id',$this->plant);
                    }
                })
                ->whereNull('item_cogs.deleted_at')
                ->leftJoin('places', 'places.id', '=', 'item_cogs.place_id')
                ->leftJoin('warehouses', 'warehouses.id', '=', 'item_cogs.warehouse_id')
                ->leftJoin('items', 'items.id', '=', 'item_cogs.item_id')
                ->leftJoin('units', 'units.id', '=', 'items.uom_unit')
                ->leftJoin('areas', 'areas.id', '=', 'item_cogs.area_id')
                ->leftJoin('item_shadings', 'item_shadings.id', '=', 'item_cogs.item_shading_id')
                ->leftJoin('production_batches', 'production_batches.id', '=', 'item_cogs.production_batch_id')
                ->orderByDesc('item_cogs.date')->orderByDesc('item_cogs.id')->first();
                if($old_data){
                    $totalQty += round($old_data->qty_final,3);
                    $totalNominal += round($old_data->total_final,2);
                    $combinedArray[] = [
                        'perlu'         => 0,
                        'plant'         => $old_data->place_code,
                        'warehouse'     => $old_data->warehouse_name,
                        'item'          => $old_data->item_name,
                        'satuan'        => $old_data->uom_unit,
                        'kode'          => $old_data->item_code,
                        'area'          => $old_data->area_code,
                        'shading'       => $old_data->item_shading,
                        'production_batch' => $old_data->batch_code,
                        'price'         => 0,
                        'total'         => 0,
                        'qty'           => 0,
                        'date'          => '-',
                        'document'      => '-',
                        'cum_qty'       => $old_data->qty_final,
                        'cum_val'       => $old_data->total_final,
                    ];
                }
                $data = ItemCogs::where('date','>=',$this->start_date)->where('date','<=',$this->finish_date)->where('item_id',$row)->where(function($query){
                    if($this->plant != 'all'){
                        $query->where('place_id',$this->plant);
                    }
                })->orderBy('date')->orderBy('id')->get();

                foreach($data as $rowdata){
                    if($rowdata->type == 'IN'){
                        $price = $rowdata->qty_in > 0 ? round($rowdata->total_in / $rowdata->qty_in,2) : 0;
                        $totalQty += round($rowdata->qty_in,3);
                        $totalNominal += round($rowdata->total_in,2);
                    }else{
                        $price = $rowdata->qty_out > 0 ? round($rowdata->total_out / $rowdata->qty_out,2) : 0;
                        $totalQty -= round($rowdata->qty_out,3);
                        $totalNominal -= round($rowdata->total_out,2);
                    }
                    $combinedArray[] = [
                        'perlu'         => 0,
                        'plant'         => $rowdata->place->code,
                        'warehouse'     => $rowdata->warehouse->name,
                        'item'          => $rowdata->item->name,
                        'satuan'        => $rowdata->item->uomUnit->code,
                        'kode'          => $rowdata->item->code,
                        'area'          => $rowdata->area->code ?? '-',
                        'shading'       => $rowdata->itemShading->code ?? '-',
                        'production_batch' => $rowdata->productionBatch->code ?? '-',
                        'price'         => $price,
                        'total'         => $rowdata->type == 'IN' ? $rowdata->total_in : -1 * $rowdata->total_out,
                        'qty'           => $rowdata->type == 'IN' ? $rowdata->qty_in : -1 * $rowdata->qty_out,
                        'date'          => date('d/m/Y',strtotime($rowdata->date)),
                        'document'      => $rowdata->lookable->code,
                        'cum_qty'       => $rowdata->qty_final,
                        'cum_val'       => $rowdata->total_final,
                    ];
                }
            }

            activity()
                ->performedOn(new ItemCogs())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export stock in rupiah data  .');

            return view('admin.exports.stock_in_rupiah', [
                'data'          => $combinedArray,
                'perlu'         =>  $perlu,
            ]);
        }
    }
}

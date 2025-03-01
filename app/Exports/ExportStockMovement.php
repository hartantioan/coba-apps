<?php

namespace App\Exports;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use App\Models\ItemCogs;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportStockMovement implements FromView,ShouldAutoSize
{
    protected $plant, $item, $warehouse, $start_date, $finish_date,$type,$group;
    public function __construct(string $plant, string $item,string $warehouse, string $start_date, string $finish_date , string $type , array $group)
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
                $data = ItemCogs::where('date','<=',$this->finish_date)->where('item_id',$row)->where(function($query){
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
                    $combinedArray[] = [
                        'plant'     => $data->place->code,
                        'warehouse' => $data->warehouse->name,
                        'kode'      => $data->item->code,
                        'item'      => $data->item->name,
                        'satuan'    => $data->item->uomUnit->code,
                        'requester' => '-',
                        'area'      => $data->area->name ?? '-',
                        'shading'   => $data->itemShading->code ?? '-',
                        'cum_qty'   => $data->qty_final,
                    ];
                }
            }

            activity()
                ->performedOn(new ItemCogs())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export stock movement data  .');
            return view('admin.exports.stock_movement', [
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
                $total = 0;
                $old_data = ItemCogs::where('date','<',$this->start_date)->where('item_id',$row)->where(function($query){
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
                if($old_data){
                    if($this->warehouse != 'all'){
                        $total += round($old_data->qtyByWarehouseBeforeDate($this->start_date),3);
                    }else{
                        $total += round($old_data->qty_final,3);
                    }
                    $combinedArray[] = [
                        'item_id'           => $old_data->item->id,
                        'requester'         => '-',
                        'plant'             => $old_data->place->code,
                        'warehouse'         => $old_data->warehouse->name,
                        'item'              => $old_data->item->name,
                        'satuan'            => $old_data->item->uomUnit->code,
                        'area'              => $old_data->area->name ?? '-',
                        'production_batch'  => $old_data->productionBatch()->exists() ? $old_data->productionBatch->code : '-',
                        'shading'           => $old_data->itemShading->code ?? '-',
                        'kode'              => $old_data->item->code,
                        'qty'               => 0,
                        'date'              => date('d/m/Y',strtotime($old_data->date)),
                        'document'          => 'Saldo',
                        'cum_qty'           => $total,
                    ];
                }
                $data = ItemCogs::where('date','>=',$this->start_date)->where('date','<=',$this->finish_date)->where('item_id',$row)->where(function($query){
                    if($this->plant != 'all'){
                        $query->whereHas('place',function($query){
                            $query->where('id',$this->plant);
                        });
                    }
                    if($this->warehouse != 'all'){
                        $query->where('warehouse_id',$this->warehouse);
                    }
                })->orderBy('date')->orderBy('id')->get();
                foreach($data as $key => $row){
                    if($row->type == 'IN'){
                        $total += round($row->qty_in,3);
                    }else{
                        $total -= round($row->qty_out,3);
                    }
                    $combinedArray[] = [
                        'item_id'           => $row->item->id,
                        'requester'         => $row->getRequester(),
                        'plant'             => $row->place->code,
                        'warehouse'         => $row->warehouse->name,
                        'item'              => $row->item->name,
                        'satuan'            => $row->item->uomUnit->code,
                        'area'              => $row->area->name ?? '-',
                        'production_batch'  => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                        'shading'           => $row->itemShading->code ?? '-',
                        'kode'              => $row->item->code,
                        'qty'               => $row->type == 'IN' ? $row->qty_in : -1 * $row->qty_out,
                        'date'              => date('d/m/Y',strtotime($row->date)),
                        'document'          => $row->lookable->code,
                        'cum_qty'           => $total,
                    ];
                }
            }

            activity()
                ->performedOn(new ItemCogs())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export stock movement data  .');
            return view('admin.exports.stock_movement', [
                'data'          => $combinedArray,
                'perlu'         => $perlu,
            ]);
        }
    }
}

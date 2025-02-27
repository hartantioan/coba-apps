<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportDeadStock;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\Place;
use App\Models\ItemGroup;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class DeadStockController extends Controller
{
    protected $dataplaces,$dataplacecode, $datawarehouses;
    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }
    public function index(Request $request)
    {
        $itemGroup = ItemGroup::whereHas('childSub',function($query){
            $query->whereHas('itemGroupWarehouse',function($query){
                $query->whereIn('warehouse_id',$this->datawarehouses);
            });
        })->get();
        $data = [
            'title'     => 'Dead Stock',
            'group'     =>  $itemGroup,
            'content'   => 'admin.inventory.dead_stock',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        $item = Item::where(function($query)use($request){
            if($request->filter_group){
                $query->whereIn('item_group_id', $request->filter_group);
            }
            if($request->warehouse != 'all'){
                $query->whereHas('itemGroup',function($query)use($request){
                    $query->whereHas('itemGroupWarehouse',function($query)use($request){
                        $query->where('warehouse_id',$request->warehouse);
                    });
                });
            }
        })->pluck('id');
        $arr = [];
        foreach($item as $row){
            /* $data = ItemCogs::where('date','<=',$request->date)->where('item_id',$row)->where(function($query)use($request){
                if($request->plant != 'all'){
                    $query->whereHas('place',function($query) use($request){
                        $query->where('id',$request->plant);
                    });
                }
                if($request->warehouse != 'all'){
                    $query->whereHas('warehouse',function($query) use($request){
                        $query->where('id',$request->warehouse);
                    });
                }
            })->orderByDesc('date')->orderByDesc('id')->first(); */
            $data = DB::table('item_cogs')->select('item_cogs.id AS id','item_cogs.qty_final AS qty_final','item_cogs.total_final AS total_final','places.code AS place_code','warehouses.name AS warehouse_name','items.code AS item_code','items.name AS item_name','units.code AS uom_unit','areas.code AS area_code','item_shadings.code AS item_shading','item_cogs.date AS date','production_batches.code AS batch_code')
                ->where('item_cogs.date','<=',$request->date)->where('item_cogs.item_id',$row)->where(function($query)use($request){
                    if($request->plant != 'all'){
                        $query->where('item_cogs.place_id',$request->plant);
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
            if($data){
                if($data->qty_final > 0){
                    $date = Carbon::parse($data->date);
                    $dateDifference = $date->diffInDays($request->date);
                    $arr[]=[
                        'plant'=>$data->place_code,
                        'gudang'=>$data->warehouse_name,
                        'kode'=>$data->item_code,
                        'item'=>$data->item_name,
                        'satuan' => $data->uom_unit,
                        'area'         => $data->area_code,
                        'production_batch' => $data->batch_code,
                        'shading'      => $data->item_shading,
                        'keterangan'=>'',
                        'date'=>$data->date,
                        'lamahari'=>$dateDifference,
                    ];
                }
            }
        }    
        $end_time = microtime(true);
  
        $execution_time = ($end_time - $start_time);
        $response =[
            'status'=>200,
            'message'  =>$arr,
            'time'  => " Waktu proses : ".$execution_time." detik"
        ];
        return response()->json($response);
    }

    public function export(Request $request){
		$plant = $request->plant ? $request->plant:'';
        $warehouse = $request->warehouse?$request->warehouse:'';
        $date = $request->date ? $request->date:'';
        $hari= $request->hari ? $request->hari:'';
        $group = $request->group ? $request->group:'';
		return Excel::download(new ExportDeadStock($plant,$warehouse,$date,$group), 'dead_stock'.uniqid().'.xlsx');
    }
}

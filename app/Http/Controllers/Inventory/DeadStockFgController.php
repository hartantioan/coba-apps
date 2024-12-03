<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportDeadStockFG;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\Place;
use App\Models\ItemGroup;
use App\Models\ItemShading;
use App\Models\ProductionBatch;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DeadStockFgController extends Controller
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
            'content'   => 'admin.inventory.dead_stock_fg',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        $item = Item::where(function($query)use($request){
            $query->where('is_sales_item','1');
            if($request->item_id){
                $query->where('id', $request->item_id);
            }
        })->pluck('id');
        $arr = [];
        $arr_batch_id = [];
        foreach($item as $row){
            $shading = ItemShading::where('item_id',$row)->get();
            foreach($shading as $row_shading){
                $mbeng = ProductionBatch::where('item_shading_id',$row_shading->id)->where('post_date','<=',$request->date)->pluck('id')->toArray();
                $arr_batch_id = array_merge($arr_batch_id, $mbeng);
            }

        }

        foreach($arr_batch_id as $row_batch_id){
            $data = ItemCogs::where('production_batch_id',$row_batch_id)->where(function($query)use($request){
                if($request->plant != 'all'){
                    $query->whereHas('place',function($query) use($request){
                        $query->where('id',$request->plant);
                    });
                }
            })->orderByDesc('date')->orderByDesc('id')->first();
            if($data){
                $infoFg = $data->infoFg();
                $qty = $infoFg['qty'];
                if( $qty > 0){
                    $date = Carbon::parse($data->date);
                    $dateDifference = $date->diffInDays($request->date);
                    $arr[]=[
                        'plant'=>$data->place->code,
                        'gudang'=>$data->warehouse->name,
                        'kode'=>$data->item->code,
                        'item'=>$data->item->name,
                        'satuan' => $data->item->uomUnit->code,
                        'area'         => $data->area->code ?? '-',
                        'production_batch' => $data->productionBatch()->exists() ? $data->productionBatch->code : '-',
                        'shading'      => $data->itemShading->code ?? '-',
                        'keterangan'=>$data->lookable->code.'-'.$data->lookable->name,
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
        $date = $request->date ? $request->date:'';
        $item_id = $request->item_id ? $request->item_id:'';
		return Excel::download(new ExportDeadStockFG($plant,$item_id,$date), 'dead_stock_fg'.uniqid().'.xlsx');
    }
}

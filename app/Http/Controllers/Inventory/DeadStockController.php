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
        })->pluck('id');
        $arr = [];
        foreach($item as $row){
            $data = ItemCogs::where('date','<=',$request->date)->where('item_id',$row)->where(function($query)use($request){
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
            })->orderByDesc('date')->orderByDesc('id')->first();
            if($data){
                if($data->qty_final > 0){
                    $arr[] = $data;
                }
            }
        }

        $array_filter=[];
       
        foreach($arr as $row){
           
            $date = Carbon::parse($row->date);
            $dateDifference = $date->diffInDays($request->date);
               
                //if ($dateDifference >= $request->hari) {
                    $array_filter[]=[
                        'plant'=>$row->place->code,
                        'gudang'=>$row->warehouse->name,
                        'kode'=>$row->item->code,
                        'item'=>$row->item->name,
                        'satuan' => $row->item->uomUnit->code,
                        'area'         => $row->area->code ?? '-',
                        'production_batch' => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                        'shading'      => $row->shading->code ?? '-',
                        'keterangan'=>$row->lookable->code.'-'.$row->lookable->name,
                        'date'=>$row->date,
                        'lamahari'=>$dateDifference,
                    ];
                //}
                      
        }
        $end_time = microtime(true);
  
        $execution_time = ($end_time - $start_time);
        $response =[
            'status'=>200,
            'message'  =>$array_filter,
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

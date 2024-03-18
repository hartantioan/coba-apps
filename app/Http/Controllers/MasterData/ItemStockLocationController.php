<?php

namespace App\Http\Controllers\MasterData;

use App\Exports\ExportItemStockLocation;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\ItemStock;
use App\Models\Menu;
use App\Models\Place;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ItemStockLocationController extends Controller
{
    protected $dataplaces, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];

    }

    public function index(Request $request)
    {
        $data = [
            'title'         => 'Lokasi Item Stock',
            'content'       => 'admin.master_data.item_stock_location',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'warehouse'     => Warehouse::where('status','1')->whereIn('id',$this->datawarehouses)->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filter(Request $request){
        $start_time = microtime(true);
        
        $query_data = ItemStock::where(function($querys) use ( $request) {
            if($request->place_id){
                $querys->where('place_id',$request->place_id);
            }
            if($request->warehouse_id){
                $querys->where('warehouse_id',$request->warehouse_id);
            }
        })
        ->get();
        
        $array_filter=[];
        $item_id=[];
        foreach($query_data as $row){
            $item_id[]=$row->id;
            $data_tempura = [
                'item_id' => CustomHelper::encrypt($row->id),
                'item' => $row->item->code.'-'.$row->item->name,
                'stock'=>CustomHelper::formatConditionalQty($row->qty),
                'plant'=>$row->place->code,
                'gudang'=>$row->warehouse->code . ' - ' . $row->warehouse->name,
                'area' => $row->area->name??'-',
                'shading' => $row->itemShading->code??'-',
                // 'final'=>number_format($qty,3,',','.'),
                'satuan'=>$row->item->uomUnit->code,
                'location'=>$row->location ?? '',
            ];
            $array_filter[]=$data_tempura;
            
        }
        $end_time = microtime(true);
  
        $execution_time = ($end_time - $start_time);
        $response =[
            'status'=>200,
            'message'  =>$array_filter,
            'item_stock_id'=>$item_id,
            'time'  => " Waktu proses : ".$execution_time." detik"
        ];
        return response()->json($response);
    }

    public function save1(Request $request){
        $find = ItemStock::where('id',CustomHelper::decrypt($request->id))->first();
        if($find){
            $find->location = $request->lokasi;
            $find->update();
            $response =[
                'status'=>200,
                'message'  =>'sukses',
                
            ];
        }else{
            $response =[
                'status'=>500,
                'message'  =>'gagal update',
                
            ];
        }
        
        
        return response()->json($response);
    }

    public function saveAll(Request $request){
     
        $itemStockIdArray = explode(',', $request->item_stock_id);
        foreach($itemStockIdArray as $key => $arr_stock){
            $find = ItemStock::where('id',$arr_stock)->first();
            
            $find->location = $request->arr_loc[$key];
            $find->update();
            
        }
       
        if($find){
           
            $response =[
                'status'=>200,
                'message'  =>'sukses',
                
            ];
        }else{
            $response =[
                'status'=>500,
                'message'  =>'gagal update',
                
            ];
        }
        
        
        return response()->json($response);
    }

    public function export(Request $request){
        $plant = $request->plant? $request->plant : '';
        $warehouse = $request->warehouse ? $request->warehouse : '';

		return Excel::download(new ExportItemStockLocation($plant,$warehouse), 'item_stock_location_'.uniqid().'.xlsx');
    }
}

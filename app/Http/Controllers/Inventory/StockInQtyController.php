<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportStockInQty;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemStock;
use App\Models\ItemGroup;
use App\Models\User;
use App\Models\Place;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockInQtyController extends Controller
{
    protected $dataplaces, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
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
            'title'     => 'Stok Dalam Qty',
            'group'     =>  $itemGroup,
            'content'   => 'admin.inventory.stock_in_qty',
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'warehouse'     => Warehouse::where('status','1')->whereIn('id',$this->datawarehouses)->get(),
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);

        $array_filter=[];
        $query_item = Item::whereDoesntHave('itemStock')->get();
        
        // $query_data = Item::where(function($query) use ($request) {
        //     if ($request->item_id) {
        //         $query->where('id', $request->item_id);
        //     }
        //     if ($request->plant != 'all') {
        //         $query->whereHas('itemStock', function ($query) use ($request) {
        //             $query->where('place_id', $request->plant);
        //         });
        //     }
        //     if ($request->warehouse != 'all') {
        //         $query->whereHas('itemStock', function ($query) use ($request) {
        //             $query->where('warehouse_id', $request->warehouse);
        //         });
        //     }
        // })->get();
        $query_data = ItemStock::join('items', 'item_stocks.item_id', '=', 'items.id')
        ->where(function ($query) use ($request) {
            $query->whereIn('items.status',['1','2']);
            
            if ($request->item_id) {
                $query->where('item_stocks.item_id', $request->item_id);
            }
            if ($request->warehouse != 'all') {
                $query->where('item_stocks.warehouse_id', $request->warehouse);
            }
            if ($request->plant != 'all') {
                $query->where('item_stocks.place_id', $request->plant);
            }
            if($request->filter_group){
                $query->whereIn('items.item_group_id', $request->filter_group);
            }
        })
        
        ->orderBy('items.code') // Assuming 'code' is the attribute you want to use for sorting
        ->get();

        if ($query_data->isEmpty()) {
            $query_data = ItemStock::where(function($query) use ($request) {
                // Your additional conditions for the second query, if needed
            })->get();
        }
        
        foreach($query_data as $row){
            if($row->item()->exists()){
                if($row->qty > 0){
                    $data_tempura = [
                        'item_id' => CustomHelper::encrypt($row->code),
                        'plant' => $row->place->code,
                        'gudang' => $row->warehouse->name ?? '',
                        'kode' => $row->item->code,
                        'item' => $row->item->name,
                        'final'=>CustomHelper::formatConditionalQty($row->qty),
                        'satuan'=>$row->item->uomUnit->code,
                        'perlu' =>1,
                    ];
                    $array_filter[]=$data_tempura;
                }
                
            
                
            }
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
        $item = $request->item ? $request->item:'';
        $group = $request->group ? $request->group:'';
		return Excel::download(new ExportStockInQty($plant,$item,$warehouse,$group), 'stock_in_qty'.uniqid().'.xlsx');
    }

    //
    // public function filter(Request $request){
    //     $start_time = microtime(true);

    //     if($request->plant != 'all' || $request->warehouse != 'all'){
    //         $query_data = ItemStock::where(function($query) use ( $request) {
    //             if($request->item_id) {
    //                 $query->whereHas('item',function($query) use($request){
    //                     $query->where('id',$request->item_id);
    //                 });
    //             }
    //             if($request->plant != 'all'){
    //                 $query->whereHas('place',function($query) use($request){
    //                     $query->where('id',$request->plant);
    //                 });
    //             }
    //             if($request->warehouse != 'all'){
    //                 $query->whereHas('warehouse',function($query) use($request){
    //                     $query->where('id',$request->warehouse);
    //                 });
    //             }
    //         })->get();  

    //         foreach($query_data as $row){
    //             $data_tempura = [
    //                 'item' => $row->item->code.'-'.$row->item->name,
    //                 'final'=>CustomHelper::formatConditionalQty($row->qty),
    //                 'satuan'=>$row->item->uomUnit->code
    //             ];
    //             $array_filter[]=$data_tempura;
    //         }
    //     }else{
    //         $query_data = Item::where(function($query) use ( $request) {
    //             if($request->item_id){
    //                 $query->where('id',$request->item_id);
    //             }
    //         })->get();
    //     }

        
        
    //     $array_filter=[];
        
    //     $end_time = microtime(true);
  
    //     $execution_time = ($end_time - $start_time);
    //     $response =[
    //         'status'=>200,
    //         'message'  =>$array_filter,
    //         'time'  => " Waktu proses : ".$execution_time." detik"
    //     ];
    //     return response()->json($response);
    // }
}

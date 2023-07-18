<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportStockInQty;
use App\Http\Controllers\Controller;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockInQtyController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Stok Dalam Qty',
            'content'   => 'admin.inventory.stock_in_qty',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);

        $query_data = ItemStock::where(function($query) use ( $request) {
            if($request->item_id) {
                $query->whereHas('item',function($query) use($request){
                    $query->where('id',$request->item_id);
                });
            }
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
        })->get();
        
        $array_filter=[];
        foreach($query_data as $row){
            $data_tempura = [
                'item' => $row->item->code.'-'.$row->item->name,
                'final'=>number_format($row->qty,3,',','.'),
                'satuan'=>$row->item->uomUnit->code
            ];
            $array_filter[]=$data_tempura;
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
      
		return Excel::download(new ExportStockInQty($plant,$item,$warehouse), 'stock_in_qty'.uniqid().'.xlsx');
    }
}

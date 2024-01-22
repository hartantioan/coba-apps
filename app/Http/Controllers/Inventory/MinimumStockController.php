<?php

namespace App\Http\Controllers\Inventory;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\ExportMinimumStock;
use App\Models\Item;

class MinimumStockController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Stok item Yang Minim',
            'content'   => 'admin.inventory.minimum_stock',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        info($request);
        $query_data = Item::where(function($querys) use ( $request) {
            if($request->item_id){
                $querys->where('id',$request->item_id);
            }
    
        })
        ->get();
        
        $array_filter=[];
        
        foreach($query_data as $row){
            if($row->itemStock()->exists()){
                $perlu = 1;
                $qty = $row->getStockAll();
            }
            else{
                $qty = 0;
                $perlu = 0;
            }
            $data_tempura = [
                'item_id' => CustomHelper::encrypt($row->code),
                'item' => $row->code.'-'.$row->name,
                'minimum'=>number_format($row->min_stock),
                'needed'=>number_format($row->min_stock-$qty),
                'maximum'=>number_format($row->max_stock),
                'final'=>number_format($qty,3,',','.'),
                'satuan'=>$row->uomUnit->code,
                'perlu' =>$perlu,
            ];
            if($qty < $row->min_stock){
                $array_filter[]=$data_tempura;
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
		$item_id = $request->item_id ? $request->item_id:'';
       
      
		return Excel::download(new ExportMinimumStock($item_id), 'minimum_stock'.uniqid().'.xlsx');
    }

    public function showDetail(Request $request){
        $show = Item::where('code',CustomHelper::decrypt($request->id))->first();

        $warehouse = Warehouse::where('status',1)->get();

        $array_warehouse = [];

        foreach($warehouse as $rowWarehouse){
            $temp = ItemStock::where('warehouse_id',$rowWarehouse->id)
            ->where('item_id',$show->id)->first();
            if($temp){
                $array_warehouse[]=[
                    'plant' =>$temp->place->name,
                    'nama'  =>$rowWarehouse->name,
                    'stock' =>$temp->qty
                ];
            }
            

        }

        $response =[
            'status'=>200,
            'message'  =>$array_warehouse,  
        ];
        return response()->json($response);
    }
}

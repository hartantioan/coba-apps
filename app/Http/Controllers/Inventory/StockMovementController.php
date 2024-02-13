<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportStockMovement;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\Place;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Undefined;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Pergerakan Stok',
            'content'   => 'admin.inventory.stock_movement',
            'place'     =>  Place::where('status','1')->get(),
            'item'      =>  Item::where('status','1')->get(),
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        $query_data = ItemCogs::where(function($query) use ( $request) {
            if($request->start_date && $request->finish_date) {
                $query->whereDate('date', '>=', $request->start_date)
                    ->whereDate('date', '<=', $request->finish_date);
            } else if($request->start_date) {
                $query->whereDate('date','>=', $request->start_date);
            } else if($request->finish_date) {
                $query->whereDate('date','<=', $request->finish_date);
            }
            if($request->item) {
                $query->whereHas('item',function($query) use($request){
                    $query->where('id',$request->item);
                });
            }
            if($request->plant != 'all'){
                $query->whereHas('place',function($query) use($request){
                    $query->where('id',$request->plant);
                });
            }
        })
        ->orderBy('item_id')
        
        ->get();
       // Initialize the previous ID variable
        $cum_qty = 0;
        $cum_val = 0 ;
        $firstDate = null;
        $array_filter=[];
        $uom_unit = null;

        foreach($query_data as $row){
            
            if($row->type=='IN'){
                $cum_qty=$row->qty_in;
                $cum_val=$row->total_in;
            }else{
                $cum_qty=$row->qty_out;
                $cum_val=$row->total_out;
            }
            
            $data_tempura = [
                'plant' => $row->place->code,
                'warehouse' => $row->warehouse->code,
                'item' => $row->item->name,
                'satuan' => $row->item->uomUnit->code,
                'kode' => $row->item->code,
                'final'=>number_format($row->price_final,2,',','.'),
                'total'=>number_format($cum_val,2,',','.'),
                'qty'=>number_format($cum_qty,3,',','.'),
                'date' =>  date('d/m/Y',strtotime($row->date)),
                'document' => $row->lookable->code,
                'cum_qty' => number_format($row->qty_final,3,',','.'),
                'cum_val' => number_format($row->total_final,2,',','.'),
            ];
            $array_filter[]=$data_tempura;
            if ($firstDate === null) {
                $firstDate = $row->date;
            }
            if($uom_unit ===null){
                $uom_unit = $row->item->uomUnit->code;
            }
        }
        $last_nominal=0;
        if($firstDate != null){
            $query_first = ItemCogs::where('date', '<', $firstDate)
            ->where('item_id',$request->item)
            ->where('place_id',$request->plant)
         
            ->first();
            if($query_first){
                $last_nominal=number_format($query_first->qty_final,3,',','.');
            }
            
        }
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
        $response =[
            'status'=>200,
            'message'  =>$array_filter,
            'latest'   =>$last_nominal,
            'uomunit'  =>$uom_unit,
            'time'  => " Waktu proses : ".$execution_time." detik"
        ];
        return response()->json($response);
    }

    public function export(Request $request){
		$plant = $request->plant ? $request->plant:'';
        $item = $request->item ? $request->item:'';
        $start_date = $request->start_date ? $request->start_date:'';
        $finish_date = $request->finish_date ? $request->finish_date:'';

		return Excel::download(new ExportStockMovement($plant,$item,$start_date,$finish_date), 'stock_movement'.uniqid().'.xlsx');
    }
}

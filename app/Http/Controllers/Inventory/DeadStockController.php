<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportDeadStock;
use App\Http\Controllers\Controller;
use App\Models\ItemCogs;
use App\Models\Place;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DeadStockController extends Controller
{
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Dead Stock',
            'content'   => 'admin.inventory.dead_stock',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        $query_data = ItemCogs::whereIn('id', function ($query) use ($request) {
            $query->selectRaw('MAX(id)')
                ->from('item_cogs')
                ->where('date', '<=', $request->date)
                ->where('place_id',$request->plant)
                ->where('warehouse_id',$request->warehouse)
                ->groupBy('item_id');
        })
        ->get();
        $array_filter=[];
        
        foreach($query_data as $row){
           
            $date = Carbon::parse($row->date);
            $dateDifference = $date->diffInDays($request->date);
               
                if ($dateDifference >= $request->hari) {
                    $array_filter[]=[
                        'item'=>$row->item->name,
                        'keterangan'=>$row->lookable->code.'-'.$row->lookable->name,
                        'date'=>$row->date,
                        'lamahari'=>$dateDifference,
                    ];
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
        $date = $request->date ? $request->date:'';
        $hari= $request->hari ? $request->hari:'';
        info($request);
		return Excel::download(new ExportDeadStock($plant,$warehouse,$hari,$date), 'dead_stock'.uniqid().'.xlsx');
    }
}

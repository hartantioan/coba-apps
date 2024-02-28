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
use App\Models\ItemGroup;
use App\Models\User;

class MinimumStockController extends Controller
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
            'title'     => 'Stok item Yang Minim',
            'content'   => 'admin.inventory.minimum_stock',
            'group'     =>  $itemGroup,
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'warehouse'     => Warehouse::where('status','1')->whereIn('id',$this->datawarehouses)->get(),
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        
        // $query_data = Item::where(function($querys) use ( $request) {
        //     if($request->item_id){
        //         $querys->where('id',$request->item_id);
        //     }
    
        // })
        // ->get();
       
        $query_data = ItemStock::where(function($querys) use ( $request) {
            $querys->whereHas('item',function($query){
                $query->where('status',1);
            });
            if($request->filter_group){
                
                $querys->whereHas('item', function ($query) use ($request) {
                    $query->whereIn('item_group_id', $request->filter_group);
                });
            }
            if($request->item_id != 'null'){
           
                $querys->where('item_id',$request->item_id);
            }
            if($request->warehouse != 'all'){
                $querys->where('warehouse_id',$request->warehouse);
            }
            if($request->plant != 'all'){
                $querys->where('place_id',$request->plant);
            }
        })
        ->get();
      
        $array_filter=[];
        
        foreach($query_data as $row){
          
            $data_tempura = [
                'item_id' => CustomHelper::encrypt($row->item->code),
                'plant' => $row->place->code,
                'gudang' => $row->warehouse->name ?? '',
                'kode' => $row->item->code,
                'item' => $row->item->name,
                'minimum'=>number_format($row->item->min_stock),
                'needed'=>number_format($row->item->min_stock-$row->qty),
                'maximum'=>number_format($row->item->max_stock),
                'final'=>number_format($row->qty,3,',','.'),
                'satuan'=>$row->item->uomUnit->code,
                'perlu' =>1,
            ];
            
            if($row->qty < $row->item->min_stock){
                
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
        $plant = $request->plant ? $request->plant:'';
        $warehouse = $request->warehouse ? $request->warehouse:'';
        $item_group_id = $request->item_group_id ? $request->item_group_id:'';
      
		return Excel::download(new ExportMinimumStock($item_id,$warehouse,$plant,$item_group_id), 'minimum_stock'.uniqid().'.xlsx');
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

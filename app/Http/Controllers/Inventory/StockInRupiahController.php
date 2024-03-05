<?php

namespace App\Http\Controllers\Inventory;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportStockInRupiah;
use App\Http\Controllers\Controller;
use App\Models\ItemCogs;
use App\Models\ItemGroup;
use App\Models\Place;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class StockInRupiahController extends Controller
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
            'title'     => 'Stok Dalam Rupiah',
            'group'     =>  $itemGroup,
            'content'   => 'admin.inventory.stock_in_rupiah',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }
    
    public function filter(Request $request){
        $start_time = microtime(true);
        DB::statement("SET SQL_MODE=''");
        if($request->type == 'final'){
            $perlu = 0 ;
            $query_data = ItemCogs::whereIn('id', function ($query) use ($request) {            
                $query->selectRaw('MAX(id)')
                    ->from('item_cogs')
                    ->where('date', '<=', $request->finish_date)
                    ->groupBy('item_id');
            })
            ->where(function($query) use ( $request) {
                $query->whereHas('item',function($query) use($request){
                    $query->where('status',1);
                });
                if($request->finish_date) {
                    $query->whereDate('date','<=', $request->finish_date);
                }
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
    
                if($request->filter_group){
                   
                    $query->whereHas('item',function($query) use($request){
                        $query->whereIn('item_group_id', $request->filter_group);
                    });
                }
            })
            ->orderBy('date', 'desc')
            ->get();
        }else{
            $perlu = 1;
            $query_data = ItemCogs::where(function($query) use ( $request) {
                $query->whereHas('item',function($query) use($request){
                    $query->where('status',1);
                });
                if($request->start_date && $request->finish_date) {
                    $query->whereDate('date', '>=', $request->start_date)
                        ->whereDate('date', '<=', $request->finish_date);
                } else if($request->start_date) {
                    $query->whereDate('date','>=', $request->start_date);
                } else if($request->finish_date) {
                    $query->whereDate('date','<=', $request->finish_date);
                }
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
    
                if($request->filter_group){
                    $query->whereHas('item',function($query) use($request){
                        $query->whereIn('item_group_id', $request->filter_group);
                    });
                }
            })
            ->orderBy('item_id')
            ->get();
        }
      
        $cum_qty = 0;
        $cum_val = 0 ;
        $array_filter=[];
        $firstDate = null;
        $uom_unit = null;
        $previousId = null;
        $array_last_item = [];
        foreach($query_data as $row){
            
            if($row->type=='IN'){
                $priceNow = $row->price_in;
                $cum_qty=$row->qty_in;
                $cum_val=$row->total_in;
            }else{
                $priceNow = $row->price_out;
                $cum_qty=$row->qty_out * -1;
                $cum_val=$row->total_out * -1;
            }
            
            $data_tempura = [
                'plant' => $row->place->code,
                'warehouse' => $row->warehouse->name,
                'item' => $row->item->name,
                'satuan' => $row->item->uomUnit->code,
                'kode' => $row->item->code,
                'final'=>number_format($priceNow,2,',','.'),
                'total'=>$perlu == 0 ? '-' : number_format($cum_val,2,',','.'),
                'qty' => $perlu == 0 ? '-' : number_format($cum_qty, 3, ',', '.'),
                'date' =>  date('d/m/Y',strtotime($row->date)),
                'document' => $row->lookable->code,
                'cum_qty' => number_format($row->qty_final,3,',','.'),
                'cum_val' => number_format($row->total_final,2,',','.'),
            ];
            $array_filter[]=$data_tempura;
            
            
            if ($row->item_id !== $previousId) {
              
                $query_first =
                ItemCogs::where(function($query) use ( $request,$row) {
                    $query->where('item_id',$row->item_id)
                    ->where('date', '<', $row->date);
                    
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
                })
                ->orderBy('id', 'desc')
                ->orderBy('date', 'desc') // Order by 'date' column in descending order
                ->first();
                
                $array_last_item[] = [
                    'date'         => $query_first ? date('d/m/Y', strtotime($query_first->date)) : null,
                    'last_nominal' => $query_first ? number_format($query_first->total_final, 2, ',', '.') : 0,
                    'item'         => $row->item->name,
                    'satuan'       => $row->item->uomUnit->code,
                    'kode'         => $row->item->code,
                    'last_qty'     => $query_first ? number_format($query_first->qty_final, 2, ',', '.') : 0,
                ];


            }
            $previousId = $row->item_id;
            
            if($uom_unit ===null){
                $uom_unit = $row->item->uomUnit->code;
            }
        }
       
        
        $end_time = microtime(true);
       
        $execution_time = ($end_time - $start_time);
        $response =[
            'status'=>200,
            'message'       => $array_filter,
            'latest'        => $array_last_item,
            'perlu'         => $perlu,
            'time'          => " Waktu proses : ".$execution_time." detik"
        ];
        return response()->json($response);
    }

    public function export(Request $request){
		$plant = $request->plant ? $request->plant:'';
        $warehouse = $request->warehouse?$request->warehouse:'';
        $item = $request->item ? $request->item:'';
        $start_date = $request->start_date ? $request->start_date:'';
        $finish_date = $request->finish_date ? $request->finish_date:'';
        $group = $request->group ? $request->group:'';
        $type = $request->type ? $request->type:'';

		return Excel::download(new ExportStockInRupiah($plant,$item,$warehouse,$start_date,$finish_date,$type,$group), 'stock_in_rupiah'.uniqid().'.xlsx');
    }
}

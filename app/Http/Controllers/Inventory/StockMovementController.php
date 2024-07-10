<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportStockMovement;
use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\Place;
use App\Models\ItemGroup;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Undefined;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class StockMovementController extends Controller
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
            'title'     => 'Pergerakan Stok',
            'group'     =>  $itemGroup,
            'content'   => 'admin.inventory.stock_movement',
            'place'     =>  Place::where('status','1')->get(),
            'item'      =>  Item::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){

        $start_time = microtime(true);
        DB::statement("SET SQL_MODE=''");
        if($request->type == 'final'){
            $perlu = 0 ;
            $query_data = ItemCogs::whereRaw("id IN (SELECT MAX(id) FROM item_cogs WHERE deleted_at IS NULL AND date <= '".$request->finish_date."' GROUP BY item_id)")
            ->where(function($query) use ( $request) {
                $query->whereHas('item',function($query){
                    $query->whereIn('status',['1','2']);
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
                $query->whereHas('item',function($query){
                    $query->whereIn('status',['1','2']);
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
            ->orderBy('id')
            ->orderBy('date')
            ->get();
        }
     
       // Initialize the previous ID variable
        $cum_qty = 0;
        $cum_val = 0 ;
        $array_filter=[];
        $firstDate = null;
        $uom_unit = null;
        $previousId = null;
        $array_last_item = [];
        $array_first_item = [];
        foreach($query_data as $row){
            
            if($row->type=='IN'){
                $cum_qty=$row->qty_in;
                $cum_val=$row->total_in;
            }else{
                $cum_qty=$row->qty_out * -1;
                $cum_val=$row->total_out * -1;
            }
            
            $data_tempura = [
                'item_id'      => $row->item->id,
                'perlu'        => 0,
                'item_id'      => $row->item->id,
                'plant' => $row->place->code,
                'warehouse' => $row->warehouse->name,
                'item' => $row->item->name,
                'satuan' => $row->item->uomUnit->code,
                'kode' => $row->item->code,
                'final'=>number_format($row->price_final,2,',','.'),
                'total'=>number_format($cum_val,2,',','.'),
                'qty' => $perlu == 0 ? '-' : CustomHelper::formatConditionalQty($cum_qty),
                'date' =>  date('d/m/Y',strtotime($row->date)),
                'document' => $row->lookable->code,
                'cum_qty' => CustomHelper::formatConditionalQty($row->qty_final),
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
                    'perlu'        => 1,
                    'item_id'      => $row->item->id,
                    'id'           => $query_first->id ?? null, 
                    'date'         => $query_first ? date('d/m/Y', strtotime($query_first->date)) : null,
                    'last_nominal' => $query_first ? number_format($query_first->total_final, 2, ',', '.') : 0,
                    'item'         => $row->item->name,
                    'satuan'       => $row->item->uomUnit->code,
                    'kode'         => $row->item->code,
                    'last_qty'     => $query_first ? CustomHelper::formatConditionalQty($query_first->qty_final) : 0,
                ];


            }
            $previousId = $row->item_id;
            
            if($uom_unit ===null){
                $uom_unit = $row->item->uomUnit->code;
            }
        }
        
        if($request->type != 'final'){
            if(!$request->item_id){
                $query_no = ItemCogs::whereIn('id', function ($query) use ($request) {            
                    $query->selectRaw('MAX(id)')
                        ->from('item_cogs')
                        ->where('date', '<=', $request->finish_date)
                        ->groupBy('item_id');
                })
                ->where(function($query) use ( $request,$array_last_item) {
                    $query->whereHas('item',function($query) use($request){
                        $query->whereIn('status',['1','2']);
                    });
                    if($request->finish_date) {
                        $query->whereDate('date','<=', $request->finish_date);
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
                    $array_last_item = collect($array_last_item);
                    $excludeIds = $array_last_item->pluck('item_id')->filter()->toArray();
                    
                    if (!empty($excludeIds)) {
                       
                        $query->whereNotIn('item_id', $excludeIds);
                    }
                })
                ->orderBy('id', 'desc')
                ->orderBy('date', 'desc')
                ->get();
            }else{
                $query_no=[];
                $first = ItemCogs::where(function($query) use ( $request,$array_last_item) {
                    $query->whereHas('item',function($query) use($request){
                        $query->whereIn('status',['1','2'])->where('id',$request->item_id);
                    });
                    if($request->finish_date) {
                        $query->whereDate('date','<=', $request->finish_date);
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
                    $array_last_item = collect($array_last_item);
                    $excludeIds = $array_last_item->pluck('item_id')->filter()->toArray();
                    
                    if (!empty($excludeIds)) {
                       
                        $query->whereNotIn('item_id', $excludeIds);
                    }
                })
                ->orderBy('id', 'desc')
                ->orderBy('date', 'desc')
                ->first();
                $query_no[]=$first;
            }
            foreach($query_no as $row_tidak_ada){
    
                if($row_tidak_ada->qty_final > 0){
                    $array_first_item[] = [
                        'perlu'        => 1,
                        'item_id'      => $row_tidak_ada->item->id,
                        'id'           => $row_tidak_ada->id, 
                        'date'         => $row_tidak_ada ? date('d/m/Y', strtotime($row_tidak_ada->date)) : null,
                        'last_nominal' => $row_tidak_ada ? number_format($row_tidak_ada->total_final, 2, ',', '.') : 0,
                        'item'         => $row_tidak_ada->item->name,
                        'satuan'       => $row_tidak_ada->item->uomUnit->code,
                        'kode'         => $row_tidak_ada->item->code,
                        'last_qty'     => $row_tidak_ada ? CustomHelper::formatConditionalQty($row_tidak_ada->qty_final) : 0,
                    ]; 
                } 
            }
        }
        $combinedArray = [];
        // Merge $array_filter into $combinedArray
        foreach ($array_filter as $item) {
            $combinedArray[] = $item;
        }

        // Merge $array_last_item into $combinedArray
        foreach ($array_last_item as $item) {
            $combinedArray[] = $item;
        }

        // Merge $array_first_item into $combinedArray
        foreach ($array_first_item as $item) {
            $combinedArray[] = $item;
        }
        usort($combinedArray, function ($a, $b) {
            // First, sort by 'kode' in ascending order
            $kodeComparison = strcmp($a['kode'], $b['kode']);
            
            if ($kodeComparison !== 0) {
                return $kodeComparison;
            }

            // If 'kode' is the same, prioritize 'perlu' in descending order
            return $b['perlu'] - $a['perlu'];
        });
        if($request->type == 'final'){
            $combinedArray=$array_filter;
        }
        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);
        $response =[
            'status'=>200,
            'message'  =>$combinedArray,
            'latest'        => $array_last_item,
            'first'         => $array_first_item,
            'uomunit'  =>$uom_unit,
            'perlu'    => $perlu,
            'time'  => " Waktu proses : ".$execution_time." detik"
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

		return Excel::download(new ExportStockMovement($plant,$item,$warehouse,$start_date,$finish_date,$type,$group), 'stock_movement'.uniqid().'.xlsx');
    }
}

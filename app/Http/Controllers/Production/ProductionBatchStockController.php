<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exports\ExportProductionBatchStock;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\CustomHelper;
use App\Models\ItemCogs;
use Illuminate\Support\Facades\DB;
use App\Models\ItemGroup;
use App\Models\Place;
use App\Models\Warehouse;

class ProductionBatchStockController extends Controller
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
            'title'     => 'Stok Dalam Batch',
            'group'     =>  $itemGroup,
            'content'   => 'admin.production.production_batch_stock',
            'place'     =>  Place::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];
        
        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);
        $array_filter = [];
        
        DB::statement("SET SQL_MODE=''");
        
        $perlu = 0 ;
        $query_data = ItemCogs::whereRaw("id IN (SELECT MAX(id) FROM item_cogs WHERE deleted_at IS NULL AND date <= '".$request->finish_date."' GROUP BY item_id, production_batch_id, item_shading_id, area_id)")
        ->where(function($query) use ( $request) {
            $query->whereHas('item',function($query) use($request){
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

        })
        ->whereHas('productionBatch')
        ->orderBy('date', 'desc')
        ->get();
        
        $cum_qty = 0;
        $cum_val = 0 ;
        
        $firstDate = null;
        $uom_unit = null;
        $previousId = null;
        $array_last_item = [];
        $array_first_item = [];
        $all_total = 0;
    
        foreach($query_data as $row){
            
            if($row->type=='IN'){
                $priceNow = $row->price_in;
                $cum_qty=$row->qty_in;
                $cum_val=round($row->total_in,2);
            }else{
                $priceNow = $row->price_out;
                $cum_qty=$row->qty_out * -1;
                $cum_val=round($row->total_out,2) * -1;
            }
        
            
            $all_total += round($row->total_final,2);
            
            $data_tempura = [
                'item_id'      => $row->item->id,
                'perlu'        => 0,
                'plant' => $row->place->code,
                'warehouse' => $row->warehouse->name,
                'item' => $row->item->name,
                'satuan' => $row->item->uomUnit->code,
                'kode' => $row->item->code,
                'area' => $row->area->code ?? '-',
                'shading' => $row->itemShading->code ?? '-',
                'production_batch' => $row->productionBatch()->exists() ? $row->productionBatch->code : '-',
                'final'=>number_format($priceNow,2,',','.'),
                'total'=>$perlu == 0 ? '-' : number_format($cum_val,2,',','.'),
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
                    'area'         => $row->area->code ?? '-',
                    'production_batch' => '-',
                    'shading' => $row->shading->code ?? '-',
                    'kode'         => $row->item->code,
                    'last_qty'     => $query_first ? CustomHelper::formatConditionalQty($query_first->qty_final) : 0,
                ];


            }
            $previousId = $row->item_id;
            
            if($uom_unit ===null){
                $uom_unit = $row->item->uomUnit->code;
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
       
        $combinedArray=$array_filter;
 
      
        $end_time = microtime(true);
       
        $execution_time = ($end_time - $start_time);
       
        $response =[
            'status'=>200,
            'message'       => $combinedArray,
            'latest'        => $array_last_item,
            'first'         => $array_first_item,
            'perlu'         => $perlu,
            'time'          => " Waktu proses : ".$execution_time." detik",
            'alltotal'      => number_format($all_total,2,',','.'),
        ];
        return response()->json($response);
    }

    public function export(Request $request){
		$plant = $request->plant ? $request->plant:'';
        $warehouse = $request->warehouse?$request->warehouse:'';
        $item = $request->item ? $request->item:'';
        $finish_date = $request->finish_date ? $request->finish_date:'';

		return Excel::download(new ExportProductionBatchStock($plant,$item,$warehouse,$finish_date), 'production_batch_stock'.uniqid().'.xlsx');
    }
}

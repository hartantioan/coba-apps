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
use App\Jobs\StockMovementJob;

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
            $item = Item::where(function($query)use($request){
                if($request->item_id) {
                    $query->where('id',$request->item_id);
                }
                if($request->filter_group){
                    $query->whereIn('item_group_id', $request->filter_group);
                }
                if($request->warehouse != 'all'){
                    $query->whereHas('itemGroup',function($query)use($request){
                        $query->whereHas('itemGroupWarehouse',function($query)use($request){
                            $query->where('warehouse_id',$request->warehouse);
                        });
                    });
                }
            })->pluck('id');

            $arr = [];
            foreach($item as $row){
                $data = ItemCogs::where('date','<=',$request->finish_date)->where('item_id',$row)->where(function($query)use($request){
                    if($request->plant != 'all'){
                        $query->whereHas('place',function($query) use($request){
                            $query->where('id',$request->plant);
                        });
                    }
                })->orderByDesc('date')->orderByDesc('id')->first();
                if($data){
                    $arr[] = $data;
                }
            }

            $html = '<table class="bordered" style="font-size:10px;">
                        <thead id="t_head">
                            <tr>
                                <th class="center-align">No</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Kode</th>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Cumulative Qty.</th>
                            </tr>
                        </thead>
                        <tbody id="table_body">';
            $total = 0;
            foreach($arr as $key => $rowdata){
                $html .= '<tr>
                    <td>'.($key + 1).'</td>
                    <td>'.$rowdata->place->code.'</td>
                    <td>'.$rowdata->warehouse->name.'</td>
                    <td>'.$rowdata->item->code.'</td>
                    <td>'.$rowdata->item->name.'</td>
                    <td>'.$rowdata->item->uomUnit->code.'</td>
                    <td class="right-align">'.number_format($rowdata->qty_final,3,',','.').'</td>
                </tr>';
                $total += round($rowdata->qty_final,2);
            }

            $end_time = microtime(true);

            $execution_time = ($end_time - $start_time);

            $html .= '</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="right-align">Total</td>
                            <td class="right-align">'.number_format($total,3,',','.').'</td>
                        </tr>
                    </tfoot>
                    </table> Execution time : '.$execution_time;

            $response =[
                'status'    => 200,
                'html'      => $html,
            ];
            return response()->json($response);
        }else{
            $item = Item::where(function($query)use($request){
                if($request->item_id) {
                    $query->where('id',$request->item_id);
                }
                if($request->filter_group){
                    $query->whereIn('item_group_id', $request->filter_group);
                }
                if($request->warehouse != 'all'){
                    $query->whereHas('itemGroup',function($query)use($request){
                        $query->whereHas('itemGroupWarehouse',function($query)use($request){
                            $query->where('warehouse_id',$request->warehouse);
                        });
                    });
                }
            })->pluck('id');

            $html = '<table class="bordered" style="font-size:10px;">
                        <thead id="t_head">
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Tanggal.</th>
                                <th class="center-align">Plant.</th>
                                <th class="center-align">Gudang.</th>
                                <th class="center-align">Kode Item</th>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Requester</th>
                                <th class="center-align">Area</th>
                                <th class="center-align">Shading</th>
                                <th class="center-align">Batch Produksi</th>
                                <th class="center-align">No Dokumen</th>
                                <th class="center-align">Mutasi</th>
                                <th class="center-align">Balance</th>
                            </tr>
                        </thead>
                        <tbody id="movement_body">';

            $total = 0; 
            foreach($item as $row){
                $old_data = ItemCogs::where('date','<',$request->start_date)->where('item_id',$row)->where(function($query)use($request){
                    if($request->plant != 'all'){
                        $query->whereHas('place',function($query) use($request){
                            $query->where('id',$request->plant);
                        });
                    }
                    if($request->warehouse != 'all'){
                        $query->where('warehouse_id',$request->warehouse);
                    }
                })->orderByDesc('date')->orderByDesc('id')->first();
                if($old_data){
                    if($request->warehouse != 'all'){
                        $total += round($old_data->qtyByWarehouseBeforeDate($request->start_date),3);
                    }else{
                        $total += round($old_data->qty_final,3);
                    }
                    $html .= '<tr>
                        <td></td>
                        <td></td>
                        <td>'.$old_data->place->code.'</td>
                        <td>'.$old_data->warehouse->name.   '</td>
                        <td>'.$old_data->item->code.'</td>
                        <td>'.$old_data->item->name.'</td>
                        <td>'.$old_data->item->uomUnit->code.'</td>
                        <td></td>
                        <td>'.($old_data->area->name ?? '-').'</td>
                        <td>'.($old_data->itemShading->code ?? '-').'</td>
                        <td>'.($old_data->productionBatch->code ?? '-').'</td>
                        <td>-</td>
                        <td>-</td>
                        <td class="right-align">'.number_format($total,3,',','.').'</td>
                    </tr>';
                }
                $data = ItemCogs::where('date','>=',$request->start_date)->where('date','<=',$request->finish_date)->where('item_id',$row)->where(function($query)use($request){
                    if($request->plant != 'all'){
                        $query->whereHas('place',function($query) use($request){
                            $query->where('id',$request->plant);
                        });
                    }
                    if($request->warehouse != 'all'){
                        $query->where('warehouse_id',$request->warehouse);
                    }
                })->orderBy('date')->orderBy('id')->get();
                foreach($data as $key => $rowdata){
                    if($rowdata->type == 'IN'){
                        $total += round($rowdata->qty_in,3);
                    }else{
                        $total -= round($rowdata->qty_out,3);
                    }
                    $html .= '<tr>
                        <td>'.($key + 1).'</td>
                        <td>'.date('d/m/Y',strtotime($rowdata->date)).'</td>
                        <td>'.$rowdata->place->code.'</td>
                        <td>'.$rowdata->warehouse->name.   '</td>
                        <td>'.$rowdata->item->code.'</td>
                        <td>'.$rowdata->item->name.'</td>
                        <td>'.$rowdata->item->uomUnit->code.'</td>
                        <td>'.$rowdata->getRequester().'</td>
                        <td>'.($rowdata->area->name ?? '-').'</td>
                        <td>'.($rowdata->itemShading->code ?? '-').'</td>
                        <td>'.($rowdata->productionBatch->code ?? '-').'</td>
                        <td>'.$rowdata->lookable->code.'</td>
                        <td class="right-align">'.($rowdata->type == 'IN' ? number_format($rowdata->qty_in,3,',','.') : '-'.number_format($rowdata->qty_out,3,',','.')).'</td>
                        <td class="right-align">'.number_format($total,3,',','.').'</td>
                    </tr>';
                }
            }

            $end_time = microtime(true);

            $execution_time = ($end_time - $start_time);

            $html .= '</tbody></table> Execution time : '.$execution_time;

            $response =[
                'status'    => 200,
                'html'      => $html,
            ];
            return response()->json($response);
        }
    }

    public function export(Request $request){
		$plant = $request->plant ? $request->plant:'';
        $warehouse = $request->warehouse?$request->warehouse:'';
        $item = $request->item ? $request->item:'';
        $start_date = $request->startdate ? $request->startdate:'';
        $finish_date = $request->finishdate ? $request->finishdate:'';
        $group = $request->group ? $request->group : [];
        $type = $request->type ? $request->type:'';
        $user_id = session('bo_id');
        StockMovementJob::dispatch($plant,$item,$warehouse,$start_date,$finish_date,$type,$group, $user_id);

        return response()->json(['message' => 'Your export is being processed. Anda akan diberi notifikasi apabila report anda telah selesai']);

		// return Excel::download(new ExportStockMovement($plant,$item,$warehouse,$start_date,$finish_date,$type,$group), 'stock_movement'.uniqid().'.xlsx');
    }
}

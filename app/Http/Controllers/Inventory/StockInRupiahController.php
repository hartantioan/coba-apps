<?php

namespace App\Http\Controllers\Inventory;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportStockInRupiah;
use App\Http\Controllers\Controller;
use App\Models\ItemCogs;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\Place;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Jobs\StockInRupiahJob;

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
        $array_filter = [];

        DB::statement("SET SQL_MODE=''");
        if($request->type == 'final'){
            $perlu = 0 ;
            $item = Item::where(function($query)use($request){
                if($request->item_id) {
                    $query->where('id',$request->item_id);
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
                        <th class="center-align">No</th>
                        <th class="center-align">Plant</th>
                        <th class="center-align">Gudang</th>
                        <th class="center-align">Kode</th>
                        <th class="center-align">Nama Item</th>
                        <th class="center-align">Satuan</th>
                        <th class="center-align">Cumulative Qty.</th>
                        <th class="center-align">Cumulative Value</th>
                    </tr>
                </thead>
                <tbody id="table_body">';

            $totalNominal = 0;
            $totalQty = 0;
            foreach($item as $key => $row){
                $data = DB::table('item_cogs')->select('item_cogs.id AS id','item_cogs.qty_final AS qty_final','item_cogs.total_final AS total_final','places.code AS place_code','warehouses.name AS warehouse_name','items.code AS item_code','items.name AS item_name','units.code AS uom_unit','areas.code AS area_code','item_shadings.code AS item_shading')
                ->where('item_cogs.date','<=',$request->finish_date)->where('item_cogs.item_id',$row)->where(function($query)use($request){
                    if($request->plant != 'all'){
                        $query->where('item_cogs.place_id',$request->plant);
                    }
                    /* if($request->warehouse != 'all'){
                        $query->where('item_cogs.warehouse_id',$request->warehouse);
                    } */
                })
                ->whereNull('item_cogs.deleted_at')
                ->leftJoin('places', 'places.id', '=', 'item_cogs.place_id')
                ->leftJoin('warehouses', 'warehouses.id', '=', 'item_cogs.warehouse_id')
                ->leftJoin('items', 'items.id', '=', 'item_cogs.item_id')
                ->leftJoin('units', 'units.id', '=', 'items.uom_unit')
                ->leftJoin('areas', 'areas.id', '=', 'item_cogs.area_id')
                ->leftJoin('item_shadings', 'item_shadings.id', '=', 'item_cogs.item_shading_id')
                ->orderByDesc('item_cogs.date')->orderByDesc('item_cogs.id')->first();
                if($data){
                    /* if($request->warehouse != 'all'){
                        $cogs = ItemCogs::find($data->id);
                        $totalQty += round($cogs->qtyByWarehouseIncludeDate($request->finish_date),3);
                        $totalNominal += round($cogs->totalByWarehouseIncludeDate($request->finish_date),2);
                    }else{ */
                        $totalQty += round($data->qty_final,3);
                        $totalNominal += round($data->total_final,2);
                    //}
                    $html .= '<tr>
                        <td>'.($key + 1).'</td>
                        <td>'.$data->place_code.'</td>
                        <td>'.$data->warehouse_name.'</td>
                        <td>'.$data->item_code.'</td>
                        <td>'.$data->item_name.'</td>
                        <td>'.$data->uom_unit.'</td>
                        <td class="right-align">'.number_format($totalQty,3,',','.').'</td>
                        <td class="right-align">'.number_format($totalNominal,2,',','.').'</td>
                    </tr>';
                }
            }

            $end_time = microtime(true);

            $execution_time = ($end_time - $start_time);

            $html .= '</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" class="right-align">Total</td>
                            <td class="right-align">'.number_format($totalNominal,2,',','.').'</td>
                        </tr>
                    </tfoot>
                    </table> Execution time : '.$execution_time;

            $response =[
                'status'    => 200,
                'html'      => $html,
            ];
            return response()->json($response);
        }else{
            $perlu = 0 ;
            $item = Item::where(function($query)use($request){
                if($request->item_id) {
                    $query->where('id',$request->item_id);
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
                                <th class="center-align">Tanggal</th>
                                <th class="center-align">Plant</th>
                                <th class="center-align">Gudang</th>
                                <th class="center-align">Kode Item</th>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">Satuan</th>
                                <th class="center-align">Area</th>
                                <th class="center-align">Shading</th>
                                <th class="center-align">Batch Produksi</th>
                                <th class="center-align">No. Dokumen</th>
                                <th class="center-align">Qty</th>
                                <th class="center-align">Harga</th>
                                <th class="center-align">Total</th>
                                <th class="center-align">Cumulative Qty.</th>
                                <th class="center-align">Cumulative Value</th>
                            </tr>
                        </thead>
                        <tbody id="table_body">';

            foreach($item as $key => $row){
                $totalQty = 0;
                $totalNominal = 0;
                $old_data = DB::table('item_cogs')->select('item_cogs.id AS id','item_cogs.qty_final AS qty_final','item_cogs.total_final AS total_final','places.code AS place_code','warehouses.name AS warehouse_name','items.code AS item_code','items.name AS item_name','units.code AS uom_unit','areas.code AS area_code','item_shadings.code AS item_shading')
                ->where('item_cogs.date','<',$request->start_date)->where('item_cogs.item_id',$row)->where(function($query)use($request){
                    if($request->plant != 'all'){
                        $query->where('item_cogs.place_id',$request->plant);
                    }
                    /* if($request->warehouse != 'all'){
                        $query->where('item_cogs.warehouse_id',$request->warehouse);
                    } */
                })
                ->whereNull('item_cogs.deleted_at')
                ->leftJoin('places', 'places.id', '=', 'item_cogs.place_id')
                ->leftJoin('warehouses', 'warehouses.id', '=', 'item_cogs.warehouse_id')
                ->leftJoin('items', 'items.id', '=', 'item_cogs.item_id')
                ->leftJoin('units', 'units.id', '=', 'items.uom_unit')
                ->leftJoin('areas', 'areas.id', '=', 'item_cogs.area_id')
                ->leftJoin('item_shadings', 'item_shadings.id', '=', 'item_cogs.item_shading_id')
                ->orderByDesc('item_cogs.date')->orderByDesc('item_cogs.id')->first();
                if($old_data){
                    /* if($request->warehouse != 'all'){
                        $cogs = ItemCogs::find($old_data->id);
                        $totalQty += round($cogs->qtyByWarehouseBeforeDate($request->start_date),3);
                        $totalNominal += round($cogs->totalByWarehouseBeforeDate($request->start_date),2);
                    }else{ */
                        $totalQty += round($old_data->qty_final,3);
                        $totalNominal += round($old_data->total_final,2);
                    //}
                    $no = 1;
                    $html .= '<tr>
                        <td>'.$no.'</td>
                        <td>-</td>
                        <td>'.$old_data->place_code.'</td>
                        <td>'.$old_data->warehouse_name.'</td>
                        <td>'.$old_data->item_code.'</td>
                        <td>'.$old_data->item_name.'</td>
                        <td>'.$old_data->uom_unit.'</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td colspan="2">Saldo Sebelumnya</td>
                        <td class="right-align">'.number_format($totalQty,3,',','.').'</td>
                        <td class="right-align">'.number_format($totalNominal,2,',','.').'</td>
                    </tr>';
                }

                $data = ItemCogs::where('date','>=',$request->start_date)->where('date','<=',$request->finish_date)->where('item_id',$row)->where(function($query)use($request){
                    if($request->plant != 'all'){
                        $query->where('place_id',$request->plant);
                    }
                    /* if($request->warehouse != 'all'){
                        $query->where('warehouse_id',$request->warehouse);
                    } */
                })->orderBy('date')->orderBy('id')->get();

                foreach($data as $key1 => $rowdata){
                    $no++;
                    if($rowdata->type == 'IN'){
                        $price = $rowdata->qty_in > 0 ? round($rowdata->total_in / $rowdata->qty_in,2) : 0;
                        $totalQty += round($rowdata->qty_in,3);
                        $totalNominal += round($rowdata->total_in,2);
                    }else{
                        $price = $rowdata->qty_out > 0 ? round($rowdata->total_out / $rowdata->qty_out,2) : 0;
                        $totalQty -= round($rowdata->qty_out,3);
                        $totalNominal -= round($rowdata->total_out,2);
                    }
                    $html .= '<tr>
                        <td>'.$no.'</td>
                        <td>'.date('d/m/Y',strtotime($rowdata->date)).'</td>
                        <td>'.$rowdata->place->code.'</td>
                        <td>'.$rowdata->warehouse->name.'</td>
                        <td>'.$rowdata->item->code.'</td>
                        <td>'.$rowdata->item->name.'</td>
                        <td>'.$rowdata->item->uomUnit->code.'</td>
                        <td>'.($rowdata->area->code ?? '-').'</td>
                        <td>'.($rowdata->itemShading->code ?? '-').'</td>
                        <td>'.($rowdata->productionBatch->code ?? '-').'</td>
                        <td>'.$rowdata->lookable->code.'</td>
                        <td class="right-align">'.($rowdata->type == 'IN' ? number_format($rowdata->qty_in,3,',','.') : number_format(-1 * $rowdata->qty_out,3,',','.')).'</td>
                        <td class="right-align">'.number_format($price,3,',','.').'</td>
                        <td class="right-align">'.($rowdata->type == 'IN' ? number_format($rowdata->total_in,3,',','.') : number_format(-1 * $rowdata->total_out,3,',','.')).'</td>
                        <td class="right-align">'.number_format($totalQty,3,',','.').'</td>
                        <td class="right-align">'.number_format($totalNominal,2,',','.').'</td>
                    </tr>';
                }
            }

            $end_time = microtime(true);

            $execution_time = ($end_time - $start_time);

            $html .= '</tbody>
                    <tfoot>
                        <tr>
                            <td colspan="15" class="right-align">Total</td>
                            <td class="right-align">'.number_format($totalNominal,2,',','.').'</td>
                        </tr>
                    </tfoot>
                    </table> Execution time : '.$execution_time;

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
        $start_date = $request->start_date ? $request->start_date:'';
        $finish_date = $request->finish_date ? $request->finish_date:'';
        $group = $request->group ? $request->group : [];
        $type = $request->type ? $request->type:'';
        $user_id = session('bo_id');

        StockInRupiahJob::dispatch($plant,$item,$warehouse,$start_date,$finish_date,$type,$group, $user_id);

        return response()->json(['message' => 'Your export is being processed. Anda akan diberi notifikasi apabila report anda telah selesai']);
		// return Excel::download(new ExportStockInRupiah($plant,$item,$warehouse,$start_date,$finish_date,$type,$group), 'stock_in_rupiah'.uniqid().'.xlsx');
    }
}

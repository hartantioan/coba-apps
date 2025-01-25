<?php

namespace App\Http\Controllers\Accounting;


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
use App\Jobs\StockMovementShadingJob;
use App\Models\ItemShading;

// use App\Jobs\StockMovementJob;

class ReportStockMovementPerShadingController extends Controller
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
            'title'     => 'Pergerakan Stok By Shading',
            'group'     =>  $itemGroup,
            'content'   => 'admin.accounting.stock_movement_shading',
            'place'     =>  Place::where('status','1')->get(),
            'item'      =>  Item::where('status','1')->get(),
            'warehouse' =>  Warehouse::where('status',1)->get()
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){

        $start_time = microtime(true);

        if($request->shading_id){
            $shading = ItemShading::find($request->shading_id);
        }else{
            $shading = ItemShading::where('item_id',$request->item_id)->get();
        }

        $html = '';

        $place = Place::find($request->plant);

        if($request->type == 'final'){

            $html .= '<table class="bordered">
                <thead>
                <tr>
                    <th class="center-align">No.</th>
                    <th class="center-align">Plant</th>
                    <th class="center-align">Kode Item</th>
                    <th class="center-align">Nama Item</th>
                    <th class="center-align">Satuan</th>
                    <th class="center-align">Shading</th>
                    <th class="center-align">Batch</th>
                    <th class="center-align">Balance Qty</th>
                    <th class="center-align">Balance Nominal</th>
                </tr><thead><tbody>';

            if($request->shading_id){
                if($shading){
                    $data = ItemCogs::where('item_shading_id',$shading->id)->where(function($query)use($request){
                        $query->where('date','<=',$request->finish_date);
                        if($request->plant){
                            $query->where('place_id',$request->plant);
                        }
                    })->orderByDesc('date')->orderByDesc('id')->first();
                    $qty = 0;
                    $total = 0;
                    if($data){
                        $qty = $data->totalByShadingBeforeIncludeDate();
                        $total = $data->totalNominalByShadingBeforeIncludeDate();
                    }
                    $html .= '<tr>
                        <td class="center-align">1.</td>
                        <td class="center-align">'.$place->code.'</td>
                        <td class="center-align">'.$shading->item->code.'</td>
                        <td class="center-align">'.$shading->item->name.'</td>
                        <td class="center-align">'.$shading->item->uomUnit->code.'</td>
                        <td class="center-align">'.$shading->code.'</td>
                        <td class="center-align">-</td>
                        <td class="right-align">'.CustomHelper::formatConditionalQty($qty).'</td>
                        <td class="right-align">'.CustomHelper::formatConditionalQty($total).'</td>
                    </tr>';   
                }
            }elseif($request->batch_id){
                $data = ItemCogs::where('production_batch_id',$request->batch_id)->where(function($query)use($request){
                    $query->where('date','<=',$request->finish_date);
                    if($request->plant){
                        $query->where('place_id',$request->plant);
                    }
                })->orderByDesc('date')->orderByDesc('id')->first();
                $qty = 0;
                $total = 0;
                if($data){
                    $qty = $data->totalByBatchBeforeIncludeDate();
                    $total = $data->totalNominalByBatchBeforeIncludeDate();
                    $html .= '<tr>
                        <td class="center-align">1.</td>
                        <td class="center-align">'.$place->code.'</td>
                        <td class="center-align">'.$data->item->code.'</td>
                        <td class="center-align">'.$data->item->name.'</td>
                        <td class="center-align">'.$data->item->uomUnit->code.'</td>
                        <td class="center-align">'.$data->itemShading->code.'</td>
                        <td class="center-align">'.$data->productionBatch->code.'</td>
                        <td class="right-align">'.CustomHelper::formatConditionalQty($qty).'</td>
                        <td class="right-align">'.CustomHelper::formatConditionalQty($total).'</td>
                    </tr>';
                }
                
            }else{
                if(count($shading) > 0){
                    foreach($shading as $key => $rowshading){
                        $data = ItemCogs::where('item_shading_id',$rowshading->id)->where(function($query)use($request){
                            $query->where('date','<=',$request->finish_date);
                            if($request->plant){
                                $query->where('place_id',$request->plant);
                            }
                        })->orderByDesc('date')->orderByDesc('id')->first();
                        $qty = 0;
                        $total = 0;
                        if($data){
                            $qty = $data->totalByShadingBeforeIncludeDate();
                            $total = $data->totalNominalByShadingBeforeIncludeDate();
                        }
                        $html .= '<tr>
                            <td class="center-align">'.($key + 1).'</td>
                            <td class="center-align">'.$place->code.'</td>
                            <td class="center-align">'.$rowshading->item->code.'</td>
                            <td class="center-align">'.$rowshading->item->name.'</td>
                            <td class="center-align">'.$rowshading->item->uomUnit->code.'</td>
                            <td class="center-align">'.$rowshading->code.'</td>
                            <td class="center-align">-</td>
                            <td class="right-align">'.CustomHelper::formatConditionalQty($qty).'</td>
                            <td class="right-align">'.CustomHelper::formatConditionalQty($total).'</td>
                        </tr>';
                    }
                }
            }

        }else{

            $html .= '<table class="bordered">
                <thead>
                <tr>
                    <th class="center-align">No.</th>
                    <th class="center-align">Kode Item</th>
                    <th class="center-align">Nama Item</th>
                    <th class="center-align">Plant</th>
                    <th class="center-align">Shading</th>
                    <th class="center-align">Batch</th>
                    <th class="center-align">Area</th>
                    <th class="center-align">Satuan</th>
                    <th class="center-align">Tanggal</th>
                    <th class="center-align">Mutasi</th>
                    <th class="center-align">Balance</th>
                </tr><thead><tbody>';
            
            if($request->shading_id){
                if($shading){
                    $data = ItemCogs::where('item_shading_id',$shading->id)->where(function($query)use($request){
                        $query->where('date','<=',$request->finish_date)->where('date','>=',$request->start_date);
                        if($request->plant){
                            $query->where('place_id',$request->plant);
                        }
                    })->orderBy('date')->orderBy('id')->get();
                    $dataBefore = ItemCogs::where('item_shading_id',$shading->id)->where(function($query)use($request){
                        $query->where('date','<',$request->start_date);
                        if($request->plant){
                            $query->where('place_id',$request->plant);
                        }
                    })->orderByDesc('date')->orderByDesc('id')->first();
                    $balance = 0;
                    if($dataBefore){
                        $balance += $dataBefore->totalByShadingBeforeIncludeDate();
                    }
                    $html .= '<tr>
                            <td class="center-align"></td>
                            <td class="center-align">'.$shading->item->code.'</td>
                            <td class="center-align">'.$shading->item->name.'</td>
                            <td class="center-align">'.$place->code.'</td>
                            <td class="center-align" colspan="6">SALDO PERIODE SEBELUMNYA</td>
                            <td class="right-align">'.CustomHelper::formatConditionalQty($balance).'</td>
                        </tr>';
                    foreach($data as $key => $row){
                        $balance += $row->type == 'IN' ? $row->qty_in : -1 * $row->qty_out;
                        $html .= '<tr>
                            <td class="center-align">'.($key + 1).'</td>
                            <td class="center-align">'.$shading->item->code.'</td>
                            <td class="center-align">'.$shading->item->name.'</td>
                            <td class="center-align">'.$place->code.'</td>
                            <td class="center-align">'.$shading->code.'</td>
                            <td class="center-align">'.$row->productionBatch->code.'</td>
                            <td class="center-align">'.$row->area->code.'</td>
                            <td class="center-align">'.$row->item->uomUnit->code.'</td>
                            <td class="center-align">'.date('d/m/Y',strtotime($row->date)).'</td>
                            <td class="right-align">'.($row->type == 'IN' ? CustomHelper::formatConditionalQty($row->qty_in) : CustomHelper::formatConditionalQty(-1 * $row->qty_out)).'</td>
                            <td class="right-align">'.CustomHelper::formatConditionalQty($balance).'</td>
                        </tr>';
                    }
                }
            }elseif($request->batch_id){
                $data = ItemCogs::where('production_batch_id',$request->batch_id)->where(function($query)use($request){
                    $query->where('date','<=',$request->finish_date)->where('date','>=',$request->start_date);
                    if($request->plant){
                        $query->where('place_id',$request->plant);
                    }
                })->orderBy('date')->orderBy('id')->get();
                $dataBefore = ItemCogs::where('production_batch_id',$request->batch_id)->where(function($query)use($request){
                    $query->where('date','<',$request->start_date);
                    if($request->plant){
                        $query->where('place_id',$request->plant);
                    }
                })->orderByDesc('date')->orderByDesc('id')->first();
                $balance = 0;
                if($dataBefore){
                    $balance += $dataBefore->totalByBatchBeforeIncludeDate();
                }
                $html .= '<tr>
                        <td class="center-align"></td>
                        <td class="center-align">'.($dataBefore ? $dataBefore->item->code : '-').'</td>
                        <td class="center-align">'.($dataBefore ? $dataBefore->item->name : '-').'</td>
                        <td class="center-align">'.($dataBefore ? $dataBefore->itemShading->code : '-').'</td>
                        <td class="center-align" colspan="6">SALDO PERIODE SEBELUMNYA</td>
                        <td class="right-align">'.CustomHelper::formatConditionalQty($balance).'</td>
                    </tr>';
                foreach($data as $key => $row){
                    $balance += $row->type == 'IN' ? $row->qty_in : -1 * $row->qty_out;
                    $html .= '<tr>
                        <td class="center-align">'.($key + 1).'</td>
                        <td class="center-align">'.$row->item->code.'</td>
                        <td class="center-align">'.$row->item->name.'</td>
                        <td class="center-align">'.$place->code.'</td>
                        <td class="center-align">'.$row->itemShading->code.'</td>
                        <td class="center-align">'.$row->productionBatch->code.'</td>
                        <td class="center-align">'.$row->area->code.'</td>
                        <td class="center-align">'.$row->item->uomUnit->code.'</td>
                        <td class="center-align">'.date('d/m/Y',strtotime($row->date)).'</td>
                        <td class="right-align">'.($row->type == 'IN' ? CustomHelper::formatConditionalQty($row->qty_in) : CustomHelper::formatConditionalQty(-1 * $row->qty_out)).'</td>
                        <td class="right-align">'.CustomHelper::formatConditionalQty($balance).'</td>
                    </tr>';
                }
            }else{
                if(count($shading) > 0){
                    foreach($shading as $rowshading){
                        $data = ItemCogs::where('item_shading_id',$rowshading->id)->where(function($query)use($request){
                            $query->where('date','<=',$request->finish_date)->where('date','>=',$request->start_date);
                            if($request->plant){
                                $query->where('place_id',$request->plant);
                            }
                        })->orderBy('date')->orderBy('id')->get();
                        $dataBefore = ItemCogs::where('item_shading_id',$rowshading->id)->where(function($query)use($request){
                            $query->where('date','<',$request->start_date);
                            if($request->plant){
                                $query->where('place_id',$request->plant);
                            }
                        })->orderByDesc('date')->orderByDesc('id')->first();
                        $balance = 0;
                        if($dataBefore){
                            $balance += $dataBefore->totalByShadingBeforeIncludeDate();
                        }
                        $html .= '<tr class="gradient-45deg-red-pink">
                                <td class="center-align"></td>
                                <td class="center-align">'.$rowshading->item->code.'</td>
                                <td class="center-align">'.$rowshading->item->name.'</td>
                                <td class="center-align">'.$place->code.'</td>
                                <td class="center-align">'.$rowshading->code.'</td>
                                <td class="center-align" colspan="5">SALDO PERIODE SEBELUMNYA</td>
                                <td class="right-align">'.CustomHelper::formatConditionalQty($balance).'</td>
                            </tr>';
                        foreach($data as $key => $row){
                            $balance += $row->type == 'IN' ? $row->qty_in : -1 * $row->qty_out;
                            $html .= '<tr>
                                <td class="center-align">'.($key + 1).'</td>
                                <td class="center-align">'.$rowshading->item->code.'</td>
                                <td class="center-align">'.$rowshading->item->name.'</td>
                                <td class="center-align">'.$place->code.'</td>
                                <td class="center-align">'.$rowshading->code.'</td>
                                <td class="center-align">'.$row->productionBatch->code.'</td>
                                <td class="center-align">'.$row->area->code.'</td>
                                <td class="center-align">'.$row->item->uomUnit->code.'</td>
                                <td class="center-align">'.date('d/m/Y',strtotime($row->date)).'</td>
                                <td class="right-align">'.($row->type == 'IN' ? CustomHelper::formatConditionalQty($row->qty_in) : CustomHelper::formatConditionalQty(-1 * $row->qty_out)).'</td>
                                <td class="right-align">'.CustomHelper::formatConditionalQty($balance).'</td>
                            </tr>';
                        }
                    }
                }
            }

            $html .= '</tbody></table>';
        }

        $end_time = microtime(true);
        $execution_time = ($end_time - $start_time);

        $html .= " Waktu proses : ".$execution_time." detik";

        
        $response =[
            'status'        => 200,
            'html'          => $html
        ];
        return response()->json($response);
    }

    public function export(Request $request){
		$plant = $request->plant ? $request->plant:'';
        $warehouse = $request->warehouse?$request->warehouse:'';
        $item = $request->item ? $request->item:'';
        $start_date = $request->startdate ? $request->startdate:'';
        $finish_date = $request->finishdate ? $request->finishdate:'';
        $group = $request->group ? $request->group:'';
        $type = $request->type ? $request->type:'';
        $batch_id = $request->batch_id ? $request->batch_id:'';
        $shading_id = $request->shading_id ? $request->shading_id:'';
        $user_id = session('bo_id');
        StockMovementShadingJob::dispatch($plant,$item,$warehouse,$start_date,$finish_date,$type,$group, $user_id,$batch_id,$shading_id);

        return response()->json(['message' => 'Your export is being processed. Anda akan diberi notifikasi apabila report anda telah selesai']);

		// return Excel::download(new ExportStockMovement($plant,$item,$warehouse,$start_date,$finish_date,$type,$group), 'stock_movement'.uniqid().'.xlsx');
    }
}

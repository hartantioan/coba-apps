<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\ItemMove;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportItemMovementController extends Controller
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
            'content'   => 'admin.purchase.item_move',
            'item'      =>  Item::where('status','1')->get(),
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
            })->pluck('id');

            $arr = [];
            foreach($item as $row){
                $data = ItemMove::where('date','<=',$request->finish_date)->where('item_id',$row)->where(function($query)use($request){

                })->orderByDesc('date')->orderByDesc('id')->first();
                if($data){
                    $arr[] = $data;
                }
            }

            $html = '<table class="bordered" style="font-size:10px;">
                        <thead id="t_head">
                            <tr>
                                <th class="center-align">No</th>
                                <th class="center-align">Kode</th>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">Cumulative Qty.</th>
                            </tr>
                        </thead>
                        <tbody id="table_body">';
            $total = 0;
            foreach($arr as $key => $rowdata){
                $rowtotal = round($rowdata->qty_final,3);
                $total += $rowtotal;
                $html .= '<tr>
                    <td>'.($key + 1).'</td>
                    <td>'.$rowdata->item->code.'</td>
                    <td>'.$rowdata->item->name.'</td>
                    <td class="right-align">'.number_format($rowtotal,3,',','.').'</td>
                </tr>';
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
            })->pluck('id');

            $html = '<table class="bordered" style="font-size:10px;">
                        <thead id="t_head">
                            <tr>
                                <th class="center-align">No.</th>
                                <th class="center-align">Tanggal.</th>
                                <th class="center-align">Kode Item</th>
                                <th class="center-align">Nama Item</th>
                                <th class="center-align">No Dokumen</th>
                                <th class="center-align">Mutasi</th>
                                <th class="center-align">Balance</th>
                            </tr>
                        </thead>
                        <tbody id="movement_body">';

            foreach($item as $row){
                $total = 0;
                $old_data = ItemMove::where('date','<',$request->start_date)->where('item_id',$row)->where(function($query)use($request){

                })->orderByDesc('date')->orderByDesc('id')->first();
                if($old_data){
                    $total += round($old_data->qty_final,3);
                    $html .= '<tr>
                        <td></td>
                        <td></td>
                        <td>'.$old_data->item->code.'</td>
                        <td>'.$old_data->item->name.'</td>
                        <td colspan="2">Saldo Periode Sebelumnya</td>
                        <td class="right-align">'.number_format($total,3,',','.').'</td>
                    </tr>';
                }
                $data = ItemMove::where('date','>=',$request->start_date)->where('date','<=',$request->finish_date)->where('item_id',$row)->where(function($query)use($request){

                })->orderBy('date')->orderBy('id')->get();
                foreach($data as $key => $rowdata){
                    if($rowdata->type == 1){
                        $total += round($rowdata->qty_in,3);
                    }else{
                        $total -= round($rowdata->qty_out,3);
                    }
                    $html .= '<tr>
                        <td>'.($key + 1).'</td>
                        <td>'.date('d/m/Y',strtotime($rowdata->date)).'</td>
                        <td>'.$rowdata->item->code.'</td>
                        <td>'.$rowdata->item->name.'</td>
                        <td>'.$rowdata->lookable->code.'</td>
                        <td class="right-align">'.($rowdata->type == '1' ? number_format($rowdata->qty_in,3,',','.') : '-'.number_format($rowdata->qty_out,3,',','.')).'</td>
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

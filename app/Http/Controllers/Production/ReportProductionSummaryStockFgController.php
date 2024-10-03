<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Company;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;


use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportReportProductionSummaryStockFg;

class ReportProductionSummaryStockFgController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];

    }

    public function index(Request $request)
    {
        $parentSegment = request()->segment(2);

        $data = [
            'title'     => 'Report Summary Stock FG',
            'content'   => 'admin.production.production_summary_stock_fg',
            'shading'      => ItemShading::get(),
            'company'       => Company::where('status','1')->get(),
            'area'       => Area::where('status','1')->get(),
            'place'       => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
        $start_time = microtime(true);

        $query_data = ItemStock::where(function($querys) use ( $request) {
            $querys->whereHas('item',function($query){
                $query->where('status',1);
            });
            // if($request->item_shading_id != 'null'){

            //     $querys->where('item_shading_id',$request->item_shading_id);
            // }
            // if($request->production_batch_id != 'null'){

            //     $querys->where('production_batch_id',$request->production_batch_id);
            // }
            // if($request->area != 'all'){
            //     $querys->where('area_id',$request->area);
            // }
            // if($request->plant != 'all'){
            //     $querys->where('place_id',$request->plant);
            // }
        })
        ->join('items', 'item_stocks.item_id', '=', 'items.id')
        ->selectRaw('item_stocks.*, items.code, items.name, SUM(item_stocks.qty) as total_quantity')
        ->groupBy('item_stocks.item_id', 'items.code', 'items.name')
        ->get();

        info($query_data);
        $newData = [];

        // foreach($query_data as $index_data =>$row){
        //     foreach($row->marketingOrderDeliveryDetail as $row_detail){
        //         $newData[] = [
        //             'no'                => ($index_data+1),
        //             'no_document'       => $row->code,
        //             'status'          => $row->statusRaw(),
        //             'voider'          => $row->voidUser()->exists() ? $row->voidUser->name : '',
        //             'tgl_void'         => $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' ,
        //             'ket_void'               => $row->voidUser()->exists() ? $row->void_note : '' ,
        //             'deleter'              =>$row->deleteUser()->exists() ? $row->deleteUser->name : '',
        //             'tgl_delete'             => $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '',
        //             'ket_delete'               => $row->deleteUser()->exists() ? $row->delete_note : '',
        //             'doner'        => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
        //             'tgl_done'          => $row->doneUser ? $row->done_date : '',
        //             'ket_done'              => $row->doneUser ? $row->done_note : '' ,
        //             'nik'            => $row->user->employee_no,
        //             'user'           =>  $row->user->name,
        //             'post_date'              => date('d/m/Y',strtotime($row->post_date)),
        //             'status_kirim'          => $row->sendStatus(),
        //             'tgl_kirim'         => date('d/m/Y',strtotime($row->delivery_date)),
        //             'tipe_pengiriman'               => $row->deliveryType(),
        //             'ekspedisi'              => $row->account->name ?? '-',
        //             'pelanggan'             => $row->customer->name  ?? '-',
        //             'kode_item'               =>  $row_detail->item->code,
        //             'item'        =>    $row_detail->item->name,
        //             'plant'          => $row_detail->place->code,
        //             'qty_konversi'          => $row_detail->qty,
        //             'satuan_konversi'         => $row_detail->marketingOrderDetail->itemUnit->unit->code,
        //             'qty'               => round($row_detail->qty * $row_detail->marketingOrderDetail->qty_conversion,3),
        //             'unit'              => $row_detail->item->uomUnit->code,
        //             'note_internal'             => $row->note_internal,
        //             'note_external'               => $row->note_external,
        //             'note'        => $row_detail->note,
        //             'no_sj'          => $row->marketingOrderDeliveryProcess()->exists() ? $row->marketingOrderDeliveryProcess->code : '-',
        //         ];
        //     }

        // }

        $end_time = microtime(true);

        $execution_time = ($end_time - $start_time);

        $response =[
            'status'            => 200,
            'content'           => $newData,
            'execution_time'    => round($execution_time,5),
        ];

        return response()->json($response);
    }

    public function export(Request $request){

        $start_date = $request->start_date;
        $finish_date = $request->end_date;
		return Excel::download(new ExportReportProductionSummaryStockFg($start_date,$finish_date), 'summary_stock_fg'.uniqid().'.xlsx');
    }
}

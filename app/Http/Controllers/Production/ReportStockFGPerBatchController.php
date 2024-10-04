<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Company;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportReportStockFgPerBatch;
class ReportStockFGPerBatchController extends Controller
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
            'title'     => 'Report Summary Stock FG Per Batch',
            'content'   => 'admin.production.report_stock_fg_per_batch',
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
        })
        ->join('items', 'item_stocks.item_id', '=', 'items.id')
        ->selectRaw('item_stocks.*, items.code, items.name, SUM(item_stocks.qty) as total_quantity')
        ->groupBy('item_stocks.item_id', 'items.code', 'items.name')
        ->get();

        $newData = [];
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
		return Excel::download(new ExportReportStockFgPerBatch($start_date,$finish_date), 'stock_fg_per_batch'.uniqid().'.xlsx');
    }
}

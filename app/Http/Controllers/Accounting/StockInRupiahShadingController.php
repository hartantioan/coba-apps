<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportReportStockInRupiahAccounting;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Company;
use App\Models\ItemShading;
use App\Models\Place;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StockInRupiahShadingController extends Controller
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
            'title'     => 'Report Stock dalam Rupiah - Shading',
            'content'   => 'admin.accounting.report_stock_in_rupiah',
            'shading'      => ItemShading::get(),
            'company'       => Company::where('status','1')->get(),
            'area'       => Area::where('status','1')->get(),
            'place'       => Place::where('status','1')->get(),
            'warehouse'   => Warehouse::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function export(Request $request){
        $start_date = $request->start_date;
        $place_id = $request->place_id;
        $warehouse_id = $request->warehouse_id;
		return Excel::download(new ExportReportStockInRupiahAccounting($start_date,$place_id,$warehouse_id), 'stock_in_rupiah_shading'.uniqid().'.xlsx');
    }
}

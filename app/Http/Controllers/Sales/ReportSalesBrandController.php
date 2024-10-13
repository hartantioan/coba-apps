<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Company;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportReportSalesBrand;

class ReportSalesBrandController extends Controller
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
            'title'     => 'Report Sales',
            'content'   => 'admin.sales.report_sales_brand',
           
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filter(Request $request){
     
    }

    public function export(Request $request){
        $start_date = $request->start_date;
        $finish_date = $request->end_date;
		return Excel::download(new ExportReportSalesBrand($start_date,$finish_date), 'summary_sales_'.uniqid().'.xlsx');
    }
}

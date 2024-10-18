<?php

namespace App\Http\Controllers\Accounting;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Company;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\User;
use Illuminate\Http\Request;

use Maatwebsite\Excel\Facades\Excel;

use App\Exports\ExportReportAccountingSales;

class ReportAccountingSales extends Controller
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
            'title'     => 'Report Sales Akunting',
            'content'   => 'admin.accounting.report_accounting_sales',
            'shading'      => ItemShading::get(),
            'company'       => Company::where('status','1')->get(),
            'area'       => Area::where('status','1')->get(),
            'place'       => Place::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function export(Request $request){
        $start_date = $request->start_date;
        $finish_date = $request->end_date;
		return Excel::download(new ExportReportAccountingSales($start_date,$finish_date), 'delivery_on_the_way_report_'.uniqid().'.xlsx');
    }
}

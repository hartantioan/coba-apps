<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Area;
use App\Models\Company;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\Place;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportReportTrackingSO;


class ReportTrackingSalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $parentSegment = request()->segment(2);

        $data = [
            'title'     => 'Report Tracking Sales Order',
            'content'   => 'admin.sales.report_tracking_sales_order',
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
		return Excel::download(new ExportReportTrackingSO($start_date,$finish_date), 'report_tracking_so_'.uniqid().'.xlsx');
    }
}

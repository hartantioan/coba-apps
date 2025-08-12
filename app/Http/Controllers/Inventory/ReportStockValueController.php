<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportReportStockValue;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportStockValueController extends Controller
{
    protected $dataplaces,$dataplacecode, $datawarehouses;
    public function index(Request $request)
    {
        $data = [
            'title'     => 'Laporan Antrian Truk',
            'content'   => 'admin.inventory.report_truck_queue',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }


    public function export(Request $request){
        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->finish_date ? $request->finish_date : '';
        $status = $request->status ? $request->status : '';
		return Excel::download(new ExportReportStockValue($start_date,$finish_date), 'report_stock_'.uniqid().'.xlsx');
    }
}

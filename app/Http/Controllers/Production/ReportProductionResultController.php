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

use App\Exports\ExportReportProductionResult;
use Maatwebsite\Excel\Facades\Excel;

class ReportProductionResultController extends Controller
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
            'title'     => 'Report Hasil Produksi (ST)',
            'content'   => 'admin.production.report_production_result',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
    public function export(Request $request){

        $start_date = $request->start_date;
        $finish_date = $request->end_date;
		return Excel::download(new ExportReportProductionResult($start_date,$finish_date), 'production_result'.uniqid().'.xlsx');
    }
}

<?php

namespace App\Http\Controllers\Inventory;

use App\Exports\ExportReportGoodScaleItemFG;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportGoodScaleItemFGController extends Controller
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
            'title'     => 'Report Timbangan SJ & FG',
            'content'   => 'admin.production.report_good_scale_item_fg',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
    public function export(Request $request){

        $start_date = $request->start_date;
        $finish_date = $request->end_date;
		return Excel::download(new ExportReportGoodScaleItemFG($start_date,$finish_date), 'good_scale_item_fg_'.uniqid().'.xlsx');
    }
}

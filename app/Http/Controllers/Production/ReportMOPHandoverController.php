<?php

namespace App\Http\Controllers\Production;

use App\Exports\ExportReportMOPHandover;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class ReportMOPHandoverController extends Controller
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
        $data = [
            'title'         => 'Report MOP - Handover',
            'content'       => 'admin.production.report_mop_handover',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }


    public function export(Request $request){
        ob_end_clean();
        ob_start();
        $response = Excel::download(new ExportReportMOPHandover($request->start_date,$request->end_date), 'report_mod_handover_'.uniqid().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        return $response;
    }
}

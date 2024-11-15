<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportDeliveryOrderProcessAccountingRecap;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class ReportMarketingDeliveryOrderProcessRecapController extends Controller
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
            'title'         => 'Rekapitulasi Delivery - SJ',
            'content'       => 'admin.accounting.report_delivery_process_accounting',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }


    public function export(Request $request){
        ob_end_clean();
        ob_start();
        $response = Excel::download(new ExportDeliveryOrderProcessAccountingRecap($request->start_date,$request->end_date), 'surat_jalan_recap_accounting_'.uniqid().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        return $response;
    }
}

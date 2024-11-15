<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportDeliveryOrderProcessAccountingRecap;
use App\Http\Controllers\Controller;
use App\Jobs\DeliveryOrderProcessAccountingJob;
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
        $start_date = $request->start_date;
        $finish_date = $request->finish_date;

        $user_id = session('bo_id');


        DeliveryOrderProcessAccountingJob::dispatch($start_date, $finish_date,$user_id);

        return response()->json(['message' => 'Your export is being processed. Anda akan diberi notifikasi apabila report anda telah selesai']);
    }
}

<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportDeliveryScheduleReport;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class DeliveryScheduleController extends Controller
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
            'title'         => 'Rekapitulasi MOD',
            'content'       => 'admin.sales.delivery_schedule',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }


    public function export(Request $request){
        ob_end_clean();
        ob_start();
        $response = Excel::download(new ExportDeliveryScheduleReport($request->start_date,$request->end_date), 'mod_recap'.uniqid().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        return $response;
    }
}

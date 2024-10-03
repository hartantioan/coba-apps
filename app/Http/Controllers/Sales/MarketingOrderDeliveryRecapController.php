<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportMarketingOrderDeliveryRecap;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class MarketingOrderDeliveryRecapController extends Controller
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
            'content'       => 'admin.sales.recap_marketing_order_delivery',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }


    public function export(Request $request){
        ob_end_clean();
        ob_start();
        $response = Excel::download(new ExportMarketingOrderDeliveryRecap($request->start_date,$request->end_date), 'mo_delivery_recap'.uniqid().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        return $response;
    }


}

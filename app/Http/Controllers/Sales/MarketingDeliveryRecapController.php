<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportMarketingDeliveryRecap;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class MarketingDeliveryRecapController extends Controller
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
            'content'       => 'admin.sales.recap_marketing_delivery',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

  
    public function export(Request $request){
        ob_end_clean();
        ob_start();
        $response = Excel::download(new ExportMarketingDeliveryRecap($request->start_date,$request->end_date), 'delivery_recap_'.uniqid().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        return $response;
    }

   
}
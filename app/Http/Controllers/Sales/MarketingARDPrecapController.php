<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportMarketingARDPRecap;
use App\Exports\ExportMarketingRecapitulation;
use App\Exports\ExportMarketingRecapitulationCsv;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrder;
use Illuminate\Http\Request;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

class MarketingARDPRecapController extends Controller
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
            'title'         => 'Rekapitulasi ARDP',
            'content'       => 'admin.sales.recap_marketing_ARDP',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

  
    public function export(Request $request){
        ob_end_clean();
        ob_start();
        $response = Excel::download(new ExportMarketingARDPRecap($request->start_date,$request->end_date), 'ardp_recap_'.uniqid().'.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        return $response;
    }

   
}
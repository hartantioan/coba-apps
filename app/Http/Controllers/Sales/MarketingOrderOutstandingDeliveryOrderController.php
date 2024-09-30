<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportOutstandingAP;
use App\Exports\ExportOutstandingDeliveryOrder;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class MarketingOrderOutstandingDeliveryOrderController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Outstanding Delivery Order',
            'content'   => 'admin.sales.outstanding_delivery_order',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

   

    public function export(Request $request){
		return Excel::download(new ExportOutstandingDeliveryOrder(), 'outstanding_do_'.uniqid().'.xlsx');
    }
}

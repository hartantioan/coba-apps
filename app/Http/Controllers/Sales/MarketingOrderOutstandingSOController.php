<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportOutstandingAP;
use App\Exports\ExportOutstandingSO;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;


class MarketingOrderOutstandingSOController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Outstanding SO',
            'content'   => 'admin.sales.outstanding_so',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

   

    public function export(Request $request){
		return Excel::download(new ExportOutstandingSO(), 'outstanding_so_'.uniqid().'.xlsx');
    }
}

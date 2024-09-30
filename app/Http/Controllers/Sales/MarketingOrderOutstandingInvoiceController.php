<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportOutstandingAP;
use App\Exports\ExportOutstandingDeliveryOrder;
use App\Exports\ExportOutstandingMarketingInvoice;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class MarketingOrderOutstandingInvoiceController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Outstanding Invoice',
            'content'   => 'admin.sales.outstanding_marketing_invoice',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

   

    public function export(Request $request){
		return Excel::download(new ExportOutstandingMarketingInvoice(), 'outstanding_invoice_'.uniqid().'.xlsx');
    }
}

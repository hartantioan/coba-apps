<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportReceivableCard;
use App\Exports\ExportOutstandingDeliveryOrder;
use App\Exports\ExportOutstandingMarketingInvoice;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class ReportReceivableCardController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Kartu Piutang',
            'content'   => 'admin.sales.report_receivable_card',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

   

    public function export(Request $request){
		return Excel::download(new ExportReceivableCard($request->cust), 'receivable_card_'.uniqid().'.xlsx');
    }
}

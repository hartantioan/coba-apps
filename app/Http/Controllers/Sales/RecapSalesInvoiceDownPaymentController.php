<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportRecapSalesInvoiceDownPayment;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RecapSalesInvoiceDownPaymentController extends Controller
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
        $parentSegment = request()->segment(2);

        $data = [
            'title'     => 'Report Sales Invoice Downpayment',
            'content'   => 'admin.sales.recap_sales_invoice_dp',

        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
    public function export(Request $request){
        $start_date = $request->start_date;
        $finish_date = $request->end_date;
		return Excel::download(new ExportRecapSalesInvoiceDownPayment($start_date,$finish_date), 'recap_invoice_dp_'.uniqid().'.xlsx');
    }
}

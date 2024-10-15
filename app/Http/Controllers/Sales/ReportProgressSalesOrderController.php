<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportReportProgressSalesOrder;
use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Place;
use App\Models\Tax;
use App\Models\Transportation;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportProgressSalesOrderController extends Controller
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
            'currency'      => Currency::where('status','1')->get(),
            'company'       => Company::where('status','1')->get(),
            'transportation'=> Transportation::where('status','1')->get(),
            'place'         => Place::where('status','1')->whereIn('id',$this->dataplaces)->get(),
            'tax'           => Tax::where('status','1')->where('type','+')->orderByDesc('is_default_ppn')->get(),
            'code'          => $request->code ? CustomHelper::decrypt($request->code) : '',
            'minDate'       => $request->get('minDate'),
            'maxDate'       => $request->get('maxDate'),
            'title'     => 'Report Progress SO',
            'content'   => 'admin.sales.report_progress_sales_order',

        ];

        return view('admin.layouts.index', ['data' => $data]);

    }
    public function export(Request $request){
        $search= $request->search? $request->search : '';
        $status = $request->status? $request->status : '';
        $type_sales = $request->type_sales ? $request->type_sales : '';
        $type_pay = $request->type_pay ? $request->type_pay : '';
        $type_deliv = $request->type_deliv? $request->type_deliv : '';
        $company = $request->company ? $request->company : '';
        $customer = $request->customer? $request->customer : '';
        $delivery = $request->delivery? $request->delivery : '';
        $sales = $request->sales ? $request->sales : '';
        $currency = $request->currency ? $request->currency : '';
        $end_date = $request->end_date ? $request->end_date : '';
        $start_date = $request->start_date? $request->start_date : '';
		return Excel::download(new ExportReportProgressSalesOrder($search,$status,$type_sales,$type_pay,$type_deliv,$company,$customer,$delivery,$sales,$currency,$end_date,$start_date), 'progress_so_report_'.uniqid().'.xlsx');
    }
}

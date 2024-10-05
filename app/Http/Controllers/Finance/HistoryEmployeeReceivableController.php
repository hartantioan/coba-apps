<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\FundRequest;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportEmployeeReceivable;
use Maatwebsite\Excel\Facades\Excel;

class HistoryEmployeeReceivableController extends Controller
{
    protected $dataplaces, $datawarehouses, $dataplacecode;

    public function __construct(){
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
    }

    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Pemakaian BS Karyawan',
            'content'   => 'admin.finance.history_employee_receivable',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filter(Request $request){
        
        $start_time = microtime(true);
        
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $data = FundRequest::where('type','1')->whereIn('status',['2','3'])->where('document_status','3')->whereHas('hasPaymentRequestDetail',function($query)use($start_date,$end_date){
            $query->whereHas('paymentRequest',function($query){
                $query->whereHas('outgoingPayment');
            });
        })
        ->whereDate('post_date','<=',$end_date)
        ->whereDate('post_date','>=',$start_date)
        ->where(function($query)use($request){
            if($request->account_id){
                $query->whereIn('account_id',$request->account_id);
            }
        })
        ->get();

        $results = [];

        foreach($data as $row){
            $results[] = [
                'code'          => $row->code,
                'employee_name' => $row->account->name,
                'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                'required_date' => date('d/m/Y',strtotime($row->required_date)),
                'note'          => $row->note,
                'grandtotal'    => number_format($row->grandtotal,2,',','.'),
            ];
        }

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);
        
        $response =[
            'status'            => 200,
            'content'           => $results,
            'execution_time'    => $execution_time,
        ];

        return response()->json($response);
    }

    public function export(Request $request){
		return Excel::download(new ExportEmployeeReceivable($request->date), 'employee_receivable_'.uniqid().'.xlsx');
    }
}
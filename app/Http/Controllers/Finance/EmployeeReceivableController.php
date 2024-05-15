<?php

namespace App\Http\Controllers\Finance;

use App\Helpers\CustomHelper;
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

class EmployeeReceivableController extends Controller
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
            'title'     => 'Laporan Piutang Karyawan',
            'content'   => 'admin.finance.employee_receivable',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filter(Request $request){
        
        $start_time = microtime(true);
        
        $date = $request->date;

        $data = FundRequest::where('type','1')->whereIn('status',['2','3'])->where('document_status','3')->whereHas('hasPaymentRequestDetail',function($query)use($date){
            $query->whereHas('paymentRequest',function($query)use($date){
                $query->whereHas('outgoingPayment',function($query)use($date){
                    $query->whereDate('post_date','<=',$date);
                });
            });
        })
        ->whereHas('account',function($query){
            $query->where('type','1');
        })->get();

        $results = [];

        $totalbalance = 0;

        foreach($data as $row){
            $totalReceivable = $row->totalReceivableByDate($date);
            $totalReceivableUsed = $row->totalReceivableUsedPaidByDate($date);
            $totalReceivableBalance = $totalReceivable - $totalReceivableUsed;
            if($totalReceivableBalance > 0){
                $results[] = [
                    'code'          => $row->code,
                    'employee_name' => $row->account->name,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'required_date' => date('d/m/Y',strtotime($row->required_date)),
                    'note'          => $row->note,
                    'total'         => number_format($row->total,2,',','.'),
                    'tax'           => number_format($row->tax,2,',','.'),
                    'wtax'          => number_format($row->wtax,2,',','.'),
                    'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                    'received'      => number_format($totalReceivable,2,',','.'),
                    'used'          => number_format($totalReceivableUsed,2,',','.'),
                    'balance'       => number_format($totalReceivableBalance,2,',','.'),
                ];
                $totalbalance += $totalReceivableBalance;
            }
        }

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);
        
        $response =[
            'status'            => 200,
            'content'           => $results,
            'totalbalance'      => number_format($totalbalance,2,',','.'),
            'execution_time'    => $execution_time,
        ];

        return response()->json($response);
    }

    public function export(Request $request){
		return Excel::download(new ExportEmployeeReceivable($request->date), 'employee_receivable_'.uniqid().'.xlsx');
    }
}
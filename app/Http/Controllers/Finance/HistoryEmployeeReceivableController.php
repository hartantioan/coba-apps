<?php

namespace App\Http\Controllers\Finance;

use App\Exports\ExportHistoryEmployeeReceivable;
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
            $detail = [];

            foreach($row->personalCloseBillDetail as $rowcb){
                $detail[] = [
                    'no'            => $rowcb->personalCloseBill->code,
                    'post_date'     => date('d/m/Y',strtotime($rowcb->personalCloseBill->post_date)),
                    'status'        => $rowcb->personalCloseBill->status(),
                    'nominal'       => CustomHelper::formatConditionalQty($rowcb->nominal),
                    'note'          => $rowcb->note.' - '.$rowcb->personalCloseBill->note,
                ];
            }

            foreach($row->hasPaymentRequestDetail()->whereHas('paymentRequest',function($query){
                $query->whereHas('outgoingPayment',function($query){
                    $query->whereHas('paymentRequestCross');
                });
            })->get() as $rowpay){
                foreach($rowpay->paymentRequest->outgoingPayment->paymentRequestCross as $rowcross){
                    $detail[] = [
                        'no'            => $rowcross->paymentRequest->code,
                        'post_date'     => date('d/m/Y',strtotime($rowcross->paymentRequest->post_date)),
                        'status'        => $rowcross->paymentRequest->status(),
                        'nominal'       => CustomHelper::formatConditionalQty($rowcross->nominal),
                        'note'          => $rowcross->paymentRequest->note,
                    ];
                }
            }

            $results[] = [
                'code'          => $row->code,
                'employee_name' => $row->account->name,
                'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                'required_date' => date('d/m/Y',strtotime($row->required_date)),
                'note'          => $row->note,
                'grandtotal'    => number_format($row->grandtotal,2,',','.'),
                'details'       => $detail,
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
        $start_date = $request->start_date ?? '';
        $end_date = $request->end_date ?? '';
        $account_id = $request->account_id ?? '';
		return Excel::download(new ExportHistoryEmployeeReceivable($start_date,$end_date, $account_id), 'history_employee_receivable_'.uniqid().'.xlsx');
    }
}
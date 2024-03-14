<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportOutstandingAP;
use App\Http\Controllers\Controller;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OutStandingAPController extends Controller
{
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        
        $data = [
            'title'     => 'Laporan Outstanding AP',
            'content'   => 'admin.purchase.outstanding_ap',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filterByDate(Request $request){
        $array_filter = [];
        $query_data = PurchaseInvoice::where(function($query) use ( $request) {
                if($request->date) {
                    $query->whereDate('post_date', '<=', $request->date);
                }
            })
            ->whereIn('status',['2','3'])
            ->get();
        /* $query_data2 = PurchaseDownPayment::where(function($query) use ( $request) {
                if($request->date) {
                    $query->whereDate('post_date', '<=', $request->date);
                }
            })
            ->whereIn('status',['2','3'])
            ->get(); */
        $totalAll = 0;
        if($query_data /* || $query_data2 */){
            foreach($query_data as $row_invoice){
                $totalPayed = $row_invoice->getTotalPaidDate($request->date);
                $balance = $row_invoice->balance - $totalPayed;
                $data_tempura = [
                    'code'      => $row_invoice->code,
                    'vendor'    => $row_invoice->account->name,
                    'post_date' =>date('d/m/Y',strtotime($row_invoice->post_date)),
                    'rec_date'  =>date('d/m/Y',strtotime($row_invoice->received_date)),
                    'due_date'  =>date('d/m/Y',strtotime($row_invoice->due_date)),
                    'top'       => $row_invoice->getTop(),
                    'total'     =>number_format($row_invoice->total,2,',','.'),
                    'tax'       =>number_format($row_invoice->tax,2,',','.'),
                    'wtax'      =>number_format($row_invoice->wtax,2,',','.'),
                    'grandtotal'=>number_format($row_invoice->balance,2,',','.'),
                    'payed'     =>number_format($totalPayed,2,',','.'),
                    'sisa'      =>number_format($balance,2,',','.'),
                ];
                
                if($balance > 0){
                    $totalAll += str_replace(',','.',str_replace('.','',$data_tempura['sisa']));
                    $array_filter[] = $data_tempura;
                }
            }

            /* foreach($query_data2 as $row_dp){
                $total = $row_dp->balancePaymentRequestByDate($request->date);
                $due_date = $row_dp->due_date ? $row_dp->due_date : date('Y-m-d', strtotime($row_dp->post_date. ' + '.$row_dp->top.' day'));
                $data_tempura = [
                    'code'      => $row_dp->code,
                    'vendor'    => $row_dp->supplier->name,
                    'post_date' =>date('d/m/Y',strtotime($row_dp->post_date)),
                    'rec_date'  =>'',
                    'due_date'  =>date('d/m/Y',strtotime($due_date)),
                    'top'       => 0,
                    'total'     =>number_format($row_invoice->total,2,',','.'),
                    'tax'       =>number_format($row_invoice->tax,2,',','.'),
                    'wtax'      =>number_format($row_invoice->wtax,2,',','.'),
                    'grandtotal'=>number_format($row_dp->grandtotal,2,',','.'),
                    'payed'     =>number_format($row_dp->totalMemoByDate($request->date),2,',','.'),
                    'sisa'      =>number_format($total,2,',','.'),
                ];

                if($total > 0){
                    $totalAll += $total;
                    $array_filter[] = $data_tempura;
                }
            } */

            $response =[
                'status'=>200,
                'message'  =>$array_filter,
                'totalall' =>number_format($totalAll,2,',','.')
            ];
        }else{
            $response =[
                'status'  =>500,
                'message' =>'Data error'
            ];
        }
        return response()->json($response);
    }

    public function export(Request $request){
		return Excel::download(new ExportOutstandingAP($request->date), 'outstanding_ap_'.uniqid().'.xlsx');
    }
}

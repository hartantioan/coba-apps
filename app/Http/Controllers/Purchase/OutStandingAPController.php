<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportOutstandingAP;
use App\Http\Controllers\Controller;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

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
        /* $query_data = PurchaseInvoice::where(function($query) use ( $request) {
                if($request->date) {
                    $query->whereDate('post_date', '<=', $request->date);
                }
            })
            ->whereIn('status',['2','3'])
            ->get();
        $query_data2 = PurchaseDownPayment::where(function($query) use ( $request) {
                if($request->date) {
                    $query->whereDate('post_date', '<=', $request->date);
                }
            })
            ->whereIn('status',['2','3'])
            ->get(); */

        $date = $request->date;

        $results = DB::select("
            SELECT 
                *,
                IFNULL((SELECT 
                    SUM(nominal) 
                    FROM payment_request_details prd 
                    JOIN outgoing_payments op
                        ON op.payment_request_id = prd.payment_request_id
                    WHERE 
                        prd.lookable_id = pi.id 
                        AND prd.lookable_type = 'purchase_invoices'
                        AND op.pay_date <= :date1
                        AND op.status IN ('2','3')
                        AND prd.deleted_at IS NULL
                ),0) AS total_payment,
                IFNULL((
                    SELECT
                        SUM(pmd.grandtotal)
                        FROM purchase_memo_details pmd
                        JOIN purchase_memos pm
                            ON pm.id = pmd.purchase_memo_id
                        JOIN purchase_invoice_details pid
                            ON pid.purchase_invoice_id = pi.id
                            AND pid.id = pmd.lookable_id
                        WHERE pmd.lookable_type = 'purchase_invoice_details'
                        AND pm.post_date <= :date2
                        AND pm.status IN ('2','3')
                        AND pmd.deleted_at IS NULL
                ),0) AS total_memo,
                IFNULL((
                    SELECT
                        SUM(prd.nominal)
                        FROM payment_request_details prd
                        JOIN payment_requests pr
                            ON pr.id = prd.payment_request_id
                        WHERE prd.lookable_type = 'purchase_invoices'
                        AND prd.lookable_id = pi.id
                        AND pr.post_date <= :date3
                        AND pr.status IN ('2','3')
                        AND pr.payment_type = '5'
                        AND prd.deleted_at IS NULL
                ),0) AS total_reconcile,
                IFNULL((
                    SELECT
                        SUM(jd.nominal)
                        FROM journal_details jd
                        JOIN journals j
                            ON j.id = jd.journal_id
                        JOIN coas c
                            ON jd.coa_id = c.id
                        WHERE c.code = '200.01.03.01.01'
                        AND jd.note = CONCAT('VOID*',pi.code)
                        AND j.post_date <= :date4
                        AND j.status IN ('2','3')
                        AND jd.deleted_at IS NULL
                ),0) AS total_journal,
                u.name AS account_name,
                u.employee_no AS account_code,
                pi.code,
                pi.post_date,
                pi.received_date,
                pi.due_date,
                pi.total,
                pi.tax,
                pi.wtax,
                pi.balance
                FROM purchase_invoices pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date5
                    AND pi.balance > 0
                    AND pi.status IN ('2','3')
                    AND pi.deleted_at IS NULL
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
            'date5' => $date,
        ));

        $results2 = DB::select("
            SELECT 
                *,
                pi.top AS topdp,
                IFNULL((SELECT 
                    SUM(nominal) 
                    FROM payment_request_details prd 
                    JOIN outgoing_payments op
                        ON op.payment_request_id = prd.payment_request_id
                    WHERE 
                        prd.lookable_id = pi.id 
                        AND prd.lookable_type = 'purchase_down_payments'
                        AND op.pay_date <= :date1
                        AND op.status IN ('2','3')
                        AND prd.deleted_at IS NULL
                ),0) AS total_payment,
                IFNULL((
                    SELECT
                        SUM(pmd.grandtotal)
                        FROM purchase_memo_details pmd
                        JOIN purchase_memos pm
                            ON pm.id = pmd.purchase_memo_id
                        WHERE pmd.lookable_type = 'purchase_down_payments'
                        AND pmd.lookable_id = pi.id
                        AND pm.post_date <= :date2
                        AND pm.status IN ('2','3')
                        AND pmd.deleted_at IS NULL
                ),0) AS total_memo,
                IFNULL((
                    SELECT
                        SUM(prd.nominal)
                        FROM payment_request_details prd
                        JOIN payment_requests pr
                            ON pr.id = prd.payment_request_id
                        WHERE prd.lookable_type = 'purchase_down_payments'
                        AND prd.lookable_id = pi.id
                        AND pr.post_date <= :date3
                        AND pr.status IN ('2','3')
                        AND pr.payment_type = '5'
                        AND prd.deleted_at IS NULL
                ),0) AS total_reconcile,
                u.name AS account_name,
                u.employee_no AS account_code,
                pi.code,
                pi.post_date,
                pi.document_date,
                pi.due_date,
                pi.total,
                pi.tax,
                pi.wtax,
                pi.grandtotal,
                pi.currency_rate
                FROM purchase_down_payments pi
                LEFT JOIN users u
                    ON u.id = pi.account_id
                WHERE 
                    pi.post_date <= :date4
                    AND pi.grandtotal > 0
                    AND pi.status IN ('2','3')
                    AND pi.deleted_at IS NULL
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
        ));

        $totalAll = 0;

        if($results || $results2){
            /* foreach($query_data as $row_invoice){
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

            foreach($query_data2 as $row_dp){
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

            foreach($results as $row){
                $totalPayed = $row->total_payment + $row->total_memo + $row->total_reconcile + $row->total_journal;
                $balance = $row->balance - $totalPayed;
                $data_tempura = [
                    'code'      => $row->code,
                    'vendor'    => $row->account_name,
                    'post_date' => date('d/m/Y',strtotime($row->post_date)),
                    'rec_date'  => date('d/m/Y',strtotime($row->received_date)),
                    'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                    'top'       => '-',
                    'total'     => number_format($row->total,2,',','.'),
                    'tax'       => number_format($row->tax,2,',','.'),
                    'wtax'      => number_format($row->wtax,2,',','.'),
                    'grandtotal'=> number_format($row->balance,2,',','.'),
                    'payed'     => number_format($totalPayed,2,',','.'),
                    'sisa'      => number_format($balance,2,',','.'),
                ];
                
                if($balance > 0){
                    $totalAll += $balance;
                    $array_filter[] = $data_tempura;
                }
            }

            foreach($results2 as $row){
                $totalPayed = $row->total_payment + $row->total_memo + $row->total_reconcile;
                $balance = ($row->grandtotal * $row->currency_rate) - $totalPayed;
                $data_tempura = [
                    'code'      => $row->code,
                    'vendor'    => $row->account_name,
                    'post_date' => date('d/m/Y',strtotime($row->post_date)),
                    'rec_date'  => date('d/m/Y',strtotime($row->document_date)),
                    'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                    'top'       => '-',
                    'total'     => number_format($row->total,2,',','.'),
                    'tax'       => number_format($row->tax,2,',','.'),
                    'wtax'      => number_format($row->wtax,2,',','.'),
                    'grandtotal'=> number_format($row->grandtotal * $row->currency_rate,2,',','.'),
                    'payed'     => number_format($totalPayed,2,',','.'),
                    'sisa'      => number_format($balance,2,',','.'),
                ];
                
                if($balance > 0){
                    $totalAll += $balance;
                    $array_filter[] = $data_tempura;
                }
            }

            $response =[
                'status'    => 200,
                'message'   => $array_filter,
                'totalall'  => number_format($totalAll,2,',','.')
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

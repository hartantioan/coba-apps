<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportOutstandingAP;
use App\Http\Controllers\Controller;
use App\Models\OutstandingAP;
use App\Models\PurchaseDownPayment;
use App\Models\PurchaseInvoice;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
        
        $start_time = microtime(true);

        $date = $request->date;

        $results = DB::select("
            SELECT 
                rs.account_name,
                rs.account_code,
                rs.code,
                rs.post_date,
                rs.received_date,
                rs.due_date,
                rs.total,
                rs.tax,
                rs.wtax,
                rs.balance,
                rs.currency_rate,
                rs.adjust_nominal,
                rs.total_payment,
                rs.total_memo,
                rs.total_reconcile,
                rs.total_journal,
                rs.status_cancel
            FROM
                (SELECT 
                    IFNULL((SELECT 
                        SUM(prd.nominal) 
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
                    IFNULL((SELECT
                        SUM(ROUND(ard.nominal,2))
                        FROM adjust_rate_details ard
                        JOIN adjust_rates ar
                            ON ar.id = ard.adjust_rate_id
                        WHERE 
                            ar.post_date <= :date5
                            AND ar.status IN ('2','3')
                            AND ard.lookable_type = 'purchase_invoices'
                            AND ard.lookable_id = pi.id
                            AND (
                                CASE 
                                    WHEN ar.post_date >= '2024-06-01' THEN ard.type = '2'
                                    WHEN ar.post_date < '2024-06-01' THEN ard.type IS NOT NULL
                                END
                            )
                    ),0) AS adjust_nominal,
                    IFNULL((SELECT
                        '1'
                        FROM cancel_documents cd
                        WHERE 
                            cd.post_date <= :date6
                            AND cd.lookable_type = 'purchase_invoices'
                            AND cd.lookable_id = pi.id
                            AND cd.deleted_at IS NULL
                    ),0) AS status_cancel,
                    u.name AS account_name,
                    u.employee_no AS account_code,
                    pi.code,
                    pi.post_date,
                    pi.received_date,
                    pi.due_date,
                    pi.total,
                    pi.tax,
                    pi.wtax,
                    pi.balance,
                    pi.currency_rate
                    FROM purchase_invoices pi
                    LEFT JOIN users u
                        ON u.id = pi.account_id
                    WHERE 
                        pi.post_date <= :date7
                        AND pi.balance > 0
                        AND pi.status IN ('2','3','7','8')
                        AND pi.deleted_at IS NULL
                ) AS rs
            WHERE (rs.balance - rs.total_payment - rs.total_memo - rs.total_reconcile - rs.total_journal) > 0
            AND rs.status_cancel = '0'
            ORDER BY rs.post_date ASC
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
            'date5' => $date,
            'date6' => $date,
            'date7' => $date,
        ));

        $results2 = DB::select("
            SELECT 
                rs.code,
                rs.total_payment,
                rs.total_memo,
                rs.total_reconcile,
                rs.adjust_nominal,
                rs.account_name,
                rs.account_code,
                rs.post_date,
                rs.document_date,
                rs.due_date,
                rs.total,
                rs.tax,
                rs.wtax,
                rs.grandtotal,
                rs.currency_rate,
                rs.adjust_nominal,
                rs.total_payment,
                rs.total_memo,
                rs.total_reconcile,
                rs.status_cancel,
                rs.total_journal
            FROM
                (SELECT
                    pi.top AS topdp,
                    IFNULL((SELECT 
                        SUM(prd.nominal) 
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
                    IFNULL((SELECT
                        SUM(ROUND(ard.nominal,2))
                        FROM adjust_rate_details ard
                        JOIN adjust_rates ar
                            ON ar.id = ard.adjust_rate_id
                        WHERE 
                            ar.post_date <= :date4
                            AND ar.status IN ('2','3')
                            AND ard.lookable_type = 'purchase_down_payments'
                            AND ard.lookable_id = pi.id
                            AND (
                                CASE 
                                    WHEN ar.post_date >= '2024-06-01' THEN ard.type = '2'
                                    WHEN ar.post_date < '2024-06-01' THEN ard.type IS NOT NULL
                                END
                            )
                    ),0) AS adjust_nominal,
                    IFNULL((SELECT
                        '1'
                        FROM cancel_documents cd
                        WHERE 
                            cd.post_date <= :date5
                            AND cd.lookable_type = 'purchase_down_payments'
                            AND cd.lookable_id = pi.id
                            AND cd.deleted_at IS NULL
                    ),0) AS status_cancel,
                    IFNULL((
                        SELECT
                            SUM(jd.nominal)
                            FROM journal_details jd
                            JOIN journals j
                                ON j.id = jd.journal_id
                            JOIN coas c
                                ON jd.coa_id = c.id
                            WHERE c.code = '200.01.03.01.01'
                            AND jd.note = CONCAT('REVERSE*',pi.code)
                            AND j.post_date <= :date6
                            AND j.status IN ('2','3')
                            AND jd.deleted_at IS NULL
                    ),0) AS total_journal,
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
                        pi.post_date <= :date7
                        AND pi.grandtotal > 0
                        AND pi.status IN ('2','3','7','8')
                        AND pi.deleted_at IS NULL
                ) AS rs
                WHERE (rs.grandtotal - rs.total_payment - rs.total_memo - rs.total_reconcile) > 0
                AND rs.status_cancel = '0'
                ORDER BY rs.post_date ASC
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
            'date5' => $date,
            'date6' => $date,
            'date7' => $date,
        ));

        $totalAll = 0;

        if($results || $results2){
            foreach($results as $row){
                $total_received_after_adjust = round(($row->balance * $row->currency_rate) + $row->adjust_nominal,2);
                $total_invoice_after_adjust = round(($row->total_payment + $row->total_memo + $row->total_reconcile) * $row->currency_rate,2);
                $balance_after_adjust = round($total_received_after_adjust - $total_invoice_after_adjust,2);
                $data_tempura = [
                    'code'      => $row->code,
                    'vendor'    => $row->account_name,
                    'post_date' => date('d/m/Y',strtotime($row->post_date)),
                    'rec_date'  => date('d/m/Y',strtotime($row->received_date)),
                    'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                    'top'       => '-',
                    'grandtotal'=> number_format($total_received_after_adjust,2,',','.'),
                    'payed'     => number_format($total_invoice_after_adjust,2,',','.'),
                    'sisa'      => number_format($balance_after_adjust,2,',','.'),
                ];
                $totalAll += $balance_after_adjust;
                $array_filter[] = $data_tempura;
            }

            foreach($results2 as $row){
                $total_received_after_adjust = round(($row->grandtotal * $row->currency_rate) + $row->adjust_nominal,2);
                $total_invoice_after_adjust = round(($row->total_payment + $row->total_memo + $row->total_reconcile) * $row->currency_rate,2) + $row->total_journal;
                $balance_after_adjust = round($total_received_after_adjust - $total_invoice_after_adjust,2);
                $data_tempura = [
                    'code'      => $row->code,
                    'vendor'    => $row->account_name,
                    'post_date' => date('d/m/Y',strtotime($row->post_date)),
                    'rec_date'  => date('d/m/Y',strtotime($row->document_date)),
                    'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                    'top'       => '-',
                    'grandtotal'=> number_format($total_received_after_adjust,2,',','.'),
                    'payed'     => number_format($total_invoice_after_adjust,2,',','.'),
                    'sisa'      => number_format($balance_after_adjust,2,',','.'),
                ];
                
                $totalAll += $balance_after_adjust;
                $array_filter[] = $data_tempura;
            }

            $end_time = microtime(true);
        
            $execution_time = ($end_time - $start_time);

            $response =[
                'status'        => 200,
                'message'       => $array_filter,
                'totalall'      => number_format($totalAll,2,',','.'),
                'execution_time'=> $execution_time,
            ];
        }else{
            $response =[
                'status'  =>500,
                'message' =>'Data error'
            ];
        }
        return response()->json($response);

        /* $array_filter = [];
        
        $start_time = microtime(true);

        $date = $request->date;

        $results = OutstandingAP::where('post_date',$date)->first();

        if($results){
            if($results->status){
                foreach($results->outstandingApDetail as $row){
                    $data_tempura = [
                        'code'      => $row->code,
                        'vendor'    => $row->account,
                        'post_date' => date('d/m/Y',strtotime($row->post_date)),
                        'rec_date'  => date('d/m/Y',strtotime($row->received_date)),
                        'due_date'  => date('d/m/Y',strtotime($row->due_date)),
                        'top'       => $row->top,
                        'grandtotal'=> number_format($row->total,2,',','.'),
                        'payed'     => number_format($row->paid,2,',','.'),
                        'sisa'      => number_format($row->balance,2,',','.'),
                    ];
                    $array_filter[] = $data_tempura;
                }

                $end_time = microtime(true);
            
                $execution_time = ($end_time - $start_time);

                $response =[
                    'status'        => 200,
                    'message'       => $array_filter,
                    'totalall'      => number_format($results->total,2,',','.'),
                    'execution_time'=> $execution_time,
                    'updated_at'    => date('d/m/Y H:i:s',strtotime($results->updated_at)),
                ];
            }else{
                $response =[
                    'status'  => 500,
                    'message' => 'Laporan masih dalam proses sinkronisasi. Mohon ditunggu.'
                ];
            }
        }else{
            $response =[
                'status'    => 500,
                'message'   => 'Laporan tanggal '.date('d/m/Y',strtotime($date)).' masih belum tersedia. Silahkan jalankan SYNC.'
            ];
        }

        return response()->json($response); */
    }

    public function syncReport(Request $request){
        Artisan::call('report:generate',[
            '--date' => $request->date,
        ]);
        return response()->json([
            'status'        => 200
        ]);
    }

    public function export(Request $request){
        /* $results = OutstandingAP::where('post_date',$request->date)->first();

        if($results){
            if($results->status){ */
                ob_end_clean(); // this
                ob_start(); // and this
                return Excel::download(new ExportOutstandingAP($request->date), 'outstanding_ap_'.uniqid().'.xlsx');
            /* }else{
                return back()->withErrors(['error' => 'Laporan masih dalam proses sinkronisasi. Mohon ditunggu.']);
            }
        }else{
            return back()->withErrors(['error' => 'Laporan tanggal '.date('d/m/Y',strtotime($request->date)).' masih belum tersedia. Silahkan jalankan SYNC.']);
        } */
    }
}

<?php

namespace App\Http\Controllers\Purchase;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\PurchaseDownPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportDownPayment;
use App\Exports\ExportOutstandingDP;
use App\Models\Tax;
use Maatwebsite\Excel\Facades\Excel;

class DownPaymentController extends Controller
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
            'title'     => 'Laporan Sisa Down Payment',
            'content'   => 'admin.purchase.report_down_payment',
            'company'   => Company::where('status','1')->get(),
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filter(Request $request){
        
        $start_time = microtime(true);
        
        $date = $request->date;

        $data = DB::select("
                SELECT 
                *,
                pdp.type as typepdp,
                IFNULL((SELECT 
                    SUM(nominal) 
                    FROM purchase_invoice_dps pid 
                    JOIN purchase_invoices pi
                        ON pid.purchase_invoice_id = pi.id
                    WHERE 
                        pid.purchase_down_payment_id = pdp.id
                        AND pi.post_date <= :date1
                        AND pi.status IN ('2','3','7')
                ),0) AS total_used,
                IFNULL((
                    SELECT
                        SUM(pmd.grandtotal)
                        FROM purchase_memo_details pmd
                        JOIN purchase_memos pm
                            ON pm.id = pmd.purchase_memo_id
                        WHERE pmd.lookable_type = 'purchase_down_payments'
                        AND pmd.lookable_id = pdp.id
                        AND pm.post_date <= :date2
                        AND pm.status IN ('2','3','7')
                ),0) AS total_memo,
                IFNULL((SELECT
                    SUM(ROUND(ard.nominal,2))
                    FROM adjust_rate_details ard
                    JOIN adjust_rates ar
                        ON ar.id = ard.adjust_rate_id
                    WHERE 
                        ar.post_date <= :date3
                        AND ar.status IN ('2','3')
                        AND ard.lookable_type = 'purchase_down_payments'
                        AND ard.lookable_id = pdp.id
                        AND (
                            CASE 
                                WHEN ar.post_date >= '2024-06-01' THEN ard.type = '1'
                                WHEN ar.post_date < '2024-06-01' THEN ard.type IS NOT NULL
                            END
                        )
                ),0) AS adjust_nominal,
                IFNULL((SELECT
                    ar.currency_rate
                    FROM adjust_rate_details ard
                    JOIN adjust_rates ar
                        ON ar.id = ard.adjust_rate_id
                    WHERE 
                        ar.post_date <= :date4
                        AND ar.status IN ('2','3')
                        AND ard.lookable_type = 'purchase_down_payments'
                        AND ard.lookable_id = pdp.id
                    ORDER BY ar.id DESC
                    LIMIT 1
                ),0) AS latest_currency,
                IFNULL((
                    SELECT
                        SUM(jd.nominal)
                        FROM journal_details jd
                        JOIN journals j
                            ON j.id = jd.journal_id
                        JOIN coas c
                            ON jd.coa_id = c.id
                        WHERE c.code = '100.01.07.01.01'
                        AND jd.note = CONCAT('REVERSE*',pdp.code)
                        AND j.post_date <= :date5
                        AND j.status IN ('2','3')
                        AND jd.deleted_at IS NULL
                ),0) AS total_journal,
                IFNULL((
                    SELECT
                        SUM(jd.nominal)
                        FROM journal_details jd
                        JOIN journals j
                            ON j.id = jd.journal_id
                        JOIN coas c
                            ON jd.coa_id = c.id
                        WHERE c.code = '100.01.07.01.01'
                        AND jd.note = CONCAT('REVERSE*',pdp.code)
                        AND j.post_date <= :date6
                        AND j.status IN ('2','3')
                        AND jd.deleted_at IS NULL
                ),0) AS total_journal,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM purchase_down_payments pdp
                LEFT JOIN users u
                    ON u.id = pdp.account_id
                WHERE 
                    pdp.post_date <= :date7
                    AND pdp.grandtotal > 0
                    AND pdp.status IN ('2','3','7','8')
                    AND IFNULL((SELECT
                        '1'
                        FROM cancel_documents cd
                        WHERE 
                            cd.post_date <= :date8
                            AND cd.lookable_type = 'purchase_down_payments'
                            AND cd.lookable_id = pdp.id
                            AND cd.deleted_at IS NULL
                    ),'0') = '0'
            ",array(
                'date1' => $date,
                'date2' => $date,
                'date3' => $date,
                'date4' => $date,
                'date5' => $date,
                'date6' => $date,
                'date7' => $date,
                'date8' => $date,
            ));

        $results = [];

        $totalbalance = 0;

        foreach($data as $row){
            $currency_rate = $row->latest_currency > 0 ? $row->latest_currency : $row->currency_rate;
            $total_received_after_adjust = round($row->grandtotal * $currency_rate,2);
            $total_invoice_after_adjust = round(($row->total_used + $row->total_memo) * $currency_rate,2);
            $balance_after_adjust = round($total_received_after_adjust - $total_invoice_after_adjust,2);
            $balance = round($row->grandtotal - $row->total_used - $row->total_memo,2);
            $currency_rate = $row->latest_currency;
            /* $balance_rp = round($balance * $currency_rate,2) + $row->adjust_nominal - $row->total_journal; */
            if($balance > 0){
                $results[] = [
                    'code'          => $row->code,
                    'supplier_name' => $row->name,
                    'type'          => PurchaseDownPayment::typeStatic($row->typepdp),
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'due_date'      => date('d/m/Y',strtotime($row->due_date)),
                    'note'          => $row->note,
                    'subtotal'      => number_format($row->subtotal * $currency_rate,2,',','.'),
                    'discount'      => number_format($row->discount * $currency_rate,2,',','.'),
                    'total'         => number_format($row->total * $currency_rate,2,',','.'),
                    'used'          => number_format($row->total_used * $currency_rate,2,',','.'),
                    'memo'          => number_format($row->total_memo * $currency_rate,2,',','.'),
                    'balance'       => number_format($balance_after_adjust,2,',','.'),
                    'balance_fc'    => number_format($balance,2,',','.'),
                ];
                $totalbalance += round($balance_after_adjust,2);
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
		return Excel::download(new ExportDownPayment($request->date), 'down_payment_'.uniqid().'.xlsx');
    }

    public function getOutstanding(Request $request){
		return Excel::download(new ExportOutstandingDP(), 'outstanding_purchase_down_payment'.uniqid().'.xlsx');
    }
}
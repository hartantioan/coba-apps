<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\MarketingOrderDownPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportDownPaymentAR;
use Maatwebsite\Excel\Facades\Excel;

class MarketingOrderDPReportController extends Controller
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
            'content'   => 'admin.sales.report_down_payment',
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
            modp.type as typepdp,
            IFNULL((SELECT 
                SUM(moid.total)
                FROM marketing_order_invoice_details moid 
                JOIN marketing_order_invoices moi
                    ON moi.id = moid.marketing_order_invoice_id
                WHERE 
                    moid.lookable_id = modp.id 
                    AND moid.lookable_type = 'marketing_order_down_payments'
                    AND moi.post_date <= :date1
                    AND moi.status IN ('2','3')
            ),0) AS total_used,
            IFNULL((
                SELECT
                    SUM(momd.total)
                    FROM marketing_order_memo_details momd
                    JOIN marketing_order_memos mom
                        ON mom.id = momd.marketing_order_memo_id
                    WHERE momd.lookable_type = 'marketing_order_down_payments'
                    AND momd.lookable_id = modp.id
                    AND mom.post_date <= :date2
                    AND mom.status IN ('2','3')
            ),0) AS total_memo,
            u.name AS account_name,
            u.employee_no AS account_code
            FROM marketing_order_down_payments modp
            LEFT JOIN users u
                ON u.id = modp.account_id
            WHERE 
                modp.post_date <= :date3
                AND modp.grandtotal > 0
                AND modp.status IN ('2','3')
            ",array(
                'date1' => $date,
                'date2' => $date,
                'date3' => $date,
            ));

        $results = [];

        $totalbalance = 0;

        foreach($data as $row){
            $balance = $row->total - $row->total_used - $row->total_memo;
            if($balance > 0){
                $results[] = [
                    'code'          => $row->code,
                    'customer_name' => $row->account_name,
                    'type'          => MarketingOrderDownPayment::typeStatic($row->typepdp),
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'due_date'      => date('d/m/Y',strtotime($row->due_date)),
                    'note'          => $row->note,
                    'subtotal'      => number_format($row->subtotal,2,',','.'),
                    'discount'      => number_format($row->discount,2,',','.'),
                    'total'         => number_format($row->total,2,',','.'),
                    'used'          => number_format($row->total_used,2,',','.'),
                    'memo'          => number_format($row->total_memo,2,',','.'),
                    'balance'       => number_format($balance,2,',','.'),
                ];
                $totalbalance += $balance;
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
		return Excel::download(new ExportDownPaymentAR($request->date), 'down_payment_ar_'.uniqid().'.xlsx');
    }
}
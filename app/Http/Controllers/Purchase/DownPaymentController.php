<?php

namespace App\Http\Controllers\Purchase;

use App\Helpers\CustomHelper;
use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\Company;
use App\Models\JournalDetail;
use App\Models\PurchaseDownPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ExportDownPayment;
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
                        AND pi.status IN ('2','3')
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
                ),0) AS total_memo,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM purchase_down_payments pdp
                LEFT JOIN users u
                    ON u.id = pdp.account_id
                WHERE 
                    pdp.post_date <= :date3
                    AND pdp.grandtotal > 0
                    AND pdp.status IN ('2','3')
            ",array(
                'date1' => $date,
                'date2' => $date,
                'date3' => $date,
            ));

        $results = [];

        $totalbalance = 0;

        foreach($data as $row){
            $balance = $row->grandtotal - $row->total_used - $row->total_memo;
            if($balance > 0){
                $results[] = [
                    'code'          => $row->code,
                    'supplier_name' => $row->name,
                    'type'          => PurchaseDownPayment::typeStatic($row->typepdp),
                    'post_date'     => date('d/m/y',strtotime($row->post_date)),
                    'due_date'      => date('d/m/y',strtotime($row->due_date)),
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
		return Excel::download(new ExportDownPayment($request->date), 'down_payment_'.uniqid().'.xlsx');
    }
}
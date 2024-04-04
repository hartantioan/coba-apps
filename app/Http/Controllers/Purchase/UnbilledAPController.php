<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportUnbilledAP;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UnbilledAPController extends Controller
{
    protected $dataplaces, $lasturl, $mindate, $maxdate;
    public function __construct(){
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {
        $parentSegment = request()->segment(2);
        $menu = Menu::where('url', $parentSegment)->first();
        $data = [
            'title'     => 'Laporan Hutang Belum Ditagihkan',
            'content'   => 'admin.purchase.unbilled_ap',
        ];

        return view('admin.layouts.index', ['data' => $data]);

    }

    public function filterByDate(Request $request){
        $array_filter = [];
        $date = $request->date;

        $start_time = microtime(true);

        $results = DB::select("
            SELECT 
                *,
                u.name AS account_name,
                (SELECT 
                    SUM(pid.total) 
                    FROM purchase_invoice_details pid
                    JOIN purchase_invoices pi
                        ON pi.id = pid.purchase_invoice_id
                    WHERE pid.lookable_type = 'good_receipt_details' 
                    AND pid.lookable_id 
                        IN (
                            SELECT 
                                grd.id 
                                FROM good_receipt_details grd
                                WHERE grd.good_receipt_id = gr.id 
                                AND grd.deleted_at IS NULL
                            )
                    AND pid.deleted_at IS NULL 
                    AND pi.status IN ('2','3','7') 
                    AND pi.post_date <= :date1
                    AND pi.id
                        NOT IN (
                            SELECT
                                j.lookable_id
                                FROM journals j
                                WHERE j.lookable_type = 'purchase_invoices'
                                AND j.status IN ('2','3')
                                AND j.deleted_at IS NULL
                                AND j.post_date <= :date2
                                AND j.note = CONCAT('VOID*',pi.code)
                        )
                ) AS total_invoice,
                (SELECT 
                    SUM(grtd.total) 
                    FROM good_return_details grtd 
                    WHERE grtd.good_receipt_detail_id 
                        IN (
                            SELECT 
                                grd.id 
                                FROM good_receipt_details grd
                                WHERE grd.good_receipt_id = gr.id 
                                AND grd.deleted_at IS NULL
                            )
                    AND grtd.deleted_at IS NULL 
                    AND grtd.good_return_id 
                        IN (
                            SELECT 
                                grt.id 
                                FROM good_returns grt 
                                WHERE grt.status IN ('2','3') 
                                AND grt.post_date <= :date3
                            )
                ) AS total_return,
                (SELECT 
                    j.currency_rate
                    FROM journals j 
                    WHERE 
                        j.lookable_id = gr.id
                        AND j.lookable_type = 'good_receipts'
                        AND j.deleted_at IS NULL
                ) AS currency_rate
                FROM good_receipts gr
                LEFT JOIN users u
                    ON u.id = gr.account_id
                WHERE 
                    gr.post_date <= :date4
                    AND gr.status IN ('2','3')
                    AND gr.deleted_at IS NULL
        ", array(
            'date1'     => $date,
            'date2'     => $date,
            'date3'     => $date,
            'date4'     => $date,
        ));

        $totalUnbilled = 0;

        foreach($results as $key => $row){
            $balance = $row->total - $row->total_invoice - $row->total_return;
            if($balance > 0){
                $array_filter[] = [
                    'no'            => ($key + 1),
                    'code'          => $row->code,
                    'vendor'        => $row->account_name,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'delivery_no'   => $row->delivery_no,
                    'note'          => $row->note,
                    'total_received'=> number_format($row->total * $row->currency_rate,2,',','.'),
                    'total_invoice' => number_format($row->total_invoice * $row->currency_rate,2,',','.'),
                    'total_balance' => number_format($balance * $row->currency_rate,2,',','.'),
                ];
                $totalUnbilled += $balance * $row->currency_rate;
            }
        }

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);

        $response =[
            'status'        => 200,
            'data'          => $array_filter,
            'total'         => number_format($totalUnbilled,2,',','.'),
            'time'          => $execution_time,
        ];

        return response()->json($response);
    }

    public function export(Request $request){
		return Excel::download(new ExportUnbilledAP($request->date), 'unbilled_ap_'.uniqid().'.xls');
    }
}

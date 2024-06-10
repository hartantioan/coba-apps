<?php

namespace App\Http\Controllers\Purchase;

use App\Exports\ExportUnbilledAP;
use App\Http\Controllers\Controller;
use App\Models\JournalDetail;
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
            gr.*,
            u.name AS account_name,
            IFNULL((SELECT 
                SUM(round(pid.total * pi.currency_rate,2))
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
            ),0) AS total_invoice,
            IFNULL((
                SELECT 
                    GROUP_CONCAT(DISTINCT pi.code)
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
                    AND pi.post_date <= :date2
            ),'') AS data_reconcile,
            IFNULL((SELECT 
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
            ),0) AS total_return,
            (SELECT 
                j.currency_rate
                FROM journals j 
                WHERE 
                    j.lookable_id = gr.id
                    AND j.lookable_type = 'good_receipts'
                    AND j.deleted_at IS NULL
            ) AS currency_rate,
            IFNULL((SELECT
                SUM(ROUND(ard.nominal,2))
                FROM adjust_rate_details ard
                JOIN adjust_rates ar
                    ON ar.id = ard.adjust_rate_id
                WHERE 
                    ar.post_date <= :date4
                    AND ar.status IN ('2','3')
                    AND ard.lookable_type = 'good_receipts'
                    AND ard.lookable_id = gr.id
            ),0) AS adjust_nominal
            FROM good_receipts gr
            LEFT JOIN users u
                ON u.id = gr.account_id
            WHERE 
                gr.post_date <= :date5
                AND gr.status IN ('2','3')
                AND gr.deleted_at IS NULL;
        ", array(
            'date1'     => $date,
            'date2'     => $date,
            'date3'     => $date,
            'date4'     => $date,
            'date5'     => $date,
        ));

        $totalUnbilled = 0;

        foreach($results as $key => $row){
            $invoices = explode(',',$row->data_reconcile);
            $total_reconcile = 0;
            if(count($invoices) > 0){
                foreach($invoices as $rowinvoice){
                    $total_reconcile += JournalDetail::where('note','VOID*'.$rowinvoice)
                    ->whereHas('coa',function($query){
                        $query->where('code','200.01.03.01.02');
                    })->whereHas('journal',function($query)use($date){
                        $query->where('post_date','<=',$date)->whereIn('status',['2','3']);
                    })->sum('nominal_fc');
                }
            }
            $balance = round($row->total - ($row->total_invoice - $total_reconcile) - $row->total_return,2);
            $currency_rate = $row->currency_rate;
            $total_received_after_adjust = round(($row->total * $currency_rate) + $row->adjust_nominal,2);
            $total_invoice_after_adjust = round($row->total_invoice - $total_reconcile + $row->total_return,2);
            $balance_after_adjust = round($total_received_after_adjust - $total_invoice_after_adjust,2);
            if(round($balance_after_adjust,2) > 0){
                $array_filter[] = [
                    'no'            => ($key + 1),
                    'code'          => $row->code,
                    'vendor'        => $row->account_name,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'delivery_no'   => $row->delivery_no,
                    'note'          => $row->note,
                    'total_received'=> number_format($total_received_after_adjust,2,',','.'),
                    'total_invoice' => number_format($total_invoice_after_adjust,2,',','.'),
                    'total_balance' => number_format($balance_after_adjust,2,',','.'),
                ];
                $totalUnbilled += $balance_after_adjust;
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

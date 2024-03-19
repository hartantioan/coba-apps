<?php

namespace App\Http\Controllers\Purchase;

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
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $start_time = microtime(true);

        $resultsbefore = DB::select("
            SELECT 
                *,
                u.name AS account_name,
                (SELECT 
                    SUM(pid.total) 
                    FROM purchase_invoice_details pid 
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
                    AND pid.purchase_invoice_id 
                        IN (
                            SELECT 
                                pi.id 
                                FROM purchase_invoices pi 
                                WHERE pi.status IN ('2','3') 
                                AND pi.post_date <= :dateend
                            )
                ) AS total_invoice
                FROM good_receipts gr
                LEFT JOIN users u
                    ON u.id = gr.account_id
                WHERE 
                    gr.post_date < :datestart
                    AND gr.status IN ('2','3')
                    AND gr.deleted_at IS NULL
        ", array(
            'datestart'     => $start_date,
            'dateend'       => $end_date,
        ));

        $results = DB::select("
            SELECT 
                *,
                u.name AS account_name,
                (SELECT 
                    SUM(pid.total) 
                    FROM purchase_invoice_details pid 
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
                    AND pid.purchase_invoice_id 
                        IN (
                            SELECT 
                                pi.id 
                                FROM purchase_invoices pi 
                                WHERE pi.status IN ('2','3') 
                                AND pi.post_date <= :dateend2
                            )
                ) AS total_invoice
                FROM good_receipts gr
                LEFT JOIN users u
                    ON u.id = gr.account_id
                WHERE 
                    gr.post_date <= :dateend
                    AND gr.post_date >= :datestart
                    AND gr.status IN ('2','3')
                    AND gr.deleted_at IS NULL
        ", array(
            'datestart' => $start_date,
            'dateend'   => $end_date,
            'dateend2'  => $end_date,
        ));

        $totalUnbilled = 0;
        $totalUnbilledBefore = 0;

        foreach($resultsbefore as $key => $row){
            $balance = $row->total - $row->total_invoice;
            if($balance > 0){
                $totalUnbilledBefore += $balance;
            }
        }
        
        $totalUnbilled += $totalUnbilledBefore;

        foreach($results as $key => $row){
            $balance = $row->total - $row->total_invoice;
            if($balance > 0){
                $array_filter[] = [
                    'no'            => ($key + 1),
                    'code'          => $row->code,
                    'vendor'        => $row->account_name,
                    'post_date'     => date('d/m/Y',strtotime($row->post_date)),
                    'delivery_no'   => $row->delivery_no,
                    'note'          => $row->note,
                    'total_received'=> number_format($row->total,2,',','.'),
                    'total_invoice' => number_format($row->total_invoice,2,',','.'),
                    'total_balance' => number_format($balance,2,',','.'),
                ];
                $totalUnbilled += $balance;
            }
        }

        $end_time = microtime(true);
        
        $execution_time = ($end_time - $start_time);

        $response =[
            'status'        => 200,
            'data'          => $array_filter,
            'total'         => number_format($totalUnbilled,2,',','.'),
            'totalbefore'   => number_format($totalUnbilledBefore,2,',','.'),
            'time'          => $execution_time,
        ];

        return response()->json($response);
    }
}

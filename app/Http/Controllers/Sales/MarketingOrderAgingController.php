<?php

namespace App\Http\Controllers\Sales;

use App\Exports\ExportAgingAR;
use App\Http\Controllers\Controller;
use App\Models\MarketingOrderDownPayment;
use App\Models\MarketingOrderInvoice;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MarketingOrderAgingController extends Controller
{
    public function __construct()
    {
        $user = User::find(session('bo_id'));
    }
    public function index(Request $request)
    {

        $data = [
            'title'     => 'Laporan Aging AR',
            'content'   => 'admin.sales.aging_ar',
        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function filter(Request $request)
    {

        $start_time = microtime(true);

        $date = $request->date;

        $results = DB::select("
            SELECT 
                moi.*,u.*,gr.name as grup,ifnull(oc.outstandcheck,0) as outstandcheck,
                IFNULL((SELECT 
                    SUM(ipd.subtotal) 
                    FROM incoming_payment_details ipd 
                    JOIN incoming_payments ip
                        ON ip.id = ipd.incoming_payment_id
                    WHERE 
                        ipd.lookable_id = moi.id 
                        AND ipd.lookable_type = 'marketing_order_invoices'
                        AND ip.post_date <= :date1
                        AND ip.status IN ('2','3')
                ),0) AS total_payment,
                0 AS total_memo,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM marketing_order_invoices moi
                JOIN users u
                    ON u.id = moi.account_id
                JOIN `groups` gr on u.group_id=gr.id
                LEFT JOIN (select account_id,sum(nominal-coalesce(grandtotal,0)) as outstandcheck 
                      from list_bg_checks where void_date is null and deleted_at is null group by account_id)oc on oc.account_id=u.id
                WHERE 
                    moi.post_date <= :date2
                    AND moi.grandtotal > 0
                    AND moi.status IN ('2','3')
        ", array(
            'date1' => $date,
            'date2' => $date,
        ));

        $countPeriod = 1;
        $column = intval($request->column);
        $countPeriod += $column + 1;
        $interval = intval($request->interval);
        $totalDays = $column * $interval;
        $arrColumn = [];
        $arrColumn[] = [
            'name'                          => 'Belum jatuh tempo',
            'start'                         => -999999999999999999,
            'end'                           => 0,
            'total'                         => 0,
        ];
        for ($i = 1; $i <= $column; $i++) {
            $end = $i * $interval;
            $start = ($end - $interval) + 1;
            $arrColumn[] = [
                'name'                          => '' . $start . '-' . $end . ' hari',
                'start'                         => $start,
                'end'                           => $end,
                'total'                         => 0,
            ];
        }
        $arrColumn[] = [
            'name'                          => 'Diatas ' . $totalDays . ' hari',
            'start'                         => $totalDays + 1,
            'end'                           => 999999999999999999,
            'total'                         => 0,
        ];

        $newData = [];

        foreach ($results as $row) {
            $balance = $row->grandtotal - $row->total_payment - $row->total_memo;
            info($row->code);
            if ($balance > 0) {
                $daysDiff = $this->dateDiffInDays($row->due_date, $date);
                $index = $this->findDuplicate($row->account_code, $newData);
                if ($index >= 0) {
                    foreach ($newData[$index]['data'] as $key => $rowdata) {
                        if ($daysDiff <= $rowdata['end'] && $daysDiff >= $rowdata['start']) {
                            $newData[$index]['data'][$key]['balance'] += $balance;
                            $newData[$index]['total'] += $balance;
                            $arrColumn[$key]['total'] += $balance;
                            $newData[$index]['credit_balance'] -= $balance;
                            $newData[$index]['data'][$key]['list_invoice'][] = $row->code;
                        }
                    }
                } else {
                    $arrDetail = [];
                    foreach ($arrColumn as $key => $rowcolumn) {
                        if ($daysDiff <= $rowcolumn['end'] && $daysDiff >= $rowcolumn['start']) {
                            $arrDetail[] = [
                                'name'          => $rowcolumn['name'],
                                'start'         => $rowcolumn['start'],
                                'end'           => $rowcolumn['end'],
                                'balance'       => $balance,
                                'list_invoice'  => array($row->code),
                            ];
                            $arrColumn[$key]['total'] += $balance;
                        } else {
                            $arrDetail[] = [
                                'name'          => $rowcolumn['name'],
                                'start'         => $rowcolumn['start'],
                                'end'           => $rowcolumn['end'],
                                'balance'       => 0,
                                'list_invoice'  => [],
                            ];
                        }
                    }
                    $newData[] = [
                        'customer_code'         => $row->account_code,
                        'customer_name'         => $row->account_name,
                        'customer_group'        => $row->grup,
                        'data'                  => $arrDetail,
                        'total'                 => $balance,
                        'credit_balance'        => $row->limit_credit - $balance,
                        'limit_credit'          => $row->limit_credit,
                        'outstand_check'          => $row->outstandcheck ?? 0,

                    ];
                }
            }
        }


        $html = '<table class="bordered" style="font-size:10px;min-width:100% !important;">
        <thead id="head_detail">
            <tr>
                <th rowspan="2" class="center-align">No.</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Code</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Supplier</th>
                    <th rowspan="2" class="center-align" style="min-width:250px !important;">Grup</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Credit Limit</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Sisa Limit</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Outstand BG</th>
                <th rowspan="2" class="center-align" style="min-width:175px !important;">Total Piutang</th>
                <th colspan="' . $countPeriod . '">Nominal Jatuh Tempo (Dari Tgl. Posting dan Tgl. Jatuh Tempo)</th>
            </tr>
            <tr>';

        foreach ($arrColumn as $row) {
            $html .= '<th class="center-align" style="min-width:175px !important;">' . $row['name'] . '</th>';
        }

        $html .= '</tr>
        </thead>
        <tbody>';

        foreach ($newData as $key => $row) {
            $html .= '<tr class="row_detail"><td class="center-align">' . ($key + 1) . '</td><td>' . $row['customer_code'] . '</td><td>' . $row['customer_name'] . '</td><td>' . $row['customer_group'] . '</td><td class="right-align">' . number_format($row['limit_credit'], 2, ',', '.') . '</td><td class="right-align">' . number_format($row['credit_balance'], 2, ',', '.') . '</td><td class="right-align">' . number_format($row['outstand_check'], 2, ',', '.') . '</td>';

            $html .= '<td class="right-align">' . number_format($row['total'], 2, ',', '.') . '</td>';

            foreach ($row['data'] as $rowdetail) {
                $html .= '<td class="right-align ' . ($rowdetail['balance'] > 0 ? 'gradient-45deg-yellow-teal blue-text text-darken-2' : '') . '" onclick="detailShow(this)" data-invoice="' . implode(',', $rowdetail['list_invoice']) . '">' . number_format($rowdetail['balance'], 2, ',', '.') . '</td>';
            }

            $html .= '</tr>';
        }

        $grandtotal = 0;

        foreach ($arrColumn as $row) {
            $grandtotal += $row['total'];
        }

        $html .= '<tr id="text-grandtotal">
                    <td class="right-align" colspan="7">Total</td>
                    <td class="right-align">' . number_format($grandtotal, 2, ',', '.') . '</td>';

        foreach ($arrColumn as $row) {
            $html .= '<td class="right-align">' . number_format($row['total'], 2, ',', '.') . '</td>';
            $grandtotal += $row['total'];
        }

        $html .= '</tr>';

        $end_time = microtime(true);

        $execution_time = ($end_time - $start_time);

        $html .= '<tr id="text-grandtotal">
                    <td colspan="' . ($countPeriod + 3) . '">Waktu proses : ' . $execution_time . ' detik</td>
                </tr>';

        $html .= '</tbody></table>';

        $response = [
            'status'            => 200,
            'content'           => count($newData) > 0 ? $html : '',
        ];

        return response()->json($response);
    }

    public function filterDetail(Request $request)
    {

        $start_time = microtime(true);

        $date = $request->date;

        $results = DB::select("
            SELECT 
                *,
                IFNULL((SELECT 
                    SUM(ipd.subtotal) 
                    FROM incoming_payment_details ipd 
                    JOIN incoming_payments ip
                        ON ip.id = ipd.incoming_payment_id
                    WHERE 
                        ipd.lookable_id = moi.id 
                        AND ipd.lookable_type = 'marketing_order_invoices'
                        AND ip.post_date <= :date1
                        AND ip.status IN ('2','3')
                ),0) AS total_payment,
                0 AS total_memo,
                u.name AS account_name,
                u.employee_no AS account_code, gr.name as grupcust, datediff(:date3,post_date) as invoiceage,
                datediff(:date4,due_date_internal) as dueage, moi.code as invoice
                FROM marketing_order_invoices moi
                JOIN users u
                    ON u.id = moi.account_id
                JOIN `groups` gr on gr.id=u.group_id
                WHERE 
                    moi.post_date <= :date2
                    AND moi.grandtotal > 0
                    AND moi.status IN ('2','3')
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
            'date4' => $date,
        ));

        $countPeriod = 1;
        $column = intval($request->column);
        $countPeriod += $column + 1;
        $interval = intval($request->interval);
        $totalDays = $column * $interval;
        $arrColumn = [];
        $arrColumn[] = [
            'name'                          => 'Belum jatuh tempo',
            'start'                         => -999999999999999999,
            'end'                           => 0,
            'total'                         => 0,
        ];
        for ($i = 1; $i <= $column; $i++) {
            $end = $i * $interval;
            $start = ($end - $interval) + 1;
            $arrColumn[] = [
                'name'                          => '' . $start . '-' . $end . ' hari',
                'start'                         => $start,
                'end'                           => $end,
                'total'                         => 0,
            ];
        }
        $arrColumn[] = [
            'name'                          => 'Diatas ' . $totalDays . ' hari',
            'start'                         => $totalDays + 1,
            'end'                           => 999999999999999999,
            'total'                         => 0,
        ];

        $newData = [];

        foreach ($results as $row) {
            $balance = $row->grandtotal - $row->total_payment - $row->total_memo;
            if ($balance > 0) {
                $daysDiff = $this->dateDiffInDays($row->due_date, $date);
                $arrDetail = [];
                foreach ($arrColumn as $key => $rowcolumn) {
                    if ($daysDiff <= $rowcolumn['end'] && $daysDiff >= $rowcolumn['start']) {
                        $arrDetail[] = [
                            'name'          => $rowcolumn['name'],
                            'start'         => $rowcolumn['start'],
                            'end'           => $rowcolumn['end'],
                            'balance'       => $balance,
                        ];
                        $arrColumn[$key]['total'] += $balance;
                    } else {
                        $arrDetail[] = [
                            'name'          => $rowcolumn['name'],
                            'start'         => $rowcolumn['start'],
                            'end'           => $rowcolumn['end'],
                            'balance'       => 0,
                        ];
                    }
                }
                $newData[] = [
                    'customer_code'         => $row->account_code,
                    'customer_name'         => $row->account_name,
                    'customer_group'         => $row->grupcust,
                    'date' => $row->post_date,
                    'invoice_age' => $row->invoiceage,
                    'due_age' => $row->dueage,
                    'invoice'               => $row->invoice,
                    'data'                  => $arrDetail,
                    'total'                 => $balance,
                ];
            }
        }

        $html = '<table class="bordered" style="font-size:10px;min-width:100% !important;">
        <thead id="head_detail">
            <tr>
                <th rowspan="2" class="center-align">No.</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Code</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Customer</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Grup</th>
               
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Invoice</th>
                 <th rowspan="2" class="center-align" style="min-width:250px !important;">Tanggal Invoice</th>
                 <th rowspan="2" class="center-align" style="min-width:250px !important;">Usia Invoice</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Usia Jatuh Tempo</th>
                <th rowspan="2" class="center-align" style="min-width:175px !important;">Total Piutang</th>
                <th colspan="' . $countPeriod . '">Nominal Jatuh Tempo (Dari Tgl. Posting dan Tgl. Jatuh Tempo)</th>
                
            </tr>
            <tr>';

        foreach ($arrColumn as $row) {
            $html .= '<th class="center-align" style="min-width:175px !important;">' . $row['name'] . '</th>';
        }

        $html .= '</tr>
        </thead>
        <tbody>';

        foreach ($newData as $key => $row) {
            $html .= '<tr class="row_detail"><td class="center-align">' . ($key + 1) . '</td><td>' . $row['customer_code'] . '</td><td>' . $row['customer_name'] . '</td><td>' . $row['customer_group'] . '</td><td>' . $row['invoice'] . '</td><td>' . $row['date'] . '</td><td>' . $row['invoice_age'] . '</td><td>' . $row['due_age'] . '</td>';

            $html .= '<td class="right-align">' . number_format($row['total'], 2, ',', '.') . '</td>';

            foreach ($row['data'] as $rowdetail) {
                $html .= '<td class="right-align ' . ($rowdetail['balance'] > 0 ? 'gradient-45deg-yellow-teal blue-text text-darken-2' : '') . '">' . number_format($rowdetail['balance'], 2, ',', '.') . '</td>';
            }

            $html .= '</tr>';
        }

        $grandtotal = 0;

        foreach ($arrColumn as $row) {
            $grandtotal += $row['total'];
        }

        $html .= '<tr id="text-grandtotal">
                    <td class="right-align" colspan="8">Total</td>
                    <td class="right-align">' . number_format($grandtotal, 2, ',', '.') . '</td>';

        foreach ($arrColumn as $row) {
            $html .= '<td class="right-align">' . number_format($row['total'], 2, ',', '.') . '</td>';
        }

        $html .= '</tr>';

        $end_time = microtime(true);

        $execution_time = ($end_time - $start_time);

        $html .= '<tr id="text-grandtotal">
                    <td colspan="' . ($countPeriod + 3) . '">Waktu proses : ' . $execution_time . ' detik</td>
                </tr>';

        $html .= '</tbody></table>';

        $response = [
            'status'            => 200,
            'content'           => count($newData) > 0 ? $html : '',
        ];

        return response()->json($response);
    }

    public function showDetail(Request $request)
    {

        $arrInvoice = explode(',', $request->invoice);
        $date = $request->date;
        $results = [];
        $grandtotal = 0;

        foreach ($arrInvoice as $row) {
            $prefix = substr($row, 0, 4);
            $pi = MarketingOrderInvoice::where('code', $row)->first();
            if ($pi) {
                $memo = $pi->totalMemoByDate($date);
                $paid = $pi->totalPayByDate($date);
                $balance = $pi->grandtotal - $memo - $paid;
                $results[] = [
                    'code'          => $pi->code,
                    'vendor'        => $pi->account->name,
                    'post_date'     => date('d/m/Y', strtotime($pi->post_date)),
                    'due_date'      => date('d/m/Y', strtotime($pi->due_date)),
                    'due_days'      => $this->dateDiffInDays($pi->due_date, $date),
                    'grandtotal'    => number_format($pi->grandtotal, 2, ',', '.'),
                    'memo'          => number_format($memo, 2, ',', '.'),
                    'paid'          => number_format($paid, 2, ',', '.'),
                    'balance'       => number_format($balance, 2, ',', '.'),
                ];
                $grandtotal += $balance;
            }
        }

        $response = [
            'status'    => 200,
            'result'    => $results,
            'grandtotal' => number_format($grandtotal, 2, ',', '.'),
        ];

        return response()->json($response);
    }

    public function export(Request $request)
    {
        return Excel::download(new ExportAgingAR($request->date, $request->interval, $request->column, $request->type), 'aging_ar_' . uniqid() . '.xlsx');
    }

    function findDuplicate($value, $array)
    {
        $index = -1;
        foreach ($array as $key => $row) {
            if ($row['customer_code'] == $value) {
                $index = $key;
            }
        }
        return $index;
    }

    function dateDiffInDays($date1, $date2)
    {

        // Calculating the difference in timestamps
        $diff = strtotime($date2) - strtotime($date1);

        // 1 day = 24 hours
        // 24 * 60 * 60 = 86400 seconds
        return round($diff / 86400);
    }
}

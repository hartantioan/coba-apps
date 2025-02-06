<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMailOutstandingARInvoiceInternal;
use Illuminate\Support\Facades\DB;
class MailOutstandingARInvoiceInternal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailoutstandingarinvoiceinternal:run';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All cron job and custom script goes here.';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $recipient = ['andrew@superior.co.id', 'henrianto@superior.co.id', 'haidong@superiorporcelain.co.id', 'annabela@superior.co.id', 'yorghi@superior.co.id', 'marisa@superiorporcelain.co.id'];
        $recipient = ['andrew@superior.co.id'];
        $date = date('Y-m-d');
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
                IFNULL((SELECT 
                    gr.name 
                    FROM groups gr
                    WHERE gr.id = u.group_id
                ),'-') AS grup,
                IFNULL((SELECT 
                    SUM(lbc.nominal - IFNULL(lbc.grandtotal,0))
                    FROM list_bg_checks lbc
                    WHERE 
                        lbc.account_id = moi.account_id
                        AND lbc.status IN ('2','3')
                        AND lbc.deleted_at IS NULL
                ),0) AS outstandcheck,
                0 AS total_memo,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM marketing_order_invoices moi
                JOIN users u
                    ON u.id = moi.account_id
                WHERE 
                    moi.post_date <= :date2
                    AND moi.grandtotal > 0
                    AND moi.status IN ('2','3','8')
                    AND IFNULL((SELECT
                        1
                        FROM cancel_documents cd
                        WHERE 
                            cd.post_date <= :date3
                            AND cd.lookable_type = 'marketing_order_invoices'
                            AND cd.lookable_id = moi.id
                            AND cd.deleted_at IS NULL
                    ),0) = 0
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
        ));
        $results2 = DB::select("
            SELECT 
                *,
                IFNULL((SELECT 
                    SUM(ipd.subtotal) 
                    FROM incoming_payment_details ipd 
                    JOIN incoming_payments ip
                        ON ip.id = ipd.incoming_payment_id
                    WHERE 
                        ipd.lookable_id = moi.id 
                        AND ipd.lookable_type = 'marketing_order_memos'
                        AND ip.post_date <= :date1
                        AND ip.status IN ('2','3','8')
                ),0) AS total_payment,
                IFNULL((SELECT 
                    gr.name 
                    FROM groups gr
                    WHERE gr.id = u.group_id
                ),'-') AS grup,
                0 AS outstandcheck,
                0 AS total_memo,
                u.name AS account_name,
                u.employee_no AS account_code
                FROM marketing_order_memos moi
                JOIN users u
                    ON u.id = moi.account_id
                WHERE 
                    moi.post_date <= :date2
                    AND moi.grandtotal > 0
                    AND moi.status IN ('2','3','8')
                    AND IFNULL((SELECT
                        1
                        FROM cancel_documents cd
                        WHERE 
                            cd.post_date <= :date3
                            AND cd.lookable_type = 'marketing_order_memos'
                            AND cd.lookable_id = moi.id
                            AND cd.deleted_at IS NULL
                    ),0) = 0
        ", array(
            'date1' => $date,
            'date2' => $date,
            'date3' => $date,
        ));
        $countPeriod = 1;
        $column = intval(4);
        $countPeriod += $column + 1;
        $interval = intval(30);
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
            if (substr($row->tax_no, 0, 3) == '070') {
                $balance = $row->total - $row->total_payment - $row->total_memo;
            } else {
                $balance = $row->grandtotal - $row->total_payment - $row->total_memo;
            }
            if ($balance > 0) {
                $daysDiff = $this->dateDiffInDays($row->due_date_internal, $date);
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
                        'outstand_check'        => $row->outstandcheck ?? 0,
                    ];
                }
            }
        }
        foreach ($results2 as $row) {
            $balance = (-1 * $row->grandtotal) - $row->total_payment - $row->total_memo;
            if ($balance < 0) {
                $daysDiff = $this->dateDiffInDays($row->post_date, $row->post_date);
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
                        'outstand_check'        => $row->outstandcheck ?? 0,
                    ];
                }
            }
        }
        $html = '<table class="bordered" style="font-size:10px;min-width:100% !important;">
        <thead class="sidebar-sticky" id="head_detail" style="background-color:white;">
            <tr>
                <th rowspan="2" class="center-align">No.</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Code</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Customer</th>
                    <th rowspan="2" class="center-align" style="min-width:250px !important;">Grup</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Credit Limit</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Sisa Limit</th>
                <th rowspan="2" class="center-align" style="min-width:250px !important;">Outstand BG</th>
                <th rowspan="2" class="center-align" style="min-width:175px !important;">Total Piutang</th>
                <th colspan="' . $countPeriod . '">Nominal Jatuh Tempo (Dari Tgl. Posting dan Tgl. Jatuh Tempo Internal)</th>
            </tr>
            <tr>';
        foreach ($arrColumn as $row) {
            $html .= '<th class="center-align" style="min-width:175px !important;">' . $row['name'] . '</th>';
        }
        $html .= '</tr>
        </thead>
        <tbody>';
        foreach ($newData as $key => $row) {
            $html .= '<tr class="row_detail"><td class="center-align" style="left: 0px;position: sticky;background-color:white;">' . ($key + 1) . '</td><td>' . $row['customer_code'] . '</td><td style="left: 50px;position: sticky;background-color:white;">' . $row['customer_name'] . '</td><td>' . $row['customer_group'] . '</td><td class="right-align">' . number_format($row['limit_credit'], 2, ',', '.') . '</td><td class="right-align">' . number_format($row['credit_balance'], 2, ',', '.') . '</td><td class="right-align">' . number_format($row['outstand_check'], 2, ',', '.') . '</td>';
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
                    <td class="right-align" colspan="7" style="min-width:250px !important;"><b>TOTAL</b></td>
                    <td class="right-align"><b>' . number_format($grandtotal, 2, ',', '.') . '</b></td>';
        foreach ($arrColumn as $row) {
            $html .= '<td class="right-align"><b>' . number_format($row['total'], 2, ',', '.') . '</b></td>';
            $grandtotal += $row['total'];
        }
        $html .= '</tr>';
        $data[] = ['val' => $html,];
        $obj = json_decode(json_encode($data));
        Mail::to($recipient)->send(new SendMailOutstandingARInvoiceInternal($obj));
    }
    function dateDiffInDays($date1, $date2)
    {
        // Calculating the difference in timestamps
        $diff = strtotime($date2) - strtotime($date1);
        // 1 day = 24 hours
        // 24 * 60 * 60 = 86400 seconds
        return round($diff / 86400);
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
}

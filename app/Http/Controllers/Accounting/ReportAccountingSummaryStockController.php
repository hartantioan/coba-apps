<?php

namespace App\Http\Controllers\Accounting;

use App\Exports\ExportReportAccountingSummaryStock;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class ReportAccountingSummaryStockController extends Controller
{
    protected $dataplaces, $dataplacecode, $datawarehouses;

    public function __construct()
    {
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];
    }

    public function index(Request $request)
    {
        $parentSegment = request()->segment(2);

        $data = [
            'title'     => 'Summary Stock FG',
            'content'   => 'admin.accounting.report_accounting_summary_stock',

        ];

        return view('admin.layouts.index', ['data' => $data]);
    }

    public function view(Request $request)
    {

        $start_date = $request->start_date;
        $finish_date = $request->finish_date;

        $totalinitial = 0.000;
        $totalreceivefg = 0.000;
        $totalrepackout = 0.000;
        $totalrepackin = 0.000;
        $totalgr = 0.000;
        $totalgi = 0.000;
        $totalqtysjbelumbarcode = 0.000;
        $totalendstockblmbarcode = 0.000;
        $totalqtysjsudahbarcode = 0.000;
        $totalendstock = 0.000;

        $query = DB::select("
              SELECT a.code,a.name,v.`name` AS jenis, br.name AS brand, pa.name AS motif, gr.name AS grade,
	case when br.type='1' then 'HB' ELSE 'OEM' end AS 'kategori',a.shading,coalesce(b.initialstock,0) AS INITIAL,coalesce(b.totalinitial,0) AS totalinitial,COALESCE(c.receivefg,0) AS receivefg,COALESCE(c.totalreceivefg,0) AS totalreceivefg,
            COALESCE(d.repackout,0) AS repackout, COALESCE(d.totalrepackout,0) AS totalrepackout, COALESCE(e.repackin,0) AS repackin, COALESCE(e.totalrepackin,0) AS totalrepackin,COALESCE(f.gr,0) AS gr, COALESCE(f.totalgr,0) AS totalgr,COALESCE(j.rm,0) AS rm, COALESCE(j.totalrm,0) AS totalrm, COALESCE(g.gi,0) AS gi,
            COALESCE(g.totalgi,0) AS totalgi,
            COALESCE(h.qtysjbelumbarcode,0) AS qtysjbelumbarcode,  COALESCE(h.totalsjbelumbarcode,0) AS totalsjbelumbarcode,
             coalesce(b.initialstock,0)+COALESCE(c.receivefg,0)+COALESCE(d.repackout,0)+COALESCE(e.repackin,0)+COALESCE(f.gr,0)+COALESCE(g.gi,0)+COALESCE(h.qtysjbelumbarcode,0) as 'endstockblmbarcode',
              coalesce(b.totalinitial,0)+COALESCE(c.totalreceivefg,0)+COALESCE(d.totalrepackout,0)+COALESCE(e.totalrepackin,0)+COALESCE(f.totalgr,0)+COALESCE(g.totalgi,0)+COALESCE(h.totalsjbelumbarcode,0) as 'totalblmbarcode',
            COALESCE(i.qtysjsudahbarcode,0) AS qtysjsudahbarcode, COALESCE(i.totalsjsudahbarcode,0) AS totalsjsudahbarcode,
            coalesce(b.initialstock,0)+COALESCE(c.receivefg,0)+COALESCE(d.repackout,0)+COALESCE(e.repackin,0)+COALESCE(f.gr,0)+COALESCE(j.rm,0)+COALESCE(g.gi,0)+COALESCE(h.qtysjbelumbarcode,0)+COALESCE(i.qtysjsudahbarcode,0) AS endstock,
				coalesce(b.totalinitial,0)+COALESCE(c.totalreceivefg,0)+COALESCE(d.totalrepackout,0)+COALESCE(e.totalrepackin,0)+COALESCE(f.totalgr,0)+COALESCE(j.totalrm,0)+COALESCE(g.totalgi,0)+COALESCE(h.totalsjbelumbarcode,0)+COALESCE(i.totalsjsudahbarcode,0) AS totalakhir
				 FROM (
            SELECT  distinct a.code,a.name,a.shading FROM (
                    SELECT d.code,d.name,k.code AS shading
                        FROM production_handovers a
                        LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
                        LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
                        LEFT JOIN items d ON d.id=b.item_id
                        LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7
                    UNION ALL
                SELECT d.code,d.name,k.code AS shading
                        FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id
                LEFT JOIN item_units c ON c.id=item_unit_source_id
                LEFT JOIN items d ON d.id=b.item_source_id
                    LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                        WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7
                        UNION ALL
                        SELECT d.code,d.name,k.code AS shading
                        FROM production_repacks a
                LEFT JOIN production_repack_details b ON a.id=b.production_repack_id
                LEFT JOIN item_units c ON c.id=item_unit_target_id
                LEFT JOIN items d ON d.id=b.item_target_id
                    LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                        WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7
                        UNION ALL
                        SELECT d.code,d.name,k.code
                FROM good_receives a
                LEFT JOIN good_receive_details b ON a.id=b.good_receive_id
                LEFT JOIN items d ON d.id=b.item_id
                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                        WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7
                        UNION ALL
                        SELECT d.code,d.name,k.code
                FROM marketing_order_memos a
                LEFT JOIN marketing_order_memo_details b ON a.id=b.marketing_order_memo_id
                LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                LEFT JOIN items d ON d.id=c.item_id
                LEFT JOIN item_shadings k ON k.id=c.item_shading_id
                        WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  )a
                            )a
            LEFT JOIN (
            SELECT code,name,shading, SUM(qty) AS initialstock,SUM(totalinitial) AS totalinitial FROM (
                            SELECT  d.code,d.name,k.code AS shading, coalesce(SUM(b.qty*c.conversion),0) AS Qty,coalesce(SUM(b.total),0) AS totalinitial
                                FROM production_handovers a
                                LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
                                LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
                                LEFT JOIN items d ON d.id=b.item_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                            WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 AND a.post_date<'" . $start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT d.code,d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS RepackOut, coalesce(SUM(b.total),0)*-1 AS total
                                FROM production_repacks a
                            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                            LEFT JOIN item_units c ON c.id=item_unit_source_id
                            LEFT JOIN items d ON d.id=b.item_source_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id AND d.item_group_id=7  AND a.post_date<'" . $start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT d.code,d.name,k.code, coalesce(SUM(b.qty),0) AS RepackIn, coalesce(SUM(b.total),0) AS total
                                FROM production_repacks a
                            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                            LEFT JOIN item_units c ON c.id=item_unit_target_id
                            LEFT JOIN items d ON d.id=b.item_target_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date<'" . $start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT d.code,d.name,k.code, coalesce(SUM(b.qty),0) AS GR, coalesce(SUM(b.total),0) AS total
                            FROM good_receives a
                            LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                            LEFT JOIN items d ON d.id=b.item_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date<'" . $start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT d.code,d.name,k.code, coalesce(SUM(b.qty),0) AS RM, coalesce(e.nominal,0) AS total
                            FROM marketing_order_memos a
                            LEFT JOIN marketing_order_memo_details b ON a.id=b.marketing_order_memo_id and b.deleted_at is null
                            LEFT JOIN journal_details e ON e.detailable_id = b.id and e.detailable_type = 'marketing_order_memo_details' and e.deleted_at is null and e.type = '1'
                            LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                            LEFT JOIN items d ON d.id=c.item_id
                            LEFT JOIN item_shadings k ON k.id=c.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date<'" . $start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT d.code,d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS GI, coalesce(SUM(b.total)*-1,0) AS total
                            FROM good_issues a
                            LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                            LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                            LEFT JOIN items d ON d.id=c.item_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date<'" . $start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT c.code,c.name,k.code, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ, coalesce(SUM(b.total)*-1,0) AS total
                                FROM marketing_order_delivery_processes a
                                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
                                LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
                                LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
                                LEFT JOIN item_stocks l ON l.id=b.item_stock_id
                                LEFT JOIN items c ON c.id=e.item_id
                            LEFT JOIN item_shadings k ON k.id=l.item_shading_id
                                WHERE a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  AND a.post_date<'" . $start_date . "'
                            GROUP BY c.`code`,c.name,k.code)a GROUP BY code,NAME,shading)b ON a.code=b.code AND a.shading=b.shading
                            LEFT JOIN (
                            SELECT  d.code,d.name,k.code AS shading, coalesce(SUM(b.qty*c.conversion),0) AS receivefg,coalesce(SUM(b.total),0) AS totalreceivefg
                                FROM production_handovers a
                                LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
                                LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
                                LEFT JOIN items d ON d.id=b.item_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                            WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 AND a.post_date>='" . $start_date . "' AND a.post_date<='" . $finish_date . "'
                            GROUP BY d.code,d.name,k.code)c ON c.code=a.code AND c.shading=a.shading
                            LEFT JOIN (
                            SELECT d.code,d.name,k.code AS shading, coalesce(SUM(b.qty),0)*-1 AS RepackOut,coalesce(SUM(b.total)*-1,0) AS totalrepackout
                                FROM production_repacks a
                            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                            LEFT JOIN item_units c ON c.id=item_unit_source_id
                            LEFT JOIN items d ON d.id=b.item_source_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id AND d.item_group_id=7  AND a.post_date>='" . $start_date . "' AND a.post_date<='" . $finish_date . "'
                            GROUP BY d.code,d.name,k.code
                                )d ON d.code=a.code AND d.shading=a.shading
                                LEFT JOIN (
                                SELECT d.code,d.name,k.code AS shading, coalesce(SUM(b.qty),0) AS RepackIn,coalesce(SUM(b.total),0) AS totalrepackin
                                FROM production_repacks a
                            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                            LEFT JOIN item_units c ON c.id=item_unit_target_id
                            LEFT JOIN items d ON d.id=b.item_target_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date>='" . $start_date . "' AND a.post_date<='" . $finish_date . "'
                            GROUP BY d.code,d.name,k.code
                                )e ON e.code=a.code AND e.shading=a.shading
                                LEFT JOIN (
                                    SELECT d.code,d.name,k.code AS shading, coalesce(SUM(b.qty),0) AS GR,coalesce(SUM(b.total),0) AS totalgr
                            FROM good_receives a
                            LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                            LEFT JOIN items d ON d.id=b.item_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date>='" . $start_date . "' AND a.post_date<='" . $finish_date . "'
                            GROUP BY d.code,d.name,k.code
                                )f ON f.code=a.code AND f.shading=a.shading
                                LEFT JOIN (
                                    SELECT d.code,d.name,k.code AS shading, coalesce(SUM(b.qty),0) AS RM,coalesce(e.nominal,0) AS totalrm
                            FROM marketing_order_memos a
                            LEFT JOIN marketing_order_memo_details b ON a.id=b.marketing_order_memo_id and b.deleted_at is null
                            LEFT JOIN journal_details e ON e.detailable_id = b.id and e.detailable_type = 'marketing_order_memo_details' and e.deleted_at is null and e.type = '1'
                            LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                            LEFT JOIN items d ON d.id=c.item_id
                            LEFT JOIN item_shadings k ON k.id=c.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date>='" . $start_date . "' AND a.post_date<='" . $finish_date . "'
                            GROUP BY d.code,d.name,k.code
                                )j ON j.code=a.code AND j.shading=a.shading
                                LEFT JOIN (
                                SELECT d.code,d.name,k.code AS shading, coalesce(SUM(b.qty),0)*-1 AS GI,coalesce(SUM(b.total)*-1,0) AS totalgi
                            FROM good_issues a
                            LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                            LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                            LEFT JOIN items d ON d.id=c.item_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date>='" . $start_date . "' AND a.post_date<='" . $finish_date . "'
                            GROUP BY d.code,d.name,k.code
                                )g ON g.code=a.code AND g.shading=a.shading
                                LEFT JOIN (
                                SELECT c.code,c.name,k.code AS shading, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJbelumbarcode,coalesce(SUM(b.total)*-1,0) AS totalsjbelumbarcode
                                FROM marketing_order_delivery_processes a
                                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
                                LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
                                LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
                                LEFT JOIN item_stocks l ON l.id=b.item_stock_id
                                LEFT JOIN items c ON c.id=e.item_id
                                LEFT JOIN (select distinct marketing_order_delivery_process_id from marketing_order_delivery_process_tracks where status=1 and deleted_at is null )mo ON mo.marketing_order_delivery_process_id=a.id 
                                LEFT JOIN item_shadings k ON k.id=l.item_shading_id
                                WHERE a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  AND a.post_date>='" . $start_date . "' AND a.post_date<='" . $finish_date . "'
                            and a.id not in (select marketing_order_delivery_process_id from  marketing_order_delivery_process_tracks where status ='2' and created_at <= '" . $finish_date . " 23:59:59' and deleted_at is null)
                                    GROUP BY c.`code`,c.name,k.code
                                )h ON h.code=a.code and h.shading=a.shading
                                LEFT JOIN (
                                SELECT c.code,c.name,k.code AS shading, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJsudahbarcode,coalesce(SUM(b.total)*-1,0) AS totalsjsudahbarcode
                                FROM marketing_order_delivery_processes a
                                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
                                LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
                                LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
                                LEFT JOIN item_stocks l ON l.id=b.item_stock_id
                                LEFT JOIN items c ON c.id=e.item_id
                                LEFT JOIN (select distinct marketing_order_delivery_process_id from marketing_order_delivery_process_tracks where status=2 and deleted_at is null)mo ON mo.marketing_order_delivery_process_id=a.id 
                            LEFT JOIN item_shadings k ON k.id=l.item_shading_id
                                WHERE a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  AND a.post_date>='" . $start_date . "' AND a.post_date<='" . $finish_date . "'
                        and a.id in (select  marketing_order_delivery_process_id from  marketing_order_delivery_process_tracks where status='2' and deleted_at is null)
                                    GROUP BY c.`code`,c.name,k.code
                                )i ON i.code=a.code and i.shading=a.shading 
                                LEFT JOIN items it ON it.code=a.code
                                LEFT JOIN `types` v ON v.code=it.type_id
                                LEFT JOIN brands br ON br.id=it.brand_id
                                LEFT JOIN patterns pa ON pa.id=it.pattern_id
                                LEFT JOIN grades gr ON gr.id=it.grade_id 
                                ");


        $html = '<table class="bordered" style="font-size:10px;min-width:100% !important;">
        <thead id="head_detail">
            <tr>
                <th  class="center-align">No.</th>
                <th  class="center-align" style="min-width:150px !important;">Kode Item</th>
                <th  class="center-align" style="min-width:180px !important;">Nama Item</th>
                <th  class="center-align" style="min-width:50px !important;">Jenis</th>
                <th  class="center-align" style="min-width:150px !important;">Brand</th>
                <th  class="center-align" style="min-width:250px !important;">Motif</th>
                <th  class="center-align" style="min-width:50px !important;">Grade</th>
                <th  class="center-align" style="min-width:50px !important;">Kategori</th>
                <th  class="center-align" style="min-width:50px !important;">Shading</th>
                <th  class="center-align" style="min-width:50px !important;">Initial (M2)</th>
                <th  class="center-align" style="min-width:50px !important;">Receive FG (M2)</th>
                <th  class="center-align" style="min-width:50px !important;">Repack Out (M2)</th>
                <th  class="center-align" style="min-width:50px !important;">Repack In (M2)</th>
                <th  class="center-align" style="min-width:50px !important;">GR (M2)</th>
                <th  class="center-align" style="min-width:50px !important;">GI (M2)</th>
                <th  class="center-align" style="min-width:50px !important;">Delivery Blm Barcode (M2)</th>
                <th  class="center-align" style="min-width:50px !important;">End Stock (Delivery Blm Barcode) (M2)</th>
                <th  class="center-align" style="min-width:50px !important;">Delivery Sudah Barcode (M2)</th>
                <th  class="center-align" style="min-width:50px !important;">End Stock All (M2)</th>
                
            </tr>
          </thead><tbody><tr>';
        foreach ($query as $key => $row) {

            $html .= '<tr class="row_detail"><td class="center-align">' . ($key + 1) . '</td><td>' . $row->code . '</td><td>' . $row->name . '</td><td>' . $row->jenis . '</td><td>' . $row->brand   . '</td><td>' . $row->motif   . '</td><td>' . $row->grade   . '</td><td>' . $row->kategori   . '</td><td>' . $row->shading   . '</td>
            <td class="right-align">' . round($row->INITIAL, 3)   . '</td><td class="right-align">' . round($row->receivefg, 3)   . '</td><td class="right-align">' . round($row->repackout, 3)   . '</td><td class="right-align">' . round($row->repackin, 3)   . '</td><td class="right-align">' . round($row->gr, 3)   . '</td><td class="right-align">' . round($row->gi, 3)   . '</td><td class="right-align">' . round($row->qtysjbelumbarcode, 3)   . '</td>
            <td class="right-align">' . round($row->endstockblmbarcode, 3)   . '</td><td class="right-align">' . round($row->qtysjsudahbarcode, 3)   . '</td><td class="right-align">' . round($row->endstock, 3)   . '</td>
            </tr>';

            $totalinitial += round($row->INITIAL, 3);
            $totalreceivefg += round($row->receivefg, 3);
            $totalrepackout += round($row->repackout, 3);
            $totalrepackin += round($row->repackin, 3);
            $totalgr += round($row->gr, 3);
            $totalgi += round($row->gi, 3);
            $totalqtysjbelumbarcode += round($row->qtysjbelumbarcode, 3);
            $totalendstockblmbarcode += round($row->endstockblmbarcode, 3);
            $totalqtysjsudahbarcode += round($row->qtysjsudahbarcode, 3);
            $totalendstock += round($row->endstock, 3);
        }
        $html .= '<tr class="row_detail"><td colspan="9" class="center-align">TOTAL</td><td class="right-align">' . round($totalinitial, 3)   . '</td>
        <td class="right-align">' . round($totalreceivefg, 3)   . '</td>
        <td class="right-align">' . round($totalrepackout, 3)   . '</td>
        <td class="right-align">' . round($totalrepackin, 3)   . '</td>
        <td class="right-align">' . round($totalgr, 3)   . '</td>
        <td class="right-align">' . round($totalgi, 3)   . '</td>
        <td class="right-align">' . round($totalqtysjbelumbarcode, 3)   . '</td>
        <td class="right-align">' . round($totalendstockblmbarcode, 3)   . '</td>
        <td class="right-align">' . round($totalqtysjsudahbarcode, 3)   . '</td>
         <td class="right-align">' . round($totalendstock, 3)   . '</td></tr>';


        $html .= '
          
          </tbody></table>';



        $response = [
            'status'            => 200,
            'content'           => count($query) > 0 ? $html : '',
        ];

        return response()->json($response);
    }



    public function export(Request $request)
    {

        $start_date = $request->start_date ? $request->start_date : '';
        $finish_date = $request->finish_date ? $request->finish_date : '';

        $user_id = session('bo_id');




        return Excel::download(new ExportReportAccountingSummaryStock($start_date, $finish_date), 'summary_stock_fg_value' . uniqid() . '.xlsx');
    }
}

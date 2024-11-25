<?php

namespace App\Exports;


use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportSummaryStockFG2 implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date;
    protected $finish_date;
    public function __construct(string $start_date, string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }
    private $headings = [
        'Kode',
        'Item',
        'Jenis',
        'Brand',
        'Motif',
        'Grade',
        'Kategori',
        'Shading',
        'Initial (M2)',
        'Receive FG (M2)',
        'Repack Out (M2)',
        'Repack In (M2)',
        'GR (M2)',
        'GI (M2)',
        'Delivery Belum Barcode(M2)',
        'End Stock Delivery Belum Barcode (M2)',
        'Delivery Sudah Barcode(M2)',
        'End Stock All(M2)',


    ];



    public function collection()
    {

        $arr = [];

        $query = DB::select("
              SELECT a.code,a.name,v.`name` AS jenis, br.name AS brand, pa.name AS motif, gr.name AS grade,
	case when br.type='1' then 'HB' ELSE 'OEM' end AS 'kategori',a.shading,coalesce(b.initialstock,0) AS initial,COALESCE(c.receivefg,0) AS receivefg,
            COALESCE(d.repackout,0) AS repackout, COALESCE(e.repackin,0) AS repackin,COALESCE(f.gr,0) AS gr,COALESCE(g.gi,0) AS gi,
            COALESCE(h.qtysjbelumbarcode,0) AS qtysjbelumbarcode,  
             coalesce(b.initialstock,0)+COALESCE(c.receivefg,0)+COALESCE(d.repackout,0)+COALESCE(e.repackin,0)+COALESCE(f.gr,0)+COALESCE(g.gi,0)+COALESCE(h.qtysjbelumbarcode,0) as 'endstockblmbarcode',
            COALESCE(i.qtysjsudahbarcode,0) AS qtysjsudahbarcode,
            coalesce(b.initialstock,0)+COALESCE(c.receivefg,0)+COALESCE(d.repackout,0)+COALESCE(e.repackin,0)+COALESCE(f.gr,0)+COALESCE(g.gi,0)+COALESCE(h.qtysjbelumbarcode,0)+COALESCE(i.qtysjsudahbarcode,0) AS endstock FROM (
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
                        WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  )a
                            )a
            LEFT JOIN (
            SELECT code,name,shading, SUM(qty) AS initialstock FROM (
                            SELECT  d.code,d.name,k.code AS shading, coalesce(SUM(b.qty*c.conversion),0) AS Qty
                                FROM production_handovers a
                                LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
                                LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
                                LEFT JOIN items d ON d.id=b.item_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                            WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 AND a.post_date<'" . $this->start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT d.code,d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS RepackOut
                                FROM production_repacks a
                            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                            LEFT JOIN item_units c ON c.id=item_unit_source_id
                            LEFT JOIN items d ON d.id=b.item_source_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id AND d.item_group_id=7  AND a.post_date<'" . $this->start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT d.code,d.name,k.code, coalesce(SUM(b.qty),0) AS RepackIn
                                FROM production_repacks a
                            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                            LEFT JOIN item_units c ON c.id=item_unit_target_id
                            LEFT JOIN items d ON d.id=b.item_target_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date<'" . $this->start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT d.code,d.name,k.code, coalesce(SUM(b.qty),0) AS GR
                            FROM good_receives a
                            LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                            LEFT JOIN items d ON d.id=b.item_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date<'" . $this->start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT d.code,d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS GI
                            FROM good_issues a
                            LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                            LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                            LEFT JOIN items d ON d.id=c.item_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date<'" . $this->start_date . "'
                            GROUP BY d.code,d.name,k.code
                            UNION ALL
                            SELECT c.code,c.name,k.code, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
                                FROM marketing_order_delivery_processes a
                                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
                                LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
                                LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
                                LEFT JOIN item_stocks l ON l.id=b.item_stock_id
                                LEFT JOIN items c ON c.id=e.item_id
                            LEFT JOIN item_shadings k ON k.id=l.item_shading_id
                                WHERE a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  AND a.post_date<'" . $this->start_date . "'
                            GROUP BY c.`code`,c.name,k.code)a GROUP BY code,NAME,shading)b ON a.code=b.code AND a.shading=b.shading
                            LEFT JOIN (
                            SELECT  d.code,d.name,k.code AS shading, coalesce(SUM(b.qty*c.conversion),0) AS receivefg
                                FROM production_handovers a
                                LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
                                LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
                                LEFT JOIN items d ON d.id=b.item_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                            WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 AND a.post_date>='" . $this->start_date . "' AND a.post_date<='" . $this->finish_date . "'
                            GROUP BY d.code,d.name,k.code)c ON c.code=a.code AND c.shading=a.shading
                            LEFT JOIN (
                            SELECT d.code,d.name,k.code AS shading, coalesce(SUM(b.qty),0)*-1 AS RepackOut
                                FROM production_repacks a
                            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                            LEFT JOIN item_units c ON c.id=item_unit_source_id
                            LEFT JOIN items d ON d.id=b.item_source_id
                                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id AND d.item_group_id=7  AND a.post_date>='" . $this->start_date . "' AND a.post_date<='" . $this->finish_date . "'
                            GROUP BY d.code,d.name,k.code
                                )d ON d.code=a.code AND d.shading=a.shading
                                LEFT JOIN (
                                SELECT d.code,d.name,k.code AS shading, coalesce(SUM(b.qty),0) AS RepackIn
                                FROM production_repacks a
                            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
                            LEFT JOIN item_units c ON c.id=item_unit_target_id
                            LEFT JOIN items d ON d.id=b.item_target_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date>='" . $this->start_date . "' AND a.post_date<='" . $this->finish_date . "'
                            GROUP BY d.code,d.name,k.code
                                )e ON e.code=a.code AND e.shading=a.shading
                                LEFT JOIN (
                                    SELECT d.code,d.name,k.code AS shading, coalesce(SUM(b.qty),0) AS GR
                            FROM good_receives a
                            LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
                            LEFT JOIN items d ON d.id=b.item_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date>='" . $this->start_date . "' AND a.post_date<='" . $this->finish_date . "'
                            GROUP BY d.code,d.name,k.code
                                )f ON f.code=a.code AND f.shading=a.shading
                                LEFT JOIN (
                                SELECT d.code,d.name,k.code AS shading, coalesce(SUM(b.qty),0)*-1 AS GI
                            FROM good_issues a
                            LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
                            LEFT JOIN item_stocks c ON c.id=b.item_stock_id
                            LEFT JOIN items d ON d.id=c.item_id
                            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  AND a.post_date>='" . $this->start_date . "' AND a.post_date<='" . $this->finish_date . "'
                            GROUP BY d.code,d.name,k.code
                                )g ON g.code=a.code AND g.shading=a.shading
                                LEFT JOIN (
                                SELECT c.code,c.name,k.code AS shading, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJbelumbarcode
                                FROM marketing_order_delivery_processes a
                                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
                                LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
                                LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
                                LEFT JOIN item_stocks l ON l.id=b.item_stock_id
                                LEFT JOIN items c ON c.id=e.item_id
                                LEFT JOIN (select distinct marketing_order_delivery_process_id from marketing_order_delivery_process_tracks where status=1 and deleted_at is null )mo ON mo.marketing_order_delivery_process_id=a.id 
                                LEFT JOIN item_shadings k ON k.id=l.item_shading_id
                                WHERE a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  AND a.post_date>='" . $this->start_date . "' AND a.post_date<='" . $this->finish_date . "'
                            and a.id not in (select marketing_order_delivery_process_id from  marketing_order_delivery_process_tracks where status ='2' and created_at <= '" . $this->finish_date . " 23:59:59' and deleted_at is null)
                                    GROUP BY c.`code`,c.name,k.code
                                )h ON h.code=a.code and h.shading=a.shading
                                LEFT JOIN (
                                SELECT c.code,c.name,k.code AS shading, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJsudahbarcode
                                FROM marketing_order_delivery_processes a
                                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
                                LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
                                LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
                                LEFT JOIN item_stocks l ON l.id=b.item_stock_id
                                LEFT JOIN items c ON c.id=e.item_id
                                LEFT JOIN (select distinct marketing_order_delivery_process_id from marketing_order_delivery_process_tracks where status=2 and deleted_at is null)mo ON mo.marketing_order_delivery_process_id=a.id 
                            LEFT JOIN item_shadings k ON k.id=l.item_shading_id
                                WHERE a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  AND a.post_date>='" . $this->start_date . "' AND a.post_date<='" . $this->finish_date . "'
                        and a.id in (select  marketing_order_delivery_process_id from  marketing_order_delivery_process_tracks where status='2' and created_at <= '" . $this->finish_date . " 23:59:59' and deleted_at is null)
                                    GROUP BY c.`code`,c.name,k.code
                                )i ON i.code=a.code and i.shading=a.shading 
                                LEFT JOIN items it ON it.code=a.code
                                LEFT JOIN `types` v ON v.code=it.type_id
                                LEFT JOIN brands br ON br.id=it.brand_id
                                LEFT JOIN patterns pa ON pa.id=it.pattern_id
                                LEFT JOIN grades gr ON gr.id=it.grade_id 
                                order by a.name,a.shading");

        foreach ($query as $row) {

            $arr[] = [
                'kode' => $row->code,
                'name' => $row->name,
                'Jenis' =>$row->jenis,
                'Brand' =>$row->brand,
                'Motif' =>$row->motif,
                'Grade' =>$row->grade,
                'Kategori' =>$row->kategori,
                'shading' => $row->shading,
                'initial' => $row->initial,
                'receivefg' => $row->receivefg,
                'repackout' => $row->repackout,
                'repackin' => $row->repackin,
                'gr' => $row->gr,
                'gi' => $row->gi,
                'qtysjbelumbarcode' => $row->qtysjbelumbarcode,
                'endstockblmbarcode' => $row->endstockblmbarcode,
                'qtysjsudahbarcode' => $row->qtysjsudahbarcode,
                'endstock' => $row->endstock
            ];
        }






        return collect($arr);
    }

    public function title(): string
    {
        return 'Stock';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

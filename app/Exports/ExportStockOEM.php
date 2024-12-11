<?php

namespace App\Exports;

use App\Models\InventoryTransferOut;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class ExportStockOEM implements FromView, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */



    public function __construct() {}

    public function view(): View
    {

        $query = DB::select("	
        SELECT brand ,tipe ,jenis,pattern,grade,CODE, SUM(qty) AS endstock FROM (
         SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty*c.conversion),0) AS Qty
              FROM production_handovers a
              LEFT JOIN production_handover_details b ON a.id=b.production_handover_id and b.deleted_at is null
              LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
              LEFT JOIN items d ON d.id=b.item_id
               INNER JOIN types e ON e.id=d.type_id
         INNER JOIN brands f ON f.id=d.brand_id
         INNER JOIN varieties g ON g.id=d.variety_id
             LEFT JOIN patterns i ON i.id=d.pattern_id
             LEFT JOIN grades j ON j.id=d.grade_id
             LEFT JOIN item_shadings k ON k.id=b.item_shading_id
         WHERE a.void_date IS NULL AND a.deleted_at IS NULL
         GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
         UNION ALL
         SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty),0)*-1 AS RepackOut
              FROM production_repacks a
         LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
         LEFT JOIN item_units c ON c.id=item_unit_source_id
         LEFT JOIN items d ON d.id=b.item_source_id
         INNER JOIN types e ON e.id=d.type_id
         INNER JOIN brands f ON f.id=d.brand_id
         INNER JOIN varieties g ON g.id=d.variety_id
             LEFT JOIN patterns i ON i.id=d.pattern_id
             LEFT JOIN grades j ON j.id=d.grade_id
                 LEFT JOIN item_shadings k ON k.id=b.item_shading_id
              WHERE a.void_date IS NULL AND a.deleted_at IS NULL
         GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
         UNION ALL
         SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty),0) AS RepackIn
              FROM production_repacks a
         LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
         LEFT JOIN item_units c ON c.id=item_unit_target_id
         LEFT JOIN items d ON d.id=b.item_target_id
         INNER JOIN types e ON e.id=d.type_id
         INNER JOIN brands f ON f.id=d.brand_id
         INNER JOIN varieties g ON g.id=d.variety_id
             LEFT JOIN patterns i ON i.id=d.pattern_id
             LEFT JOIN grades j ON j.id=d.grade_id
                 LEFT JOIN item_shadings k ON k.id=b.item_shading_id
              WHERE a.void_date IS NULL AND a.deleted_at IS NULL
         GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
         UNION ALL
         SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty),0) AS GR
         FROM good_receives a
         LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
         LEFT JOIN items d ON d.id=b.item_id
         INNER JOIN types e ON e.id=d.type_id
         INNER JOIN brands f ON f.id=d.brand_id
         INNER JOIN varieties g ON g.id=d.variety_id
             LEFT JOIN patterns i ON i.id=d.pattern_id
             LEFT JOIN grades j ON j.id=d.grade_id
                 LEFT JOIN item_shadings k ON k.id=b.item_shading_id
              WHERE a.void_date IS NULL AND a.deleted_at IS null
         GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
         UNION ALL
         SELECT e.`name` AS tipe, f.name AS brand, g.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty),0)*-1 AS GI
         FROM good_issues a
         LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
         LEFT JOIN item_stocks c ON c.id=b.item_stock_id
         LEFT JOIN items d ON d.id=c.item_id
         INNER JOIN types e ON e.id=d.type_id
         INNER JOIN brands f ON f.id=d.brand_id
         INNER JOIN varieties g ON g.id=d.variety_id
             LEFT JOIN patterns i ON i.id=d.pattern_id
             LEFT JOIN grades j ON j.id=d.grade_id
                 LEFT JOIN item_shadings k ON k.id=b.item_shading_id
              WHERE a.void_date IS NULL AND a.deleted_at IS null
         GROUP BY e.name,f.name,g.name, i.name,j.name,k.code
         UNION ALL
        SELECT d.`name` AS tipe,g.name AS brand,h.name AS jenis,i.name AS pattern,j.`name` AS grade,k.code, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
             FROM marketing_order_delivery_processes a
             LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id and b.deleted_at is null
             LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
             LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
             LEFT JOIN item_stocks l ON l.id=b.item_stock_id
             LEFT JOIN items c ON c.id=e.item_id
             LEFT JOIN types d ON d.id=c.type_id
             LEFT JOIN brands g ON g.id=c.brand_id
             LEFT JOIN varieties h ON h.id=c.variety_id
             LEFT JOIN patterns i ON i.id=c.pattern_id
             LEFT JOIN grades j ON j.id=c.grade_id
                 LEFT JOIN item_shadings k ON k.id=l.item_shading_id
             WHERE a.void_date is null AND a.deleted_at is NULL
        GROUP BY d.name,g.`name`,h.name, i.name,j.name,k.code)a GROUP BY tipe,brand,jenis,pattern,grade,code");


        foreach ($query as $row) {

            $data5[] = [
                'brand'  => $row->brand,
                'tipe'  => $row->tipe,
                'jenis'  => $row->jenis,
                'pattern'  => $row->pattern,
                'grade'  => $row->grade,
                'code'  => $row->CODE,
                'endstock'  => $row->endstock,



            ];
        }

        $obj5 = json_decode(json_encode($data5));
        return view('admin.exports.inventory_stock_oem', [
            'data5' => $obj5
        ]);
    }
}

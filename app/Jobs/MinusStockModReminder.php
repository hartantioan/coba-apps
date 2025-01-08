<?php

namespace App\Jobs;

use App\Helpers\WaBlas;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class MinusStockModReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->queue = 'wablas';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $query = DB::select("
        
                SELECT a.name,a.shading, a.initial as stock, COALESCE(b.total,0) as outstandmod,a.initial-coalesce(b.total,0) AS sisa FROM (
            SELECT a.name,a.shading, SUM(qty) AS initial FROM (

            SELECT  d.name,k.code AS shading, coalesce(SUM(b.qty*c.conversion),0) AS Qty
                FROM production_handovers a
                LEFT JOIN production_handover_details b ON a.id=b.production_handover_id
                LEFT JOIN production_fg_receive_details c ON c.id=b.production_fg_receive_detail_id and c.deleted_at IS null
                LEFT JOIN items d ON d.id=b.item_id
                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
            WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 
            GROUP BY d.code,d.name,k.code
            UNION ALL
            SELECT d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS RepackOut
                FROM production_repacks a
            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
            LEFT JOIN item_units c ON c.id=item_unit_source_id
            LEFT JOIN items d ON d.id=b.item_source_id
                LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id AND d.item_group_id=7  
            GROUP BY d.name,k.code
            UNION ALL
            SELECT d.name,k.code, coalesce(SUM(b.qty),0) AS RepackIn
                FROM production_repacks a
            LEFT JOIN production_repack_details b ON a.id=b.production_repack_id and b.deleted_at is null
            LEFT JOIN item_units c ON c.id=item_unit_target_id
            LEFT JOIN items d ON d.id=b.item_target_id
            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
            GROUP BY d.name,k.code
            UNION ALL
            SELECT d.name,k.code, coalesce(SUM(b.qty),0) AS GR
            FROM good_receives a
            LEFT JOIN good_receive_details b ON a.id=b.good_receive_id and b.deleted_at is null
            LEFT JOIN items d ON d.id=b.item_id
            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
            GROUP BY d.name,k.code
            UNION ALL
            SELECT d.name,k.code, coalesce(SUM(b.qty),0) AS GR
            FROM marketing_order_memos a
            LEFT JOIN marketing_order_memo_details b ON a.id=b.marketing_order_memo_id and b.deleted_at is null
            LEFT JOIN item_stocks c ON c.id=b.item_stock_id
            LEFT JOIN items d ON d.id=c.item_id
            LEFT JOIN item_shadings k ON k.id=c.item_shading_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7  
            GROUP BY d.name,k.code
            UNION ALL
            SELECT d.name,k.code, coalesce(SUM(b.qty),0)*-1 AS GI
            FROM good_issues a
            LEFT JOIN good_issue_details b ON a.id=b.good_issue_id and b.deleted_at is null
            LEFT JOIN item_stocks c ON c.id=b.item_stock_id
            LEFT JOIN items d ON d.id=c.item_id
            LEFT JOIN item_shadings k ON k.id=b.item_shading_id
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND d.item_group_id=7 
            GROUP BY d.name,k.code
            UNION ALL
            SELECT c.name,k.code, coalesce(SUM(b.qty*f.qty_conversion),0)*-1 AS qtySJ
                FROM marketing_order_delivery_processes a
                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
                LEFT JOIN marketing_order_delivery_details e ON e.id=b.marketing_order_delivery_detail_id and e.deleted_at is null
                LEFT JOIN marketing_order_details f ON f.id=e.marketing_order_detail_id and f.deleted_at is null
                LEFT JOIN item_stocks l ON l.id=b.item_stock_id
                LEFT JOIN items c ON c.id=e.item_id
            LEFT JOIN item_shadings k ON k.id=l.item_shading_id
                WHERE a.void_date is null AND a.deleted_at is NULL AND c.item_group_id=7  
            GROUP BY c.name,k.code)a GROUP BY NAME,shading)a

            LEFT JOIN (            
            SELECT f.`name`,g.`code` AS shading, sum(c.qty*h.qty_conversion) AS total 

            FROM marketing_order_deliveries a 
            LEFT JOIN marketing_order_delivery_details b ON b.marketing_order_delivery_id=a.id AND b.deleted_at IS null
            LEFT JOIN marketing_order_delivery_detail_stocks c ON b.id=c.marketing_order_delivery_detail_id AND c.deleted_at IS NULL 
            LEFT JOIN (SELECT b.marketing_order_delivery_detail_id FROM marketing_order_delivery_processes a 
                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id AND b.deleted_at IS null
                WHERE a.void_date IS NULL AND a.deleted_at IS NULL 
            )d ON d.marketing_order_delivery_detail_id = b.id 
            LEFT JOIN items f ON f.id=b.item_id
            LEFT JOIN item_shadings g ON g.id=c.item_shading_id
            LEFT JOIN marketing_order_details h ON h.id=b.marketing_order_detail_id
            WHERE d.marketing_order_delivery_detail_id IS NULL AND a.void_date IS NULL AND a.deleted_at IS NULL
            GROUP BY f.name,g.code)b ON a.name=b.name AND a.shading=b.shading where COALESCE(b.total,0)>0");
        $message = '';
        $array = [];
        foreach ($query as $key => $row) {
            if(round($row->sisa,3) < 0){
                $array[] = 'Item : '.$row->name.' - Shading : '.$row->shading.' Sisa  : '.round($row->sisa,3);
            }
        }
        if(count($array) > 0){
            $message = implode(', ',$array);
            WaBlas::kirim_wa('081365590831','Halo, bund. Terdapat qty stock yang minus ya berikut daftarnya: \n'.$message.' \nIni adalah pesan otomatis jangan dibalas ya. Terima kasih');
            WaBlas::kirim_wa('081330074432','Halo, bund. Terdapat qty stock yang minus ya berikut daftarnya: \n'.$message.' \nIni adalah pesan otomatis jangan dibalas ya. Terima kasih');
        }
    }
}

<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\ProductionBatch;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\DB;

class ExportReportStockInRupiahShadingBatchAccounting implements FromArray, WithTitle, ShouldAutoSize
{
    protected $start_date, $finish_date,$place_id,$warehouse_id;

    public function __construct(string $start_date,string $place_id, string $warehouse_id)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->place_id = $place_id ? $place_id : '';
        $this->warehouse_id = $warehouse_id ? $warehouse_id : '';
    }

    public function array(): array
    {
        $arr = [];
        
        DB::statement("SET SQL_MODE=''");

        $arr[] = [
            'No.',
            'Plant',
            'Kode Item',
            'Nama Item',
            'Satuan',
            'Shading',
            'Balance Qty',
            'Balance Nominal',
        ];
        
        $datadetail = DB::select("
            SELECT 
                rs.batch_code AS batch_code,
                rs.shading_code AS shading_code,
                rs.item_code AS item_code,
                rs.item_name AS item_name,
                rs.place_code AS place_code,
                rs.total_qty_in AS total_qty_in,
                rs.total_qty_out AS total_qty_out,
                (rs.total_qty_in - rs.total_qty_out) AS balance_qty,
                rs.total_in AS total_in,
                rs.total_out AS total_out,
                (rs.total_in - rs.total_out) AS balance_nominal
                FROM (
                    SELECT 
                        IFNULL(SUM(ROUND(ic.qty_in,3)),0) AS total_qty_in,
                        IFNULL(SUM(ROUND(ic.qty_out,3)),0) AS total_qty_out,
                        IFNULL(SUM(ROUND(ic.total_in,2)),0) AS total_in,
                        IFNULL(SUM(ROUND(ic.total_out,2)),0) AS total_out,
                        pb.code AS batch_code,
                        ish.code AS shading_code,
                        i.code AS item_code,
                        i.name AS item_name,
                        p.code AS place_code
                    FROM item_cogs ic
                        LEFT JOIN production_batches pb
                            ON pb.id = ic.production_batch_id
                        LEFT JOIN item_shadings ish
                            ON ish.id = ic.item_shading_id
                        LEFT JOIN items i
                            ON i.id = ic.item_id
                        LEFT JOIN places p
                            ON p.id = ic.place_id
                    WHERE 
                        ic.date <= :date
                        AND ic.deleted_at IS NULL
                    GROUP BY ic.production_batch_id
                ) AS rs
        ", array(
            'date'              => $this->finish_date,
        ));

        foreach($datadetail as $key => $rowdetail){
            $arr[] = [
                $rowdetail->place_code,
                $rowdetail->item_code,
                $rowdetail->item_name,
                'M2',
                $rowdetail->shading_code,
                $rowdetail->batch_code,
                $rowdetail->balance_qty,
                number_format($rowdetail->balance_nominal,2,',','.'),
            ];
        }

        return $arr;
    }

    public function title(): string
    {
        return 'Report Stock In Rupiah - Batch & Shading';
    }

    public function chunkSize(): int
    {
        return 1000;  // Process in chunks of 1000 rows
    }
}

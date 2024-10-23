<?php

namespace App\Exports;

use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\ItemShading;
use App\Models\ItemStock;
use App\Models\ProductionBatch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportStockInRupiahShadingBatchAccounting implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date,$place_id,$warehouse_id;

    public function __construct(string $start_date,string $place_id, string $warehouse_id)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->place_id = $place_id ? $place_id : '';
        $this->warehouse_id = $warehouse_id ? $warehouse_id : '';
    }

    private $headings = [
        'No',
        'Code',
        'Nama Item',
        'Batch',
        'Unit',
        'Shading',
        'Qty',
        'Total',
    ];


    public function collection()
    {
        $arr = [];
        ProductionBatch::join('items', 'production_batches.item_id', '=', 'items.id')
        ->whereHas('item', function ($query) {
            $query->whereNull('deleted_at');
        })
        ->where('place_id', $this->place_id)
        ->where('warehouse_id', $this->warehouse_id)
        ->orderBy('items.code')
        ->orderBy('items.id')
        ->select('production_batches.*')
        ->chunk(1000, function ($items) use (&$arr) {
            $keys = count($arr) + 1; // Continue numbering from where you left off
            foreach ($items as $row) {
                $itemstock = ItemStock::where('item_shading_id', $row->item_shading_id)->first();

                $arr[] = [
                    'no' => $keys,
                    'item_code' => $row->item->code,
                    'item_name' => $row->item->name,
                    'unit' => $row->item->uomUnit->code,
                    'batch' => $row->code,
                    'shading' => $row->itemShading->code ?? ' ',
                    'total' => $itemstock->stockByDate($this->start_date),
                    'rp_total' => $itemstock->priceFgNow($this->start_date)
                ];
                $keys++;
            }
        });

        return collect($arr);
    }

    public function title(): string
    {
        return 'Report Stock In Rupiah - Batch & Shading';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

<?php

namespace App\Exports;

use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportProductionSummaryStockFg implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $finish_date)
    {
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'item code',
        'name',
        'shading',
        'in',
        'out',
        'total',
        'konversi palet',
        'konversi box',
    ];

    public function collection()
    {
        $query_data = "call report_stock_fg('".$this->finish_date."');";
        $submit = DB::select($query_data);

        foreach ($submit as $row) {
            $row_item = Item::find($row->itemid);
            $total_palet = $row->total / $row_item->sellConversion();
            $total_box = ($row->total/$row_item->sellConversion())*$row_item->pallet->box_conversion;
            $arr[] = [
                'item_code' => $row->itemcode, // Directly use the itemcode from the object
                'item_name' => $row->name, // Use the name from the object
                'shading' => $row->shading, // Use the shading from the object
                
                'IN' => $row->IN, // Use the IN value from the object
                'out' => $row->out, // Use the out value from the object
                'total' => $row->total, // Use the total value from the object
                'total_palet' => $total_palet, // Use the total value from the object
                'total_box' => $total_box, // Use the total value from the object
            ];
        }

        return collect($arr);

        $x=1;
        

    }

    public function title(): string
    {
        return 'Summary Stock FG Produksi';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

<?php

namespace App\Exports;

use App\Models\ProductionRepack;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
class ExportProductionRepacking implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $end_date;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ?? '';
		$this->end_date = $end_date ?? '';
    }

    private $headings = [
        'No',
        'Kode Document',
        'Kode Item Sumber',
        'Item Sumber',
        'Unit Sumber',
        'Tanggal',
        'Plant',
        'Gudang',
        'Area',
        'Item Target',
        'Shading',
        'Unit Target',
        'Qty',
        'Batch',
        'Total',
    ];

    public function collection()
    {
        $query_data = ProductionRepack::where(function($query) {

            if($this->start_date && $this->end_date) {
                $query->whereDate('created_at', '>=', $this->start_date)
                    ->whereDate('created_at', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('created_at','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('created_at','<=', $this->end_date);
            }
        })
        ->get();

        $arr = [];
        $x= 0;
        foreach($query_data as $key => $row){
            foreach($row->productionRepackDetail as $row_detail){
                $arr[] = [
                    'id'            => ($x + 1),
                    'document'      => $row->code,
                    'code'          => $row_detail->itemSource->code,
                    'item'          => $row_detail->itemSource->name,
                    'item_unit_source'=> $row_detail->itemUnitSource->unit->code,
                    'date'          => date('d/m/Y H:i:s',strtotime($row->post_date)),
                    'plant'         => $row_detail->place()->exists() ? $row_detail->place->code : '-',
                    'warehouse'     => $row_detail->warehouse()->exists() ? $row_detail->warehouse->name : '-',
                    'area'          => $row_detail->area()->exists() ? $row_detail->area->code : '-',
                    'item_target'   => $row_detail->itemTarget->name,
                    'shading'       => $row_detail->itemShading()->exists() ? $row_detail->itemShading->code : '-',
                    'item_unit_target'=> $row_detail->itemUnitTarget->unit->code,
                    'qty'           => $row_detail->qty,
                    'batch_no'      => $row_detail->batch_no,
                    'total'      => $row_detail->total,
                ];
                $x++;
            }

        }

        activity()
            ->performedOn(new ProductionRepack())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export production batch.');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Repacking';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	/**
	 * @return array
	 */
	public function headings() : array
	{
		return $this->headings;
	}
}

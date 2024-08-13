<?php

namespace App\Exports;

use App\Helpers\CustomHelper;
use App\Models\ProductionBatch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportProductionBatch implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $item_parent_id, $search;

    public function __construct(string $start_date, string $end_date, string $item_parent_id, string $search)
    {
        $this->start_date = $start_date ?? '';
		$this->end_date = $end_date ?? '';
        $this->item_parent_id = $item_parent_id ?? '';
        $this->search = $search ?? '';
    }


    private $headings = [
        'No',
        'No.Batch',
        'Item',
        'Tgl.Dibuat',
        'Plant',
        'Gudang',
        'Area',
        'Shading',
        'Qty Awal',
        'Qty Terpakai',
        'Qty Sisa',
        'Satuan',
        'Nilai Rupiah Awal',
        'Nilai Rupiah Terpakai',
        'Nilai Rupiah Sisa',
        'Dokumen Ref.'
    ];

    public function collection()
    {
        $query_data = ProductionBatch::where(function($query) {
            if($this->search) {
                $query->where(function($query){
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhereHas('item',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('code','like',"%$this->search%");
                        });
                });
            }

            if($this->item_parent_id){
                $query->whereHas('item',function($query){
                    $query->whereHas('parentFg',function($query){
                        $query->where('parent_id',$this->item_parent_id);
                    });
                })
                ->whereDoesntHave('area')
                ->whereDoesntHave('itemShading');
            }

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

        foreach($query_data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->code,
                'item'          => $row->item->code.' - '.$row->item->name,
                'date'          => date('d/m/Y H:i:s',strtotime($row->created_at)),
                'plant'         => $row->place()->exists() ? $row->place->code : '-',
                'warehouse'     => $row->warehouse()->exists() ? $row->warehouse->name : '-',
                'area'          => $row->area()->exists() ? $row->area->code : '-',
                'shading'       => $row->itemShading()->exists() ? $row->itemShading->code : '-',
                'qty_real'      => $row->qty_real,
                'qty_used'      => $row->qtyUsed(),
                'qty_balance'   => $row->qtyBalance(),
                'unit'          => $row->item->uomUnit->code,
                'value_total'   => $row->total,
                'value_used'    => round($row->price() * $row->qtyUsed(),2),
                'value_balance' => round($row->price() * $row->qtyBalance(),2),
                'ref_code'      => $row->lookable->parent->code,
            ];
        }

        activity()
            ->performedOn(new ProductionBatch())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export production batch.');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Batch Produksi';
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

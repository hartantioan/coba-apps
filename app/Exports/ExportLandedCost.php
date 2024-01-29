<?php

namespace App\Exports;

use App\Models\LandedCost;
use App\Models\LandedCostDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportLandedCost implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $mode;

    public function __construct(string $start_date, string $end_date, string $mode)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
    }


    private $headings = [
        'No',
        'No.Dokumen',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Tgl.Posting',
        'Kode Supplier',
        'Nama Supplier',
        'Keterangan',
        'Kode Item',
        'Nama Item',
        'Plant',
        'Qty',
        'Satuan',
        'Total',
        'Based On',
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = LandedCostDetail::whereHas('landedCost',function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }elseif($this->mode == '2'){
            $data = LandedCostDetail::withTrashed()->whereHas('landedCost',function ($query) {
                $query->withTrashed()->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->landedCost->code,
                'status'        => $row->landedCost->statusRaw(),
                'voider'        => $row->landedCost->voidUser()->exists() ? $row->landedCost->voidUser->name : '',
                'void_date'     => $row->landedCost->voidUser()->exists() ? $row->landedCost->void_date : '',
                'void_note'     => $row->landedCost->voidUser()->exists() ? $row->landedCost->void_note : '',
                'deleter'       => $row->landedCost->deleteUser()->exists() ? $row->landedCost->deleteUser->name : '',
                'delete_date'   => $row->landedCost->deleteUser()->exists() ? $row->landedCost->deleted_at : '',
                'delete_note'   => $row->landedCost->deleteUser()->exists() ? $row->landedCost->delete_note : '',
                'post_date'     => date('d/m/Y',strtotime($row->landedCost->post_date)),
                'vendor_code'   => $row->landedCost->vendor->employee_no,
                'vendor'        => $row->landedCost->vendor->name,
                'note'          => $row->landedCost->note,
                'item_code'     => $row->item->code,
                'item_name'     => $row->item->name,
                'place'         => $row->place->code,
                'qty'           => $row->qty,
                'unit'          => $row->item->uomUnit->code,
                'total'         => number_format($row->nominal,2,',','.'),
                'based_on'      => $row->landedCost->getReference(),
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Landed Cost';
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

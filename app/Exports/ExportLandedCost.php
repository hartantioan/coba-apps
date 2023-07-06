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

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';

    }


    private $headings = [
        'NO',
        'LC.NO',
        'TGL.POST',
        'VENDOR CODE',
        'VENDOR',
        'REFERENSI',
        'ITEM CODE',
        'ITEM NAME',
        'QTY',
        'UNIT',
        'PENGGUNA',
        'PERUSAHAAN',
        'WAREHOUSE',
        'STATUS',
    ];

    public function collection()
    {
        $data = LandedCostDetail::whereHas('landedCost',function ($query) {
            $query->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->end_date);
        })
        ->get();

        $arr = [];

        foreach($data as $key => $row){

            info($row->landedCost->supplier);

            if($row->landedCost->supplier()->exists()){
                $arr[] = [
                    'id'            => ($key + 1),
                    'code'          => $row->landedCost->code,
                    'tgl_post'      => $row->landedCost->post_date,
                    'vendor_code'   => $row->landedCost->supplier->employee_no,
                    'vendor'        => $row->landedCost->supplier->name,
                    'ref'           => $row->landedCost->reference,
                    'item_code'     => $row->item->code,
                    'item_name'     => $row->item->name,
                    'qty'           => $row->qty,
                    'unit'          => $row->item->buyUnit,
                    'user'          => $row->landedCost->user->name,
                    'company'       => $row->landedCost->company->name,
                    'warehouse'     => $row->place->name,
                    'status'        => $row->landedCost->statusRaw()
                ];
            }else{
                $arr[] = [
                    'id'            => ($key + 1),
                    'code'          => $row->landedCost->code,
                    'tgl_post'      => $row->landedCost->post_date,
                    'vendor_code'   => '',
                    'vendor'        => '',
                    'ref'           => $row->landedCost->reference,
                    'item_code'     => $row->item->code,
                    'item_name'     => $row->item->name,
                    'qty'           => $row->qty,
                    'unit'          => $row->item->buyUnit,
                    'user'          => $row->landedCost->user->name,
                    'company'       => $row->landedCost->company->name,
                    'warehouse'     => $row->place->name,
                    'status'        => $row->landedCost->statusRaw()
                ];
            }
            
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

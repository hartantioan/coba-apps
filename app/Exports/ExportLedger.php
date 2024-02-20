<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportLedger implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $coa_id, $company_id, $search;

    public function __construct(string $start_date, string $end_date, int $coa_id, int $company_id, string $search)
    {
        $this->start_date = $start_date ?? '';
		$this->end_date = $end_date ?? '';
        $this->coa_id = $coa_id ?? '';
        $this->company_id = $company_id ?? '';
    }


    private $headings = [
        'No',
        'Kode Coa',
        'Nama Coa',
        'Perusahaan',
        'Saldo Awal',
        'Debit',
        'Kredit',
        'Saldo Akhir',
    ];

    public function collection()
    {
        

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
        return 'Laporan Buku Besar';
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

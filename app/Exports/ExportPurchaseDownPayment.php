<?php

namespace App\Exports;

use App\Models\PurchaseDownPayment;
use App\Models\PurchaseDownPaymentDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportPurchaseDownPayment implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
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
        'POSTING DATE',
        'CODE',
        'SUPPLIER CODE',
        'SUPPLIER NAME',
        'TGL.POST',
        'TGL.TENGGAT',
        'TAX CODE',
        'TAX NAME',
        'TIPE',
        'KETERANGAN',
        'STATUS',
    ];

    public function collection()
    {
        $data = PurchaseDownPayment::where(function ($query) {
            $query->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->end_date);
        })
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                '1'                => ($key + 1),
                '2'              => $row->post_date,
                '3'                 => $row->code,
                '4'         => $row->supplier->employee_no??'',
                '5'          => $row->supplier->name??'',
                '6'              => $row->post_date,
                '8'              => $row->due_date,
                '11'            => $row->tax->code ?? '',
                '12'            => $row->tax->name ?? '',
                '7'              => $row->type,
                '9'           => $row->note,
                '14'              => $row->statusRaw(),
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Purchase Down Payment';
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

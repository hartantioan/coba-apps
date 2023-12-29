<?php

namespace App\Exports;

use App\Models\FundRequest;
use App\Models\FundRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportFundRequest implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell,ShouldAutoSize
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
        'ID',
        'PENGGUNA',
        'KODE',
        'SITE',
        'DEPARTEMEN',
        'PARTNER BISNIS',
        'TIPE',
        'PENGAJUAN',
        'REQ PEMBAYARAN',
        'MATA UANG',
        'KONVERSI',
        'KETERANGAN',
        'TERMIN',
        'TIPE PEMBAYARAN',
        'REK TUJUAN',
        'NO REKENING',
        'TOTAL',
        'PPN',
        'PPh',
        'GRANDTOTAL',
        'STATUS',
    ];

    public function collection()
    {
        $data = FundRequest::where(function($query) {
            $query->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->end_date); 
        })
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'name'          => $row->user->name,
                'code'          => $row->code,
                'place_id'      => $row->place->code.' - '.$row->place->company->name,
                'department'    => $row->department->name,
                'bp'            => $row->account->name,
                'type'          => $row->type(),
                'post_date'     => $row->post_date,
                'required_date' => $row->required_date,
                'currency_id'   => $row->currency->code,
                'currency_rate' => $row->currency_rate,
                'note'          => $row->note,
                'termin_note'   => $row->termin_note,
                'payment_type'  => $row->paymentType(),
                'name_account'  => $row->name_account,
                'no_account'    => $row->no_account,
                'total'         => $row->total,
                'ppn'           => $row->tax,
                'pph'           => $row->wtax,
                'grandtotal'    => $row->grandtotal,
                'status'        => $row->statusRaw(),
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Fund Request';
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

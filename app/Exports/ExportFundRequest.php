<?php

namespace App\Exports;

use App\Models\FundRequest;
use App\Models\FundRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportFundRequest implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search, string $status, string $document, array $dataplaces)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->document = $document ? $document : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
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
        'PPH',
        'GRANDTOTAL',
        'STATUS',
    ];

    public function collection()
    {
        $data = FundRequest::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('post_date', 'like', "%$this->search%")
                        ->orWhere('required_date', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%");
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }

            if($this->document){
                $query->where('document_status', $this->document);
            }
        })
        ->whereIn('place_id',$this->dataplaces)
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'name'          => $row->user->name,
                'code'          => $row->code,
                'place_id'      => $row->place->name.' - '.$row->place->company->name,
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

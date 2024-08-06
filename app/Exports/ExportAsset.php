<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportAsset implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search = null, string $status = null, string $balance = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->balance = $balance ? $balance : '';
        $this->dataplaces = $dataplaces ? $dataplaces : [];
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA',
        'PLANT',
        'GRUP ASET',
        'TGL.KAPITALISASI',
        'NOMINAL KAPITALISASI',
        'AKUM.DEPRESIASI',
        'SALDO BUKU',
        'METODE',
        'KETERANGAN',
        'STATUS',
    ];

    public function collection()
    {
        $data = Asset::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%")
                        ->orWhere('nominal','like', "%$this->search%")
                        ->orWhere('note','like',"%$this->search%");
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }

            if($this->balance){
                if($this->balance == '1'){
                    $query->where('book_balance', '>', 0)->whereNotNull('book_balance');
                }elseif($this->balance == '2'){
                    $query->where('book_balance', '=', 0)->whereNotNull('book_balance');
                }
            }
        })->get();

        $arr = [];

        foreach($data as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->code,
                'name'          => $row->name,
                'plant'         => $row->place->code,
                'group'         => $row->assetGroup->name,
                'date_cap'      => date('d/m/Y',strtotime($row->date)),
                'nominal'       => number_format($row->nominal,2,',','.'),
                'depreciation'  => number_format($row->accumulation_total,2,',','.'),
                'balance'       => number_format($row->book_balance,2,',','.'),
                'method'        => $row->method(),
                'note'          => $row->note,
                'status'        => $row->statusRaw(),
            ];
        }
        activity()
            ->performedOn(new Asset())
            ->causedBy(session('bo_id'))
            ->withProperties($data)
            ->log('Export Asset data.');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Aset';
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

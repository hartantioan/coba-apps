<?php

namespace App\Exports;

use App\Models\Place;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportPlace implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA',
        'ALAMAT',
        'PROVINSI',
        'KOTA',
        'KECAMATAN',
    ];

    public function collection()
    {
        $data = Place::where(function ($query) {
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%")
                        ->orWhere('address', 'like', "%$this->search%")
                        ->orWhereHas('province', function ($query) {
                            $query->where('name', 'like', "%$this->search%");
                        })->orWhereHas('city', function ($query) {
                            $query->where('name', 'like', "%$this->search%");
                        })->orWhereHas('subdistrict', function ($query) {
                            $query->where('name', 'like', "%$this->search%");
                        });
                });
            }
            if($this->status){
                $query->where('status', $this->status);
            }
        })->get();

        $arr = [];

        foreach($data as $row){
            $arr[] = [
                'id' => $row->id,
                'code' => $row->code,
                'name' => $row->name,
                'address' => $row->address,
                'branch' => $row->branch->name,
                'type' => $row->type(),
                'province' => $row->province->name,
                'city' => $row->city->name,
                'subdistrict' => $row->subdistrict->name
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Penempatan';
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

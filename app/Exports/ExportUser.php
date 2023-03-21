<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportUser implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search, string $status, string $type)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA',
        'TIPE',
        'ALAMAT',
        'KOTA',
        'PROVINSI'
    ];

    public function collection()
    {
        $data = User::where(function ($query) {
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%$this->search%")
                        ->orWhere('employee_no', 'like', "%$this->search%")
                        ->orWhere('username', 'like', "%$this->search%")
                        ->orWhere('phone', 'like', "%$this->search%")
                        ->orWhere('address', 'like', "%$this->search%");
                });
            }
            if($this->status){
                $query->where('status', $this->status);
            }
            if($this->type){
                $query->where('type', $this->type);
            }
        })->get();

        $arr = [];

        foreach($data as $row){
            $arr[] = [
                'id'        => $row->id,
                'code'      => $row->employee_no,
                'name'      => $row->name,
                'type'      => $row->type(),
                'address'   => $row->address,
                'city'      => $row->city->name,
                'province'  => $row->province->name,
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Pengguna';
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

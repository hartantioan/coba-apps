<?php

namespace App\Exports;

use App\Models\Country;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportCountry implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
   
    protected $search;

    public function __construct(string $search)
    {
        $this->search = $search ? $search : '';
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA',
        'KODE TELEPON',
    ];

    public function collection()
    {   
        $data =  Country::where(function ($query) {
            if ($this->search) {
                $query->where('code', 'like', "%$this->search%")
                    ->orWhere('name', 'like', "%$this->search%")
                    ->orWhere('phone_code', 'like', "%$this->search%");
            }
        })->get(['id','code','name','phone_code']);

        activity()
                ->performedOn(new Country())
                ->causedBy(session('bo_id'))
                ->withProperties($data)
                ->log('Export Country data.');
                
        return $data;
    }

    public function title(): string
    {
        return 'Laporan Negara';
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

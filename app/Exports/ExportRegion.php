<?php

namespace App\Exports;

use App\Models\Region;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportRegion implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct(string $search,string $level)
    {
        $this->search = $search ? $search : '';
        $this->level = $level ? $level : '';
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA'
    ];

    public function collection()
    {
        return Region::where(function ($query) {
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('code', 'like', "%$this->search%")
                    ->orWhere('name', 'like', "%$this->search%");
                });
            }

            if($this->level){
                $query->whereRaw("CHAR_LENGTH(code) = ".intval($this->level));
            }
        })->get(['id','code','name']);
    }

    public function title(): string
    {
        return 'Laporan Daerah';
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

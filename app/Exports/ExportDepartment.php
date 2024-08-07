<?php

namespace App\Exports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportDepartment implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    protected $search, $status;

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA',
    ];

    public function collection()
    {
        $data = Department::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }
        })->get(['id','code','name']);

        activity()
            ->performedOn(new Department())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export department data.');

        return $data;
    }

    public function title(): string
    {
        return 'Laporan Departemen';
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

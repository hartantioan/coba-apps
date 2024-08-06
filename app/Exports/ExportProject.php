<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportProject implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    protected $search,$status;

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA',
        'CATATAN',
    ];

    public function collection()
    {
        $data = Project::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%");
                });
                
            }

            if($this->status){
                $query->where('status', $this->status);
            }
        })->get(['id','code','name','note']);
        
        activity()
            ->performedOn(new Project())
            ->causedBy(session('bo_id'))
            ->withProperties($data)
            ->log('Export project.');
        return $data;
    }

    public function title(): string
    {
        return 'Laporan Proyek';
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

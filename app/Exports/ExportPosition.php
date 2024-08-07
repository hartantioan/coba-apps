<?php

namespace App\Exports;

use App\Models\Position;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportPosition implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

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
        'DIVISI',
        'LEVEL',
    ];

    public function collection()
    {
        $position = Position::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                });
                
            }

            if($this->status){
                $query->where('status', $this->status);
            }
        })->get();

        $arr = [];
        foreach($position as $row){
            $arr[] = [
                'id'        => $row->id,
                'code'      => $row->code,
                'name'      => $row->name,
                'division'  => $row->division->name,
                'level'     => $row->level->name,
            ];
        }

        activity()
            ->performedOn(new Position())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export position data.');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Posisi / Level';
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

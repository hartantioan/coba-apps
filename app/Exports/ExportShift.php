<?php

namespace App\Exports;

use App\Models\Shift;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportShift implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
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
        'PABRIK/KANTOR',
        'DEPARTEMEN',
        'MIN TIME IN',
        'TIME IN',
        'TIME OUT',
        'MAX TIME OUT',
        'STATUS'
    ];

    public function collection()
    {
        $data = Shift::where(function ($query) {
            if ($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%")
                        ->orWhereHas('place',function($query){
                            $query->where('name','like',"%$this->search%");
                        })->orWhereHas('department',function($query){
                            $query->where('name','like',"%$this->search%");
                        });
                });
            }
            if($this->status){
                $query->where('status', $this->status);
            }
        })->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                'id'            => ($key + 1),
                'code'          => $row->code,
                'name'          => $row->name,
                'place'         => $row->place->name,
                'department'    => $row->department->name,
                'min_time_in'   => date('H:i',strtotime($row->min_time_in)),
                'time_in'       => date('H:i',strtotime($row->time_in)),
                'time_out'      => date('H:i',strtotime($row->time_out)),
                'max_time_out'  => date('H:i',strtotime($row->max_time_out)),
                'status'        => $row->status == '1' ? 'Active' : 'Non-active'
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Shift';
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

<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportUserActivity implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $search;

    public function __construct(string $search)
    {
        $this->search = $search ?? '';
    }


    private $headings = [
        'No',
        'Pengguna',
        'Aktivitas',
        'Form',
        'Waktu',
        'Data',
    ];

    public function collection()
    {
        $query_data = ActivityLog::where(function($query){
            if($this->search) {
                $query->where(function($query){
                    $query->where('description', 'like', "%$this->search%")
                    ->orWhere('properties', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        });
                });
            }
        })
        ->get();

        $arr = [];

        foreach($query_data as $key => $val){
            $arr[] = [
                'id'            => ($key + 1),
                'user'          => $val->user()->exists() ? $val->user->employee_no.' - '.$val->user->name : 'System',
                'description'   => $val->description,
                'subject_type'  => $val->subject_type,
                'time'          => date('d/m/Y H:i:s',strtotime($val->updated_at)),
                'properties'    => $val->properties,
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Aktivitas Pengguna';
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

<?php

namespace App\Exports;

use App\Models\Line;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportLine implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
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
        'PLANT',
        'NAMA',
        'NOTE',
        'STATUS',
    ];

    public function collection()
    {
        $lines = Line::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        ->orWhereHas('place',function($query){
                            $query->where('code', 'like', "%$this->search%")
                                ->orWhere('name', 'like', "%$this->search%");
                        });
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }
        })->get();

        $arr = [];

        foreach($lines as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->code,
                'place_id'      => $row->place->code.' - '.$row->place->code,
                'name'          => $row->name,
                'note'          => $row->note,
                'status'        => $row->statusRaw(),
            ];
        }

        activity()
            ->performedOn(new Line())
            ->causedBy(session('bo_id'))
            ->withProperties($lines)
            ->log('Export Line data.');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Line';
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

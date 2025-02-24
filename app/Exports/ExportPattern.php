<?php

namespace App\Exports;

use App\Models\Pattern;
use App\Models\Place;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportPattern implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell,ShouldAutoSize
{
    protected $search,$status;

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    private $headings = [
        'ID',
        'BRAND',
        'KODE',
        'NAMA',
        'STATUS',
    ];

    public function collection()
    {
        $data = Pattern::where(function ($query) {
            if ($this->search) {
                $query->where(function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%")
                        ->orWhereHas('brand',function($query) {
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

        foreach($data as $row){
            $arr[] = [
                'id' => $row->id,
                'brand' => $row->brand->name,
                'code' => $row->code,
                'nama' => $row->name,
                'status' => $row->statusRaw(),
            ];
        }

        activity()
            ->performedOn(new Pattern())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Pattern data.');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Motif dan Warna';
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

<?php

namespace App\Exports;

use App\Models\Residence;
use App\Models\ResidenceDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportResidence implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    protected $search,$status;

    public function __construct(string $search,string $status)
    {
        $this->search = $search ? $search : '';
        $this->status = $status ? $status : '';
    }

    private $headings = [
        'ID',
        'KODE KERESIDENAN', 
        'NAMA KERESIDENAN',
        'KODE WILAYAH',
        'NAMA WILAYAH'
    ];

    public function collection()
    {
        $data = ResidenceDetail::where(function ($query) {
            if ($this->search) {
                $query->whereHas('residence',function ($query) {
                    $query->where('code', 'like', "%$this->search%")
                    ->orWhere('name', 'like', "%$this->search%");
                });
            }

            if($this->status){
                $query->whereHas('residence',function ($query) {
                    $query->where('status',$this->status);
                });
            }
        })->get();

        $arr = [];

        foreach($data as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->residence->code,
                'name'          => $row->residence->name,
                'region_no'     => $row->region->code,
                'region_name'   => $row->region->name,
            ];
        }

        activity()
                ->performedOn(new ResidenceDetail())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Residence data  .');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Keresidenan';
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

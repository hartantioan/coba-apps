<?php

namespace App\Exports;

use App\Models\Asset;
use App\Models\Resource;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportResource implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $search, $status;

    public function __construct(string $search = null, string $status = null, string $balance = null, array $dataplaces = null)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    private $headings = [
        'ID',
        'KODE', 
        'NAMA',
        'NAMA LAIN',
        'GRUP RESOURCE',
        'SATUAN',
        'QTY',
        'BIAYA',
        'PLANT',
        'STATUS',
    ];

    public function collection()
    {
        $data = Resource::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                    ->orWhere('name', 'like', "%$this->search%")
                    ->orWhere('other_name','like',"%$this->search%");
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }
        })->get();

        $arr = [];

        foreach($data as $row){
            $arr[] = [
                'id'            => $row->id,
                'code'          => $row->code,
                'name'          => $row->name,
                'other_name'    => $row->other_name,
                'group'         => $row->resourceGroup->name,
                'unit'          => $row->uomUnit->code,
                'qty'           => number_format($row->qty,3,',','.'),
                'cost'          => number_format($row->cost,2,',','.'),
                'plant'         => $row->place()->exists() ? $row->place->code : '-',
                'status'        => $row->statusRaw(),
            ];
        }

        activity()
                ->performedOn(new Resource())
                ->causedBy(session('bo_id'))
                ->withProperties(null)
                ->log('Export Resource  .');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Resource';
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

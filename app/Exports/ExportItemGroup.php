<?php

namespace App\Exports;

use App\Models\ItemGroup;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ExportItemGroup implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell
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
        'PARENT',
        'COA',
    ];

    public function collection()
    {
        $data = ItemGroup::where(function ($query) {
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                });
            }
            if($this->status){
                $query->where('status', $this->status);
            }
        })->get();

        $arr = [];

        foreach($data as $row){
            $arr[] = [
                'id'        => $row->id,
                'code'      => $row->code,
                'name'      => $row->name,
                'parent'    => $row->parentSub()->exists() ? $row->parentSub->name : 'is Parent',
                'coa'       => $row->coa->code.' - '.$row->coa->name,
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Grup Item';
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

<?php

namespace App\Exports;

use App\Models\BomMap;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportBomMap implements FromCollection
{
    protected $search;

    public function __construct(string $search)
    {
        $this->search = $search ? $search : '';
    }

    private $headings = [
        'No',
        'CODE BOM PARENT',
        'Nama Parent',
        'CODE BOM CHILD',
        'Nama Child'
    ];
    public function collection()
    {

        $itemWeight = BomMap::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->whereHas('parent',function($query){
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('name', 'like', "%$this->search%");
                    })->orWhereHas('child',function($query){
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('name', 'like', "%$this->search%");
                    });
                });
            }
        })->get();



        $arr = [];

        foreach ($itemWeight as $key => $row) {
            $arr[] = [
                "no"=> $key+1,
                "parent_code"=> $row->parent->code,
                "parent_name"=> $row->parent->name,
                "child_code"=> $row->child->code,
                "child_name"=> $row->child->name,
            ];
        }


        return collect($arr);
    }

    public function title(): string
    {
        return 'Report BOM MAP';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

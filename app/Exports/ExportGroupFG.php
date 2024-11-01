<?php

namespace App\Exports;

use App\Models\FgGroup;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportGroupFG implements FromCollection
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

        $itemWeight = FgGroup::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->whereHas('parent',function($query){
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('name', 'like', "%$this->search%");
                    })->orWhereHas('item',function($query){
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
                "child_code"=> $row->item->code ?? '-',
                "child_name"=> $row->item->name ?? '-',
            ];
        }


        return collect($arr);
    }

    public function title(): string
    {
        return 'Report FG Group';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

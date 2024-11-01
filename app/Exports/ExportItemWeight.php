<?php

namespace App\Exports;

use App\Models\ItemWeightFg;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportItemWeight implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search;

    public function __construct(string $search)
    {
        $this->search = $search ? $search : '';
    }

    private $headings = [
        'No',
        'Item',
        'Berat Netto',
        'Berat Gross',
    ];
    public function collection()
    {

        $itemWeight = ItemWeightFg::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->orWhere('code','like',"%$this->search%")
                    ->orWhereHas('item',function($query){
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
                "item"=> $row->item->code.'#'.$row->item->name,
                "netto_weight"=> $row->netto_weight,
                "gross_weight"=> $row->gross_weight,
            ];
        }


        return collect($arr);
    }

    public function title(): string
    {
        return 'Report Item For Weight';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

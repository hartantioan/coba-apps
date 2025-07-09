<?php

namespace App\Exports;

use App\Models\StoreItemStock;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportStoreItemStock implements FromCollection
{
    protected $search;

    public function __construct(string $search)
    {
        $this->search = $search ? $search : '';
    }
    private $headings = [
        'No',
        'Item',
        'Item Original',
        'Qty'

    ];

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings() : array
	{
		return $this->headings;
	}

    public function title(): string
    {
        return 'Stock Item Toko';
    }

    public function collection()
    {

        $data = StoreItemStock::where(function ($query) {
            if ($this->search) {
                $query->whereHas('item', function ($query)  {
                    $query->where('name', 'like', "%$this->search%")
                        ->orWhere('code', 'like', "%$this->search%");
                });
            }
        })->get();
        $arr=[];
        $nomor = 1;
        foreach($data as $row){
            $arr[] = [
                'id'        => $nomor,
                'code'      => $row->item->name,
                'item_pa'   => $row->itemStockNew?->item->name ?? '-',
                'qty'       => $row->qty,
            ];
            $nomor++;
        }

        return collect($arr);
    }
}

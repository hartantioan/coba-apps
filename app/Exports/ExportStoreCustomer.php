<?php

namespace App\Exports;

use App\Models\StoreCustomer;
use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportStoreCustomer implements FromCollection,WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    protected $search;

    public function __construct(string $search)
    {
        $this->search = $search ? $search : '';
    }
    private $headings = [
        'No',
        'Nama',
        'Telepon'
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
        return 'Stock Item';
    }

    public function collection()
    {

        $data = StoreCustomer::where(function ($query) {
            if ($this->search) {
                $query->where('name', 'like', "%$this->search%")
                ->orWhere('code', 'like', "%$this->search%")
                ->orWhere('no_telp', 'like', "%$this->search%");
            }
        })->get();
        $arr=[];
        $nomor = 1;
        foreach($data as $row){
            $arr[] = [
                'id'        => $nomor,
                'code'      => $row->name,
                'qty'       => $row->no_telp,
            ];
            $nomor++;
        }

        return collect($arr);
    }
}

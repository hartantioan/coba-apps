<?php

namespace App\Exports;

use App\Models\UserBrand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportUserBrand implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search;

    public function __construct(string $search)
    {
        $this->search = $search ?? '';
    }
    private $headings = [
        'No',
        'Customer Code',
        'Brand Code',
    ];

    public function collection()
    {
        $query_data = UserBrand::where(function($query){
            if($this->search) {
                $query->where(function($query){
                    $query->where('description', 'like', "%$this->search%")
                    ->orWhere('properties', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        });
                });
            }
        })
        ->get();

        $arr = [];

        foreach($query_data as $key => $val){
            $arr[] = [
                'id'            => ($key + 1),
                'customer'          => $val->account->employee_no,
                'brand'         => $val->brand->code,
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Aktivitas Pengguna';
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

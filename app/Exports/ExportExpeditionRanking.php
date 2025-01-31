<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\DeliveryCost;
use App\Models\Region;

class ExportExpeditionRanking implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $end_date,$search,$status,$account,$filter_province,$filter_city,$filter_district;

    public function __construct(string $search ,string $start_date, string $end_date,string $status,string $account,string $filter_province,string $filter_city,string $filter_district)
    {
        $this->search = $search ? $search : '';
        $this->end_date = $end_date ? $end_date : '';
        $this->start_date = $start_date ? $start_date : '';
        $this->status = $status ? $status : '';
        $this->account = $account ? $account : '';


        $this->filter_province = $filter_province ? $filter_province : '';
        $this->filter_city = $filter_city ? $filter_city : '';
        $this->filter_district = $filter_district ? $filter_district : '';
    }

    private $headings = [
        'No.',
        'No. Dokumen',
        'Status',
        'Nama',
        'Mitra Bisnis',
        'Jenis Kendaraan',
        'Tonase',
        'Harga/Kg',
        'Harga/Ritase',
        'Kota Tujuan',
        'Kecamatan Tujuan',
    ];
    public function collection()
    {
        $array_filter = [];
        $mo = DeliveryCost::where( function ($query) {
            if($this->search) {
                $query->where('name','like',"%$this->search%")
                ->orWhere('code','like',"%$this->search%")
                ->orWhereHas('account',function($query)  {
                    $query->where('employee_no', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                })
                ->orWhereHas('fromCity',function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                })
                ->orWhereHas('fromSubdistrict',function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                })
                ->orWhereHas('toCity',function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                })
                ->orWhereHas('toSubdistrict',function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                });
            }

            if($this->filter_province&& !$this->filter_city && !$this->filter_district){

                $region = Region::find($this->filter_province);

                $query->whereHas('toCity', function($query) use ($region) {
                    $query->whereRaw('substr(code, 1, 2) = ?', [substr($region->code, 0, 2)]);
                });
            }

            if($this->filter_city && !$this->filter_district ){
                $query->where('to_city_id', $this->filter_city);
            }
            if($this->filter_district){
                $query->where('to_subdistrict_id', $this->filter_district);
            }

            if($this->account){
                $query->where('account_id', $this->account);
            }

            if($this->start_date && $this->end_date) {
                $query->where(function($query)  {
                    $query->whereDate('valid_from', '>=', $this->start_date)
                        ->whereDate('valid_from', '<=', $this->end_date);
                })->orWhere(function($query)  {
                    $query->whereDate('valid_to', '>=', $this->start_date)
                        ->whereDate('valid_to', '<=', $this->end_date);
                });
            } else if($this->start_date) {
                $query->whereDate('valid_from','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('valid_to','<=', $this->end_date);
            }

            if($this->status){
                $query->where('status', $this->status);
            }
        })
        ->orderBy('tonnage', 'asc')
        ->get();


        foreach ($mo as $key=>$row) {

            $array_filter[] = [
                'No.' => ($key+1),
                'No. Dokumen'=> $row->code,
                'Status'=> $row->statusRaw(),
                'Nama'=> $row->name,
                'Mitra Bisnis'=> $row->account->name,
                'Jenis Kendaraan'=> $row->transportation->name ?? '',
                'Tonase'=> $row->qty_tonnage,
                'Harga/Kg'=> $row->tonnage,
                'Harga/Ritase'=> $row->ritage,
                'Kota Tujuan'=>  $row->toCity->name,
                'Kecamatan Tujuan'=>  $row->toSubdistrict->name,

            ];
        }
        return collect($array_filter);
    }

    public function title(): string
    {
        return 'Delivery Cost';
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

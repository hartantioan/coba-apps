<?php

namespace App\Exports;

use App\Models\Outlet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportOutlet implements FromArray,WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$status;
    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    public function array(): array
    {
        $query_data = Outlet::where(function($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%")
                        ->orWhere('phone', 'like', "%$this->search%")
                        ->orWhere('address', 'like', "%$this->search%")
                        ->orWhereHas('province',function($query){
                            $query->where('code', 'like', "%$this->search%")
                                ->orWhere('name', 'like', "%$this->search%");
                        })
                        ->orWhereHas('city',function($query){
                            $query->where('code', 'like', "%$this->search%")
                                ->orWhere('name', 'like', "%$this->search%");
                        })
                        ->orWhereHas('district',function($query){
                            $query->where('code', 'like', "%$this->search%")
                                ->orWhere('name', 'like', "%$this->search%");
                        })
                        ->orWhereHas('subdistrict',function($query){
                            $query->where('code', 'like', "%$this->search%")
                                ->orWhere('name', 'like', "%$this->search%");
                        });
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }
        })
        ->get();
        $data = [];
        $nomor =  1;
        foreach($query_data as $val){
            $data[]=[
                'No.'=>$nomor,
                'Kode Outlet'=>$val->code,
                'Nama Outlet'=>$val->name,
                'Grup Outlet'=>$val->outletGroup()->exists() ? $val->outletGroup->name : '-',
                'Tipe'=>$val->type(),
                'Alamat'=>$val->address,
                'Nomor Telepon'=>$val->phone,
                'Provinsi'=>$val->province->name,
                'Kota'=>$val->city->name,
                'Kecamatan'=>$val->district->name,
                'Kelurahan'=>$val->subdistrict->name ?? '-',
                'Link Map'=>$val->link_gmap,
                'Status'=>$val->statusRaw()
            ];
            $nomor++;
        }

        return $data;
    }
    public function title(): string
    {
        return 'Rekap Outlet';
    }

    private $headings = [
        'No.',
        'Kode Outlet',
        'Nama Outlet',
        'Grup Outlet',
        'Tipe',
        'Alamat',
        'Nomor Telepon',
        'Provinsi',
        'Kota',
        'Kecamatan',
        'Kelurahan',
        'Link Map',
        'Status'
    ];

    public function headings() : array
	{
		return $this->headings;
    }
}

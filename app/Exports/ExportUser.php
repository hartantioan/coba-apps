<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportUser implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $search, $status, $type,$group;

    public function __construct(string $search, string $status, string $type,string $group)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
        $this->group = $group ? $group : '';
    }

    private $headings = [
        'ID',
        'KODE NIK', 
        'NAMA',
        'TIPE',
        'ALAMAT',
        'KECAMATAN',
        'KOTA',
        'PROVINSI',
        'NO IDENTITAS',
        'ALAMAT IDENTITAS',
        'GROUP',
        'PERUSAHAAN',
        'PLANT',
        'POSISI',
        'NO NPWP',
        'NAMA NPWP',
        'ALAMAT NPWP',
        'NAMA PIC',
        'JABATAN PIC',
        'NOMOR PIC',
        'NOMOR KANTOR',
        'LIMIT KREDIT',
        'EMAIL',
        'GENDER',
        'STATUS KARYAWAN',
        'STATUS AKTIF',
    ];

    public function collection()
    {
        
        $data = User::where(function ($query) {
            if ($this->search) {
                $query->where(function ($query) {
                    $query->where('name', 'like', "%$this->search%")
                        ->orWhere('employee_no', 'like', "%$this->search%")
                        ->orWhere('username', 'like', "%$this->search%")
                        ->orWhere('phone', 'like', "%$this->search%")
                        ->orWhere('address', 'like', "%$this->search%");
                });
            }
            if($this->status){
                $query->where('status', $this->status);
            }
            if($this->type){
                $query->where('type', $this->type);
            }
            if($this->group){
                $groupIds = explode(',', $this->group);
                $query->whereIn('group_id', $groupIds);
            }
        })->get();

        $arr = [];

        foreach($data as $row){
            $arr[] = [
                'id'                => $row->id,
                'code'              => $row->employee_no,
                'name'              => $row->name,
                'type'              => $row->type(),
                'address'           => $row->address,
                'district'          => $row->district_id ? $row->district->name : '-',
                'city'              => $row->city->name ?? '-',
                'province'          => $row->province->name  ?? '-',
                'id_card'           => $row->id_card  ?? '-',
                'id_card_address'   => $row->id_card_address  ?? '-',
                'group'             => $row->group()->exists() ? $row->group->name : '-',
                'company'           => $row->company->name  ?? '-',
                'plant'             => $row->place()->exists() ? $row->place->code : '-',
                'position'          => $row->position()->exists() ? $row->position->name : '-',
                'npwp_no'           => $row->tax_id  ?? '-',
                'npwp_name'         => $row->tax_name  ?? '-', 
                'npwp_address'      => $row->tax_address  ?? '-',
                'pic'               => $row->pic  ?? '-',
                'pic_position'      => $row->pic_position ?? '-',
                'pic_no'            => $row->pic_no  ?? '-',
                'office_no'         => $row->office_no  ?? '-',
                'limit_credit'      => $row->limit_credit  ?? '-',
                'email'             => $row->email  ?? '-',
                'gender'            => $row->gender(),
                'employee_status'   => $row->employeeType(),
                'status'            => $row->statusRaw(),
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Pengguna';
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

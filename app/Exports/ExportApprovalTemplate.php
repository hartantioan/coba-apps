<?php

namespace App\Exports;

use App\Models\ApprovalTemplate;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportApprovalTemplate implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $search, $status, $type;

    public function __construct(string $search, string $status)
    {
        $this->search = $search ? $search : '';
		$this->status = $status ? $status : '';
    }

    private $headings = [
        'NO',
        'FORM', 
        'KODE',
        'NAMA TEMPLATE',
        'ITEM TYPE',
        'SYARAT GRANDTOTAL',
        'SYARAT BENCHMARK',
        'NOMINAL',
        'NIK ORIGINATOR',
        'NAMA ORIGINATOR',
        'STAGE / LEVEL',
        'AUTHORIZER',
        'MIN APPROVAL',
        'MIN REJECT',
    ];

    public function collection()
    {
        $query_data = ApprovalTemplate::where(function($query){
            if($this->search) {
                $query->where(function($query){
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('name', 'like', "%$this->search%");
                });
            }

            if($this->status){
                $query->where('status', $this->status);
            }

        })
        ->get();

        $arr = [];

        foreach($query_data as $row){
            $arr[] = [
                'id'                => $row->id,
                'code'              => $row->employee_no,
                'name'              => $row->name,
                'type'              => $row->type(),
                'address'           => $row->address,
                'subdistrict'       => $row->subdistrict()->exists() ? $row->subdistrict->name : '-',
                'district'          => $row->district_id ? $row->district->name : '-',
                'city'              => $row->city->name,
                'province'          => $row->province->name,
                'id_card'           => $row->id_card,
                'id_card_address'   => $row->id_card_address,
                'group'             => $row->group()->exists() ? $row->group->name : '-',
                'company'           => $row->company->name,
                'plant'             => $row->place()->exists() ? $row->place->code : '-',
                'position'          => $row->position()->exists() ? $row->position->name : '-',
                'npwp_no'           => $row->tax_id,
                'npwp_name'         => $row->tax_name,
                'npwp_address'      => $row->tax_address,
                'pic'               => $row->pic,
                'pic_no'            => $row->pic_no,
                'office_no'         => $row->office_no,
                'email'             => $row->email,
                'gender'            => $row->gender(),
                'employee_status'   => $row->employeeType(),
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

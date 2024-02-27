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
            foreach($row->approvalTemplateMenu as $rowmenu){
                foreach($row->approvalTemplateOriginator as $roworiginator){
                    foreach($row->approvalTemplateStage as $rowstage){
                        $arr[] = [
                            'no'                => 1,
                            'form'              => $rowmenu->menu->name,
                            'code'              => $row->code,
                            'name'              => $row->name,
                            'item_type'         => $row->itemGroupList(),
                            'is_check_nominal'  => $row->is_check_nominal ? 'Yes '.$row->nominalType() : 'No',
                            'is_check_benchmark'=> $row->is_check_benchmark ? 'Yes' : 'No',
                            'nominal'           => $row->is_check_nominal ? $row->formatSignNominal() : '',
                            'originator_code'   => $roworiginator->user->employee_no,
                            'originator_name'   => $roworiginator->user->name,
                            'stage'             => $rowstage->approvalStage->level,
                            'authorizer'        => $rowstage->approvalStage->textApprover(),
                            'min_approve'       => $rowstage->approvalStage->min_approve,
                            'min_reject'        => $rowstage->approvalStage->min_reject,
                        ];
                    }
                }
            }
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Template Approval';
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

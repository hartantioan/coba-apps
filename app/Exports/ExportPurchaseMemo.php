<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportPurchaseMemo implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $start_date, $end_date, $mode;

    public function __construct(string $start_date, string $end_date, string $mode)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->mode = $mode ? $mode : '';
    }

    private $headings = [
        'NO',
        'POSTING DATE',
        'CODE',
        'SUPPLIER CODE',
        'SUPPLIER NAME',
        'TGL.POST',
        'TGL.RETUR',
        'TAX CODE',
        'TIPE',
        'KETERANGAN',
        'TOTAL',
        'PPN',
        'PPH',
        'ROUNDING',
        'GRANDTOTAL',
        'STATUS',
        'VOIDER',
        'TGL.VOID',
        'KET.VOID',
        'DELETER',
        'TGL.DELETE',
        'KET.DELETE',
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = PurchaseMemo::where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }elseif($this->mode == '2'){
            $data = PurchaseMemo::withTrashed()->where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                '1'                 => ($key + 1),
                '2'                 => date('d/m/Y',strtotime($row->post_date)),
                '3'                 => $row->code,
                '4'                 => $row->supplier->employee_no ?? '',
                '5'                 => $row->supplier->name ?? '',
                '6'                 => date('d/m/Y',strtotime($row->post_date)),
                '8'                 => date('d/m/Y',strtotime($row->return_date)),
                '11'                => $row->return_tax_no,
                '7'                 => $row->type,
                '9'                 => $row->note,
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'rounding'          => number_format($row->rounding,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                '14'                => $row->statusRaw(),
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'void_date'         => $row->voidUser()->exists() ? $row->void_date : '',
                'void_note'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'delete_date'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'delete_note'       => $row->deleteUser()->exists() ? $row->delete_note : '',
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Purchase Memo';
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

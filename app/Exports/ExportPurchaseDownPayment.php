<?php

namespace App\Exports;

use App\Models\PurchaseDownPayment;
use App\Models\PurchaseDownPaymentDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportPurchaseDownPayment implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
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
        'No',
        'NO.Dokumen',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Tgl.Posting',
        'Kode Supplier',
        'Nama Supplier',
        'Tipe',
        'Keterangan',
        'Subtotal',
        'Diskon',
        'Total',
        'Based On',
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = PurchaseDownPayment::where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }elseif($this->mode == '2'){
            $data = PurchaseDownPayment::withTrashed()->where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                '1'                 => ($key + 1),
                '3'                 => $row->code,
                '14'                => $row->statusRaw(),
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'void_date'         => $row->voidUser()->exists() ? $row->void_date : '',
                'void_note'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'delete_date'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'delete_note'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                '6'                 => date('d/m/Y',strtotime($row->post_date)),
                '4'                 => $row->supplier->employee_no??'',
                '5'                 => $row->supplier->name??'',
                '7'                 => $row->type(),
                '9'                 => $row->note,
                'subtotal'          => number_format($row->subtotal,2,',','.'),
                'discount'          => number_format($row->discount,2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'based_on'          => $row->getReference(),
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Purchase Down Payment';
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

<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseMemo;
use App\Models\PurchaseMemoDetail;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Helpers\CustomHelper;
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
        'No',
        'No.Dokumen',
        'Status',
        'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Tgl.Posting',
        'Tgl.Retur',
        'No.Faktur Pajak Balikan',
        'Kode Supplier',
        'Nama Supplier',
        'Keterangan',
        'Item/Coa',
        'No.SPK',
        'No.Invoice',
        'Qty',
        'Nominal',
        'Total',
        'PPN',
        'PPh',
        'Grandtotal',
        'Based On',
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = PurchaseMemoDetail::whereHas('purchaseMemo',function($query){
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })->get();
        }elseif($this->mode == '2'){
            $data = PurchaseMemoDetail::withTrashed()->whereHas('purchaseMemo',function($query){
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                '1'                 => ($key + 1),
                '3'                 => $row->purchaseMemo->code,
                '14'                => $row->purchaseMemo->statusRaw(),
                'voider'            => $row->purchaseMemo->voidUser()->exists() ? $row->purchaseMemo->voidUser->name : '',
                'void_date'         => $row->purchaseMemo->voidUser()->exists() ? $row->purchaseMemo->void_date : '',
                'void_note'         => $row->purchaseMemo->voidUser()->exists() ? $row->purchaseMemo->void_note : '',
                'deleter'           => $row->purchaseMemo->deleteUser()->exists() ? $row->purchaseMemo->deleteUser->name : '',
                'delete_date'       => $row->purchaseMemo->deleteUser()->exists() ? $row->purchaseMemo->deleted_at : '',
                'delete_note'       => $row->purchaseMemo->deleteUser()->exists() ? $row->purchaseMemo->delete_note : '',
                '6'                 => date('d/m/Y',strtotime($row->purchaseMemo->post_date)),
                '8'                 => date('d/m/Y',strtotime($row->purchaseMemo->return_date)),
                '11'                => $row->purchaseMemo->return_tax_no,
                '4'                 => $row->purchaseMemo->account->employee_no ?? '',
                '5'                 => $row->purchaseMemo->account->name ?? '',
                '9'                 => $row->purchaseMemo->note,
                'ref'               => $row->getCode(),
                'spk'               => $row->getSpk(),
                'invoice'           => $row->getInvoiceNo(),
                'qty'               => CustomHelper::formatConditionalQty($row->qty),
                'nominal'           => number_format($row->getNominal(),2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                'based_on'          => $row->getCode(),
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

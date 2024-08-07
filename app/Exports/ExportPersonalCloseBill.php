<?php

namespace App\Exports;

use App\Models\PersonalCloseBill;
use App\Models\PersonalCloseBillCost;
use App\Models\PersonalCloseBillDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportPersonalCloseBill implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell,ShouldAutoSize
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
        'No. Dokumen',
        'Status',
        'Voider',
        'Tgl. Void',
        'Ket. Void',
        'Deleter',
        'Tgl. Delete',
        'Ket. Delete',
        'Pengguna',
        'Tgl. Posting',
        'Partner Bisnis',
        'Keterangan',
        'Deskripsi',
        'Qty.',
        'Satuan',
        'Harga',
        'Total',
        'PPN',
        'PPh',
        'Grandtotal'
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = PersonalCloseBill::where(function($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date); 
            })
            ->get();
        }elseif($this->mode == '2'){
            $data = PersonalCloseBill::withTrashed()->where(function($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date); 
            })
            ->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            foreach($row->personalCloseBillCost as $rowDetail){
                $arr[] = [
                    'no'            => ($key + 1),
                    'no_dokumen'    => $row->code,
                    'status'        => $row->statusRaw(),
                    'voider'        => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'void_date'     => $row->voidUser()->exists() ? $row->void_date : '',
                    'void_note'     => $row->voidUser()->exists() ? $row->void_note : '',
                    'deleter'       => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'delete_date'   => $row->deleteUser()->exists() ? $row->deleted_at : '',
                    'delete_note'   => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'name'          => $row->user->name,
                    'post_date'     => $row->post_date,
                    'bussiness_partner'  => $row->user->name,
                    'note'          => $row->note,
                    'dekripsi'      => $rowDetail->note,
                    'qty'           => CustomHelper::formatConditionalQty($rowDetail->qty),
                    'unit'          => $rowDetail->unit->name,
                    'harga'         => $rowDetail->price,
                    'subtotal'      => $rowDetail->total,
                    'ppn'           => $rowDetail->tax,
                    'pph'           => $rowDetail->wtax,
                    'grandtotal'    => $rowDetail->grandtotal,
                ];
            }
            
        }

        activity()
            ->performedOn(new PersonalCloseBill())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export outstanding close bill.');

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Fund Request';
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

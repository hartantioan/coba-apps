<?php

namespace App\Exports;

use App\Models\FundRequest;
use App\Models\FundRequestDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
class ExportFundRequest implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell,ShouldAutoSize
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
        'Tgl. Req Pembayaran',
        'Partner Bisnis',
        'Tipe Permohonan',
        'Divisi',
        'Plant',
        'Keterangan',
        'Termin',
        'Tipe Pembayaran',
        'No. Rekening',
        'Rekening Penerima',
        'Bank Tujuan',
        'Deskripsi',
        'Qty.',
        'Satuan',
        'Harga',
        'Subtotal',
        'PPN',
        'PPh',
        'Total'
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = FundRequest::where(function($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date); 
            })
            ->get();
        }elseif($this->mode == '2'){
            $data = FundRequest::withTrashed()->where(function($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date); 
            })
            ->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            foreach($row->fundRequestDetail as $rowDetail){
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
                    'required_date' => $row->required_date,
                    'bussiness_partner'  => $row->account->name,
                    'type'          => $row->type(),
                    'division'      => $row->division()->exists() ? $row->division->name : '',
                    'company_id'    => $row->company->name,
                    'note'          => $row->note,
                    'termin_note'   => $row->termin_note,
                    'payment_type'  => $row->paymentType(),
                    'no_account'    => $row->no_account,
                    'name_account'  => $row->name_account,
                    'bank_account'  => $row->bank_account,
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

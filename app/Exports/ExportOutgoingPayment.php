<?php

namespace App\Exports;

use App\Models\OutgoingPayment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportOutgoingPayment implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
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
        'Partner Bisnis',
        'Tgl. Posting',
        'No. Rekening',
        'Rekening Penerima',
        'Bank Tujuan',
        'Kas / Bank',
        'Keterangan',
        'Total',
        'Based On'
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = OutgoingPayment::where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }elseif($this->mode == '2'){
            $data = OutgoingPayment::withTrashed()->where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }

        $arr = [];

        foreach($data as $key => $row){

            $arr[] = [
                '1'                 => ($key + 1),
                'code'              => $row->code,
                'status'            => $row->statusRaw(),
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'void_date'         => $row->voidUser()->exists() ? $row->void_date : '',
                'void_note'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'delete_date'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'delete_note'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                '4'                 => $row->account->name??'',
                '3'                 => $row->post_date,
                'no_rekening'       => $row->paymentRequest->account_no,
                'rekening_penerima'       => $row->paymentRequest->account_name,
                'bank_tujuan'       => $row->paymentRequest->account_bank,
                'kas/bank'       => $row->coaSource->name,
                '6'                 => $row->note,
                '8'                 => $row->grandtotal,
                'basedon'           => $row->paymentRequest->code
               
                
            ];
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Outgoing Payment';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	public function headings() : array
	{
		return $this->headings;
	}



    // public function view(): View
    // {
    //     return view('admin.exports.outgoing_payment', [
    //         'data' => OutgoingPayment::where(function($query) {
    //             if($this->search) {
    //                 $query->where(function($query) {
    //                     $query->where('code', 'like', "%$this->search%")
    //                         ->orWhere('grandtotal', 'like', "%$this->search%")
    //                         ->orWhere('admin', 'like', "%$this->search%")
    //                         ->orWhere('note', 'like', "%$this->search%")
    //                         ->orWhereHas('user',function($query) {
    //                             $query->where('name','like',"%$this->search%")
    //                                 ->orWhere('employee_no','like',"%$this->search%");
    //                         })
    //                         ->orWhereHas('account',function($query) {
    //                             $query->where('name','like',"%$this->search%")
    //                                 ->orWhere('employee_no','like',"%$this->search%");
    //                         });
    //                 });
    //             }

    //             if($this->status){
    //                 $query->where('status', $this->status);
    //             }

    //             if($this->account){
    //                 $arrAccount = explode(',',$this->account);
    //                 $query->whereIn('account_id',$arrAccount);
    //             }

    //             if($this->currency){
    //                 $arrCurrency = explode(',',$this->currency);
    //                 $query->whereIn('currency_id',$arrCurrency);
    //             }

    //             if($this->company){
    //                 $query->where('company_id',$this->company);
    //             }
    //         })
    //         ->get()
    //     ]);
    // }
}

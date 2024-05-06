<?php

namespace App\Exports;

use App\Models\PaymentRequest;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportPaymentRequest implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
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
        'Tgl. Bayar',
        'Tipe Pembayaran',
        'Kas / Bank',
        'Reimburse',
        'No. Rekening',
        'Rekening Penerima',
        'Bank Tujuan',
        'Keterangan',
        'Ket. Detail',
        'Nominal PR',
        'Pembulatan',
        'Biaya Admin',
        'Total PR',
        'Plant',
        'Line',
        'Mesin',
        'Departemen',
        'Proyek',
        'Based On',
        'Tgl.Bayar OP',
    ];

    public function collection()
    {
        if($this->mode == '1'){
            $data = PaymentRequest::where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }elseif($this->mode == '2'){
            $data = PaymentRequest::withTrashed()->where(function ($query) {
                $query->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->end_date);
            })
            ->get();
        }

        $arr = [];

        foreach($data as $key => $row){
            foreach($row->paymentRequestDetail as $row_detail){
                $arr[] = [
                    'No.'           => ($key + 1),
                    'No. Dokumen'   => $row->code,
                    'Status'        => $row->statusRaw(),
                    'voider'        => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'void_date'     => $row->voidUser()->exists() ? $row->void_date : '',
                    'void_note'     => $row->voidUser()->exists() ? $row->void_note : '',
                    'deleter'       => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'delete_date'   => $row->deleteUser()->exists() ? $row->deleted_at : '',
                    'delete_note'   => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'partner_bisnis'=> $row->account->name ?? '',
                    'tgl_post'      => $row->post_date,
                    'required_date' => $row->pay_date,
                    'type'          => $row->paymentType(),
                    'kas/bank'            => $row->coaSource->name ?? '-',
                    'reimburse'             => $row->is_reimburse ? 'ya' : 'tidak' ,
                    'no rekening'   => $row->account_no,
                    'rekening penerima'            => $row->account_name,
                    'bank tujuan'            => $row->account_bank,
                    'keterangan'            => $row->note,
                    'ket_detail'            => $row_detail->note,
                    'nominal'               => $row_detail->nominal,
                    'rounding'              => $row->rounding,
                    'admin'                 => $row->admin,
                    'total'                 => $row->grandtotal,
                    'plant'                 => $row_detail->place()->exists() ? $row_detail->place->code : '',
                    'line'                  => $row_detail->line()->exists() ? $row_detail->line->code : '',
                    'mesin'                 => $row_detail->machine()->exists() ? $row_detail->machine->name : '',
                    'Departmen'             => $row_detail->department()->exists() ? $row_detail->department->name : '',
                    'proyek'                => $row_detail->project()->exists() ? $row_detail->project->name : '',
                    'basedon'               => $row_detail->getCode().' - '.$row_detail->paymentRequest->getPaymentCrossCode(),
                    'op_date'               => $row_detail->paymentRequest->outgoingPayment()->exists() ? $row_detail->paymentRequest->outgoingPayment->pay_date : '-',
                ];
            }
            
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Payment Request';
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
    //     return view('admin.exports.payment_request', [
    //         'data' => PaymentRequest::where(function($query) {
    //             if($this->search) {
    //                 $query->where(function($query) {
    //                     $query->where('code', 'like', "%$this->search%")
    //                         ->orWhere('grandtotal', 'like', "%$this->search%")
    //                         ->orWhere('admin', 'like', "%$this->search%")
    //                         ->orWhere('note', 'like', "%$this->search%")
    //                         ->orWhere('account_bank', 'like', "%$this->search%")
    //                         ->orWhere('account_no', 'like', "%$this->search%")
    //                         ->orWhere('account_name', 'like', "%$this->search%")
    //                         ->orWhereHas('user',function($query) {
    //                             $query->where('name','like',"%$this->search%")
    //                                 ->orWhere('employee_no','like',"%$this->search%");
    //                         })
    //                         ->orWhereHas('account',function($query) {
    //                             $query->where('name','like',"%$this->search%")
    //                                 ->orWhere('employee_no','like',"%$this->search%");
    //                         })
    //                         ->orWhereHas('paymentRequestDetail',function($query) {
    //                             $query->whereHasMorph('lookable',
    //                                 [FundRequest::class, PurchaseDownPayment::class, PurchaseInvoice::class],
    //                                 function (Builder $query) {
    //                                     $query->where('code','like',"%$this->search%");
    //                                 });
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

    //             if($this->place){
    //                 $query->where('place_id',$this->place);
    //             }
    //         })
    //         ->whereIn('place_id',$this->dataplaces)
    //         ->get()
    //     ]);
    // }
}

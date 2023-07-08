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

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
    }

    private $headings = [
        'NO',
        'DOC NUM',
        'TGL.POST',
        'ACCOUNT CODE',
        'ACCOUNT NAME',
        'KETERANGAN',
        'PAYMENT TYPE',
        'PAYMENT NO',
        'PAY DATE',
        'RECEIVING ACCOUNT CODE',
        'RECEIVING ACCOUNT NAME',
        'RECEIVING ACCOUNT BANK',
        'STATUS',
    ];

    public function collection()
    {
        $data = PaymentRequest::where(function ($query) {
            $query->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->end_date);
        })
        ->get();

        $arr = [];

        foreach($data as $key => $row){

            $arr[] = [
                '1'                => ($key + 1),
                '2'              => $row->code,
                '3'              => $row->post_date,
                '4'         => $row->account->employee_no??'',
                '5'          => $row->account->name??'',
                '6'              => $row->note,
                '8'              => $row->payment_type,
                '11'            => $row->payment_no,
                '7'              => $row->pay_date,
                '9'           => $row->account_no,
                '13'           => $row->account_name,
                '15'           => $row->account_bank,
                '14'              => $row->statusRaw(),
            ];
            
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

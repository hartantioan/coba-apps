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


class ExportOutgoingPaymentTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $status, $type_buy,$type_deliv, $company, $type_pay,$account, $currency, $end_date, $start_date , $search , $modedata;


    public function __construct(string $search,string $status, string $company, string $type_pay,string $account, string $currency, string $end_date, string $start_date,  string $modedata )
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->company = $company ? $company : '';
        $this->type_pay = $type_pay ? $type_pay : '';
        $this->account = $account ? $account : '';
        $this->currency = $currency ? $currency : '';
        $this->modedata = $modedata ? $modedata : '';
        
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
        
        $data = OutgoingPayment::where(function ($query) {
            if($this->search) {
                $query->where(function($query) {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('post_date', 'like', "%$this->search%")
                        ->orWhere('due_date', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        });
                });
            }
            if($this->status){
                $groupIds = explode(',', $this->status);
                $query->whereIn('status', $groupIds);
            }
    
            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }
    
           
    
            if($this->account){
                $groupIds = explode(',', $this->account);
                $query->whereIn('account_id',$groupIds);
            }
            
            if($this->company){
                $query->where('company_id',$this->company);
            }
    
            if($this->type_pay){
                $query->where('payment_type',$this->type_pay);
            }                
            
            if($this->currency){
                $groupIds = explode(',', $this->currency);
                $query->whereIn('currency_id',$groupIds);
            }
    
            if(!$this->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })
        ->get();
        

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
}

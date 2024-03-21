<?php

namespace App\Exports;

use App\Models\PaymentRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ExportPaymentRequestTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
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
        'Based On'
    ];

    public function collection()
    {
       
        $data = PaymentRequest::where(function ($query) {
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
}

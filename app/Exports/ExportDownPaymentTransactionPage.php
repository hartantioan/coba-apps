<?php

namespace App\Exports;

use App\Models\PurchaseDownPayment;
use App\Models\PurchaseDownPaymentDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportDownPaymentTransactionPage implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    protected $status, $type_buy,$type_deliv, $company, $type_pay,$supplier, $currency, $end_date, $start_date , $search , $modedata,$status_dokumen;


    public function __construct(string $search,string $status, string $company, string $type_pay,string $supplier, string $currency, string $end_date, string $start_date,  string $modedata, string $status_dokumen )
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->status_dokumen = $status_dokumen? $status_dokumen : '';
        $this->company = $company ? $company : '';
        $this->type_pay = $type_pay ? $type_pay : '';
        $this->supplier = $supplier ? $supplier : '';
        $this->currency = $currency ? $currency : '';
        $this->modedata = $modedata ? $modedata : '';
        
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
        
        $data = PurchaseDownPayment::where(function ($query) {
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
    
           
    
            if($this->supplier){
                $groupIds = explode(',', $this->supplier);
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

            if($this->status_dokumen == 1 && $this->status_dokumen){
                $query->where('balance_status',$this->status_dokumen);
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

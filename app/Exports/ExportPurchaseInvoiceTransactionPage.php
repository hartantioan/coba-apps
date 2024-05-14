<?php

namespace App\Exports;
use App\Helpers\CustomHelper;
use App\Models\PurchaseInvoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportPurchaseInvoiceTransactionPage implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    protected $status, $type_buy,$type_deliv, $company, $type_pay,$supplier, $end_date, $start_date , $search , $modedata;


    public function __construct(string $search,string $status, string $company, string $type_pay,string $supplier, string $end_date, string $start_date,  string $modedata )
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->company = $company ? $company : '';
        $this->type_pay = $type_pay ? $type_pay : '';
        $this->supplier = $supplier ? $supplier : '';
        $this->modedata = $modedata ? $modedata : '';
        
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
        'Doner',
        'Tgl.Done',
        'Ket.Done',
        'Tgl.Posting',
        'Tgl.Terima',
        'Tgl.Dokumen',
        'TOP',
        'Tgl.Jatuh Tempo',
        'No.Dokumen',
        'No.Invoice',
        'No.Faktur Pajak',
        'No.Bukti Potong',
        'No.SPK',
        'NIK',
        'User',
        'Kode Supplier',
        'Nama Supplier',
        'Keterangan',
        'Mata Uang',
        'Konversi',
        'Harga',
        'Total',
        'PPN',
        'PPh',
        'Grandtotal',
        'Downpayment',
        'Balance',
    ];

    public function collection()
    {
        
        $data = PurchaseInvoice::where(function ($query) {
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
            
    
            if(!$this->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })
        ->get();
        

        $arr = [];
        foreach($data as $key => $row){
            $arr[] = [
                '1'                 => ($key + 1),
                'invoice_code'                 => $row->code ?? '',
                'status'                 => $row->statusRaw() ?? '',
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'void_date'         => $row->voidUser()->exists() ? $row->void_date : '',
                'void_note'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'delete_date'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'delete_note'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                'done_date'         => $row->doneUser()->exists() ? $row->done_date : '',
                'done_note'         => $row->doneUser()->exists() ? $row->done_note : '',
                'post_date'                 => date('d/m/Y',strtotime($row->post_date)),
                'recieve_date'                 => date('d/m/Y',strtotime($row->received_date)),
                'document_date'                 => date('d/m/Y',strtotime($row->document_date)),
                'top'               => $row->top(),
                'due_date'                => date('d/m/Y',strtotime($row->due_date)),
                'document_no'                 => $row->document_no,
                'invoice_no'                 => $row->invoice_no,
                'fp'                => $row->tax_no,
                'fp_cut'            => $row->tax_cut_no,
                'spk'               => $row->spk_no,
                'user_code'         => $row->user->employee_no ?? '',
                'user_name'         => $row->user->name ?? '',
                'supplier_code'     => $row->account->employee_no ?? '',
                'supplier_name'     => $row->account->name ?? '',
                'note'              => $row->note,
                'currency_id'       => $row->currency->name,
                'currency_rate'     => number_format($row->currency_rate,2,',','.'),
                
                'price'             => number_format($row->price,2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                'downpayment'       => number_format($row->downpayment,2,',','.'),
                'balance'           => number_format($row->balance,2,',','.'),
            ];
            
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Purchase Invoice';
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

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
        'Kode Supplier',
        'Nama Supplier',
        'Keterangan',
        'Mata Uang',
        'Konversi',
        'GR/LC/PO/Coa No.',
        'NO.PO/GRPO',
        'No. SJ',
        'Kode Item / COA',
        'Nama Item / COA',
        /* 'Plant', */
        'Qty',
        'Satuan',
        'Line',
        'Mesin',
        'Divisi',
        'Gudang',
        'Proyek',
        'Harga',
        'Total',
        'PPN',
        'PPh',
        'Grandtotal',
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
                '3'                 => $row->purchaseInvoice->code,
                '4'                 => $row->purchaseInvoice->statusRaw(),
                'voider'            => $row->purchaseInvoice->voidUser()->exists() ? $row->purchaseInvoice->voidUser->name : '',
                'void_date'         => $row->purchaseInvoice->voidUser()->exists() ? $row->purchaseInvoice->void_date : '',
                'void_note'         => $row->purchaseInvoice->voidUser()->exists() ? $row->purchaseInvoice->void_note : '',
                'deleter'           => $row->purchaseInvoice->deleteUser()->exists() ? $row->purchaseInvoice->deleteUser->name : '',
                'delete_date'       => $row->purchaseInvoice->deleteUser()->exists() ? $row->purchaseInvoice->deleted_at : '',
                'delete_note'       => $row->purchaseInvoice->deleteUser()->exists() ? $row->purchaseInvoice->delete_note : '',
                'doner'             => ($row->purchaseInvoice->status == 3 && is_null($row->purchaseInvoice->done_id)) ? 'sistem' : (($row->purchaseInvoice->status == 3 && !is_null($row->purchaseInvoice->done_id)) ? $row->purchaseInvoice->doneUser->name : null),
                'done_date'         => $row->purchaseInvoice->doneUser()->exists() ? $row->purchaseInvoice->done_date : '',
                'done_note'         => $row->purchaseInvoice->doneUser()->exists() ? $row->purchaseInvoice->done_note : '',
                '6'                 => date('d/m/Y',strtotime($row->purchaseInvoice->post_date)),
                '7'                 => date('d/m/Y',strtotime($row->purchaseInvoice->received_date)),
                '8'                 => date('d/m/Y',strtotime($row->purchaseInvoice->document_date)),
                'top'               => $row->purchaseInvoice->top(),
                '11'                => date('d/m/Y',strtotime($row->purchaseInvoice->due_date)),
                '5'                 => $row->purchaseInvoice->document_no,
                '6'                 => $row->purchaseInvoice->invoice_no,
                'fp'                => $row->purchaseInvoice->tax_no,
                'fp_cut'            => $row->purchaseInvoice->tax_cut_no,
                'spk'               => $row->purchaseInvoice->spk_no,
                'supplier_code'     => $row->purchaseInvoice->account->employee_no,
                'supplier_name'     => $row->purchaseInvoice->account->name,
                'note'              => $row->purchaseInvoice->note,
                'currency_id'       => $row->purchaseInvoice->currency->name,
                'currency_rate'     => number_format($row->purchaseInvoice->currency_rate,2,',','.'),
                'code'              => $row->getHeaderCode(),
                'po_no'             => $row->getPurchaseCode(),
                'no_sj'             => $row->getDeliveryCode(),
                'item_code'         => $row->getCodeExport(),
                'item_name'         => $row->getNameExport(),
                /* 'plant'             => $row->place->code, */
                'qty'               => $row->qty,
                'unit'              => $row->getUnitConversion(),
                'line'              => $row->line()->exists() ? $row->line->code : '',
                'machine'           => $row->machine()->exists() ? $row->machine->name : '',
                'department'        => $row->department()->exists() ? $row->department->name : '',
                'warehouse'         => $row->warehouse()->exists() ? $row->warehouse->name : '',
                'project'           => $row->project()->exists() ? $row->project->name : '',
                'price'             => number_format($row->price,2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
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

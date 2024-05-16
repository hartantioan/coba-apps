<?php

namespace App\Exports;

use App\Models\PurchaseMemo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;

class ExportPurchaseMemoTransactionPage implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    protected $status, $company, $supplier, $end_date, $start_date , $search , $modedata;

    public function __construct(string $search,string $status, string $company,string $supplier, string $end_date, string $start_date,  string $modedata )
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->company = $company ? $company : '';
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
        'Tgl.Retur',
        'No.Faktur Pajak Balikan',
        'NIK',
        'User',
        'Kode Supplier',
        'Nama Supplier',
        'Keterangan',
        'Item/Coa',
        'No.SPK',
        'No.Invoice',
        'Qty',
        'Nominal',
        'Total',
        'PPN',
        'PPh',
        'Grandtotal',
        'Based On',
    ];
    public function collection()
    {
        $data = PurchaseMemo::where(function ($query) {
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
    
          
    
            if(!$this->modedata){
                $query->where('user_id',session('bo_id'));
            }
        })
        ->get();

        $arr = [];

        foreach($data as $key => $row){
            $arr[] = [
                '1'                 => ($key + 1),
                '2'                 => $row->purchaseMemo->code,
                '3'                => $row->purchaseMemo->statusRaw(),
                '4'            => $row->purchaseMemo->voidUser()->exists() ? $row->purchaseMemo->voidUser->name : '',
                '5'         => $row->purchaseMemo->voidUser()->exists() ? $row->purchaseMemo->void_date : '',
                '6'         => $row->purchaseMemo->voidUser()->exists() ? $row->purchaseMemo->void_note : '',
                '7'           => $row->purchaseMemo->deleteUser()->exists() ? $row->purchaseMemo->deleteUser->name : '',
                '8'       => $row->purchaseMemo->deleteUser()->exists() ? $row->purchaseMemo->deleted_at : '',
                '9'       => $row->purchaseMemo->deleteUser()->exists() ? $row->purchaseMemo->delete_note : '',
                '10'             => ($row->purchaseMemo->status == 3 && is_null($row->purchaseMemo->done_id)) ? 'sistem' : (($row->purchaseMemo->status == 3 && !is_null($row->purchaseMemo->done_id)) ? $row->purchaseMemo->doneUser->name : null),
                '11'         => $row->purchaseMemo->doneUser()->exists() ? $row->purchaseMemo->done_date : '',
                '12'         => $row->purchaseMemo->doneUser()->exists() ? $row->purchaseMemo->done_note : '',
                '13'                 => date('d/m/Y',strtotime($row->purchaseMemo->post_date)),
                '14'                 => date('d/m/Y',strtotime($row->purchaseMemo->return_date)),
                '15'                => $row->purchaseMemo->return_tax_no,
                '16'                 => $row->purchaseMemo->user->employee_no ?? '',
                '17'                 => $row->purchaseMemo->user->name ?? '',
                '18'                 => $row->purchaseMemo->account->employee_no ?? '',
                '19'                 => $row->purchaseMemo->account->name ?? '',
                '20'                 => $row->purchaseMemo->note,
                'ref'               => $row->getCode(),
                'spk'               => $row->getSpk(),
                'invoice'           => $row->getInvoiceNo(),
                'qty'               => $row->qty,
                'nominal'           => number_format($row->getNominal(),2,',','.'),
                'total'             => number_format($row->total,2,',','.'),
                'tax'               => number_format($row->tax,2,',','.'),
                'wtax'              => number_format($row->wtax,2,',','.'),
                'grandtotal'        => number_format($row->grandtotal,2,',','.'),
                'based_on'          => $row->getCode(),
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Laporan Purchase Memo';
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

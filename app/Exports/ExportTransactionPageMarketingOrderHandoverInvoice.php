<?php

namespace App\Exports;

use App\Models\MarketingOrderHandoverInvoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportTransactionPageMarketingOrderHandoverInvoice implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$status,$company,$marketing_order,$end_date,$start_date,$dataplaces,$dataplacecode,$datawarehouses;


    public function __construct(string $search,string $status,string $company, string $end_date,string $start_date)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
       
        $this->company = $company ? $company : '';
        
    }

    private $headings = [
        'No',
        'Kode',
        'Petugas',
        'Voider',
        'Tgl Void',
        'Ket Void',
        'Deleter',
        'Tgl Delete',
        'Ket Delete',
        'Doner',
        'Tgl Done',
        'Ket Done',
        'Tgl Posting',
        'Pelanggan',
        'Perusahaan',
        'Catatan',
        'Status',
    ];

    public function collection()
    {
        $query_data = MarketingOrderHandoverInvoice::where(function($query)  {
                if($this->search) {
                    $query->where(function($query)  {
                        $query->where('code', 'like', "$this->search%")
                        ->orWhere('note', 'like', "$this->search%")
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"$this->search%")
                                ->orWhere('employee_no','like',"$this->search%");
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

                
                if($this->company){
                    $query->where('company_id',$this->company);
                }

            })
        ->whereRaw("SUBSTRING(code,8,2) IN ('".implode("','",$this->dataplacecode)."')")
        
        ->get();

        $arr=[];
        foreach($query_data as $key => $row){
            
            $arr[] = [
                'no'                => ($key + 1),
                'kode'              => $row->code,
                'petugas'           => $row->user->name,
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'tgl_void'         => $row->voidUser()->exists() ? $row->void_date : '',
                'ket_void'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'tgl_delete'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'ket_delete'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                'tgl_done'         => $row->doneUser()->exists() ? $row->done_date : '',
                'ket_done'         => $row->doneUser()->exists() ? $row->done_note : '',
                'tgl_posting'         => date('d/m/Y',strtotime($row->post_date)),
                'pelanggan'        => $row->account->name,
                'perusahaan'           => $row->company->name,
                'catatan'           => $row->note,
                'status'            => $row->statusRaw(),
            ];
        
            
        }
        
        return collect($arr);
    }

    public function title(): string
    {
        return 'Tanda Terima Kwitansi';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

<?php

namespace App\Exports;

use App\Models\MarketingOrderInvoice;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportTransactionPageMarketingOrderInvoice implements  FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$status,$account_id,$type,$company,$marketing_order,$end_date,$start_date,$dataplaces,$dataplacecode,$datawarehouses;


    public function __construct(string $search,string $status, string $account_id, string $type,string $company, string $end_date,string $start_date)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->type = $type ? $type : '';
        $this->account_id = $account_id ? $account_id : '';
        $this->company = $company ? $company : '';

    }

    private $headings = [
        'No',
        'Kode',
        'Pengguna',
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
        'Alamat Penagihan & NPWP',
        'Jatuh Tempo',
        'Jatuh Tempo Internal',
        'Jenis',
        'Tipe Invoice',
        'Seri Pajak',
        'No Ppbj',
        'Catatan',
        'Subtotal',
        'Downpayment',
        'Total',
        'PPN',
        'Grandtotal',
        'Status',
        'DPP sesuai FP',
        'PPN sesuai FP',
    ];

    public function collection()
    {
        $user = User::find(session('bo_id'));

        $this->dataplaces = $user ? $user->userPlaceArray() : [];
        $this->dataplacecode = $user ? $user->userPlaceCodeArray() : [];
        $this->datawarehouses = $user ? $user->userWarehouseArray() : [];

        $query_data = MarketingOrderInvoice::where(function($query)  {
            if($this->search) {
                $query->where(function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('total', 'like', "%$this->search%")
                        ->orWhere('tax', 'like', "%$this->search%")
                        ->orWhere('grandtotal', 'like', "%$this->search%")
                        ->orWhere('subtotal', 'like', "%$this->search%")
                        ->orWhere('downpayment', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query) {
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })
                        ->orWhereHas('account',function($query) {
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        });
                });
            }

            if($this->start_date && $this->end_date) {
                $query->whereDate('post_date', '>=', $this->start_date)
                    ->whereDate('post_date', '<=', $this->end_date);
            } else if($this->start_date) {
                $query->whereDate('post_date','>=', $this->start_date);
            } else if($this->end_date) {
                $query->whereDate('post_date','<=', $this->end_date);
            }

            if($this->status){
                $query->whereIn('status', $this->status);
            }

            if($this->type){
                $query->where('type',$this->type);
            }

            if($this->account_id){
                $query->whereIn('account_id',$this->account_id);
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
                'pengguna'          => $row->user->name,
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
                'alamat_penagihan'         =>  $row->userData->title.' - '.$row->userData->npwp.' - '.$row->userData->address,
                'jatuh_tempo'         => date('d/m/Y',strtotime($row->due_date)),
                'jatuh_tempo_internal'           => $row->due_date_internal ? date('d/m/Y',strtotime($row->due_date_internal)) : '-',
                'jenis'       => $row->type(),
                'invoice_type' => $row->invoiceType(),
                'seri_pajak'             => $row->tax_no,
                'no_pjb'             => $row->no_pjb ?? '',
                'catatan'         => $row->note,
                'subtotal'         => $row->subtotal,
                'downpayment'        => $row->downpayment,
                'total'           => $row->total,
                'ppn'        => $row->tax,
                'grandtotal'           => $row->grandtotal,
                'status'            => $row->statusRaw(),
                'total_fp'           => floor($row->total),
                'ppn_fp'        => floor($row->tax),
            ];


        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Marketing Order Return';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

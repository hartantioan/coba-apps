<?php

namespace App\Exports;

use App\Models\ListBgCheck;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportListBGCheck implements FromCollection
{
    protected $status, $search;

    public function __construct(string $search,string $status)
    {
        $this->search = $search ? $search : '';
        $this->status = $status ? $status : '';

    }
    private $headings = [
        'No',
        'No. BG Check',
        'Status',
        /* 'Voider',
        'Tgl.Void',
        'Ket.Void',
        'Deleter',
        'Tgl.Delete',
        'Ket.Delete',
        'Doner',
        'Tgl.Done',
        'Ket.Done', */
        'Tgl.Posting',
        'NIK',
        'User',
        'Kode Customer',
        'Nama Customer',
        'Keterangan',
        'Nomor Dokumen',
        'Tipe',
        'Tgl.Jatuh Tempo',
        'Tgl. Bayar',
        'Bank Tujuan',
        'Grandtotal',
    ];

    public function collection()
    {
        $arr = [];
        $data = ListBgCheck::where(function($query) {
            // Apply the search conditions within the 'purchaseOrder' relationship
            $query->where(function($query){
                $query->where('code', 'like', "%$this->search%")
                    ->orWhere('document_no', 'like', "%$this->search%")
                    ->orWhere('note', 'like', "%$this->search%")
                    ->orWhere('pay_date', 'like', "%$this->search%")
                    ->orWhere('valid_until_date', 'like', "%$this->search%")
                    ->orWhere('nominal', 'like', "%$this->search%")
                    ->orWhere('grandtotal', 'like', "%$this->search%")
                    ->orWhereHas('user',function($query){
                        $query->where('name','like',"%$this->search%")
                            ->orWhere('employee_no','like',"%$this->search%");
                    })
                    ->orWhereHas('account',function($query){
                        $query->where('name','like',"%$this->search%")
                            ->orWhere('employee_no','like',"%$this->search%");
                    })
                    ->orWhereHas('coa',function($query) {
                        $query->where('code','like',"%$this->search%")
                            ->orWhere('name','like',"%$this->search%");
                    });
            });

            // Other conditions for the 'purchaseOrder' relationship
            if($this->status){
                $groupIds = explode(',', $this->status);
                $query->whereIn('status', $groupIds);
            }

        })

        ->get();


        foreach($data as $key => $row){
            $arr[]=[
                'No' =>$key+1,
                'No. BG Check'=>$row->code,
                'Status'=>$row->statusRaw(),
                // 'voider'            => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->voidUser->name : '',
                // 'void_date'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_date : '',
                // 'void_note'         => $row->purchaseOrder->voidUser()->exists() ? $row->purchaseOrder->void_note : '',
                // 'deleter'           => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleteUser->name : '',
                // 'delete_date'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->deleted_at : '',
                // 'delete_note'       => $row->purchaseOrder->deleteUser()->exists() ? $row->purchaseOrder->delete_note : '',
                // 'doner'             => ($row->purchaseOrder->status == 3 && is_null($row->purchaseOrder->done_id)) ? 'sistem' : (($row->purchaseOrder->status == 3 && !is_null($row->purchaseOrder->done_id)) ? $row->purchaseOrder->doneUser->name : null),
                // 'done_date'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_date : '',
                // 'done_note'         => $row->purchaseOrder->doneUser()->exists() ? $row->purchaseOrder->done_note : '',
                'Tgl.Posting'=> date('d/m/Y',$row->post_date),
                'NIK'=>$row->user->employee_no,
                'User'=>$row->user->name,
                'Kode Customer'=>$row->account->employee_no,
                'Nama Customer'=>$row->account->name,
                'Keterangan'=>$row->note,
                'Nomor Dokumen'=>$row->document_no,
                'Tipe'=>$row->type(),
                'Tgl.Jatuh Tempo'=>date('d/m/Y',$row->valid_until_date),
                'Tgl. Bayar'=>date('d/m/Y',$row->pay_date),
                'Bank Tujuan'=>$row->coa->code ?? '-' .' '.$row->coa->name ?? '',
                'grandtotal'=>$row->grandtotal,
            ];

        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Rekap List BG Check';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

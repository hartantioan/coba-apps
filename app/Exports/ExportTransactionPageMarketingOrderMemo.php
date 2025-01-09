<?php

namespace App\Exports;

use App\Models\MarketingOrderMemo;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportTransactionPageMarketingOrderMemo implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$status,$account_id,$company,$marketing_order,$end_date,$start_date,$dataplaces,$dataplacecode,$datawarehouses;


    public function __construct(string $search,string $status, string $account_id,string $company, string $end_date,string $start_date)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->account_id = $account_id ? $account_id : '';
        $this->company = $company ? $company : '';

    }

    private $headings = [
        'No',
        'Kode',
        'Status',
        'Voider',
        'Tgl Void',
        'Ket Void',
        'Deleter',
        'Tgl Delete',
        'Ket Delete',
        'Doner',
        'Tgl Done',
        'Ket Done',
        'Pengguna',
        'Tgl Posting',
        'Pelanggan',
        'Perusahaan',
        'Tipe Memo',
        'No Seri Pajak',
        'No SJ',
        'No ARIN',
        'Keterangan',
        'Item SJ',
        'Item Kembali',
        'Qty',
        'Satuan',
        'Qty Jual',
        'Satuan Jual',
        'Batch',
        'Shading',
        'Total',
        'PPN',
        'Grandtotal',
    ];

    public function collection()
    {
        $query_data = MarketingOrderMemo::where(function($query) {
            if($this->search) {
                $query->where(function($query)  {
                    $query->where('code', 'like', "%$this->search%")
                        ->orWhere('total', 'like', "%$this->search%")
                        ->orWhere('tax', 'like', "%$this->search%")
                        ->orWhere('grandtotal', 'like', "%$this->search%")
                        ->orWhere('note', 'like', "%$this->search%")
                        ->orWhere('tax_no', 'like', "%$this->search%")
                        ->orWhereHas('user',function($query){
                            $query->where('name','like',"%$this->search%")
                                ->orWhere('employee_no','like',"%$this->search%");
                        })
                        ->orWhereHas('account',function($query){
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
                $groupIds = explode(',', $this->status);
                $query->whereIn('status', $groupIds);
            }

            if($this->account_id){
                $groupIds = explode(',', $this->account_id);
                $query->whereIn('account_id',$groupIds);
            }

            if($this->company){
                $query->where('company_id',$this->company);
            }
        })
        ->get();

        $arr=[];
        foreach($query_data as $key => $row){
            foreach($row->marketingOrderMemoDetail as $row_detail){
                $arr[] = [
                    'no'            => ($key + 1),
                    'kode'          => $row->code,
                    'status'        => $row->statusRaw(),
                    'voider'        => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'tgl_void'      => $row->voidUser()->exists() ? $row->void_date : '',
                    'ket_void'      => $row->voidUser()->exists() ? $row->void_note : '',
                    'deleter'       => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'tgl_delete'    => $row->deleteUser()->exists() ? $row->deleted_at : '',
                    'ket_delete'    => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'doner'         => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                    'tgl_done'      => $row->doneUser()->exists() ? $row->done_date : '',
                    'ket_done'      => $row->doneUser()->exists() ? $row->done_note : '',
                    'petugas'       => $row->user->name,
                    'tgl_posting'   => date('d/m/Y',strtotime($row->post_date)),
                    'pelanggan'     => $row->account->name,
                    'perusahaan'    => $row->company->name,
                    'jenis'         => $row->memoType(),
                    'no_seri_pajak' => $row->tax_no,
                    'no_sj'         => $row->getSJCode(),
                    'no_arin'       => $row->getArinCode(),
                    'catatan'       => $row->note,
                    'item_sj'        => $row_detail->lookable->itemStock->item->code . ' - ' . $row_detail->lookable->itemStock->item->name,
                    'item_kembali'   => ($row_detail->itemStock()->exists() ? $row_detail->itemStock->item->code . ' - ' . $row_detail->itemStock->item->name : ''),
                    'qty'            => $row_detail->qty,
                    'satuan'         => ($row_detail->itemStock()->exists() ? $row_detail->itemStock->item->uomUnit->code : ''),
                    'qty_jual'       => $row_detail->qty_sell,
                    'satuan_jual'    => $row_detail->lookable->marketingOrderDeliveryDetail->marketingOrderDetail->itemUnit->unit->code,
                    'batch'          => ($row_detail->itemStock()->exists() ? $row_detail->itemStock->productionBatch->code : ''),
                    'shading'        => ($row_detail->itemStock()->exists() ? $row_detail->itemStock->itemShading->code : ''),
                    'total'         => $row->total,
                    'ppn'           => $row->tax,
                    'grandtotal'    => $row->grandtotal,
                ];
            }


        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Marketing Order Memo';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

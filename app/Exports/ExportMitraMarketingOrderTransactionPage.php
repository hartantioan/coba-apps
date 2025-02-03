<?php

namespace App\Exports;

use App\Models\MitraMarketingOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Support\Collection;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\MarketingOrder;

class ExportMitraMarketingOrderTransactionPage implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search, $status, $end_date , $start_date;

    public function __construct(string $search,string $status, string $end_date,string $start_date )
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
    }
    private $headings = [
        'No',
        'No. Dokumen',
        'No. Referensi',
        'Status',
        'Broker',
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
        'Valid Date',
        'Customer',
        'Tipe Pengiriman',
        'Tgl Kirim',
        'Tipe Pembayaran',
        'Alamat Tujuan',
        'Provinsi Tujuan',
        'Kota Tujuan',
        'Kecamatan Tujuan',
        '%DP',
        'Catatan',
        'Item',
        'Qty',
        'Satuan',
        'Harga',
        'Total',
        'PPN',
        'Grandtotal',
        'Catatan Item',
    ];

    public function collection()
    {
        $data = MitraMarketingOrder::where(function($query) {
            // Apply the search conditions within the 'purchaseOrder' relationship
            $query->where(function($query){
                $query->where('code', 'like', "%$this->search%")
                ->orWhere('document_no', 'like', "%$this->search%")
                ->orWhere('note', 'like', "%$this->search%")
                ->orWhere('total', 'like', "%$this->search%")
                ->orWhere('tax', 'like', "%$this->search%")
                ->orWhere('grandtotal', 'like', "%$this->search%")
                ->orWhereHas('user',function($query){
                    $query->where('name','like',"%$this->search%")
                        ->orWhere('employee_no','like',"%$this->search%");
                })
                ->orWhereHas('account',function($query){
                    $query->where('name','like',"%$this->search%")
                        ->orWhere('employee_no','like',"%$this->search%");
                })
                ->orWhereHas('mitraMarketingOrderDetail',function($query) {
                    $query->whereHas('item',function($query) {
                        $query->where('code','like',"%$this->search%")
                            ->orWhere('name','like',"%$this->search%");
                    });
                });
            });

            // Other conditions for the 'purchaseOrder' relationship
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

        })->get();


        foreach($data as $key => $row){
            foreach($row->mitraMarketingOrderDetail as $rowdetail){
                $arr[] = [
                    'no'                => ($key + 1),
                    'code'              => $row->code,
                    'babi'              => $row->document_no,
                    'status'            => $row->statusRaw(),
                    'nik'               => $row->user->employee_no.' - '.$row->user->name,
                    'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'void_date'         => $row->voidUser()->exists() ? $row->void_date : '',
                    'void_note'         => $row->voidUser()->exists() ? $row->void_note : '',
                    'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'delete_date'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                    'delete_note'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                    'done_date'         => $row->doneUser()->exists() ? $row->done_date : '',
                    'done_note'         => $row->doneUser()->exists() ? $row->done_note : '',
                    'post_date'         => date('d/m/Y',strtotime($row->post_date)),
                    'valid_date'        => date('d/m/Y',strtotime($row->valid_date)),
                    'customer'          => $row->account->name,
                    'deliv_type'        => $row->deliveryType(),
                    'delivery_date'     => date('d/m/Y',strtotime($row->delivery_date)),
                    'payment_type'      => $row->paymentType(),
                    'address'           => $row->delivery_address,
                    'province'          => $row->deliveryProvince->name,
                    'city'              => $row->deliveryCity->name,
                    'district'          => $row->deliveryDistrict->name,
                    'dp'                => round($row->percent_dp,2),
                    'note'              => $row->note,
                    'item'              => $rowdetail->item->code.' - '.$rowdetail->item->name,
                    'qty'               => round($rowdetail->qty,3),
                    'unit'              => $rowdetail->item->uomUnit->code,
                    'price'             => round($rowdetail->price,2),
                    'total'             => round($rowdetail->total,2),
                    'tax'               => round($rowdetail->tax,2),
                    'grandtotal'        => round($rowdetail->grandtotal,2),
                    'note_detail'       => $rowdetail->note,
                ];
            }
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Marketing Order';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

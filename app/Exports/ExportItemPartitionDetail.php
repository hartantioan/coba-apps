<?php

namespace App\Exports;

use App\Models\ItemPartitionDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportItemPartitionDetail implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search,$status,$account_id,$company,$marketing_order,$end_date,$start_date,$dataplaces,$dataplacecode,$datawarehouses;


    public function __construct(string $search,string $status, string $end_date,string $start_date)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';

    }

    private $headings = [
        'No',
        'Kode',
        'Status',
        'GrandTotal',
        'Tgl Posting',
        'Item Asal',
        'Qty',
        'Harga',
        'Total',
        'Item Tujuan',
        'Qty Partisi',
        'Keterangan',
    ];


    public function collection(): Collection
    {
        $query_data = ItemPartitionDetail::whereHas('itemPartition', function ($query) {
                if($this->search) {
                    $query->where(function($query)  {
                        $query->where('code', 'like', "%$this->search%")
                            ->orWhere('note', 'like', "%$this->search%")
                            ->orWhereHas('user',function($query) {
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
            })
        ->get();

        $arr=[];
        foreach($query_data as $key => $row){

            $arr[] = [
                'no'           => $key + 1,
                'kode'         => $row->itemPartition->code ?? '',
                'status'       => $row->itemPartition->statusRaw() ?? '',
                'grandtotal'   => number_format($row->itemPartition->grandtotal, 2, ',', '.'),
                'tgl_posting'  => $row->itemPartition->post_date ? date('d/m/Y', strtotime($row->itemPartition->post_date)) : '',
                'item_asal'    => $row->fromStock->item->name ?? '',
                'qty'          => number_format($row->qty, 2, ',', '.'),
                'harga'        => number_format($row->price, 2, ',', '.'),
                'total'        => number_format($row->total, 2, ',', '.'),
                'item_tujuan'  => $row->toStock->item->name ?? '',
                'qty_part'     => number_format($row->qty_partition, 2, ',', '.'),
                'keterangan'   => $row->note ?? '',
            ];


        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Partisi Item';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

<?php

namespace App\Exports;

use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportSalesOrderDetail implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
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
        'Status',
        'Grand Total',
        'Kode',
        'Tipe Pembayaran',
        'Tipe Penjualan',
        'Tgl Posting',
        'Customer',
        'Item',
        'Qty',
        'Harga',
        'Diskon 3 (Rp)',
        'Keterangan',
    ];



    public function collection(): Collection
    {
        $query_data = SalesOrderDetail::whereHas('salesOrder', function ($query) {
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
                'no'          => $key + 1,
                'status'      => $row->salesOrder->statusRaw() ?? '',                                                        // assuming there's a statusRaw() method
                'grandtotal'  => number_format($row->salesOrder->grandtotal, 2, ',', '.'),
                'kode'        => $row->salesOrder->code ?? '',
                'paymentType' => $row->salesOrder->paymentType() ?? '',
                'type_sales'  => $row->salesOrder->typeSales() ?? '',
                'tgl_posting' => $row->salesOrder->post_date ? date('d/m/Y', strtotime($row->salesOrder->post_date)) : '',
                'customer'    => $row->salesOrder->customer->name ?? '',                                                     // optional if you have relationship
                'item'        => $row->item->name ?? '',
                'qty'         => number_format($row->qty, 2, ',', '.'),
                'harga'       => number_format($row->price, 2, ',', '.'),
                'discount_3'  => number_format($row->discount_3, 2, ',', '.'),
                'keterangan'  => $row->note ?? '',
            ];



        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Penjualan';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

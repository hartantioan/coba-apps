<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportInvoiceStore implements FromCollection
{
    protected $search,$post_date,$end_date,$store_customer_id;

    public function __construct(string $search,$post_date,$end_date,$store_customer_id)
    {
        $this->search = $search ? $search : '';
        $this->post_date = $post_date ? $post_date : '';
        $this->end_date = $end_date ? $end_date : '';
        $this->store_customer_id = $store_customer_id ? $store_customer_id : '';
    }
    private $headings = [
        'No',
        'Kode',
        'Kasir',
        'Customer',
        'Grandtotal',
        'Discount',
    ];

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings() : array
	{
		return $this->headings;
	}

    public function title(): string
    {
        return 'Invoice';
    }

    public function collection()
    {

        $data = Invoice::where(function ($query) {
            if ($this->search) {
                $query->whereHas('item', function ($query)  {
                    $query->where('name', 'like', "%$this->search%")
                        ->orWhere('code', 'like', "%$this->search%");
                });
            }
            if($this->store_customer_id){
                $query->where('store_customer_id', $this->store_customer_id);
            }


            if($this->post_date && $this->end_date) {
                $query->where(function($query)  {
                    $query->whereDate('valid_from', '>=', $this->post_date)
                        ->whereDate('valid_from', '<=', $this->end_date);
                })->orWhere(function($query)  {
                    $query->whereDate('valid_to', '>=', $this->post_date)
                        ->whereDate('valid_to', '<=', $this->end_date);
                });
            } else if($this->post_date) {
                $query->whereDate('valid_from','>=', $this->post_date);
            } else if($this->end_date) {
                $query->whereDate('valid_to','<=', $this->end_date);
            }
        })->get();
        $arr=[];
        $nomor = 1;
        foreach($data as $row){
            $arr[] = [
                'No'=>$nomor,
                'Kode'=>$row->code,
                'Kasir'=>$row->user->name,
                'Customer'=>$row->storeCustomer?->name ?? '',
                'Grandtotal'=>$row->grandtotal,
                'Discount'=>$row->discount,
            ];
            $nomor++;
        }

        return collect($arr);
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\MarketingOrder;

class ExportTransactionPageMarketingOrderDetail1 implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search, $status,$type_sales, $type_pay,$type_deliv, $company, $customer, $delivery , $sales , $currency , $end_date , $start_date;

    public function __construct(string $search,string $status, string $type_sales,string $type_pay, string $type_deliv, string $company,string $customer, string $delivery, string $sales, string $currency,  string $end_date,string $start_date)
    {
        $this->search = $search ? $search : '';
        $this->start_date = $start_date ? $start_date : '';
		$this->end_date = $end_date ? $end_date : '';
        $this->status = $status ? $status : '';
        $this->type_sales = $type_sales ? $type_sales : '';
       
        $this->type_deliv = $type_deliv ? $type_deliv : '';
        $this->company = $company ? $company : '';
        $this->type_pay = $type_pay ? $type_pay : '';
        $this->customer = $customer ? $customer : '';
        $this->currency = $currency ? $currency : '';
        $this->delivery = $delivery ? $delivery : '';
        $this->sales = $sales ? $sales : '';
    }

    private $headings = [
        'No',
        'No. Document',
        'Status',
        'Tgl. Post',
        'PO Cust',
        'Customer',
        'Variant Item',
        'Item Code',
        'Item Name',
        'Tipe Penjualan',
        'Pengiriman',
        'Tipe Customer',
        'Transport',
        'Alamat Kirim',
        'Kabupaten',
        'Kecamatan',
        'Pembayaran',
        'TOP',
        'Qty',
        'Harga Satuan',
        'Discount 1',
        'Discount 2',
        'Discount 3',
        'DP',
        'Total',
        'PPN',
        'Grandtotal',
    ];

    public function collection()
    {
        $data = MarketingOrder::where(function($query) {
          
            
    
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
    
            if($this->type_sales){
                $query->where('type',$this->type_sales);
            }
    
            if($this->type_deliv){
                $query->where('shipping_type',$this->type_deliv);
            }
    
            if($this->customer){
                $groupIds = explode(',', $this->customer);
                $query->whereIn('account_id',$groupIds);
            }

            if($this->sales){
                $groupIds = explode(',', $this->sales);
                $query->whereIn('sales_id',$groupIds);
            }

            if($this->delivery){
                $groupIds = explode(',', $this->delivery);
                $query->whereIn('sender_id',$groupIds);
            }
            
            if($this->company){
                $query->where('company_id',$this->company);
            }
    
            if($this->type_pay){
                $query->where('payment_type',$this->type_pay);
            }                
            
            if($this->currency){
                $groupIds = explode(',', $this->currency);
                $query->whereIn('currency_id',$groupIds);
            }
    
           
        })->get();
        $arr = [];
        
        $x=1;
        foreach($data as $key => $row){
            $subtotal = $row->subtotal * $row->currency_rate;
            $discount = $row->discount * $row->currency_rate;
            $total = $subtotal - $discount;
            foreach($row->marketingOrderDetail as  $key_detail =>$row_detail){
                $arr[] = [
                    'no'                => $x,
                    'no_document'             => $row->code,
                    'status'            => $row->statusSAP(),
                    'tgl_post'          => date('d/m/Y',strtotime($row->post_date)),
                    'po_cust'             => $row->document_no,
                    'customer'         => $row->account->name,
                    'variant_item'      => $row_detail->item->type->name,
                    'item_code'              => $row_detail->item->code,
                    'item_name'              => $row_detail->item->name,
                    'tipe_penjualan'    => $row->type(),
                    'pengiriman'     => $row->deliveryType(),
                    'tipe_customer'     => $row->group->name ?? '-',
                    'transport'   => $row->transportation->name ?? '-',
                    'alamat_kirim'      => $row->destination_address,
                    'kabupaten'  => $row->city->name,
                    'kecamatan'  => $row->district->name,
                    'pembayaran'   => $row->paymentType(),
                    'top'               => $row->top_customer,
                    'qty'               => $row_detail->qty,
                    'harga_satuan'      => $row_detail->price,
                    'discount_1'        => $row_detail->percent_discount_1,
                    'discount_2'        => $row_detail->percent_discount_2,
                    'discount_3'        => $row_detail->discount_3,
                    'dp'                => $row->percent_dp,
                    'total'             => $row_detail->total,
                    'ppn'               => $row_detail->tax,
                    'grandtotal'        => $row_detail->grandtotal,
                    
                ];
                $x++;
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

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\MarketingOrder;

class ExportTransactionPageMarketingOrderDetail2 implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $search, $status,$type_sales, $type_pay,$type_deliv, $company, $customer, $delivery , $sales , $currency , $end_date , $start_date;

    public function __construct(string $search,string $status, string $type_sales,string $type_pay, string $type_deliv, string $company,string $customer, string $delivery, string $sales, string $currency,  string $end_date,string $start_date )
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
        'Variant Item',
        'Status',
        'No DO',
        'No. SO',
        'No. MOD',
        'No Invoice',

        'Customer Code',
        'Customer',
        'Cust Detail',
        'Alamat Tujuan',
        'Tipe Penjualan',
        'Tipe Pengiriman',
        'Kabupaten Tujuan',
        'Kecamatan Tujuan',

        'PPN',
        'Item',
        'Tanggal Kirim',
        'Quantity',
        'Unit',
        'Harga Satuan(Exclude PPN)',
        'Discount 1',
        'Discount 2',
        'Discount 3',
        'Disc Global',
        'DP Percentage',
        'Payment Type',

        'Ekspedisi Name',
        'Transport Name',
        'Plat No',
        'Sales Employee Name',
    ];

    public function collection()
    {
        $data = MarketingOrder::where(function($query) {
            // Apply the search conditions within the 'purchaseOrder' relationship
            $query->where(function($query){
                $query->where('code', 'like', "%$this->search%")
                ->orWhere('document_no', 'like', "%$this->search%")
                ->orWhere('note_internal', 'like', "%$this->search%")
                ->orWhere('note_external', 'like', "%$this->search%")
                ->orWhere('discount', 'like', "%$this->search%")
                ->orWhere('total', 'like', "%$this->search%")
                ->orWhere('tax', 'like', "%$this->search%")
                ->orWhere('grandtotal', 'like', "%$this->search%")
                ->orWhere('phone', 'like', "%$this->search%")
                ->orWhereHas('user',function($query){
                    $query->where('name','like',"%$this->search%")
                        ->orWhere('employee_no','like',"%$this->search%");
                })
                ->orWhereHas('marketingOrderDetail',function($query) {
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


        $x=1;
        foreach($data as $key => $row){
            $subtotal = $row->subtotal * $row->currency_rate;
            $discount = $row->discount * $row->currency_rate;
            $total = $subtotal - $discount;

            foreach($row->marketingOrderDetail as  $key_detail =>$row_detail){
                $arr[] = [
                    'variant_item'      => $row_detail->item->type->name,
                    'status'            => $row->statusSAP(),
                    'no_do'             => $row_detail->marketingOrderDeliveryDetail->first()->marketingOrderDelivery->marketingOrderDeliveryProcess->code ?? '-',
                    'no_so'             => $row->code,
                    'no_mod'            => $row_detail->listCodeMOD(),
                    'no_invoice'        => $row_detail->marketingOrderDeliveryDetail->first()->marketingOrderDelivery->marketingOrderDeliveryProcess->marketingOrderInvoice->code??'-',
                    'customer_code'     => $row->account->employee_no,
                    'customer'          => $row->account->name,
                    'customer_detail'   => isset($row->outlet->name) ? $row->outlet->name : '-',
                    'alamat_kirim'      => $row->destination_address,
                    'tipe_penjualan'   => $row->type(),
                    'tipe_pengiriman'   => $row->deliveryType(),
                    'kabupaten_tujuan'  => $row->city->name,
                    'kecamatan_tujuan'  => $row->district->name,


                    'ppn'               => $row_detail->percent_tax,
                    'item'              => $row_detail->item->code.'-'.$row_detail->item->name,

                    'delivery_date'     => date('d/m/Y',strtotime($row->delivery_date)),
                    'qty'               => $row_detail->qty,
                    'unit'              => $row_detail->itemUnit->unit->code,
                    'harga_satuan'      => $row_detail->price,
                    'discount_1'        => $row_detail->percent_discount_1,
                    'discount_2'        => $row_detail->percent_discount_2,
                    'discount_3'        => $row_detail->discount_3,
                    'disc_global'       => $row->discount,
                    'dp_percentage'     => $row->percent_dp,
                    'tipe_pembayaran'    => $row->paymentType(),
                    'ekspedisi_name'               => $row_detail->marketingOrderDeliveryDetail->first()->marketingOrderDelivery->marketingOrderDeliveryProcess->account->name ?? '-',
                    'transport_name'               => $row_detail->marketingOrderDeliveryDetail->first()->marketingOrderDelivery->marketingOrderDeliveryProcess->vehicle_name ?? '-',
                    'plat_no'                      => $row_detail->marketingOrderDeliveryDetail->first()->marketingOrderDelivery->marketingOrderDeliveryProcess->vehicle_no ?? '-',
                    // 'nama_supir'                   => $row_detail->marketingOrderDeliveryDetail->first()->marketingOrderDelivery->marketingOrderDeliveryProcess->driver_name ?? '-',
                    'sales_employee_name'          => $row->sales->name,
                    /* 'project_name'               => $row->project->name??'-',
                    'other_fee'             => $row_detail->other_fee,
                    'ongkir'        => $row_detail->price_delivery, */

                ];
                $x++;
            }



        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Marketing Order Detail 2';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

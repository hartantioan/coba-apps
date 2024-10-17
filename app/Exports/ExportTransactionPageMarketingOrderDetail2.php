<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDeliveryProcess;

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
        $data = MarketingOrderDeliveryProcess::where(function($query) {

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
                $query->whereHas('marketingOrderDeliveryProcessDetail', function ($subquery) {
                    $subquery->whereHas('marketingOrderDeliveryDetail', function($subquery2) {
                        $subquery2->whereHas('marketingOrderDetail', function($subquery3) {
                            $subquery3->whereHas('marketingOrder', function($subquery4) {
                                $subquery4->where('type',$this->type_sales);
                            });
                        });
                    });
                });

            }

            if($this->type_deliv){
                $query->whereHas('marketingOrderDeliveryProcessDetail', function ($subquery) {
                    $subquery->whereHas('marketingOrderDeliveryDetail', function($subquery2) {
                        $subquery2->whereHas('marketingOrderDetail', function($subquery3) {
                            $subquery3->whereHas('marketingOrder', function($subquery4) {
                                $subquery4->where('shipping_type',$this->type_deliv);
                            });
                        });
                    });
                });

            }

            if($this->customer){
                $groupIds = explode(',', $this->customer);
                $query->whereIn('account_id',$groupIds);
            }

            if($this->sales){
                $groupIds = explode(',', $this->sales);
                $query->whereHas('marketingOrderDeliveryProcessDetail', function ($subquery)use( $groupIds ) {
                    $subquery->whereHas('marketingOrderDeliveryDetail', function($subquery2)use( $groupIds ) {
                        $subquery2->whereHas('marketingOrderDetail', function($subquery3)use( $groupIds ) {
                            $subquery3->whereHas('marketingOrder', function($subquery4)use( $groupIds ) {
                                $subquery4->whereIn('sales_id',$groupIds);
                            });
                        });
                    });
                });

            }

            if($this->delivery){
                $groupIds = explode(',', $this->delivery);
                $query->whereHas('marketingOrderDeliveryProcessDetail', function ($subquery)use( $groupIds ) {
                    $subquery->whereHas('marketingOrderDeliveryDetail', function($subquery2)use( $groupIds ) {
                        $subquery2->whereHas('marketingOrderDetail', function($subquery3)use( $groupIds ) {
                            $subquery3->whereHas('marketingOrder', function($subquery4)use( $groupIds ) {
                                $subquery4->whereIn('sender_id',$groupIds);
                            });
                        });
                    });
                });
            }

            if($this->company){
                $query->where('company_id',$this->company);
            }

            if($this->type_pay){
                $query->whereHas('marketingOrderDeliveryProcessDetail', function ($subquery) {
                    $subquery->whereHas('marketingOrderDeliveryDetail', function($subquery2) {
                        $subquery2->whereHas('marketingOrderDetail', function($subquery3) {
                            $subquery3->whereHas('marketingOrder', function($subquery4) {
                                $subquery4->where('payment_type',$this->type_pay);
                            });
                        });
                    });
                });
            }

            if($this->currency){
                $groupIds = explode(',', $this->currency);
                $query->whereIn('currency_id',$groupIds);
            }


        })->get();


        $x=1;
        foreach($data as $key => $row){
            foreach($row->marketingOrderDeliveryProcessDetail as  $key_detail =>$row_process_detail){
                $row_detail = $row_process_detail->marketingOrderDeliveryDetail->marketingOrderDetail;
                if($row_detail->deleted_at == null){
                    $arr[] = [
                        'variant_item'      => $row_detail->item->type->name,
                        'status'            => $row->statusSAP(),
                        'no_do'             => $row->code ?? '-',
                        'no_so'             => $row_detail->marketingOrder->code,
                        'no_mod'            => $row_detail->listCodeMOD(),
                        'no_invoice'        => $row->marketingOrderInvoice->code??'-',
                        'customer_code'     => $row_detail->marketingOrder->account->employee_no,
                        'customer'          => $row_detail->marketingOrder->account->name,
                        'customer_detail'   => isset($row_detail->marketingOrder->outlet->name) ? $row_detail->marketingOrder->outlet->name : '-',
                        'alamat_kirim'      => $row_detail->marketingOrder->destination_address,
                        'tipe_penjualan'   => $row_detail->marketingOrder->type(),
                        'tipe_pengiriman'   => $row_detail->marketingOrder->deliveryType(),
                        'kabupaten_tujuan'  => $row_detail->marketingOrder->city->name,
                        'kecamatan_tujuan'  => $row_detail->marketingOrder->district->name,


                        'ppn'               => $row_detail->percent_tax,
                        'item'              => $row_detail->item->code.'-'.$row_detail->item->name,

                        'delivery_date'     => date('d/m/Y',strtotime($row_detail->marketingOrder->delivery_date)),
                        'qty'               => $row_detail->qty,
                        'unit'              => $row_detail->itemUnit->unit->code,
                        'harga_satuan'      => $row_detail->price,
                        'discount_1'        => $row_detail->percent_discount_1,
                        'discount_2'        => $row_detail->percent_discount_2,
                        'discount_3'        => $row_detail->discount_3,
                        'disc_global'       => $row_detail->marketingOrder->discount,
                        'dp_percentage'     => $row_detail->marketingOrder->percent_dp,
                        'tipe_pembayaran'    => $row_detail->marketingOrder->paymentType(),
                        'ekspedisi_name'               => $row->account->name ?? '-',
                        'transport_name'               => $row_detail->marketingOrderDeliveryDetail->first()->marketingOrderDetail->marketingOrder->transportation->name ?? '-',
                        'plat_no'                      => $row->vehicle_no ?? '-',
                        // 'nama_supir'                   => $row->driver_name ?? '-',
                        'sales_employee_name'          => $row_detail->marketingOrder->sales->name,
                        /* 'project_name'               => $row->project->name??'-',
                        'other_fee'             => $row_detail->other_fee,
                        'ongkir'        => $row_detail->price_delivery, */

                    ];
                    $x++;
                }

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

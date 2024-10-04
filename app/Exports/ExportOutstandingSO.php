<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\MarketingOrder;
use App\Models\MarketingOrderDetail;

class ExportOutstandingSO implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public function __construct()
    {

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
        'Qty (Palet)',
        'Qty (M2)',
    ];
    public function collection()
    {
        $data =  MarketingOrder::whereIn('status',['2'])->get();
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
                    'transport'   => $row->transportation->name,
                    'alamat_kirim'      => $row->destination_address,
                    'kabupaten'  => $row->city->name,
                    'kecamatan'  => $row->district->name,
                    'pembayaran'   => $row->paymentType(),
                    'top'               => $row->top_customer,
                    'qty'               => $row_detail->qty,
                    'qty_m2'               => $row_detail->qty_uom,

                ];
                $x++;
            }



        }

        return collect($arr);
    }
    public function title(): string
    {
        return 'Outstanding SO';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

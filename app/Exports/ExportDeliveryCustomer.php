<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\MarketingOrderDeliveryDetail;
use App\Helpers\CustomHelper;

class ExportDeliveryCustomer implements FromCollection, WithTitle, WithHeadings, WithCustomStartCell, ShouldAutoSize
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $start_date, $end_date, $customer;

    public function __construct(string $start_date, string $end_date, string $customer)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
        $this->customer = $customer ? $customer : '';
    }

    private $headings = [
        'No',
        'Dokumen SJ',
        'Tgl. Post',
        'Customer',
        'Item Code',
        'Item Name',
        'Brand',
        'Qty Delivery',
        'Satuan',
        'Qty',
        'Satuan',
        'Qty',
        'Satuan',
        'Shading',
        'Batch',
        'Tipe Pengiriman',
        'Truk',
        'Outlet',
        'Alamat Tujuan',
        'No Invoice',
        'MOD',
        'No Timbangan',
        'PO Customer',
        'SO',
        'Tipe SO',
        'No Faktur Pajak',
        'Harga Exclude',
        'Disc 1',
        'Disc 2',
        'Disc 3',
        'Subtotal (Exclude)',
        'Tax',
        'Grand Total (Include)'



    ];

    public function startCell(): string
    {
        return 'A1';
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return 'Delivery';
    }

    public function collection()
    {

        $totalAll = 0;
        $array_filter = [];
        $mo = MarketingOrderDeliveryDetail::whereHas('marketingOrderDelivery', function ($query) {
            $query->whereHas('marketingOrderDeliveryProcessAll', function ($query) {
                $query->whereHas('marketingOrderInvoice');
                $query->where('post_date', '>=', $this->start_date)->whereNull('void_date')->where('customer_id', $this->customer)
                    ->where('post_date', '<=', $this->end_date);
            });
        })->with(['marketingOrderDelivery.marketingOrderDeliveryProcessAll']) // Include the related process for sorting
            ->get()
            ->sortBy(function ($item) {
                return $item->marketingOrderDelivery->marketingOrderDeliveryProcessAll->code ?? '';
            })->values();


        $key = 0;
        foreach ($mo as $key => $row) {

 
                $array_filter[] = [
                    'no'                => ($key + 1),
                    'code'              => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->code,
                    'post_date'         => date('d/m/Y', strtotime($row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->post_date)),
                    'customer' => $row->marketingOrderDelivery->customer->name,
                    'itemcode' => $row->item->code,
                    'itemname' => $row->item->name,
                    'brand' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->getBrand(),
                    'qtysj' => $row->qty,
                    'satuan_konversi' => $row->marketingOrderDetail->itemUnit->unit->code,
                    'qty' => $row->qty * $row->getQtyM2(),
                    'satuan' => 'M2',
                    'qtybox' => $row->qty * $row->getQtyM2()/1.44,
                    'satuanbox' => 'BOX',
                    'shading' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->getShading(),
                    'batch' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->getBatch(),
                    'delivery_type' => $row->marketingOrderDelivery->deliveryType(),
                    'truk' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->vehicle_name,
                    'outlet' => $row->marketingOrderDetail->marketingOrder->outlet->name ?? '-',
                    'alamat_tujuan' => $row->marketingOrderDelivery->destination_address,
                    'list_invoice' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->marketingOrderInvoice->code ?? '',
                    'based_on' => $row->marketingOrderDelivery->code,
                    'no_timbangan' => $row->marketingOrderDelivery->goodScaleDetail->goodScale->code ?? '-',
                    'po_customer' => $row->marketingOrderDetail->marketingOrder->document_no,
                    'so' => $row->marketingOrderDetail->marketingOrder->code,
                    'so_type' => $row->marketingOrderDelivery->soType(),
                    'fp' => $row->marketingOrderDelivery->marketingOrderDeliveryProcessAll->marketingOrderInvoice->tax_no,
                    'pricebefdisc' => $row->marketingOrderDetail->priceBeforeDiscWTax(),
                    'disc1' => $row->marketingOrderDetail->percent_discount_1,
                    'disc2' => $row->marketingOrderDetail->percent_discount_2,
                    'disc3' => $row->marketingOrderDetail->discount_3,
                    'subtotal' => $row->totalInvoice(),
                    'tax' => $row->taxInvoice(),
                    'grandtotal' => $row->grandtotalInvoice(),

                ];
        
        }

        return collect($array_filter);
    }
}

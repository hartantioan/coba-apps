<?php

namespace App\Exports;

use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderInvoiceDetail;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportAccountingSales implements  FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date, string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No',
        'Status',
        'Dokumen',
        'Voider',
        'Tanggal Void',
        'Keterangan Void',
        'Deleter',
        'Tanggal Delete',
        'Keterangan Delete',
        'Doner',
        'Tanggal Done',
        'Keterangan Done',
        'NIK',
        'User',
        'Tanggal Post',
        'Due Date Internal',
        'No SO',
        'No MOD',
        'No SJ',
        'Tax No',
        'Customer Code',
        'Customer',
        'No NPWP',
        'Nama NPWP',
        'Tipe Penjualan',
        'Tipe Pengiriman',
        'Tipe Transport',
        'Nama Ekspedisi',
        'Nopol',
        'Supir',
        'Tipe Payment',
        'Deliver Address',
        'Alamat NPWP',
        'Item Code',
        'Item Name',
        'Qty',
        'Satuan',
        'Qty M2',
        'Harga (exc PPN)',
        'Discount 1',
        'Discount 2',
        'Discount 3',
        'Harga Setelah Diskon',
        'Total'
    ];





    public function collection()
    {


        $invoice_detail = MarketingOrderInvoiceDetail::where('deleted_at',null)
        ->whereHas('marketingOrderInvoice',function ($query)  {
            $query->whereIn('status',["2","3"])
            ->where('post_date', '>=',$this->start_date)
            ->where('post_date', '<=', $this->finish_date);
        })->get();


        $arr = [];

        foreach ($invoice_detail as $key => $row) {

            $arr[] = [
                'no'=> ($key+1),
                'status'              => $row->marketingOrderInvoice->statusRaw(),
                'code'              => $row->marketingOrderInvoice->code,
                'voider'            => $row->marketingOrderInvoice->voidUser()->exists() ? $row->marketingOrderInvoice->voidUser->name : '',
                'tgl_void'         => $row->marketingOrderInvoice->voidUser()->exists() ? $row->marketingOrderInvoice->void_date : '',
                'ket_void'         => $row->marketingOrderInvoice->voidUser()->exists() ? $row->marketingOrderInvoice->void_note : '',
                'deleter'           => $row->marketingOrderInvoice->deleteUser()->exists() ? $row->marketingOrderInvoice->deleteUser->name : '',
                'tgl_delete'       => $row->marketingOrderInvoice->deleteUser()->exists() ? $row->marketingOrderInvoice->deleted_at : '',
                'ket_delete'       => $row->marketingOrderInvoice->deleteUser()->exists() ? $row->marketingOrderInvoice->delete_note : '',
                'doner'             => ($row->marketingOrderInvoice->status == 3 && is_null($row->marketingOrderInvoice->done_id)) ? 'sistem' : (($row->marketingOrderInvoice->status == 3 && !is_null($row->marketingOrderInvoice->done_id)) ? $row->marketingOrderInvoice->doneUser->name : null),
                'tgl_done'         => $row->marketingOrderInvoice->doneUser()->exists() ? $row->marketingOrderInvoice->done_date : '',
                'ket_done'         => $row->marketingOrderInvoice->doneUser()->exists() ? $row->marketingOrderInvoice->done_note : '',
                'NIK'=>  $row->marketingOrderInvoice->user->employee_no,
                'User'=>   $row->marketingOrderInvoice->user->name,
                'post_date'         => date('d/m/Y', strtotime($row->marketingOrderInvoice->post_date)),
                'Due Date Internal' =>  $row->marketingOrderInvoice->due_date_internal,

                'No SO' =>  $row->marketingOrderInvoice->getlistSO(),
                'No MOD' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->code,
                'No SJ' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->code,
                'Tax No' =>  $row->marketingOrderInvoice->tax_no ?? '-',
                'Customer Code' =>  $row->marketingOrderInvoice->account->employee_no,
                'Customer' =>  $row->marketingOrderInvoice->account->name,
                'No NPWP' =>  $row->marketingOrderInvoice->getNpwp(),
                'Nama NPWP' =>  $row->marketingOrderInvoice->userData->user->name,
                'Tipe Penjualan' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->soType(),
                'Tipe Pengiriman' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->deliveryType(),
                'Transport' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->transportation->name,
                'Ekspedisi' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->vehicle_name,
                'Nopol' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->vehicle_no,
                'Supir' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->driver_name,
                'Payment' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->getTypePayment(),
                'Deliver Address' =>  $row->marketingOrderInvoice->marketingOrderDeliveryProcess->marketingOrderDelivery->destination_address,
                'Alamat NPWP' =>  $row->marketingOrderInvoice->userData->address,
                'itemcode' => $row->getItemCode(),
                'itemname' => $row->getItem(),
                'qty' => $row->qty,
                'satuan' => $row->getItemReal()->uomUnit->code ?? '-',
                'qtym2' => $row->getQtyM2(),
                'value' => $row->price,
                'Discount 1' => $row->getMoDetail()->percent_discount_1 ?? '-',
                'Discount 2' => $row->getMoDetail()->percent_discount_2 ?? '-',
                'Discount 3' => $row->getMoDetail()->discount_3 ?? '-',
                'Total' => $row->total,
                'Harga Setelah Diskon' => $row->grandtotal,

            ];
        }


        return collect($arr);
    }

    public function title(): string
    {
        return 'Report Sales';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

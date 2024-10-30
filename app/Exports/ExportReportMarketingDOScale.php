<?php

namespace App\Exports;

use App\Models\MarketingOrderDeliveryProcess;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportMarketingDOScale implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date,string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No',
        'Kode',
        'Status',
        'Voider',
        'Tgl Void',
        'Ket Void',
        'Deleter',
        'Tgl Delete',
        'Ket Delete',
        'Doner',
        'Tgl Done',
        'Ket Done',
        'NIK',
        'Petugas',
        'Tgl Posting',
        'Plant',
        'Customer',
        'Qty(M2)',
        'Satuan',
        'Gudang',
        'Area',
        'Tipe Pengiriman',
        'Ekspedisi',
        'Sopir',
        'No WA Supir',
        'Truk',
        'Nopol',
        'No Kontainer',
        'Outlet',
        'Alamat Tujuan',
        'Catatan Internal',
        'Catatan Eksternal',
        'Barang Dikirimkan',
        'Barang Diterima Customer',
        'SJ Kembali ',
        'No Invoice ',
        'No Timbangan ',
        'Timbangan ( KG ) ',
        'PO Customer',
        'SO',
    ];

    public function collection()
    {
        $mo = MarketingOrderDeliveryProcess::where('post_date', '>=', $this->start_date)
        ->where('post_date', '<=', $this->finish_date)->get();


        $arr = [];
        foreach ($mo as $key => $row) {
            $arr[] = [
                'no'                => ($key + 1),
                'code'              => $row->code,
                'status'            => $row->statusRaw(),
                'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                'tgl_void'         => $row->voidUser()->exists() ? $row->void_date : '',
                'ket_void'         => $row->voidUser()->exists() ? $row->void_note : '',
                'deleter'           => $row->deleteUser()->exists() ? $row->deleteUser->name : '',
                'tgl_delete'       => $row->deleteUser()->exists() ? $row->deleted_at : '',
                'ket_delete'       => $row->deleteUser()->exists() ? $row->delete_note : '',
                'doner'             => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                'tgl_done'         => $row->doneUser()->exists() ? $row->done_date : '',
                'ket_done'         => $row->doneUser()->exists() ? $row->done_note : '',
                'NIK'               => $row->user->employee_no,
                'petugas'           => $row->user->name,
                'tgl_posting'         => date('d/m/Y',strtotime($row->post_date)),
                'perusahaan'           => $row->getPlace(),
                'customer'              => $row->marketingOrderDelivery->customer->name,
                'qty(M2)'              => $row->totalQty(),
                'satuan'              => $row->getUnit(),
                'gudang'              => $row->getWarehouse(),
                'area'              => $row->getArea(),
                'type_send'              => $row->marketingOrderDelivery->deliveryType(),
                'ekspedisi'              => $row->account->name,
                'nama_supir'        => $row->driver_name,
                'no_wa_supir'            => $row->driver_hp,
                'tipe_kendaraan'    => $row->vehicle_name,
                'nopol_kendaraan'     => $row->vehicle_no,
                'no_kontainer'          => $row->no_container,
                'outlet'          => $row->getOutlet(),
                'alamat_tujuan'          => $row->marketingOrderDelivery->destination_address,
                'catatan_internal'            => $row->note_internal,
                'catatan_eksternal'      => $row->note_external,
                'barang dikirimkan'   =>  $row->post_date ? date('d/m/Y',strtotime($row->post_date)) : '-',
                'barang diterima'   =>  $row->receive_date ? date('d/m/Y',strtotime($row->receive_date)) : '-',
                'tgl_kembali_sj'   =>  $row->return_date ? date('d/m/Y',strtotime($row->return_date)) : '-',
                'no_invoice' => !empty($row->marketingOrderInvoice->code) ? $row->marketingOrderInvoice->code : '-',
                'no_timbangan' => $row->marketingOrderDelivery->goodScaleDetail->goodScale->code ?? '-',
                'berat_(kg)'      => $row->weight_netto,
                'po_customer'   =>  $row->getPoCustomer(),
                'so'            =>  $row->getSalesOrderCode(),


            ];
        }

        return collect($arr);


    }

    public function title(): string
    {
        return 'Report DO Scale';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

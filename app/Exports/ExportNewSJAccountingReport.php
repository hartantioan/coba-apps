<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportNewSJAccountingReport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $end_date;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
    }

    private $headings = [
        'No',
        'Dokumen',
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
        'User',
        'Tgl.Post',
        'Customer',
        'Item Code',
        'Item Name',
        'Brand',
        'Plant',
        'Qty Delivery',
        'Satuan',
        'Qty (M2)',
        'Satuan',
        'Gudang',
        'Area',
        'Shading',
        'Batch',
        'Tipe Pengiriman',
        'Expedisi',
        'Sopir',
        'No WA Sopir',
        'Truk',
        'Nopol',
        'No Kontainer',
        'Outlet',
        'Alamat Tujuan',
        'Catatan Internal',
        'Catatan Eksternal',
        'Barang dikirimkan',
        'Barang diterima customer',
        'SJ Kembali',
        'No Invoice',
        'Based On',
        'No Timbangan',
        'Po.Customer',
        'SO',
        'Tipe SO',
    ];

    public function collection()
    {
        $arr = [];

        $sql = "
            SELECT
                ROW_NUMBER() OVER() AS no,
                modp.code,
                modp.status AS status,
                CASE
                    WHEN vu.id IS NOT NULL THEN vu.name
                    ELSE ''
                END AS voider,
                CASE
                    WHEN vu.id IS NOT NULL THEN DATE_FORMAT(modp.void_date, '%d/%m/%Y')
                    ELSE ''
                END AS tgl_void,
                CASE
                    WHEN vu.id IS NOT NULL THEN modp.void_note
                    ELSE ''
                END AS ket_void,
                CASE
                    WHEN du.id IS NOT NULL THEN du.name
                    ELSE ''
                END AS deleter,
                CASE
                    WHEN du.id IS NOT NULL THEN DATE_FORMAT(modp.deleted_at, '%d/%m/%Y')
                    ELSE ''
                END AS tgl_delete,
                CASE
                    WHEN du.id IS NOT NULL THEN modp.delete_note
                    ELSE ''
                END AS ket_delete,
                CASE
                    WHEN modp.status = 3 AND modp.done_id IS NULL THEN 'sistem'
                    WHEN modp.status = 3 AND modp.done_id IS NOT NULL THEN du2.name
                    ELSE NULL
                END AS doner,
                CASE
                    WHEN du2.id IS NOT NULL THEN DATE_FORMAT(modp.done_date, '%d/%m/%Y')
                    ELSE ''
                END AS tgl_done,
                CASE
                    WHEN du2.id IS NOT NULL THEN modp.done_note
                    ELSE ''
                END AS ket_done,
                u.employee_no AS nik,
                u.name AS user,
                DATE_FORMAT(modp.post_date, '%d/%m/%Y') AS post_date,
                c.name AS customer,
                idt.`code` AS itemcode,
                idt.name AS itemname,
                bra.`name` AS brand,
                p.code AS plant,
                modd.qty AS qtysj,
                uu.unit_id AS satuan_konversi,
                (mdd.qty * modet.qty_conversion) AS qty,
                'M2' AS satuan,
                w.name AS gudang,
                a.name AS area,
                ishad.code AS shading,
                pb.code AS batch,
                md.type_delivery AS delivery_type,
                ac.name AS expedisi,
                modp.driver_name AS sopir,
                modp.driver_hp AS no_wa_supir,
                modp.vehicle_name AS truk,
                modp.vehicle_no AS nopol,
                modp.no_container AS no_kontainer,
                ot.`name` AS outlet,
                md.destination_address AS alamat_tujuan,
                md.note_internal AS catatan_internal,
                md.note_external AS catatan_eksternal,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM marketing_order_delivery_process_tracks modpt
                        WHERE modpt.marketing_order_delivery_process_id = modp.id
                        AND modpt.status = '2'
                    ) THEN DATE_FORMAT(modp.post_date, '%d/%m/%Y')
                    ELSE ''
                END AS status_item_sent,
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM marketing_order_delivery_process_tracks modpt
                        WHERE modpt.marketing_order_delivery_process_id = modp.id
                        AND modpt.status = '3'
                    ) THEN DATE_FORMAT(modp.receive_date, '%d/%m/%Y')
                    ELSE ''
                END AS status_received_by_customer,
                CASE
                    WHEN modp.return_date IS NOT NULL THEN DATE_FORMAT(modp.return_date, '%d/%m/%Y')
                    ELSE ''
                END AS status_returned_document,
                mi.code AS list_invoice,
                md.`code` AS based_on,
                gs.code AS no_timbangan,
                mo.document_no AS po_customer,
                mo.code AS so,
                CASE
                    WHEN md.so_type = '1' THEN 'Proyek'
                    WHEN md.so_type = '2' THEN 'Retail'
                    WHEN md.so_type = '3' THEN 'Khusus'
                    WHEN md.so_type = '4' THEN 'Sample'
                    ELSE 'Invalid'
                END AS so_type
            FROM
                marketing_order_delivery_process_details modd
            JOIN
                marketing_order_delivery_processes modp ON modp.id = modd.marketing_order_delivery_process_id
            JOIN
                marketing_order_delivery_details mdd ON mdd.id = modd.marketing_order_delivery_detail_id
            JOIN
                marketing_order_deliveries md ON md.id = mdd.marketing_order_delivery_id
            JOIN
                marketing_order_details modet ON modet.id = mdd.marketing_order_detail_id
            JOIN
                item_stocks isd ON isd.id = modd.item_stock_id
            JOIN
                item_shadings ishad ON ishad.id = isd.item_shading_id
            JOIN
                item_units uu ON uu.id = modet.item_unit_id
            JOIN
                items idt ON idt.id = mdd.item_id
            LEFT JOIN
                brands bra ON bra.id = idt.brand_id
            JOIN
                marketing_orders mo ON mo.id = modet.marketing_order_id
            LEFT JOIN
                outlets ot ON ot.id = mo.outlet_id
            JOIN
                users u ON u.id = modp.user_id
            LEFT JOIN
                users vu ON vu.id = modp.void_id
            LEFT JOIN
                users du ON du.id = modp.delete_id
            LEFT JOIN
                users du2 ON du2.id = modp.done_id
            LEFT JOIN
                users ac ON ac.id = modp.account_id
            LEFT JOIN
                users c ON c.id = md.customer_id
            LEFT JOIN
                places p ON p.id = mdd.place_id
            LEFT JOIN
                warehouses w ON w.id = isd.warehouse_id
            LEFT JOIN
                areas a ON a.id = isd.area_id
            LEFT JOIN
                item_shadings sd ON sd.id = isd.item_shading_id
            LEFT JOIN
                production_batches pb ON pb.id = isd.production_batch_id
            LEFT JOIN
                marketing_order_invoices mi ON mi.marketing_order_delivery_process_id = modp.id
            LEFT JOIN
                good_scale_details gsd ON gsd.lookable_id = md.id AND gsd.lookable_type = 'marketing_order_deliveries'
            LEFT JOIN
                good_scales gs ON gs.id = gsd.good_scale_id
            WHERE
                modp.post_date >= :start_date
                AND modp.post_date <= :end_date
        ";
        info($this->start_date);
        info($this->end_date);

        $results = DB::select($sql, [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ]);
        foreach ($results as $row) {

            $arr[] = [
                'no' => $row->no,
                'kode' => $row->code,
                'status' => $row->status,
                'voider' => $row->voider,
                'tgl_void' => $row->tgl_void,
                'ket_void' => $row->ket_void,
                'deleter' => $row->deleter,
                'tgl_delete' => $row->tgl_delete,
                'ket_delete' => $row->ket_delete,
                'doner' => $row->doner,
                'tgl_done' => $row->tgl_done,
                'ket_done' => $row->ket_done,
                'nik' => $row->nik,
                'user' => $row->user,
                'post_date' => $row->post_date,
                'customer' => $row->customer,
                'itemcode' => $row->itemcode,
                'itemname' => $row->itemname,
                'brand' => $row->brand,
                'plant' => $row->plant,
                'qtysj' => $row->qtysj,
                'satuan_konversi' => $row->satuan_konversi,
                'qty' => $row->qty,
                'satuan' => $row->satuan,
                'gudang' => $row->gudang,
                'area' => $row->area,
                'shading' => $row->shading,
                'batch' => $row->batch,
                'delivery_type' => $row->delivery_type,
                'expedisi' => $row->expedisi,
                'sopir' => $row->sopir,
                'no_wa_supir' => $row->no_wa_supir,
                'truk' => $row->truk,
                'nopol' => $row->nopol,
                'no_kontainer' => $row->no_kontainer,
                'outlet' => $row->outlet,
                'alamat_tujuan' => $row->alamat_tujuan,
                'catatan_internal' => $row->catatan_internal,
                'catatan_eksternal' => $row->catatan_eksternal,
                'status_item_sent' => $row->status_item_sent,
                'status_received_by_customer' => $row->status_received_by_customer,
                'status_returned_document' => $row->status_returned_document,
                'list_invoice' => $row->list_invoice,
                'based_on' => $row->based_on,
                'no_timbangan' => $row->no_timbangan,
                'po_customer' => $row->po_customer,
                'so' => $row->so,
                'so_type' => $row->so_type,
            ];
        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'SJ RECAP ACCOUNTING';
    }

    public function startCell(): string
    {
        return 'A1';
    }
	public function headings() : array
	{
		return $this->headings;
	}
}

<?php

namespace App\Exports;

use App\Models\MarketingOrderDelivery;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDetail;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\MarketingOrderDeliveryProcess;
use Illuminate\Support\Facades\DB;

class ExportMarketingDeliveryRecap implements FromView, WithEvents
{

    protected $start_date, $end_date;

    public function __construct(string $start_date, string $end_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->end_date = $end_date ? $end_date : '';
    }
    public function view(): View
    {
        $totalAll = 0;
        $array_filter = [];
        $mo = MarketingOrderDeliveryProcess::where('post_date', '>=', $this->start_date)->where('post_date', '<=', $this->end_date)->orderBy('code')->get();

        $query = "
            SELECT
                ROW_NUMBER() OVER (ORDER BY mo.code) AS no,
                mo.code,
                CASE
                    WHEN mo.status = '1' THEN 'Menunggu'
                    WHEN mo.status = '2' THEN 'Proses'
                    WHEN mo.status = '3' THEN 'Selesai'
                    WHEN mo.status = '4' THEN 'Ditolak'
                    WHEN mo.status = '5' THEN 'Ditutup'
                    WHEN mo.status = '6' THEN 'Direvisi'
                    ELSE 'Invalid'
                END AS status,
                CASE WHEN mo.void_id IS NOT NULL THEN u.name ELSE '' END AS voider,
                    CASE WHEN mo.void_id IS NOT NULL THEN DATE_FORMAT(mo.void_date, '%d/%m/%Y') ELSE '' END AS tgl_void,
                    CASE WHEN mo.void_id IS NOT NULL THEN mo.void_note ELSE '' END AS ket_void,
                    CASE WHEN mo.delete_id IS NOT NULL THEN du.name ELSE '' END AS deleter,
                    CASE WHEN mo.delete_id IS NOT NULL THEN DATE_FORMAT(mo.deleted_at, '%d/%m/%Y') ELSE '' END AS tgl_delete,
                    CASE WHEN mo.delete_id IS NOT NULL THEN mo.delete_note ELSE '' END AS ket_delete,
                    CASE
                        WHEN mo.status = 3 AND mo.done_id IS NULL THEN 'sistem'
                        WHEN mo.status = 3 AND mo.done_id IS NOT NULL THEN du.name
                        ELSE NULL
                    END AS doner,
                    CASE WHEN mo.done_id IS NOT NULL THEN DATE_FORMAT(mo.done_date, '%d/%m/%Y') ELSE '' END AS tgl_done,
                    CASE WHEN mo.done_id IS NOT NULL THEN mo.done_note ELSE '' END AS ket_done,
                    u.employee_no AS nik,
                    u.name AS user,
                    DATE_FORMAT(mo.post_date, '%d/%m/%Y') AS post_date,
                    c.name AS customer,
                    i.code AS itemcode,
                    i.name AS itemname,
                    IFNULL(p.name, '-') AS plant,
                    moddet.qty AS qtysj,
                    units.code AS satuan_konversi,
                    moddet.qty * mkt_od_det.qty_conversion AS qty,
                    'M2' AS satuan,
                        COALESCE(
                        (SELECT GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ')
                        FROM item_stocks i_stock
                        JOIN warehouses a ON i_stock.warehouse_id = a.id
                        WHERE modprodet.item_stock_id = i_stock.id),
                        '-'
                    ) AS gudang,
                    COALESCE(
                        (SELECT GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ')
                        FROM item_stocks i_stock
                        JOIN areas a ON i_stock.area_id = a.id
                        WHERE modprodet.item_stock_id = i_stock.id),
                        '-'
                    ) AS area,
                    COALESCE(
                        (SELECT GROUP_CONCAT(DISTINCT CONCAT(items.name, '-', i_sh.code) ORDER BY items.name, i_sh.code SEPARATOR ', ')
                        FROM item_stocks i_stock
                        JOIN item_shadings i_sh ON i_stock.item_shading_id = i_sh.id
                                JOIN items ON i_stock.item_id = items.id
                        WHERE modprodet.item_stock_id = i_stock.id),
                        '-'
                    ) AS shading,
                    COALESCE(
                        (SELECT GROUP_CONCAT(DISTINCT pb.code ORDER BY pb.code SEPARATOR ', ')
                        FROM item_stocks i_stock
                        JOIN production_batches pb ON i_stock.production_batch_id = pb.id
                        WHERE modprodet.item_stock_id = i_stock.id),
                        '-'
                    ) AS batch,
                    CASE
                        WHEN od.type_delivery = '1' THEN 'LOCO'
                        WHEN od.type_delivery = '2' THEN 'FRANCO'
                        ELSE 'Invalid'
                    END AS delivery_type,
                    moiv.code AS list_invoice,
                    acc.name AS expedisi,
                    mo.driver_name AS sopir,
                    mo.driver_hp AS no_wa_supir,
                    mo.vehicle_name AS truk,
                    mo.vehicle_no AS nopol,
                    mo.no_container AS no_kontainer,
                    IFNULL(od2.name, '-') AS outlet,
                    od.destination_address AS alamat_tujuan,
                    od.note_internal AS catatan_internal,
                    od.note_external AS catatan_eksternal,
                    COALESCE(
                            (SELECT CASE
                                                        WHEN tracking.status = '1' THEN 'Dokumen dibuat'
                                                        WHEN tracking.status = '2' THEN 'Barang dikirimkan'
                                                        WHEN tracking.status = '3' THEN 'Barang sampai di cust'
                                                        WHEN tracking.status = '5' THEN 'SJ kembali ke admin'
                                                        ELSE 'Invalid'
                                                END
                                FROM marketing_order_delivery_process_tracks tracking
                                WHERE tracking.marketing_order_delivery_process_id = mo.id
                                ORDER BY tracking.status DESC
                                LIMIT 1),
                                'Status tracking tidak ditemukan.'
                        ) AS tracking,
                    CASE
                        WHEN EXISTS (
                            SELECT 1
                            FROM marketing_order_delivery_process_tracks tracking
                            WHERE tracking.marketing_order_delivery_process_id = mo.id
                            AND tracking.status = '2'
                        ) THEN DATE_FORMAT(mo.post_date, '%d/%m/%Y')
                        ELSE ''
                    END AS status_item_sent,
                    CASE
                                WHEN EXISTS (
                                        SELECT 1
                                        FROM marketing_order_delivery_process_tracks tracking
                                        WHERE tracking.marketing_order_delivery_process_id = mo.id
                                        AND tracking.status = '3'
                                ) THEN DATE_FORMAT(mo.receive_date, '%d/%m/%Y')
                                ELSE ''
                        END AS status_received_by_customer,
                    CASE WHEN mo.return_date IS NOT NULL THEN DATE_FORMAT(mo.return_date, '%d/%m/%Y') ELSE '' END AS status_returned_document,
                    od.code AS based_on,
                    mkt_od.code AS so,
                    IFNULL(gsh.code, '-') AS no_timbangan,
                    mkt_od.document_no AS po_customer,
                    COALESCE(
                                (SELECT GROUP_CONCAT(DISTINCT b.name ORDER BY b.name SEPARATOR ', ')
                                FROM item_stocks i_stock
                                JOIN items i ON i_stock.item_id = i.id
                                JOIN brands b ON i.brand_id = b.id
                                WHERE modprodet.item_stock_id = i_stock.id),
                                '-'
                        ) AS brand,
                    CASE
                                WHEN od.so_type = '1' THEN 'Proyek'
                                WHEN od.so_type = '2' THEN 'Retail'
                                WHEN od.so_type = '3' THEN 'Khusus'
                                WHEN od.so_type = '4' THEN 'Sample'
                                ELSE 'Invalid'
                        END AS so_type
                FROM
                    marketing_order_delivery_processes mo
                JOIN marketing_order_delivery_process_details modprodet ON mo.id = modprodet.marketing_order_delivery_process_id
                JOIN marketing_order_deliveries od ON mo.marketing_order_delivery_id = od.id
                JOIN marketing_order_delivery_details moddet ON od.id = moddet.marketing_order_delivery_id
                LEFT JOIN marketing_order_details mkt_od_det ON mkt_od_det.id = moddet.marketing_order_detail_id
                LEFT JOIN marketing_orders mkt_od ON mkt_od_det.marketing_order_id = mkt_od.id
                JOIN users u ON mo.user_id = u.id
                LEFT JOIN users du ON mo.delete_id = du.id
                LEFT JOIN users du2 ON mo.void_id = du2.id
                LEFT JOIN users c ON od.customer_id = c.id
                LEFT JOIN items i ON moddet.item_id = i.id
                LEFT JOIN places p ON moddet.place_id = p.id
                LEFT JOIN item_units iu ON mkt_od_det.item_unit_id = iu.id
                LEFT JOIN units ON iu.unit_id = units.id
                LEFT JOIN marketing_order_invoices moiv ON mo.id = moiv.marketing_order_delivery_process_id
                LEFT JOIN users acc ON mo.account_id = acc.id
                LEFT JOIN good_scale_details gs
                    ON od.id = gs.lookable_id
                    AND gs.lookable_type = 'marketing_order_deliveries'
                LEFT JOIN good_scales gsh ON gsh.id = gs.good_scale_id
                LEFT JOIN outlets od2 ON mkt_od.outlet_id = od2.id
                WHERE
                    mo.post_date BETWEEN :start_date AND :end_date
                ORDER BY
                    mo.code;
        ";

        $parameters = [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ];
        $result = DB::select($query, $parameters);
        $resultArray = json_decode(json_encode($result), true);
        activity()
            ->performedOn(new marketingOrderDeliveryProcess())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Delivery Recap.');

        return view('admin.exports.marketing_delivery_recap', [
            'data'      => $resultArray,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Auto-fit columns A to Z
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setWrapText(true);
                $event->sheet->getDelegate()->getStyle('A:Z')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $event->sheet->autoSize();
                $event->sheet->freezePane("A1");
            }
        ];
    }

    // public function array(): array
    // {
    //     $query = "
    //         SELECT
    //             ROW_NUMBER() OVER (ORDER BY mo.code) AS no,
    //             mo.code,
    //             CASE
    //                 WHEN mo.status = '1' THEN 'Menunggu'
    //                 WHEN mo.status = '2' THEN 'Proses'
    //                 WHEN mo.status = '3' THEN 'Selesai'
    //                 WHEN mo.status = '4' THEN 'Ditolak'
    //                 WHEN mo.status = '5' THEN 'Ditutup'
    //                 WHEN mo.status = '6' THEN 'Direvisi'
    //                 ELSE 'Invalid'
    //             END AS status,
    //             CASE WHEN mo.void_id IS NOT NULL THEN u.name ELSE '' END AS voider,
    //                 CASE WHEN mo.void_id IS NOT NULL THEN DATE_FORMAT(mo.void_date, '%d/%m/%Y') ELSE '' END AS tgl_void,
    //                 CASE WHEN mo.void_id IS NOT NULL THEN mo.void_note ELSE '' END AS ket_void,
    //                 CASE WHEN mo.delete_id IS NOT NULL THEN du.name ELSE '' END AS deleter,
    //                 CASE WHEN mo.delete_id IS NOT NULL THEN DATE_FORMAT(mo.deleted_at, '%d/%m/%Y') ELSE '' END AS tgl_delete,
    //                 CASE WHEN mo.delete_id IS NOT NULL THEN mo.delete_note ELSE '' END AS ket_delete,
    //                 CASE
    //                     WHEN mo.status = 3 AND mo.done_id IS NULL THEN 'sistem'
    //                     WHEN mo.status = 3 AND mo.done_id IS NOT NULL THEN du.name
    //                     ELSE NULL
    //                 END AS doner,
    //                 CASE WHEN mo.done_id IS NOT NULL THEN DATE_FORMAT(mo.done_date, '%d/%m/%Y') ELSE '' END AS tgl_done,
    //                 CASE WHEN mo.done_id IS NOT NULL THEN mo.done_note ELSE '' END AS ket_done,
    //                 u.employee_no AS nik,
    //                 u.name AS user,
    //                 DATE_FORMAT(mo.post_date, '%d/%m/%Y') AS post_date,
    //                 c.name AS customer,
    //                 i.code AS itemcode,
    //                 i.name AS itemname,
    //                 IFNULL(p.name, '-') AS plant,
    //                 moddet.qty AS qtysj,
    //                 units.code AS satuan_konversi,
    //                 moddet.qty * mkt_od_det.qty_conversion AS qty,
    //                 'M2' AS satuan,
    //                     COALESCE(
    //                     (SELECT GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ')
    //                     FROM item_stocks i_stock
    //                     JOIN warehouses a ON i_stock.warehouse_id = a.id
    //                     WHERE modprodet.item_stock_id = i_stock.id),
    //                     '-'
    //                 ) AS gudang,
    //                 COALESCE(
    //                     (SELECT GROUP_CONCAT(DISTINCT a.name ORDER BY a.name SEPARATOR ', ')
    //                     FROM item_stocks i_stock
    //                     JOIN areas a ON i_stock.area_id = a.id
    //                     WHERE modprodet.item_stock_id = i_stock.id),
    //                     '-'
    //                 ) AS area,
    //                 COALESCE(
    //                     (SELECT GROUP_CONCAT(DISTINCT CONCAT(items.name, '-', i_sh.code) ORDER BY items.name, i_sh.code SEPARATOR ', ')
    //                     FROM item_stocks i_stock
    //                     JOIN item_shadings i_sh ON i_stock.item_shading_id = i_sh.id
    //                             JOIN items ON i_stock.item_id = items.id
    //                     WHERE modprodet.item_stock_id = i_stock.id),
    //                     '-'
    //                 ) AS shading,
    //                 COALESCE(
    //                     (SELECT GROUP_CONCAT(DISTINCT pb.code ORDER BY pb.code SEPARATOR ', ')
    //                     FROM item_stocks i_stock
    //                     JOIN production_batches pb ON i_stock.production_batch_id = pb.id
    //                     WHERE modprodet.item_stock_id = i_stock.id),
    //                     '-'
    //                 ) AS batch,
    //                 CASE
    //                     WHEN od.type_delivery = '1' THEN 'LOCO'
    //                     WHEN od.type_delivery = '2' THEN 'FRANCO'
    //                     ELSE 'Invalid'
    //                 END AS delivery_type,
    //                 moiv.code AS list_invoice,
    //                 acc.name AS expedisi,
    //                 mo.driver_name AS sopir,
    //                 mo.driver_hp AS no_wa_supir,
    //                 mo.vehicle_name AS truk,
    //                 mo.vehicle_no AS nopol,
    //                 mo.no_container AS no_kontainer,
    //                 IFNULL(od2.name, '-') AS outlet,
    //                 od.destination_address AS alamat_tujuan,
    //                 od.note_internal AS catatan_internal,
    //                 od.note_external AS catatan_eksternal,
    //                 COALESCE(
    //                         (SELECT CASE
    //                                                     WHEN tracking.status = '1' THEN 'Dokumen dibuat'
    //                                                     WHEN tracking.status = '2' THEN 'Barang dikirimkan'
    //                                                     WHEN tracking.status = '3' THEN 'Barang sampai di cust'
    //                                                     WHEN tracking.status = '5' THEN 'SJ kembali ke admin'
    //                                                     ELSE 'Invalid'
    //                                             END
    //                             FROM marketing_order_delivery_process_tracks tracking
    //                             WHERE tracking.marketing_order_delivery_process_id = mo.id
    //                             ORDER BY tracking.status DESC
    //                             LIMIT 1),
    //                             'Status tracking tidak ditemukan.'
    //                     ) AS tracking,
    //                 CASE
    //                     WHEN EXISTS (
    //                         SELECT 1
    //                         FROM marketing_order_delivery_process_tracks tracking
    //                         WHERE tracking.marketing_order_delivery_process_id = mo.id
    //                         AND tracking.status = '2'
    //                     ) THEN DATE_FORMAT(mo.post_date, '%d/%m/%Y')
    //                     ELSE ''
    //                 END AS status_item_sent,
    //                 CASE
    //                             WHEN EXISTS (
    //                                     SELECT 1
    //                                     FROM marketing_order_delivery_process_tracks tracking
    //                                     WHERE tracking.marketing_order_delivery_process_id = mo.id
    //                                     AND tracking.status = '3'
    //                             ) THEN DATE_FORMAT(mo.receive_date, '%d/%m/%Y')
    //                             ELSE ''
    //                     END AS status_received_by_customer,
    //                 CASE WHEN mo.return_date IS NOT NULL THEN DATE_FORMAT(mo.return_date, '%d/%m/%Y') ELSE '' END AS status_returned_document,
    //                 od.code AS based_on,
    //                 mkt_od.code AS so,
    //                 IFNULL(gsh.code, '-') AS no_timbangan,
    //                 mkt_od.document_no AS po_customer,
    //                 COALESCE(
    //                             (SELECT GROUP_CONCAT(DISTINCT b.name ORDER BY b.name SEPARATOR ', ')
    //                             FROM item_stocks i_stock
    //                             JOIN items i ON i_stock.item_id = i.id
    //                             JOIN brands b ON i.brand_id = b.id
    //                             WHERE modprodet.item_stock_id = i_stock.id),
    //                             '-'
    //                     ) AS brand,
    //                 CASE
    //                             WHEN od.so_type = '1' THEN 'Proyek'
    //                             WHEN od.so_type = '2' THEN 'Retail'
    //                             WHEN od.so_type = '3' THEN 'Khusus'
    //                             WHEN od.so_type = '4' THEN 'Sample'
    //                             ELSE 'Invalid'
    //                     END AS so_type
    //             FROM
    //                 marketing_order_delivery_processes mo
    //             JOIN marketing_order_delivery_process_details modprodet ON mo.id = modprodet.marketing_order_delivery_process_id
    //             JOIN marketing_order_deliveries od ON mo.marketing_order_delivery_id = od.id
    //             JOIN marketing_order_delivery_details moddet ON od.id = moddet.marketing_order_delivery_id
    //             LEFT JOIN marketing_order_details mkt_od_det ON mkt_od_det.id = moddet.marketing_order_detail_id
    //             LEFT JOIN marketing_orders mkt_od ON mkt_od_det.marketing_order_id = mkt_od.id
    //             JOIN users u ON mo.user_id = u.id
    //             LEFT JOIN users du ON mo.delete_id = du.id
    //             LEFT JOIN users du2 ON mo.void_id = du2.id
    //             LEFT JOIN users c ON od.customer_id = c.id
    //             LEFT JOIN items i ON moddet.item_id = i.id
    //             LEFT JOIN places p ON moddet.place_id = p.id
    //             LEFT JOIN item_units iu ON mkt_od_det.item_unit_id = iu.id
    //             LEFT JOIN units ON iu.unit_id = units.id
    //             LEFT JOIN marketing_order_invoices moiv ON mo.id = moiv.marketing_order_delivery_process_id
    //             LEFT JOIN users acc ON mo.account_id = acc.id
    //             LEFT JOIN good_scale_details gs
    //                 ON od.id = gs.lookable_id
    //                 AND gs.lookable_type = 'marketing_order_deliveries'
    //             LEFT JOIN good_scales gsh ON gsh.id = gs.good_scale_id
    //             LEFT JOIN outlets od2 ON mkt_od.outlet_id = od2.id
    //             WHERE
    //                 mo.post_date BETWEEN :start_date AND :end_date
    //             ORDER BY
    //                 mo.code;
    //     ";
    //     $parameters = [
    //         'start_date' => $this->start_date,
    //         'end_date' => $this->end_date,
    //     ];
    //     $result = DB::select($query, $parameters);
    //     $resultArray = json_decode(json_encode($result), true);
    //     activity()
    //         ->performedOn(new marketingOrderDeliveryProcess())
    //         ->causedBy(session('bo_id'))
    //         ->withProperties(null)
    //         ->log('Export Delivery Recap.');

    //     return $resultArray;
    // }

    // public function title(): string
    // {
    //     return 'Rekapitulasi Delivery - SJ';
    // }

    // public function headings() : array
	// {
	// 	return $this->headings;
    // }

    // private $headings = [
    //     'No',
    //     'Dokumen',
    //     'Status',
    //     'Voider',
    //     'Tgl Void',
    //     'Ket Void',
    //     'Deleter',
    //     'Tgl Delete',
    //     'Ket Delete',
    //     'Doner',
    //     'Tgl Done',
    //     'Ket Done',
    //     'NIK',
    //     'User',
    //     'Tgl.Post',
    //     'Customer',
    //     'Item Code',
    //     'Item Name',
    //     'Brand',
    //     'Plant',
    //     'Qty Delivery',
    //     'Satuan',
    //     'Qty (M2)',
    //     'Satuan',
    //     'Gudang',
    //     'Area',
    //     'Shading',
    //     'Batch',
    //     'Tipe Pengiriman',
    //     'Expedisi',
    //     'Sopir',
    //     'No WA Sopir',
    //     'Truk',
    //     'Nopol',
    //     'No Kontainer',
    //     'Outlet',
    //     'Alamat Tujuan',
    //     'Catatan Internal',
    //     'Catatan Eksternal',
    //     'Barang dikirimkan',
    //     'Barang diterima customer',
    //     'SJ Kembali',
    //     'No Invoice',
    //     'Based On',
    //     'No Timbangan',
    //     'Po.Customer',
    //     'SO',
    //     'Tipe SO'
    // ];
}

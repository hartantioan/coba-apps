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
        $key = 0;
        foreach ($mo as $key => $row) {
            foreach($row->marketingOrderDelivery->marketingOrderDeliveryDetail as $rowdetail){
                $array_filter[] = [
                    'no'                => ($key+1),
                    'code'              => $row->code,
                    'status'            => $row->statusRaw(),
                    'voider'            => $row->voidUser()->exists() ? $row->voidUser->name : '',
                    'tgl_void'         => $row->voidUser()->exists() ? date('d/m/Y',strtotime($row->void_date)) : '' ,
                    'ket_void'               => $row->voidUser()->exists() ? $row->void_note : '' ,
                    'deleter'              =>$row->deleteUser()->exists() ? $row->deleteUser->name : '',
                    'tgl_delete'             => $row->deleteUser()->exists() ? date('d/m/Y',strtotime($row->deleted_at)) : '',
                    'ket_delete'               => $row->deleteUser()->exists() ? $row->delete_note : '',
                    'doner'        => ($row->status == 3 && is_null($row->done_id)) ? 'sistem' : (($row->status == 3 && !is_null($row->done_id)) ? $row->doneUser->name : null),
                    'tgl_done'          => $row->doneUser ? $row->done_date : '',
                    'ket_done'              => $row->doneUser ? $row->done_note : '' ,

                    'nik' =>$row->user->employee_no,
                    'user' =>$row->user->name,

                    'post_date'         => date('d/m/Y', strtotime($row->post_date)),
                    'customer' =>$rowdetail->marketingOrderDelivery->customer->name,
                    'itemcode' => $rowdetail->item->code,
                    'itemname' => $rowdetail->item->name,

                    'plant' => $rowdetail->place->name??'-',
                    'qtysj' => $rowdetail->qty,
                    // 'qty_konversi' => $row->getQtyM2(),
                    'satuan_konversi' => $rowdetail->marketingOrderDetail->itemUnit->unit->code,
                    'qty' => $rowdetail->qty * $rowdetail->getQtyM2(),
                    'satuan' => 'M2',
                    'gudang' => $row->getWarehouse(),
                    'area' => $row->getArea(),
                    'shading' => $row->getShading(),
                    'batch' => $row->getBatch(),
                    'delivery_type' => $rowdetail->marketingOrderDelivery->deliveryType(),
                    'list_invoice' =>$row->marketingOrderInvoice->code ?? '',
                    'expedisi' =>$row->account->name,
                    'sopir'                => $row->driver_name,
                    'no_wa_supir'                => $row->driver_hp,
                    'truk'=>$row->vehicle_name,
                    'nopol' => $row->vehicle_no,
                    'no_kontainer'          => $row->no_container,
                    'outlet' => $rowdetail->marketingOrderDetail->marketingOrder->outlet->name ?? '-',
                    'alamat_tujuan'=> $rowdetail->marketingOrderDelivery->destination_address,
                    'catatan_internal'=>$rowdetail->marketingOrderDelivery->note_internal,
                    'catatan_eksternal'=>$rowdetail->marketingOrderDelivery->note_external,
                    'tracking'=>$row->statusTrackingRaw(),
                    'status_item_sent'=>$row->isItemSent() ? date('d/m/Y', strtotime($row->post_date)) : '',
                    'status_received_by_customer'=>$row->isDelivered() ? date('d/m/Y', strtotime($row->receive_date)) : '',
                    'status_returned_document' =>$row->return_date
                        ? date('d/m/Y', strtotime($row->return_date))
                        : '',
                    'based_on'=>$rowdetail->marketingOrderDelivery->code,
                    'so' => $rowdetail->marketingOrderDetail->marketingOrder->code,
                    'no_timbangan'=> $rowdetail->marketingOrderDelivery->goodScaleDetail->goodScale->code ?? '-',
                    'po_customer' => $rowdetail->marketingOrderDetail->marketingOrder->document_no,
                    'brand' => $row->getBrand(),
                    'so_type' => $rowdetail->marketingOrderDelivery->soType(),
                ];
            }
        }
        // $query = "
        //     SELECT
        //         ROW_NUMBER() OVER() AS no,
        //         modp.code,
        //             CASE
        //                     WHEN modp.status = '1' THEN 'Menunggu'
        //                     WHEN modp.status = '2' THEN 'Proses'
        //                     WHEN modp.status = '3' THEN 'Selesai'
        //                     WHEN modp.status = '4' THEN 'Ditolak'
        //                     WHEN modp.status = '5' THEN 'Ditutup'
        //                     WHEN modp.status = '6' THEN 'Direvisi'
        //                     ELSE 'Invalid'
        //             END AS status,
        //         CASE
        //             WHEN vu.id IS NOT NULL THEN vu.name
        //             ELSE ''
        //         END AS voider,
        //         CASE
        //             WHEN vu.id IS NOT NULL THEN DATE_FORMAT(modp.void_date, '%d/%m/%Y')
        //             ELSE ''
        //         END AS tgl_void,
        //         CASE
        //             WHEN vu.id IS NOT NULL THEN modp.void_note
        //             ELSE ''
        //         END AS ket_void,
        //         CASE
        //             WHEN du.id IS NOT NULL THEN du.name
        //             ELSE ''
        //         END AS deleter,
        //         CASE
        //             WHEN du.id IS NOT NULL THEN DATE_FORMAT(modp.deleted_at, '%d/%m/%Y')
        //             ELSE ''
        //         END AS tgl_delete,
        //         CASE
        //             WHEN du.id IS NOT NULL THEN modp.delete_note
        //             ELSE ''
        //         END AS ket_delete,
        //         CASE
        //             WHEN modp.status = 3 AND modp.done_id IS NULL THEN 'sistem'
        //             WHEN modp.status = 3 AND modp.done_id IS NOT NULL THEN du2.name
        //             ELSE NULL
        //         END AS doner,
        //         CASE
        //             WHEN du2.id IS NOT NULL THEN DATE_FORMAT(modp.done_date, '%d/%m/%Y')
        //             ELSE ''
        //         END AS tgl_done,
        //         CASE
        //             WHEN du2.id IS NOT NULL THEN modp.done_note
        //             ELSE ''
        //         END AS ket_done,
        //         u.employee_no AS nik,
        //         u.name AS user,
        //         DATE_FORMAT(modp.post_date, '%d/%m/%Y') AS post_date,
        //         c.name AS customer,
        //         idt.`code` AS itemcode,
        //         idt.name AS itemname,
        //         bra.`name` AS brand,
        //         p.code AS plant,
        //         modd.qty AS qtysj,
        //         uu.unit_id AS satuan_konversi,
        //         (mdd.qty * modet.qty_conversion) AS qty,
        //         'M2' AS satuan,
        //         w.name AS gudang,
        //         a.name AS area,
        //         ishad.code AS shading,
        //         pb.code AS batch,
        //         md.type_delivery AS delivery_type,
        //         ac.name AS expedisi,
        //         modp.driver_name AS sopir,
        //         modp.driver_hp AS no_wa_supir,
        //         modp.vehicle_name AS truk,
        //         modp.vehicle_no AS nopol,
        //         modp.no_container AS no_kontainer,
        //         ot.`name` AS outlet,
        //         md.destination_address AS alamat_tujuan,
        //         md.note_internal AS catatan_internal,
        //         md.note_external AS catatan_eksternal,
        //         CASE
        //             WHEN EXISTS (
        //                 SELECT 1
        //                 FROM marketing_order_delivery_process_tracks modpt
        //                 WHERE modpt.marketing_order_delivery_process_id = modp.id
        //                 AND modpt.status = '2'
        //             ) THEN DATE_FORMAT(modp.post_date, '%d/%m/%Y')
        //             ELSE ''
        //         END AS status_item_sent,
        //             CASE
        //             WHEN EXISTS (
        //                 SELECT 1
        //                 FROM marketing_order_delivery_process_tracks modpt
        //                 WHERE modpt.marketing_order_delivery_process_id = modp.id
        //                 AND modpt.status = '3'
        //             ) THEN DATE_FORMAT(modp.receive_date, '%d/%m/%Y')
        //             ELSE ''
        //         END AS status_received_by_customer,
        //         CASE
        //             WHEN modp.return_date IS NOT NULL THEN DATE_FORMAT(modp.return_date, '%d/%m/%Y')
        //             ELSE ''
        //         END AS status_returned_document,
        //         mi.code AS list_invoice,
        //         md.`code` AS based_on,
        //         gs.code AS no_timbangan,
        //         mo.document_no AS po_customer,
        //         mo.code AS so,
        //         CASE
        //                     WHEN md.so_type = '1' THEN 'Proyek'
        //                     WHEN md.so_type = '2' THEN 'Retail'
        //                     WHEN md.so_type = '3' THEN 'Khusus'
        //                     WHEN md.so_type = '4' THEN 'Sample'
        //                     ELSE 'Invalid'
        //             END AS so_type

        //     FROM
        //         marketing_order_delivery_process_details modd
        //     JOIN
        //         marketing_order_delivery_processes modp ON modp.id = modd.marketing_order_delivery_process_id
        //     JOIN
        //         marketing_order_delivery_details mdd ON mdd.id = modd.marketing_order_delivery_detail_id
        //     JOIN
        //         marketing_order_deliveries md ON md.id = mdd.marketing_order_delivery_id
        //     JOIN
        //         marketing_order_details modet ON modet.id = mdd.marketing_order_detail_id
        //     JOIN
        //         item_stocks isd ON isd.id = modd.item_stock_id
        //     JOIN
        //         item_shadings ishad ON ishad.id = isd.item_shading_id
        //     JOIN
        //         item_units uu ON uu.id = modet.item_unit_id
        //     JOIN
        //         items idt ON idt.id = mdd.item_id
        //     LEFT JOIN
        //         brands bra ON bra.id = idt.brand_id
        //     JOIN
        //         marketing_orders mo ON mo.id = modet.marketing_order_id
        //     LEFT JOIN
        //         outlets ot ON ot.id = mo.outlet_id
        //     JOIN
        //         users u ON u.id = modp.user_id
        //     LEFT JOIN
        //         users vu ON vu.id = modp.void_id
        //     LEFT JOIN
        //         users du ON du.id = modp.delete_id
        //     LEFT JOIN
        //         users du2 ON du2.id = modp.done_id
        //     LEFT JOIN
        //         users ac ON ac.id = modp.account_id
        //     LEFT JOIN
        //         users c ON c.id = md.customer_id
        //     LEFT JOIN
        //         places p ON p.id = mdd.place_id
        //     LEFT JOIN
        //         warehouses w ON w.id = isd.warehouse_id
        //     LEFT JOIN
        //         areas a ON a.id = isd.area_id
        //     LEFT JOIN
        //         item_shadings sd ON sd.id = isd.item_shading_id
        //     LEFT JOIN
        //         production_batches pb ON pb.id = isd.production_batch_id
        //     LEFT JOIN
        //         marketing_order_invoices mi ON mi.marketing_order_delivery_process_id = modp.id
        //     LEFT JOIN
        //         good_scale_details gsd ON gsd.lookable_id = md.id AND gsd.lookable_type = 'marketing_order_deliveries'
        //     LEFT JOIN
        //         good_scales gs ON gs.id = gsd.good_scale_id
        //     WHERE
        //         modp.post_date BETWEEN :start_date AND :end_date
        // ";
        // $parameters = [
        //     'start_date' => $this->start_date,
        //     'end_date' => $this->end_date,
        // ];
        // $result = DB::select($query, $parameters);
        // $resultArray = json_decode(json_encode($result), true);
        activity()
            ->performedOn(new marketingOrderDeliveryProcess())
            ->causedBy(session('bo_id'))
            ->withProperties(null)
            ->log('Export Delivery Recap.');

        return view('admin.exports.marketing_delivery_recap', [
            'data'      => $array_filter,
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

<?php

namespace App\Exports;

use App\Models\DeliveryReceiveDetail;
use App\Models\InventoryIssue;
use App\Models\InventoryIssueDetail;
use App\Models\InvoiceDetail;
use App\Models\ItemPartition;
use App\Models\ItemPartitionDetail;
use App\Models\ItemStockNew;
use App\Models\SalesOrder;
use App\Models\SalesOrderDetail;
use App\Models\StoreItemMove;
use App\Models\StoreItemStock;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportStockValue implements FromArray, WithTitle, ShouldAutoSize
{
    protected $start_date;
    protected $finish_date;
    public function __construct(string $start_date, string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }
    private $headings = [
        'No',
        'Nama Barang',
        'Unit',
        'Saldo Awal',
        'Unit Pembelian',
        'Value Pembelian',
        'Unit Pemakaian',
        'Value Pemakaian',
        'Saldo Akhir Unit',
        'Saldo Akhir Value',
    ];



    public function array(): array
    {

        $arr=[];
        $item_Stock_new = ItemStockNew::all();$keys=1;
        $arr[]=[
            'No',
            'Nama Barang',
            'Unit',
            'Saldo Awal',
            'Unit Pembelian',
            'Value Pembelian',
            'Unit Pemakaian',
            'Value Pemakaian',
            'Saldo Akhir Unit',
            'Saldo Akhir Value',
        ];

        foreach($item_Stock_new as $row_stock){
            $start_penambahan_partisi = ItemPartitionDetail::where('to_item_stock_new_id', $row_stock->id)
                ->whereHas('itemPartition', function ($query) {
                    $query->where('post_date', '<', $this->start_date);
                })->get();

            $start_penambahan_supplier = DeliveryReceiveDetail::where('item_id', $row_stock->item_id)
                ->whereHas('deliveryReceive', function ($query) {
                    $query->where('post_date', '<', $this->start_date);
                })->get();

            // Pengurangan sebelum periode
            $start_pengurangan_partisi = ItemPartitionDetail::where('item_stock_new_id', $row_stock->id)
                ->whereHas('itemPartition', function ($query) {
                    $query->where('post_date', '<', $this->start_date);
                })->get();

            $start_pengurangan_penjualan = SalesOrderDetail::where('item_id', $row_stock->item_id)
                ->whereHas('salesOrder', function ($query) {
                    $query->where('post_date', '<', $this->start_date);
                })->get();

            $start_pengurangan_store = InventoryIssueDetail::where('item_stock_new_id', $row_stock->id)
                ->whereHas('inventoryIssue', function ($query) {
                    $query->where('post_date', '<', $this->start_date);
                })->get();



            // ===================== START QTY / VALUE =========================
            $start_qty = (
                $start_penambahan_partisi->sum('qty_partition') +
                $start_penambahan_supplier->sum('qty')
            ) - (
                $start_pengurangan_partisi->sum('qty') +
                $start_pengurangan_penjualan->sum('qty') +
                $start_pengurangan_store->sum('qty')
            );
            $start_value = (
                $start_penambahan_partisi->sum('total') +
                $start_penambahan_supplier->sum('grandtotal')
            ) - (
                $start_pengurangan_partisi->sum('total') +
                $start_pengurangan_penjualan->sum('grandtotal')+
                $start_pengurangan_store->sum('total')
            );
            // if($row_stock->id == 2){
            //     info($row_stock->item->name);
            //     info($start_penambahan_partisi);
            //     info($start_pengurangan_partisi);
            //     info($start_pengurangan_penjualan);
            //     info($start_pengurangan_store);
            // }

            $penambahan_item_partisi = ItemPartitionDetail::where('to_item_stock_new_id',$row_stock->id)
            ->whereHas('itemPartition', function ($query) {
                $query->where('post_date', '>=', $this->start_date)
                        ->where('post_date', '<=', $this->finish_date);
            })->get();
            $penambahan_item_partisi_qty = $penambahan_item_partisi->sum('qty_partition');
            $penambahan_item_partisi_total = $penambahan_item_partisi->sum('total');

            $penambahan_item_supplier = DeliveryReceiveDetail::where('item_id',$row_stock->item_id)
            ->whereHas('deliveryReceive', function ($query) {
                $query->where('post_date', '>=', $this->start_date)
                        ->where('post_date', '<=', $this->finish_date);
            })->get();
            $penambahan_item_supplier_qty = $penambahan_item_supplier->sum('qty');
            $penambahan_item_supplier_total = $penambahan_item_supplier->sum('grandtotal');

            $pengurangan_item_partisi = ItemPartitionDetail::where('item_stock_new_id',$row_stock->id)
            ->whereHas('itemPartition', function ($query) {
                $query->where('post_date', '>=', $this->start_date)
                        ->where('post_date', '<=', $this->finish_date);
            })->get();
            $pengurangan_item_partisi_qty = $pengurangan_item_partisi->sum('qty');
            $pengurangan_item_partisi_total = $pengurangan_item_partisi->sum('total');

            $pengurangan_item_penjualan = SalesOrderDetail::where('item_id',$row_stock->item_id)
            ->whereHas('salesOrder', function ($query) {
                $query->where('post_date', '>=', $this->start_date)
                        ->where('post_date', '<=', $this->finish_date);
            })->get();

            $pengurangan_item_penjualan_qty = $pengurangan_item_penjualan->sum('qty');
            $pengurangan_item_penjualan_total = $pengurangan_item_penjualan->sum('grandtotal');

            $pengurangan_item_ke_store = InventoryIssueDetail::where('item_stock_new_id',$row_stock->id)
            ->whereHas('inventoryIssue', function ($query) {
                $query->where('post_date', '>=', $this->start_date)
                        ->where('post_date', '<=', $this->finish_date);
            })->get();
            $pengurangan_item_ke_store_qty = $pengurangan_item_ke_store->sum('qty');
            $pengurangan_item_ke_store_total = $pengurangan_item_ke_store->sum('total');


            //item Store
            // Ambil data StoreItemStock


            $qty_plus_mtd = $penambahan_item_partisi_qty+$penambahan_item_supplier_qty;
            $qty_minus_mtd = $pengurangan_item_partisi_qty+$pengurangan_item_penjualan_qty+$pengurangan_item_ke_store_qty;

            $value_plus_mtd=$penambahan_item_partisi_total+$penambahan_item_supplier_total;
            $value_minus_mtd=$pengurangan_item_partisi_total+$pengurangan_item_penjualan_total+$pengurangan_item_ke_store_total;

            $keys++;
            $arr[] = [
                $keys, // Serial number
                $row_stock->item->name,
                $start_qty,
                $start_value,
                $qty_plus_mtd,
                $value_plus_mtd,
                $qty_minus_mtd,
                $value_minus_mtd,
                ($start_qty+$qty_plus_mtd)-$qty_minus_mtd,
                $start_value + $value_plus_mtd - $value_minus_mtd,
            ];


        }
        $arr[]=[
            '','','','','','','','','',''
        ];
        $arr[]=[
            '','','','','','','','','',''
        ];
        $arr[]=[
            'Stock Item di Toko','','','','','','','','',''
        ];
        $arr[]=[
            'No',
            'Nama Barang',
            'Unit',
            'Saldo Awal',
            'Unit Pembelian',
            'Value Pembelian',
            'Unit Pemakaian',
            'Value Pemakaian',
            'Saldo Akhir Unit',
            'Saldo Akhir Value',
        ];

        foreach($item_Stock_new as $row_stock){
            // Store stock sebelum periode
            $store_item_stock = StoreItemStock::where('item_stock_new_id', $row_stock->id)
                ->where('item_id', $row_stock->item_id)
                ->first();

            if ($store_item_stock) {
                $start_store_penambahan = InventoryIssueDetail::where('store_item_stock_id', $store_item_stock->id)
                    ->whereHas('inventoryIssue', function ($query) {
                        $query->where('post_date', '<', $this->start_date);
                    })->get();

                $start_store_pengurangan = InvoiceDetail::where('store_item_stock_id', $store_item_stock->id)
                    ->whereHas('invoice', function ($query) {
                        $query->where('post_date', '<', $this->start_date);
                    })->get();
            } else {
                $start_store_penambahan = collect();
                $start_store_pengurangan = collect();
            }

            $store_item_stock = StoreItemStock::where('item_stock_new_id', $row_stock->id)
                ->where('item_id', $row_stock->item_id)
                ->first();

            if ($store_item_stock) {
                // Penambahan item ke store dari Inventory Issue
                $penambahan_item_store_inventory_issue = InventoryIssueDetail::where('store_item_stock_id', $store_item_stock->id)
                    ->whereHas('inventoryIssue', function ($query) {
                        $query->where('post_date', '>=', $this->start_date)
                            ->where('post_date', '<=', $this->finish_date);
                    })->get();

                $penambahan_item_store_inventory_issue_qty = $penambahan_item_store_inventory_issue->sum('qty_store_item');
                $penambahan_item_store_inventory_issue_total = $penambahan_item_store_inventory_issue->sum('total');

                // Pengurangan item dari store karena invoice
                $pengurangan_item_store_invoice = InvoiceDetail::where('store_item_stock_id', $store_item_stock->id)
                    ->whereHas('invoice', function ($query) {
                        $query->where('post_date', '>=', $this->start_date)
                            ->where('post_date', '<=', $this->finish_date);
                    })->get();
                $total_invoice = StoreItemMove::where('lookable_type','invoices')
                ->whereIn('lookable_id',$pengurangan_item_store_invoice->pluck('id'))->get();

                $pengurangan_item_store_invoice_qty = $pengurangan_item_store_invoice->sum('qty');
                $pengurangan_item_store_invoice_total = $total_invoice->sum('total_out');
            } else {
                // Default values if no store_item_stock found
                $penambahan_item_store_inventory_issue_qty = 0;
                $penambahan_item_store_inventory_issue_total = 0;

                $pengurangan_item_store_invoice_qty = 0;
                $pengurangan_item_store_invoice_total = 0;
            }


            $start_qty_store =$start_store_penambahan->sum('qty')- $start_store_pengurangan->sum('qty');

            $start_value_store = $start_store_penambahan->sum('total') - $start_store_pengurangan->sum('total');

            $arr[] = [
                $keys, // Serial number
                $row_stock->item->name,
                $start_qty_store,
                $start_value_store,
                $penambahan_item_store_inventory_issue_qty,
                $penambahan_item_store_inventory_issue_total,
                $pengurangan_item_store_invoice_qty,
                $pengurangan_item_store_invoice_total,
                $start_qty_store + $penambahan_item_store_inventory_issue_qty-$pengurangan_item_store_invoice_qty,
                $start_value_store + $penambahan_item_store_inventory_issue_total - $pengurangan_item_store_invoice_total,
            ];
        }



        return $arr;

    }

    public function title(): string
    {
        return 'Stock Gudang';
    }

    public function headings(): array
    {
        return $this->headings;
    }
}

<?php

namespace App\Exports;

use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReceive;
use App\Models\GoodReturnPO;
use App\Models\InventoryTransferIn;
use App\Models\ItemCogs;
use App\Models\LandedCost;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\ProductionFgReceive;
use App\Models\ProductionHandover;
use App\Models\ProductionIssue;
use App\Models\ProductionReceive;
use App\Models\ProductionRepack;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportTransactionCogs implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date, string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'No',
        'Transaksi',
        'No Dokumen',
        'Grandtotal',
        'ID ITEM COGS',
        'Total Item Cogs',
    ];





    public function collection()
    {
        $keys = 1;
        $arr = [];
        $good_receipt = GoodReceipt::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($good_receipt as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();



            $cogIds = $cogs->pluck('id'); // Get just the IDs
            $totalInSum = $cogs->sum('total_in'); // Sum the total_in values

            foreach($row_x->goodReceiptDetail as $row_detail) {
                $currency = $row_detail->purchaseOrderDetail->purchaseOrder->currency_rate;

                $total += round($row_detail->total * $currency,2);
            }
            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {

                    info(round($total,2));
                    info($totalInSum);
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>round($total,2),
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>round($total,2),
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }


            $keys++;
        }

        $good_receive = GoodReceive::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($good_receive as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $currency = $row_x->currency_rate;

            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_in');
            foreach($row_x->goodReceiveDetail as $row_detail) {
                $total += round($row_detail->total * $currency,2);
            }
            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>round($total,2),
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }
            $keys++;
        }

        $inventory_transfer_in = InventoryTransferIn::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($inventory_transfer_in as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_in');

            foreach($row_x->inventoryTransferOut->inventoryTransferOutDetail as $row_to){
                $total += $row_to->price;
            }

            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>$total,
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }


            $keys++;
        }


        $landed_cost = LandedCost::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();

        foreach ($landed_cost as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $currency = $row_x->currency_rate;
            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_in');

            foreach($row_x->landedCostDetail as $row_detail) {
                $total += round($row_detail->nominal * $currency,2);
            }
            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>round($total,2),
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }



            $keys++;
        }


        $production_receive = ProductionReceive::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();

        foreach ($production_receive as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_in');
            foreach($row_x->productionReceiveDetail as $row_b){
                $total += round($row_b->total,2);
            }

            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>$total,
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }

            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }

            $keys++;
        }

        $production_fg_receive = ProductionFgReceive::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($production_fg_receive as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_in');
            foreach($row_x->productionFgReceiveDetail as $row_b){
                $total += round($row_b->total,2);
            }

            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>$total,
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }

            $keys++;
        }

        $production_issue = ProductionIssue::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($production_issue as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_out');
            foreach($row_x->productionIssueDetail as $row_b){
                $total += round($row_b->total,2);
            }

            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>$total,
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }

            $keys++;
        }

        $production_handover_out = ProductionHandover::where('deleted_at',null)
        ->whereHas('productionHandoverDetail',function($query){
            $query->whereHas('item',function($query){
                $query->where('item_group_id','!=',7);
            });
        })
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($production_handover_out as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_out');
            foreach($row_x->productionHandoverDetail as $row_b){
                $total += round($row_b->total,2);
            }

            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>$total,
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }

            $keys++;
        }

        $production_handover_in = ProductionHandover::where('deleted_at',null)
        ->whereHas('productionHandoverDetail',function($query){
            $query->whereHas('item',function($query){
                $query->where('item_group_id','!=',7);
            });
        })
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($production_handover_in as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_in');
            foreach($row_x->productionHandoverDetail as $row_b){
                $total += round($row_b->total,2);
            }

            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>$total,
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }

            $keys++;
        }

        $production_repack = ProductionRepack::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($production_repack as $row_x) {
            $cogs_out = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->where('type','OUT')
            ->whereNull('deleted_at')
            ->get();

            $cogs_in = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->where('type','IN')
            ->whereNull('deleted_at')
            ->get();

            $cogIdsOut = $cogs_out->pluck('id');
            $totalInSumOut = $cogs_out->sum('total_out');

            $cogIdsIn = $cogs_in->pluck('id');
            $totalInSumIn = $cogs_in->sum('total_in');
            foreach($row_x->productionRepackDetail as $row_b){
                $total += round($row_b->total,2);
            }

            if(count($cogs_in) > 0 || count($cogs_out) > 0){
                if ($totalInSumIn != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable().' IN',
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>$total,
                        'ID ITEM COGS'=>$cogIdsIn,
                        'Total Item Cogs'=>$totalInSumIn
                    ];
                }
                if ($totalInSumOut != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable().' Out',
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>$total,
                        'ID ITEM COGS'=>$cogIdsOut,
                        'Total Item Cogs'=>$totalInSumOut
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }

            $keys++;
        }


        $marketing_order_delivery_process = MarketingOrderDeliveryProcess::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($marketing_order_delivery_process as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_out');
            foreach($row_x->marketingOrderDeliveryProcessDetail as $row_detail) {
                $total += round($row_detail->total,2);
            }

            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>$total,
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }

            $keys++;
        }

        $good_return = GoodReturnPO::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($good_return as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();



            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_out');
            foreach($row_x->goodReturnPODetail as $row_detail) {
                $currency = $row_detail->goodReceiptDetail->purchaseOrderDetail->purchaseOrder->currency_rate;
                $total += round($row_detail->total * $currency,2);
            }
            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>round($total,2),
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }
            $keys++;
        }



        $good_issue = GoodIssue::where('deleted_at',null)
        ->where('post_date', '>=',$this->start_date)
        ->where('post_date', '<=', $this->finish_date)
        ->whereIn('status',['2','3'])
        ->get();


        foreach ($good_issue as $row_x) {
            $total = 0;
$cogs = ItemCogs::where('lookable_type', $row_x->getTable())
            ->where('lookable_id', $row_x->id)
            ->whereNull('deleted_at')
            ->get();

            $cogIds = $cogs->pluck('id');
            $totalInSum = $cogs->sum('total_out');
            $currency = $row_x->currency_rate ?? 1;

            foreach($row_x->goodIssueDetail as $row_detail) {
                $total += round($row_detail->total * $currency,2);
            }

            if(count($cogs) > 0){
                if (round($totalInSum,2) != round($total,2)) {
                    $arr[]=[
                        'no' =>$keys,
                        'Transaksi'=>$row_x->getTable(),
                        'No Dokumen'=>$row_x->code,
                        'Grandtotal'=>round($total,2),
                        'ID ITEM COGS'=>$cogIds,
                        'Total Item Cogs'=>$totalInSum
                    ];
                }
            }else{
                $arr[]=[
                    'no' =>$keys,
                    'Transaksi'=>$row_x->getTable(),
                    'No Dokumen'=>$row_x->code,
                    'Grandtotal'=>$row_x->total,
                    'ID ITEM COGS'=>'tidak punya cogs',
                    'Total Item Cogs'=>'tidak punya cogs',
                ];
            }
            $keys++;
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

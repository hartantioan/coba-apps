<?php

namespace App\Exports;

use App\Models\ProductionBatch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\GoodIssueDetail;
use App\Models\GoodReceiveDetail;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\ProductionHandover;
use App\Models\ProductionHandoverDetail;
use App\Models\ProductionRepackDetail;

class ExportReportStockFgPerBatch implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $start_date, $finish_date;

    public function __construct(string $start_date,string $finish_date)
    {
        $this->start_date = $start_date ? $start_date : '';
        $this->finish_date = $finish_date ? $finish_date : '';
    }

    private $headings = [
        'Kode',
        'Item',
        'Shading',
        'Batch',
        'Awal',
        'Receive FG',
        'Good Receive',
        'Repack(+)',
        'Delivery',
        'Repack(-)',
        'Good Issue',
        'Total',
    ];

    public function collection()
    {
        $item_batch = ProductionBatch::join('items', 'production_batches.item_id', '=', 'items.id')
        ->whereNotNull('item_shading_id')
        ->orderBy('items.id')
        ->orderBy('items.code')
        ->select('production_batches.*')
        ->get();


        $arr = [];
        foreach($item_batch as $row){


            $handover_awal = ProductionHandoverDetail::where('item_shading_id',$row->item_shading_id)
            ->whereHas('productionBatch',function ($query) use ($row) {
                $query->where('id',$row->id);
            })
            ->where('deleted_at',null)
            ->whereHas('productionHandover',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->get();

            $totalQty_handover_awal = 0;

            if($handover_awal){
                foreach ($handover_awal as $handover) {
                    $qtyConversion = $handover->productionFgReceiveDetail->conversion ?? 1;

                    $totalQty_handover_awal += $handover->qty * $qtyConversion;
                }
            }

            $repack_in_awal = ProductionRepackDetail::where('item_shading_id',$row->item_shading_id)
            ->where('production_batch_id',$row->id)
            ->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {

                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty');

            $repack_out_awal = ProductionRepackDetail::where('deleted_at',null)
            ->where('production_batch_id',$row->id)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->item_shading_id);
            })
            ->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty');




            $goodReceive_awal = GoodReceiveDetail::where('item_shading_id',$row->item_shading_id)
            ->whereHas('productionBatch',function ($query) use ($row) {
                $query->where('id',$row->id);
            })
            ->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty') ?? 0;

            $delivery_process_awal = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->item_shading_id)
                ->where('production_batch_id',$row->id);
            })
            ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date)
                ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                    $query->whereIn('status',['2']);
                });
            })->get();

            $total_sj_awal = 0;
            if($delivery_process_awal){
                foreach ($delivery_process_awal as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj_awal += $row_sj->qty * $qtyConversion;
                }
            }


            $goodIssue_awal = GoodIssueDetail::where('item_shading_id',$row->item_shading_id)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('production_batch_id',$row->id);
            })
            ->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty') ?? 0;

            $awal =($totalQty_handover_awal + $goodReceive_awal + $repack_in_awal) - ( $total_sj_awal+$goodIssue_awal  + $repack_out_awal);

            $handover = ProductionHandoverDetail::where('item_shading_id',$row->item_shading_id)
            ->whereHas('productionBatch',function ($query) use ($row) {
                $query->where('id',$row->id);
            })
            ->where('deleted_at',null)->whereHas('productionHandover',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->get();

            $total_handover = 0;
            if($handover) {
                foreach ($handover as $handovered) {
                    $qtyConversion = $handovered->productionFgReceiveDetail->conversion ?? 1;

                    $total_handover += $handovered->qty * $qtyConversion;
                }
            }


            $goodReceive = GoodReceiveDetail::where('item_shading_id',$row->item_shading_id)
            ->whereHas('productionBatch',function ($query) use ($row) {
                $query->where('id',$row->id);
            })
            ->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty') ?? 0;

            $delivery_process = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->item_shading_id)
                ->where('production_batch_id',$row->id);
            })
            ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date)
                ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                    $query->whereIn('status',['2']);
                });
            })->get();

            $total_sj = 0;
            if($delivery_process){
                foreach ($delivery_process as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj += $row_sj->qty * $qtyConversion;
                }
            }


            $goodIssue = GoodIssueDetail::where('item_shading_id',$row->item_shading_id)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('production_batch_id',$row->id);
            })
            ->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty') ?? 0;

            $repack_in = ProductionRepackDetail::where('item_shading_id',$row->item_shading_id)
            ->where('production_batch_id',$row->id)
            ->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty');

            $repack_out = ProductionRepackDetail::where('deleted_at',null)
            ->where('production_batch_id',$row->id)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->item_shading_id);
            })
            ->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty');


            $total = $awal + (($total_handover+ $goodReceive+$repack_in) - ( $total_sj+$goodIssue+$repack_out));

            $arr[] = [
                'item_code'=> $row->item->code,
                'item_name'=>$row->item->name,
                'shading'=>$row->code,
                'batch'=>$row->code,
                'awal'=>$awal,
                'receiveFG'=>$total_handover,
                'good_receive'=>$goodReceive,
                'repack_in'=>$repack_in,
                'SJ'=>$total_sj,
                'repack_out'=>$repack_out,
                'good_issue'=>$goodIssue,
                'total'=>$total
            ];

        }

        return collect($arr);
    }

    public function title(): string
    {
        return 'Summary Stock FG Produksi Per-Batch';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

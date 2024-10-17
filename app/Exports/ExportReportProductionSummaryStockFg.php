<?php

namespace App\Exports;

use App\Models\GoodIssueDetail;
use App\Models\GoodReceiveDetail;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\ProductionHandover;
use App\Models\ProductionHandoverDetail;
use App\Models\ProductionRepackDetail;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExportReportProductionSummaryStockFg implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
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
        'Awal',
        'Receive FG',
        'Good Receive',
        'Repack(+)',
        'Delivery',
        'SJ Belum Scan',
        'Repack(-)',
        'Good Issue',
        'Total',
    ];

    public function collection()
    {
        // $query_data = "call report_stock_fg('".$this->finish_date."');";
        // $submit = DB::select($query_data);

        $item = ItemShading::join('items', 'item_shadings.item_id', '=', 'items.id')
        ->whereHas('item',function ($query)  {
            $query->whereNull('deleted_at');
        })
        ->orderBy('items.code')
        ->orderBy('items.id')
        ->select('item_shadings.*')
        ->get();


        $arr = [];
        foreach($item as $index => $row){

            $handover_awal = ProductionHandoverDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionHandover',function ($query) use ($row) {
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

            $repack_in_awal = ProductionRepackDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty');

            $repack_out_awal = ProductionRepackDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->id);
            })
            ->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty');




            $goodReceive_awal = GoodReceiveDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty') ?? 0;

            $delivery_process_awal = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->id);
            })
            ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
                // ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                //     $query->whereIn('status',['2']);
                // });
            })->get();

            $total_sj_awal = 0;
            if($delivery_process_awal){
                foreach ($delivery_process_awal as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj_awal += $row_sj->qty * $qtyConversion;
                }
            }


            $goodIssue_awal = GoodIssueDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty') ?? 0;

            $awal =($totalQty_handover_awal + $goodReceive_awal + $repack_in_awal) - ( $total_sj_awal+$goodIssue_awal  + $repack_out_awal);

            $handover = ProductionHandoverDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionHandover',function ($query) use ($row) {
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


            $goodReceive = GoodReceiveDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty') ?? 0;

            $delivery_process = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->id);
            })
            ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date)
                ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                    $query->whereIn('status',['2']);
                });
            })->get();

            $delivery_process_no_scan = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->id);
            })
            ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date)
                ->whereHas('marketingOrderDeliveryProcessTrack', function ($query) {
                    $query->where('status', '1');
                })
                ->whereDoesntHave('marketingOrderDeliveryProcessTrack', function ($query) {
                    $query->where('status', '!=', '1');
                });
            })->get();

            // if($row->id == 48){
            //    info($delivery_process);
            //    info( $delivery_process_no_scan);
            // }

            $total_sj = 0;
            $total_sj_no_scan = 0;
            if($delivery_process){
                foreach ($delivery_process as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj += $row_sj->qty * $qtyConversion;
                }
            }

            if($delivery_process_no_scan){
                foreach ($delivery_process_no_scan as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj_no_scan += $row_sj->qty * $qtyConversion;
                }
            }


            $goodIssue = GoodIssueDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty') ?? 0;

            $repack_in = ProductionRepackDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty');

            $repack_out = ProductionRepackDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->id);
            })
            ->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty');


            $total = $awal + (($total_handover+ $goodReceive+$repack_in) - ( $total_sj+$goodIssue+$repack_out+$total_sj_no_scan));

            $arr[] = [
                'item_code'=> $row->item->code,
                'item_name'=>$row->item->name,
                'shading'=>$row->code,
                'awal'=>$awal,
                'receiveFG'=>$total_handover,
                'good_receive'=>$goodReceive,
                'repack_in'=>$repack_in,
                'SJ'=>$total_sj,
                'SJ_no_scan'=>$total_sj_no_scan,
                'repack_out'=>$repack_out,
                'good_issue'=>$goodIssue,
                'total'=>$total
            ];

        }


        // foreach ($submit as $row) {
        //     $row_item = Item::find($row->itemid);
        //     $total_palet = $row->total / $row_item->sellConversion();
        //     $total_box = ($row->total/$row_item->sellConversion())*$row_item->pallet->box_conversion;
        //     $arr[] = [
        //         'item_code' => $row->itemcode, // Directly use the itemcode from the object
        //         'item_name' => $row->name, // Use the name from the object
        //         'shading' => $row->shading, // Use the shading from the object

        //         'IN' => $row->IN, // Use the IN value from the object
        //         'out' => $row->out, // Use the out value from the object
        //         'total' => $row->total, // Use the total value from the object
        //         'total_palet' => $total_palet, // Use the total value from the object
        //         'total_box' => $total_box, // Use the total value from the object
        //     ];
        // }

        return collect($arr);


    }

    public function title(): string
    {
        return 'Summary Stock FG Produksi';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

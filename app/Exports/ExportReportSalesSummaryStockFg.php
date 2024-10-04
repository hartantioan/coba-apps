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

class ExportReportSalesSummaryStockFg implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
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
        'Total',
        'Total Konversi (Palet)',
        'Total Konversi (Box)',

    ];

    // private $headings = [
    //     'item code',
    //     'name',
    //     'shading',
    //     'in',
    //     'out',
    //     'total',
    // ];

    // public function collection()
    // {
    //     $query_data = ItemStock::where(function($querys){
    //         $querys->whereHas('item',function($query){
    //             $query->where('status',1);
    //         });
    //         // if($request->item_shading_id != 'null'){

    //         //     $querys->where('item_shading_id',$request->item_shading_id);
    //         // }
    //         // if($request->production_batch_id != 'null'){

    //         //     $querys->where('production_batch_id',$request->production_batch_id);
    //         // }
    //         // if($request->area != 'all'){
    //         //     $querys->where('area_id',$request->area);
    //         // }
    //         // if($request->plant != 'all'){
    //         //     $querys->where('place_id',$request->plant);
    //         // }
    //     })
    //     ->join('items', 'item_stocks.item_id', '=', 'items.id')
    //     ->selectRaw('item_stocks.*, items.code, items.name, SUM(item_stocks.qty) as total_quantity')
    //     ->groupBy('item_stocks.item_id', 'items.code', 'items.name')
    //     ->get();

    //     $x=1;
    //     foreach($query_data as $key => $row){

    //         'pallet_conversion'=>number_format(($arr['qty']/$row->item->sellConversion()),3,',','.'),
    //         'box_conversion'=>number_format(($arr['qty']/$row->item->sellConversion())*$row->item->pallet->box_conversion,3,',','.'),
    //         $subtotal = $row->subtotal * $row->currency_rate;
    //         $discount = $row->discount * $row->currency_rate;
    //         $total = $subtotal - $discount;
    //         $arr[] = [
    //             'item_code'      => $row->item->code,
    //             'item_name'      => $row->item->name,
    //             'shading'        => $row->itemShading->code,
    //             'qty_m2'         => $row->total_quantity,
    //             'box'            => $row->total_quantity/$row-,
    //             'palet'          => $row->marketingOrderDeliveryDetail->first()->marketingOrderDelivery->marketingOrderDeliveryProcess->marketingOrderInvoice->code??'-',

    //         ];
    //         $x++;



    //     }

    //     return collect($arr);
    // }

    // public function collection()
    // {
    //     // $query_data = ItemCogs::whereRaw("id IN (SELECT MAX(id) FROM item_cogs WHERE deleted_at IS NULL AND date <= '".$this->finish_date."' GROUP BY item_id, item_shading_id)")
    //     //     ->where(function($query) {
    //     //         $query->whereHas('item',function($query){
    //     //             $query->whereIn('status',['1','2']);
    //     //         });
    //     //         if($this->finish_date) {
    //     //             $query->whereDate('date','<=', $this->finish_date);
    //     //         }

    //     //     })
    //     //     ->orderBy('date', 'desc')
    //     //     ->orderBy('id', 'desc')
    //     //     ->get();
    //     $query_data = "call report_stock_fg('".$this->finish_date."');";
    //     $submit = DB::select($query_data);

    //     foreach ($submit as $row) {

    //         $arr[] = [
    //             'item_code' => $row->itemcode, // Directly use the itemcode from the object
    //             'item_name' => $row->name, // Use the name from the object
    //             'shading' => $row->shading, // Use the shading from the object

    //             'IN' => $row->IN, // Use the IN value from the object
    //             'out' => $row->out, // Use the out value from the object
    //             'total' => $row->total, // Use the total value from the object
    //         ];
    //     }

    //     return collect($arr);

    //     $x=1;


    // }

    public function collection()
    {
        // $query_data = "call report_stock_fg('".$this->finish_date."');";
        // $submit = DB::select($query_data);

        $item = ItemShading::join('items', 'item_shadings.item_id', '=', 'items.id')
        ->orderBy('items.code')
        ->orderBy('items.id')
        ->select('item_shadings.*')
        ->get();


        $arr = [];
        foreach($item as $row){


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

            $total_sj = 0;
            if($delivery_process){
                foreach ($delivery_process as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj += $row_sj->qty * $qtyConversion;
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


            $total = $awal + (($total_handover+ $goodReceive+$repack_in) - ( $total_sj+$goodIssue+$repack_out));
            $pallet_conversion=0;
            $box_conversion=0;
            if($total != 0 ){
                $pallet_conversion= $total/$row->item->sellConversion();
                $box_conversion=($total/$row->item->sellConversion())*$row->item->pallet->box_conversion;
            }

            if($pallet_conversion==$box_conversion || $pallet_conversion==$total){
                $pallet_conversion= 0;
            }

            $arr[] = [
                'item_code'=> $row->item->code,
                'item_name'=>$row->item->name,
                'shading'=>$row->code,
                'total'=>$total,
                'pallet_conversion'=>$pallet_conversion,
                'box_conversion'=>$box_conversion
            ];

        }


        return collect($arr);


    }

    public function title(): string
    {
        return 'Marketing Order Detail 2';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

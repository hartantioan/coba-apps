<?php

namespace App\Exports;

use App\Models\GoodIssueDetail;
use App\Models\GoodReceiveDetail;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\MarketingOrderDetail;
use App\Models\ProductionFgReceiveDetail;
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
        'SJ Sudah Scan',
        'SJ Belum Scan',
        'Repack(-)',
        'Good Issue',
        'Total',
    ];

    // public function collection()
    // {
    //     // $query_data = "call report_stock_fg('".$this->finish_date."');";
    //     // $submit = DB::select($query_data);

    //     $item = ItemShading::join('items', 'item_shadings.item_id', '=', 'items.id')
    //     ->whereHas('item',function ($query)  {
    //         $query->whereNull('deleted_at');
    //     })
    //     ->orderBy('items.code')
    //     ->orderBy('items.id')
    //     ->select('item_shadings.*')
    //     ->get();


    //     $arr = [];
    //     foreach($item as $index => $row){
    //         //     if( $index == 1 ){
    //         //     $query_handover = ProductionHandoverDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionHandover',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '<', $this->start_date);
    //         //     })->toSql();
    //         //     info('handover');
    //         //     info($query_handover);

    //         //     $query_handover = ProductionRepackDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '<', $this->start_date);
    //         //     })->toSql();
    //         //     info('repack in awal');
    //         //     info($query_handover);

    //         //     $query_handover = ProductionRepackDetail::where('deleted_at',null)
    //         //     ->whereHas('itemStock',function ($query) use ($row) {
    //         //         $query->where('item_shading_id',$row->id);
    //         //     })
    //         //     ->whereHas('productionRepack',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '<', $this->start_date);
    //         //     })->toSql();
    //         //     info('repack out awal');
    //         //     info($query_handover);


    //         //     $query_handover = GoodReceiveDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '<', $this->start_date);
    //         //     })->toSql();
    //         //     info('goodreceive awal');
    //         //     info($query_handover);


    //         //     $query_handover = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
    //         //     ->whereHas('itemStock',function ($query) use ($row) {
    //         //         $query->where('item_shading_id',$row->id);
    //         //     })
    //         //     ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '<', $this->start_date);
    //         //         // ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
    //         //         //     $query->whereIn('status',['2']);
    //         //         // });
    //         //     })->toSql();
    //         //     info('delivery_process_awal');
    //         //     info($query_handover);


    //         //     $query_handover = GoodIssueDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '<', $this->start_date);
    //         //     })->toSql();
    //         //     info('issue awal');
    //         //     info($query_handover);


    //         //     $query_handover = ProductionHandoverDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionHandover',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '>=',$this->start_date)
    //         //         ->where('post_date', '<=', $this->finish_date);
    //         //     })->toSql();
    //         //     info('handover bawah');
    //         //     info($query_handover);

    //         //     $query_handover = GoodReceiveDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '>=',$this->start_date)
    //         //         ->where('post_date', '<=', $this->finish_date);
    //         //     })->toSql();
    //         //     info('receive bawa');
    //         //     info($query_handover);

    //         //     $query_handover = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
    //         //     ->whereHas('itemStock',function ($query) use ($row) {
    //         //         $query->where('item_shading_id',$row->id);
    //         //     })
    //         //     ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '>=',$this->start_date)
    //         //         ->where('post_date', '<=', $this->finish_date)
    //         //         ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
    //         //             $query->whereIn('status',['2']);
    //         //         });
    //         //     })->toSql();
    //         //     info('process bawa');
    //         //     info($query_handover);


    //         //     $query_handover = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
    //         //     ->whereHas('itemStock',function ($query) use ($row) {
    //         //         $query->where('item_shading_id',$row->id);
    //         //     })
    //         //     ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '>=',$this->start_date)
    //         //         ->where('post_date', '<=', $this->finish_date)
    //         //         ->whereHas('marketingOrderDeliveryProcessTrack', function ($query) {
    //         //             $query->where('status', '1');
    //         //         })
    //         //         ->whereDoesntHave('marketingOrderDeliveryProcessTrack', function ($query) {
    //         //             $query->where('status', '!=', '1');
    //         //         });
    //         //     })->toSql();
    //         //     info('processbawa no scan');
    //         //     info($query_handover);


    //         //     $query_handover = GoodIssueDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '>=',$this->start_date)
    //         //         ->where('post_date', '<=', $this->finish_date);
    //         //     })->toSql();
    //         //     info('issue bawa');
    //         //     info($query_handover);

    //         //     $query_handover = ProductionRepackDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '>=',$this->start_date)
    //         //         ->where('post_date', '<=', $this->finish_date);
    //         //     })->toSql();
    //         //     info('irepack in bawa');
    //         //     info($query_handover);

    //         //     $query_handover = ProductionRepackDetail::where('deleted_at',null)
    //         //     ->whereHas('itemStock',function ($query) use ($row) {
    //         //         $query->where('item_shading_id',$row->id);
    //         //     })
    //         //     ->whereHas('productionRepack',function ($query) use ($row) {
    //         //         $query->whereIn('status',["2","3"])
    //         //         ->where('post_date', '>=',$this->start_date)
    //         //         ->where('post_date', '<=', $this->finish_date);
    //         //     })->toSql();
    //         //     info('repack bawa out');
    //         //     info($query_handover);

    //         // }


    //         $handover_awal = ProductionHandoverDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionHandover',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '<', $this->start_date);
    //         })->get();


    //         $totalQty_handover_awal = 0;

    //         if($handover_awal){
    //             foreach ($handover_awal as $handover) {
    //                 $qtyConversion = $handover->productionFgReceiveDetail->conversion ?? 1;

    //                 $totalQty_handover_awal += round($handover->qty * $qtyConversion,3);
    //             }
    //         }

    //         // info($totalQty_handover_awal);

    //         $repack_in_awal = ProductionRepackDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '<', $this->start_date);
    //         })->sum('qty');


    //         $repack_out_awal = ProductionRepackDetail::where('deleted_at',null)
    //         ->whereHas('itemStock',function ($query) use ($row) {
    //             $query->where('item_shading_id',$row->id);
    //         })
    //         ->whereHas('productionRepack',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '<', $this->start_date);
    //         })->sum('qty');



    //         $goodReceive_awal = GoodReceiveDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '<', $this->start_date);
    //         })->sum('qty') ?? 0;

    //         $delivery_process_awal = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
    //         ->whereHas('itemStock',function ($query) use ($row) {
    //             $query->where('item_shading_id',$row->id);
    //         })
    //         ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '<', $this->start_date);
    //             // ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
    //             //     $query->whereIn('status',['2']);
    //             // });
    //         })->get();

    //         $total_sj_awal = 0;
    //         if($delivery_process_awal){
    //             foreach ($delivery_process_awal as $row_sj) {
    //                 $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

    //                 $total_sj_awal += round($row_sj->qty * $qtyConversion,3);
    //             }
    //         }

    //         $goodIssue_awal = GoodIssueDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '<', $this->start_date);
    //         })->sum('qty') ?? 0;

    //         $awal =round(($totalQty_handover_awal + $goodReceive_awal + $repack_in_awal) - ( $total_sj_awal+$goodIssue_awal  + $repack_out_awal),3);

    //         $handover = ProductionHandoverDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionHandover',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '>=',$this->start_date)
    //             ->where('post_date', '<=', $this->finish_date);
    //         })->get();

    //         $total_handover = 0;
    //         if($handover) {
    //             foreach ($handover as $handovered) {
    //                 $qtyConversion = $handovered->productionFgReceiveDetail->conversion ?? 1;

    //                 $total_handover += round($handovered->qty * $qtyConversion,3);
    //             }
    //         }


    //         $goodReceive = GoodReceiveDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '>=',$this->start_date)
    //             ->where('post_date', '<=', $this->finish_date);
    //         })->sum('qty') ?? 0;

    //         $delivery_process = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
    //         ->whereHas('itemStock',function ($query) use ($row) {
    //             $query->where('item_shading_id',$row->id);
    //         })
    //         ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '>=',$this->start_date)
    //             ->where('post_date', '<=', $this->finish_date)
    //             ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
    //                 $query->whereIn('status',['2']);
    //             });
    //         })->get();



    //         $delivery_process_no_scan = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
    //         ->whereHas('itemStock',function ($query) use ($row) {
    //             $query->where('item_shading_id',$row->id);
    //         })
    //         ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '>=',$this->start_date)
    //             ->where('post_date', '<=', $this->finish_date)
    //             ->whereHas('marketingOrderDeliveryProcessTrack', function ($query) {
    //                 $query->where('status', '1');
    //             })
    //             ->whereDoesntHave('marketingOrderDeliveryProcessTrack', function ($query) {
    //                 $query->where('status', '!=', '1');
    //             });
    //         })->get();

    //         // if($row->id == 48){
    //         //    info($delivery_process);
    //         //    info( $delivery_process_no_scan);
    //         // }

    //         $total_sj = 0;
    //         $total_sj_no_scan = 0;
    //         if($delivery_process){
    //             foreach ($delivery_process as $row_sj) {
    //                 $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

    //                 $total_sj += round($row_sj->qty * $qtyConversion,3);
    //             }
    //         }

    //         if($delivery_process_no_scan){
    //             foreach ($delivery_process_no_scan as $row_sj) {
    //                 $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

    //                 $total_sj_no_scan += round($row_sj->qty * $qtyConversion,3);
    //             }
    //         }



    //         $goodIssue = GoodIssueDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '>=',$this->start_date)
    //             ->where('post_date', '<=', $this->finish_date);
    //         })->sum('qty') ?? 0;



    //         $repack_in = ProductionRepackDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '>=',$this->start_date)
    //             ->where('post_date', '<=', $this->finish_date);
    //         })->sum('qty');


    //         $repack_out = ProductionRepackDetail::where('deleted_at',null)
    //         ->whereHas('itemStock',function ($query) use ($row) {
    //             $query->where('item_shading_id',$row->id);
    //         })
    //         ->whereHas('productionRepack',function ($query) use ($row) {
    //             $query->whereIn('status',["2","3"])
    //             ->where('post_date', '>=',$this->start_date)
    //             ->where('post_date', '<=', $this->finish_date);
    //         })->sum('qty');


    //         $total = round($awal + (($total_handover+ $goodReceive+$repack_in) - ( $total_sj+$goodIssue+$repack_out+$total_sj_no_scan)),3);

    //         $arr[] = [
    //             ''  => $row->id,
    //             'item_code'=> $row->item->code,
    //             'item_name'=>$row->item->name,
    //             'shading'=>$row->code,
    //             'awal'=>$awal,
    //             'receiveFG'=>$total_handover,
    //             'good_receive'=>$goodReceive,
    //             'repack_in'=>$repack_in,
    //             'SJ'=>$total_sj,
    //             'SJ_no_scan'=>$total_sj_no_scan,
    //             'repack_out'=>$repack_out,
    //             'good_issue'=>$goodIssue,
    //             'total'=>$total
    //         ];

    //     }


    //     return collect($arr);


    // }


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

            /* $handover_awal = DB::select('CALL GetHandoverAwal(?, ?)', [$row->id, $this->start_date]); */

            $totalQty_handover_awal = 0;

            if($handover_awal){
                foreach ($handover_awal as $handover) {
                    $fgDetail = ProductionFgReceiveDetail::find($handover->production_fg_receive_detail_id); // Adjust as needed

                    $qtyConversion = $fgDetail->conversion ?? 1;
                    $totalQty_handover_awal += round($handover->qty * $qtyConversion,3);
                }
            }

            // info($totalQty_handover_awal);

            $repack_in_awal = ProductionRepackDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty');

            $repack_in_awal = DB::select('CALL GetRepackInAwal(?, ?)', [$row->id, $this->start_date])[0]->total_qty;
            $repack_out_awal = DB::select('CALL GetRepackOutAwal(?, ?)', [$row->id, $this->start_date])[0]->total_qty;

            $repack_out_awal = ProductionRepackDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->id);
            })
            ->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty');

            /* $goodReceive_awal = DB::select('CALL GetGoodReceiveAwal(?, ?)', [$row->id, $this->start_date])[0]->total_qty; */

            $goodReceive_awal = GoodReceiveDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty') ?? 0;

            /* $delivery_process_awal = DB::select('CALL GetDeliveryProcessAwal(?, ?)', [$row->id, $this->start_date]); */

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
                    $delivery_detail = MarketingOrderDeliveryDetail::find($row_sj->marketing_order_delivery_detail_id);
                    $marketing_order_detail = MarketingOrderDetail::find($delivery_detail->marketing_order_detail_id);
                    $qtyConversion =  $marketing_order_detail->qty_conversion ?? 1;
                    $total_sj_awal += round($row_sj->qty * $qtyConversion,3);
                }
            }
            /* $goodIssue_awal = DB::select('CALL GetGoodIssueAwal(?, ?)', [$row->id, $this->start_date])[0]->total_qty; */
            // info($row->item->name);
            // info('1');
            // info($handover_awal);
            // info('fg_receive');
            // info($totalQty_handover_awal);
            // info('2');
            // info($repack_in_awal);
            // info('3');
            // info($repack_out_awal);
            // info('4');
            // info($goodReceive_awal);
            // info('5');
            // info($delivery_process_awal);
            // info('total_sj_awal');
            // info($total_sj_awal);
            // info('6');
            // info($goodIssue_awal);
            $goodIssue_awal = GoodIssueDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->sum('qty') ?? 0;

            $awal =round(($totalQty_handover_awal + $goodReceive_awal + $repack_in_awal) - ( $total_sj_awal+$goodIssue_awal  + $repack_out_awal),3);

            /* $handover = DB::select('CALL GetHandoverDetails(?, ? ,?)', [$row->id, $this->start_date , $this->finish_date]); */
            $handover = ProductionHandoverDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionHandover',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->get();

            $total_handover = 0;
            if($handover) {
                foreach ($handover as $handovered) {
                    $fgDetail = ProductionFgReceiveDetail::find($handovered->production_fg_receive_detail_id); // Adjust as needed

                    $qtyConversion = $fgDetail->conversion ?? 1;

                    $total_handover += round($handovered->qty * $qtyConversion,3);
                }
            }

            /* $goodReceive = DB::select('CALL GetGoodReceiveTotal(?, ? ,?)', [$row->id, $this->start_date , $this->finish_date])[0]->total_qty; */

            $goodReceive = GoodReceiveDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodReceive',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty') ?? 0;

            /* $delivery_process = DB::select('CALL GetDeliveryProcessDetails(?, ? ,?)', [$row->id, $this->start_date , $this->finish_date]); */

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

            /* $delivery_process_no_scan = DB::select('CALL GetDeliveryProcessNoScan(?, ? ,?)', [$row->id, $this->start_date , $this->finish_date]); */


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
                    $delivery_detail = MarketingOrderDeliveryDetail::find($row_sj->marketing_order_delivery_detail_id);
                    $marketing_order_detail = MarketingOrderDetail::find($delivery_detail->marketing_order_detail_id);
                    $qtyConversion =  $marketing_order_detail->qty_conversion ?? 1;

                    $total_sj += round($row_sj->qty * $qtyConversion,3);
                }
            }

            if($delivery_process_no_scan){
                foreach ($delivery_process_no_scan as $row_sj) {
                    $delivery_detail = MarketingOrderDeliveryDetail::find($row_sj->marketing_order_delivery_detail_id);
                    $marketing_order_detail = MarketingOrderDetail::find($delivery_detail->marketing_order_detail_id);
                    $qtyConversion =  $marketing_order_detail->qty_conversion ?? 1;

                    $total_sj_no_scan += round($row_sj->qty * $qtyConversion,3);
                }
            }

            /* $goodIssue = DB::select('CALL GetGoodIssueSum(?, ? ,?)', [$row->id, $this->start_date , $this->finish_date])[0]->total_qty; */


            $goodIssue = GoodIssueDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('goodIssue',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty') ?? 0;

            /* $repack_in = DB::select('CALL GetRepackInSum(?, ? ,?)', [$row->id, $this->start_date , $this->finish_date])[0]->total_qty; */


            $repack_in = ProductionRepackDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty');

            /* $repack_out = DB::select('CALL GetRepackOutSum(?, ? ,?)', [$row->id, $this->start_date , $this->finish_date])[0]->total_qty; */

            $repack_out = ProductionRepackDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->id);
            })
            ->whereHas('productionRepack',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->sum('qty');

            $total = round($awal + (($total_handover+ $goodReceive+$repack_in) - ( $total_sj+$goodIssue+$repack_out+$total_sj_no_scan)),3);

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

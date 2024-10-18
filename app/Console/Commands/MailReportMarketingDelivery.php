<?php

namespace App\Console\Commands;

use App\Mail\SendMailOutstandARInvoiceToCustomer;
use App\Models\GoodScale;
use App\Mail\SendMailProcurement;
use App\Mail\SendMailReportMarketingDelivery;
use App\Models\GoodIssueDetail;
use App\Models\GoodReceiveDetail;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\MarketingOrderDeliveryDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\MarketingOrderInvoice;
use App\Models\MarketingOrderInvoiceDetail;
use App\Models\ProductionHandoverDetail;
use App\Models\ProductionRepackDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class MailReportMarketingDelivery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailmarketingdelivery:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All cron job and custom script goes here.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $recipient = ['edp@superior.co.id, henrianto@superior.co.id'];

        //  $akun = MarketingOrderInvoice::whereIn('status',[2])->distinct('account_id')->get('account_id');

        // foreach ($akun as $pangsit) {
        $data = [];

        $query = DB::select("
              
                SELECT a.name,IFNULL(b.qty,0) AS day,IFNULL(c.qty,0) as month FROM types a LEFT JOIN (
                SELECT f.name AS tipe, coalesce(SUM(b.qty*d.qty_conversion),0) AS qty from marketing_order_delivery_processes a
                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
                LEFT JOIN marketing_order_delivery_details c ON c.id=b.marketing_order_delivery_detail_id
                LEFT JOIN marketing_order_details d ON d.id=c.marketing_order_detail_id
                LEFT JOIN items e ON e.id=c.item_id
                LEFT JOIN types f ON f.id=e.type_id
                WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date=DATE_FORMAT(NOW(),'%Y-%m-%d')
                GROUP BY f.name)b ON a.`name`=b.tipe
                LEFT JOIN (
                SELECT f.name AS tipe, SUM(b.qty*d.qty_conversion) AS qty from marketing_order_delivery_processes a
                LEFT JOIN marketing_order_delivery_process_details b ON a.id=b.marketing_order_delivery_process_id
                LEFT JOIN marketing_order_delivery_details c ON c.id=b.marketing_order_delivery_detail_id
                LEFT JOIN marketing_order_details d ON d.id=c.marketing_order_detail_id
                LEFT JOIN items e ON e.id=c.item_id
                LEFT JOIN types f ON f.id=e.type_id
                WHERE a.void_date is null AND a.deleted_at is NULL AND a.post_date>=DATE_FORMAT(NOW(),'%Y-%m-01')
                AND a.post_date<=DATE_FORMAT(NOW(),'%Y-%m-%d')
                GROUP BY f.name)c ON a.name=c.tipe
                ");



        foreach ($query as $row) {

            $data[] = [
                'tipe'  => $row->name,
                'd'  => $row->day,
                'm'  => $row->month,
            ];
        }


        $item = ItemShading::join('items', 'item_shadings.item_id', '=', 'items.id')
            ->orderBy('items.name')
            ->orderBy('items.id')
            ->select('item_shadings.*')
            ->get();


        $arr = [];
        $total_m2 = [];
        $total_palet = [];
        $total_box = [];
        $uniqueItems = [];
        foreach ($item as $key => $row) {


            $handover_awal = ProductionHandoverDetail::where('item_shading_id', $row->id)->where('deleted_at', null)->whereHas('productionHandover', function ($query) use ($row) {
                $query->whereIn('status', ["2", "3"])
                    ->where('post_date', '<', '2024-10-18');
            })->get();

            $totalQty_handover_awal = 0;

            if ($handover_awal) {
                foreach ($handover_awal as $handover) {
                    $qtyConversion = $handover->productionFgReceiveDetail->conversion ?? 1;

                    $totalQty_handover_awal += round($handover->qty * $qtyConversion, 3);
                }
            }

            $repack_in_awal = ProductionRepackDetail::where('item_shading_id', $row->id)->where('deleted_at', null)->whereHas('productionRepack', function ($query) use ($row) {
                $query->whereIn('status', ["2", "3"])
                    ->where('post_date', '<', '2024-10-18');
            })->sum('qty');

            $repack_out_awal = ProductionRepackDetail::where('deleted_at', null)
                ->whereHas('itemStock', function ($query) use ($row) {
                    $query->where('item_shading_id', $row->id);
                })
                ->whereHas('productionRepack', function ($query) use ($row) {
                    $query->whereIn('status', ["2", "3"])
                        ->where('post_date', '<', '2024-10-18');
                })->sum('qty');




            $goodReceive_awal = GoodReceiveDetail::where('item_shading_id', $row->id)->where('deleted_at', null)->whereHas('goodReceive', function ($query) use ($row) {
                $query->whereIn('status', ["2", "3"])
                    ->where('post_date', '<', '2024-10-18');
            })->sum('qty') ?? 0;

            $delivery_process_awal = MarketingOrderDeliveryProcessDetail::where('deleted_at', null)
                ->whereHas('itemStock', function ($query) use ($row) {
                    $query->where('item_shading_id', $row->id);
                })
                ->whereHas('marketingOrderDeliveryProcess', function ($query) use ($row) {
                    $query->whereIn('status', ["2", "3"])
                        ->where('post_date', '<', '2024-10-18');
                    // ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                    //     $query->whereIn('status',['2']);
                    // });
                })->get();

            //sj yang belum terkirim bor
            $delivery_process_awal_blm_terkirim = MarketingOrderDeliveryProcessDetail::where('deleted_at', null)
                ->whereHas('itemStock', function ($query) use ($row) {
                    $query->where('item_shading_id', $row->id);
                })
                ->whereHas('marketingOrderDeliveryProcess', function ($query) use ($row) {
                    $query->whereIn('status', ["2", "3"])
                        ->where('post_date', '<', '2024-10-18')
                        ->whereHas('marketingOrderDeliveryProcessTrack', function ($query) {
                            $query->whereNotIn('status', ['2']);
                        });
                })->get();

            $total_sj_awal = 0;
            $total_sj_awal_blm_terkirim = 0;
            $total_sj_awal_blm_terkirim_pallet = 0;
            $total_sj_awal_blm_terkirim_box = 0;
            if ($delivery_process_awal) {
                foreach ($delivery_process_awal as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj_awal += round($row_sj->qty * $qtyConversion, 3);
                }
            }
            //belum terkirim
            if ($delivery_process_awal_blm_terkirim) {
                foreach ($delivery_process_awal_blm_terkirim as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj_awal_blm_terkirim += $row_sj->qty * $qtyConversion;
                }
            }

            if ($total_sj_awal_blm_terkirim != 0) {
                $total_sj_awal_blm_terkirim_pallet = round($total_sj_awal_blm_terkirim / $row->item->sellConversion());
                $total_sj_awal_blm_terkirim_box = round(($total_sj_awal_blm_terkirim / $row->item->sellConversion()) * $row->item->pallet->box_conversion, 3);
            }


            $goodIssue_awal = GoodIssueDetail::where('item_shading_id', $row->id)->where('deleted_at', null)->whereHas('goodIssue', function ($query) use ($row) {
                $query->whereIn('status', ["2", "3"])
                    ->where('post_date', '<', '2024-10-18');
            })->sum('qty') ?? 0;

            $awal = ($totalQty_handover_awal + $goodReceive_awal + $repack_in_awal) - ($total_sj_awal + $goodIssue_awal  + $repack_out_awal);

            $handover = ProductionHandoverDetail::where('item_shading_id', $row->id)->where('deleted_at', null)->whereHas('productionHandover', function ($query) use ($row) {
                $query->whereIn('status', ["2", "3"])
                    ->where('post_date', '>=', '2024-10-18')
                    ->where('post_date', '<=', '2024-10-18');
            })->get();

            $total_handover = 0;
            if ($handover) {
                foreach ($handover as $handovered) {
                    $qtyConversion = $handovered->productionFgReceiveDetail->conversion ?? 1;

                    $total_handover += round($handovered->qty * $qtyConversion, 3);
                }
            }


            $goodReceive = GoodReceiveDetail::where('item_shading_id', $row->id)->where('deleted_at', null)->whereHas('goodReceive', function ($query) use ($row) {
                $query->whereIn('status', ["2", "3"])
                    ->where('post_date', '>=', '2024-10-18')
                    ->where('post_date', '<=', '2024-10-18');
            })->sum('qty') ?? 0;

            $delivery_process = MarketingOrderDeliveryProcessDetail::where('deleted_at', null)
                ->whereHas('itemStock', function ($query) use ($row) {
                    $query->where('item_shading_id', $row->id);
                })
                ->whereHas('marketingOrderDeliveryProcess', function ($query) use ($row) {
                    $query->whereIn('status', ["2", "3"])
                        ->where('post_date', '>=', '2024-10-18')
                        ->where('post_date', '<=', '2024-10-18')
                        /* ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                    $query->whereIn('status',['2']);
                }) */;
                })->get();

            $delivery_process_blm_terkirim = MarketingOrderDeliveryProcessDetail::where('deleted_at', null)
                ->whereHas('itemStock', function ($query) use ($row) {
                    $query->where('item_shading_id', $row->id);
                })
                ->whereHas('marketingOrderDeliveryProcess', function ($query) use ($row) {
                    $query->whereIn('status', ["2", "3"])
                        ->where('post_date', '>=', '2024-10-18')
                        ->where('post_date', '<=', '2024-10-18')
                        ->whereHas('marketingOrderDeliveryProcessTrack', function ($query) {
                            $query->whereNotIn('status', ['2']);
                        });
                })->get();

            $total_sj = 0;
            $total_sj_blm_terkirim = 0;
            if ($delivery_process) {
                foreach ($delivery_process as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj += round($row_sj->qty * $qtyConversion, 3);
                }
            }

            if ($delivery_process_blm_terkirim) {
                foreach ($delivery_process_blm_terkirim as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj_blm_terkirim += round($row_sj->qty * $qtyConversion, 3);
                }
            }


            $goodIssue = GoodIssueDetail::where('item_shading_id', $row->id)->where('deleted_at', null)->whereHas('goodIssue', function ($query) use ($row) {
                $query->whereIn('status', ["2", "3"])
                    ->where('post_date', '>=', '2024-10-18')
                    ->where('post_date', '<=', '2024-10-18');
            })->sum('qty') ?? 0;

            $repack_in = ProductionRepackDetail::where('item_shading_id', $row->id)->where('deleted_at', null)->whereHas('productionRepack', function ($query) use ($row) {
                $query->whereIn('status', ["2", "3"])
                    ->where('post_date', '>=', '2024-10-18')
                    ->where('post_date', '<=', '2024-10-18');
            })->sum('qty');

            $repack_out = ProductionRepackDetail::where('deleted_at', null)
                ->whereHas('itemStock', function ($query) use ($row) {
                    $query->where('item_shading_id', $row->id);
                })
                ->whereHas('productionRepack', function ($query) use ($row) {
                    $query->whereIn('status', ["2", "3"])
                        ->where('post_date', '>=', '2024-10-18')
                        ->where('post_date', '<=', '2024-10-18');
                })->sum('qty');


            $total = $awal + (($total_handover + $goodReceive + $repack_in) - ($total_sj + $goodIssue + $repack_out));
            $pallet_conversion = 0;
            $box_conversion = 0;
            if ($total != 0) {
                $pallet_conversion = round($total / $row->item->sellConversion(), 3);
                $box_conversion = round(($total / $row->item->sellConversion()) * $row->item->pallet->box_conversion, 3);
                $total_sum_sj_blm_terkirim = $total - ($total_sj_blm_terkirim + $total_sj_awal_blm_terkirim);
                $pallet_conversion_total_sum = round($total_sum_sj_blm_terkirim / $row->item->sellConversion(), 3);
                $box_conversion_total_sum = round(($total_sum_sj_blm_terkirim / $row->item->sellConversion() * $row->item->pallet->box_conversion), 3);
            }
            //utk yg ke 2
            if (!isset($total_m2[$row->item->id])) {
                $total_m2[$row->item->id] = 0;
            }
            $total_m2[$row->item->id] += $total;

            if (!isset($total_palet[$row->item->id])) {
                $total_palet[$row->item->id] = 0;
            }
            $total_palet[$row->item->id] += $pallet_conversion;

            if (!isset($total_box[$row->item->id])) {
                $total_box[$row->item->id] = 0;
            }
            $total_box[$row->item->id] += $box_conversion;

            if ($pallet_conversion == $box_conversion || $pallet_conversion == $total) {
                $pallet_conversion = 0;
            }

            if ($total_sj_awal_blm_terkirim_pallet == $total_sj_awal_blm_terkirim || $total_sj_awal_blm_terkirim_pallet == $total_sj_awal_blm_terkirim_box) {
                $total_sj_awal_blm_terkirim_pallet = 0;
            }

            if ($pallet_conversion_total_sum == $total_sum_sj_blm_terkirim || $pallet_conversion_total_sum == $box_conversion_total_sum) {
                $pallet_conversion_total_sum = 0;
            }

        }

        $arr = [];



        $uniqueItems = $item->unique('item_id');

        foreach ($uniqueItems as $k => $v) {
            //palet
            $mod_p = MarketingOrderDeliveryDetail::whereHas('item', function ($q) use ($v) {
                $q->where('item_id', $v->item_id)
                    ->whereHas('pallet', function ($query) {
                        $query->where('box_conversion', '>', 1);
                    });
            })->whereHas('marketingOrderDelivery', function ($query) {
                $query->whereIn('status', ['2', '3'])->where('post_date', '>=', '2024-10-18')
                    ->where('post_date', '<=', '2024-10-18');
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->sum('qty');

            $first_mod_p = MarketingOrderDeliveryDetail::whereHas('item', function ($q) use ($v) {
                $q->where('item_id', $v->item_id)
                    ->whereHas('pallet', function ($query) {
                        $query->where('box_conversion', '>', 1);
                    });
            })->whereHas('marketingOrderDelivery', function ($query) {
                $query->whereIn('status', ['2', '3'])->where('post_date', '>=', '2024-10-18')
                    ->where('post_date', '<=', '2024-10-18');
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->first();

            if ($first_mod_p) {

                $mod_p_to_m2 = round($first_mod_p->marketingOrderDetail->qty_conversion * $mod_p);
            } else {
                $mod_p_to_m2 = $mod_p;
            }

            //box
            $mod_b = MarketingOrderDeliveryDetail::whereHas('item', function ($q) use ($v) {
                $q->where('item_id', $v->item_id)
                    ->whereHas('pallet', function ($query) {
                        $query->where('box_conversion', '=', 1);
                    });
            })->whereHas('marketingOrderDelivery', function ($query) {
                $query->whereIn('status', ['2', '3'])->where('post_date', '>=', '2024-10-18')
                    ->where('post_date', '<=', '2024-10-18');
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->sum('qty');

            $first_mod_b = MarketingOrderDeliveryDetail::whereHas('item', function ($q) use ($v) {
                $q->where('item_id', $v->item_id)
                    ->whereHas('pallet', function ($query) {
                        $query->where('box_conversion', '=', 1);
                    });
            })->whereHas('marketingOrderDelivery', function ($query) {
                $query->whereIn('status', ['2', '3'])->where('post_date', '>=', '2024-10-18')
                    ->where('post_date', '<=', '2024-10-18');
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->first();


            if ($first_mod_b) {
                $mod_b_to_m2 = round($first_mod_b->marketingOrderDetail->qty_conversion * $mod_b);
            } else {
                $mod_b_to_m2 = $mod_b;
            }
            //curah
            $mod_curah = MarketingOrderDeliveryDetail::whereHas('item', function ($q) use ($v) {
                $q->where('item_id', $v->item_id)
                    ->whereHas('pallet', function ($query) {
                        $query->where('box_conversion', '=', 0);
                    });
            })->whereHas('marketingOrderDelivery', function ($query) {
                $query->whereIn('status', ['2', '3'])->where('post_date', '>=', '2024-10-18')
                    ->where('post_date', '<=', '2024-10-18');
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->sum('qty');



            $total_m2_mod = $mod_p_to_m2 + $mod_b_to_m2 + $mod_curah;

            $box_conversion = $v->item->pallet->box_conversion ?? 1;

            $total_palet_mod = round($total_m2_mod / $v->item->sellConversion(), 3);

            $total_box_mod = round($total_palet_mod * $box_conversion, 3);

            $aviable = $total_m2[$v->item->id] - $total_m2_mod;
            $aviable2 = $total_palet[$v->item->id] - $total_palet_mod;
            $aviable3 = $total_box[$v->item->id] - $total_box_mod;

            if ($aviable2 == $aviable3 || $aviable2 == $aviable) {
                $aviable2 = 0;
            }
            if ($total_palet_mod == $total_m2_mod || $total_palet_mod == $total_box_mod) {
                $total_palet_mod = 0;
            }

            if ($total_palet[$v->item->id] == $total_m2[$v->item->id] || $total_palet[$v->item->id] == $total_box[$v->item->id]) {
                $total_palet[$v->item->id] = 0;
            }

            $arr[] = [
                'item_code' => $v->item->code,
                'item_name' => $v->item->name,
                'total' => $total_m2[$v->item->id],
                'pallet_conversion' => $total_palet[$v->item->id],
                'box_conversion' => $total_box[$v->item->id],
                'on_hand' => $total_m2_mod,
                'on_hand_p' => $total_palet_mod,
                'on_hand_b' => $total_box_mod,
                'aviable' => $aviable,
                'aviable2' => $aviable2,
                'aviable3' => $aviable3,
            ];
        }



        $obj = json_decode(json_encode($data));
        $obj2 = json_decode(json_encode($arr));


        Mail::to($recipient)->send(new SendMailReportMarketingDelivery($obj, $obj2));

        // }


    }
}

<?php

namespace App\Exports;

use App\Models\GoodIssueDetail;
use App\Models\GoodReceiveDetail;
use App\Models\Item;
use App\Models\ItemShading;
use App\Models\MarketingOrderDeliveryDetail;
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
        'Qty On Hand (M2)',
        'Qty On Hand (Palet)',
        'Qty On Hand (Box)',
        // 'Qty SJ Blm Terkirim(M2)',
        // 'Qty SJ Blm Terkirim(Palet)',
        // 'Qty SJ Blm Terkirim(Box)',
        // 'Total Aviable (M2)',
        // 'Total Aviable (Palet)',
        // 'Total Aviable (Box)',

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
        $total_m2=[];
        $total_palet=[];
        $total_box=[];
        $uniqueItems = [];
        foreach($item as $key=>$row){


            $handover_awal = ProductionHandoverDetail::where('item_shading_id',$row->id)->where('deleted_at',null)->whereHas('productionHandover',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date);
            })->get();

            $totalQty_handover_awal = 0;

            if($handover_awal){
                foreach ($handover_awal as $handover) {
                    $qtyConversion = $handover->productionFgReceiveDetail->conversion ?? 1;

                    $totalQty_handover_awal += round($handover->qty * $qtyConversion,3);
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

            //sj yang belum terkirim bor
            $delivery_process_awal_blm_terkirim = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->id);
            })
            ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '<', $this->start_date)
                ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                    $query->whereNotIn('status',['2']);
                });
            })->get();

            $total_sj_awal = 0;
            $total_sj_awal_blm_terkirim = 0;
            $total_sj_awal_blm_terkirim_pallet = 0 ;
            $total_sj_awal_blm_terkirim_box = 0;
            if($delivery_process_awal){
                foreach ($delivery_process_awal as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj_awal += round($row_sj->qty * $qtyConversion,3);
                }
            }
            //belum terkirim
            if($delivery_process_awal_blm_terkirim){
                foreach ($delivery_process_awal_blm_terkirim as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj_awal_blm_terkirim += $row_sj->qty * $qtyConversion;
                }
            }

            if($total_sj_awal_blm_terkirim != 0){
                $total_sj_awal_blm_terkirim_pallet = round($total_sj_awal_blm_terkirim / $row->item->sellConversion());
                $total_sj_awal_blm_terkirim_box = round(($total_sj_awal_blm_terkirim / $row->item->sellConversion())*$row->item->pallet->box_conversion,3);
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

                    $total_handover += round($handovered->qty * $qtyConversion,3);
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
                /* ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                    $query->whereIn('status',['2']);
                }) */;
            })->get();

            $delivery_process_blm_terkirim = MarketingOrderDeliveryProcessDetail::where('deleted_at',null)
            ->whereHas('itemStock',function ($query) use ($row) {
                $query->where('item_shading_id',$row->id);
            })
            ->whereHas('marketingOrderDeliveryProcess',function ($query) use ($row) {
                $query->whereIn('status',["2","3"])
                ->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date)
                ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                    $query->whereNotIn('status',['2']);
                });
            })->get();

            $total_sj = 0;
            $total_sj_blm_terkirim = 0;
            if($delivery_process){
                foreach ($delivery_process as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj += round($row_sj->qty * $qtyConversion,3);
                }
            }

            if($delivery_process_blm_terkirim){
                foreach ($delivery_process_blm_terkirim as $row_sj) {
                    $qtyConversion =  $row_sj->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion ?? 1;

                    $total_sj_blm_terkirim += round($row_sj->qty * $qtyConversion,3);
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
                $pallet_conversion= round($total/$row->item->sellConversion(),3);
                $box_conversion=round(($total/$row->item->sellConversion())*$row->item->pallet->box_conversion,3);
                $total_sum_sj_blm_terkirim = $total - ($total_sj_blm_terkirim + $total_sj_awal_blm_terkirim);
                $pallet_conversion_total_sum = round($total_sum_sj_blm_terkirim / $row->item->sellConversion(),3);
                $box_conversion_total_sum = round(($total_sum_sj_blm_terkirim / $row->item->sellConversion() * $row->item->pallet->box_conversion),3);
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

            if($pallet_conversion==$box_conversion || $pallet_conversion==$total){
                $pallet_conversion= 0;
            }

            if($total_sj_awal_blm_terkirim_pallet == $total_sj_awal_blm_terkirim || $total_sj_awal_blm_terkirim_pallet == $total_sj_awal_blm_terkirim_box){
                $total_sj_awal_blm_terkirim_pallet = 0;
            }

            if($pallet_conversion_total_sum == $total_sum_sj_blm_terkirim || $pallet_conversion_total_sum == $box_conversion_total_sum){
                $pallet_conversion_total_sum = 0;
            }

            $arr[] = [
                'item_code'=> $row->item->code,
                'item_name'=>$row->item->name,
                'shading'=>$row->code,
                'total'=>$total,
                'pallet_conversion'=>$pallet_conversion,
                'box_conversion'=>$box_conversion,
                // 'sj_belum_terkirim'=> $total_sj_awal_blm_terkirim,
                // 'sj_belum_terkirim_pallet'=> $total_sj_awal_blm_terkirim_pallet,
                // 'sj_belum_terkirim_box'=> $total_sj_awal_blm_terkirim_box,
                // 'total_sum_sj_blm_terkirim'=>$total_sum_sj_blm_terkirim,
                // 'pallet_conversion_total_sum'=>$pallet_conversion_total_sum,
                // 'box_conversion_total_sum'=>$box_conversion_total_sum,
            ];


        }

        $arr[] = [
            'item_code'=>'',
            'item_name'=>'',
            'shading'=>'',
            'total'=>'',
            'pallet_conversion'=>'',
            'box_conversion'=>'',
        ];

        $arr[] = [
            'item_code'=>'Kode Item',
            'item_name'=>'Nama Item',
            'total'=>'On Hand(M2)',
            'pallet_conversion'=>'On Hand(Palet)',
            'box_conversion'=>'On Hand(Box)',
            'on_hand'=>'MOD(m2)',
            'on_hand_p'=>'MOD(Palet)',
            'on_hand_b'=>'MOD(Box)',
            'aviable'=>'Aviable(m2)',
            'aviable2'=>'Aviable(Palet)',
            'aviable3'=>'Aviable(Box)',
        ];

        $uniqueItems = $item->unique('item_id');

        foreach($uniqueItems as $k=>$v){
            //palet
            $mod_p = MarketingOrderDeliveryDetail::whereHas('item', function($q) use($v){
                $q->where('item_id', $v->item_id)
                ->whereHas('pallet',function ($query) {
                    $query->where('box_conversion', '>', 1);
                });
            })->whereHas('marketingOrderDelivery',function ($query) {
                $query->whereIn('status', ['2','3'])->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->sum('qty');

            $first_mod_p =MarketingOrderDeliveryDetail::whereHas('item', function($q) use($v){
                $q->where('item_id', $v->item_id)
                ->whereHas('pallet',function ($query)  {
                    $query->where('box_conversion', '>', 1);
                });
            })->whereHas('marketingOrderDelivery',function ($query) {
                $query->whereIn('status', ['2','3'])->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->first();

            if($first_mod_p){

                $mod_p_to_m2 = round($first_mod_p->marketingOrderDetail->qty_conversion * $mod_p);
            }else{
                $mod_p_to_m2 = $mod_p;
            }

            //box
            $mod_b = MarketingOrderDeliveryDetail::whereHas('item', function($q) use($v){
                $q->where('item_id', $v->item_id)
                ->whereHas('pallet',function ($query) {
                    $query->where('box_conversion', '=', 1);
                });
            })->whereHas('marketingOrderDelivery',function ($query) {
                $query->whereIn('status', ['2','3'])->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->sum('qty');

            $first_mod_b =MarketingOrderDeliveryDetail::whereHas('item', function($q) use($v){
                $q->where('item_id', $v->item_id)
                ->whereHas('pallet',function ($query)  {
                    $query->where('box_conversion', '=', 1);
                });
            })->whereHas('marketingOrderDelivery',function ($query) {
                $query->whereIn('status', ['2','3'])->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->first();


            if($first_mod_b){
                $mod_b_to_m2 = round($first_mod_b->marketingOrderDetail->qty_conversion * $mod_b);
            }else{
                $mod_b_to_m2 = $mod_b;
            }
            //curah
            $mod_curah = MarketingOrderDeliveryDetail::whereHas('item', function($q) use($v){
                $q->where('item_id', $v->item_id)
                ->whereHas('pallet',function ($query) {
                    $query->where('box_conversion', '=', 0);
                });
            })->whereHas('marketingOrderDelivery',function ($query) {
                $query->whereIn('status', ['2','3'])->where('post_date', '>=',$this->start_date)
                ->where('post_date', '<=', $this->finish_date);
            })->whereDoesntHave('marketingOrderDeliveryProcessDetail')->sum('qty');



            $total_m2_mod = $mod_p_to_m2+$mod_b_to_m2+$mod_curah;

            $box_conversion = $v->item->pallet->box_conversion ?? 1;

            $total_palet_mod = round($total_m2_mod/$v->item->sellConversion(),3);

            $total_box_mod = round($total_palet_mod*$box_conversion,3);

            $aviable = $total_m2[$v->item->id] - $total_m2_mod;
            $aviable2 = $total_palet[$v->item->id] - $total_palet_mod;
            $aviable3 = $total_box[$v->item->id] - $total_box_mod;

            if($aviable2 == $aviable3 || $aviable2 == $aviable){
                $aviable2 = 0;
            }
            if($total_palet_mod == $total_m2_mod || $total_palet_mod == $total_box_mod){
                $total_palet_mod = 0;
            }

            if($total_palet[$v->item->id] == $total_m2[$v->item->id] ||$total_palet[$v->item->id] == $total_box[$v->item->id]){
                $total_palet[$v->item->id] = 0;
            }

            $arr[] = [
                'item_code'=>$v->item->code,
                'item_name'=>$v->item->name,
                'total'=>$total_m2[$v->item->id],
                'pallet_conversion'=>$total_palet[$v->item->id],
                'box_conversion'=>$total_box[$v->item->id],
                'on_hand'=>$total_m2_mod,
                'on_hand_p'=>$total_palet_mod,
                'on_hand_b'=>$total_box_mod,
                'aviable'=>$aviable,
                'aviable2'=>$aviable2,
                'aviable3'=>$aviable3,
            ];
        }



        return collect($arr);


    }

    public function title(): string
    {
        return 'Summary Stock Penjualan';
    }

    public function headings() : array
	{
		return $this->headings;
	}
}

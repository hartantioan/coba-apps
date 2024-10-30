<?php

namespace App\Jobs;

use App\Exports\ExportInventoryTransferIn;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Helpers\ResetCogsHelper;
use App\Models\GoodIssue;
use App\Models\GoodIssueDetail;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\GoodReceive;
use App\Models\GoodReceiveDetail;
use App\Models\GoodReturnIssue;
use App\Models\GoodReturnIssueDetail;
use App\Models\GoodReturnPO;
use App\Models\GoodReturnPODetail;
use App\Models\InventoryRevaluation;
use App\Models\InventoryRevaluationDetail;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferOut;
use App\Models\InventoryTransferOutDetail;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\ItemStock;
use App\Models\Journal;
use App\Models\LandedCost;
use App\Models\LandedCostDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryProcessDetail;
use App\Models\MarketingOrderDeliveryStock;
use App\Models\MarketingOrderReturnDetail;
use App\Models\ProductionFgReceive;
use App\Models\ProductionFgReceiveDetail;
use App\Models\ProductionHandoverDetail;
use App\Models\ProductionHandover;
use App\Models\ProductionIssueDetail;
use App\Models\ProductionIssue;
use App\Models\ProductionReceive;
use App\Models\ProductionReceiveDetail;
use App\Models\ProductionRepackDetail;
use App\Models\PurchaseMemo;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetCogsNew implements ShouldQueue/* , ShouldBeUnique */
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $company_id, $date,$place_id,$item_id,$area_id,$item_shading_id,$production_batch_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $date = null, int $company_id = null, int $place_id = null, int $item_id = null, int $area_id = null, int $item_shading_id = null, int $production_batch_id = null)
    {
		$this->company_id = $company_id;
        $this->date = $date;
        $this->place_id = $place_id;
        $this->item_id = $item_id;
        $this->area_id = $area_id;
        $this->item_shading_id = $item_shading_id;
        $this->production_batch_id = $production_batch_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $item_id = $this->item_id;
      $place_id = $this->place_id;
      $item = Item::find($item_id);
      $bomPowder = $item->bomPlace($place_id) ? $item->bomPlace($place_id)->first() : NULL;
      $bomGroup = '';
      $company_id = $this->company_id;
      if($bomPowder){
          $bomGroup = $bomPowder->group; 
      }
      $date = $this->date;
      $production_batch_id = $this->production_batch_id;
      $area_id = $this->area_id;
      $item_shading_id = $this->item_shading_id;

      if($bomPowder && $bomGroup == '1'){
          $itemcogs = ItemCogs::where('date','>=',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->orderBy('date')->delete();
          $old_data = ItemCogs::where('date','<',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->orderByDesc('date')->orderByDesc('id')->first();
      }else{
          $itemcogs = ItemCogs::where('date','>=',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id)->delete();
          $old_data = ItemCogs::where('date','<',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id)->orderByDesc('date')->orderByDesc('id')->first();
      }
      
      $today = date('Y-m-d');

      $period = CarbonPeriod::create($date, $today);

      $qtyBefore = 0;
      $totalBefore = 0;
      // Iterate over the period
      foreach ($period as $key => $date) {
        $dateloop = $date->format('Y-m-d');

        if($key == 0){
            if($old_data){
                $qtyBefore = $old_data->qty_final;
                $totalBefore = round($old_data->total_final,2);
            }
        }

        $goodreceipt = GoodReceiptDetail::whereHas('goodReceipt',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_id',$item_id)->get();

        foreach($goodreceipt as $row){
            $total = round($row->total * $row->purchaseOrderDetail->purchaseOrder->currency_rate,2);
            $qty = $row->qtyConvert();
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->goodReceipt->getTable(),
                'lookable_id'		      => $row->goodReceipt->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		      => $row->goodReceipt->company_id,
                'place_id'			      => $row->place_id,
                'warehouse_id'		    => $row->warehouse_id,
                'item_id'			        => $row->item_id,
                'qty_in'			        => $qty,
                'price_in'			      => $total / $qty,
                'total_in'			      => $total,
                'qty_final'			      => $qty_final,
                'price_final'		      => round($total_final / $qty_final,5),
                'total_final'		      => $total_final,
                'date'				        => $dateloop,
                'type'				        => 'IN'
            ]);
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }
    
        $goodreceive = GoodReceiveDetail::whereHas('goodReceive',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_id',$item_id)
        ->where(function($query)use($area_id,$item_shading_id,$production_batch_id){
            if($production_batch_id){
                $query->whereHas('productionBatch',function($query)use($area_id,$item_shading_id,$production_batch_id){
                    $query->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('id',$production_batch_id);
                })
                ->orWhereHas('itemStock',function($query)use($area_id,$item_shading_id,$production_batch_id){
                    $query->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id);
                });
            }
        })->get();

        foreach($goodreceive as $row){
            $total = $row->total;
            $qty = $row->qty;
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            $cek = ItemCogs::where('detailable_type',$row->getTable())->where('detailable_id',$row->id)->count();
            if($cek == 0){
                ItemCogs::create([
                    'lookable_type'		    => $row->goodReceive->getTable(),
                    'lookable_id'		      => $row->goodReceive->id,
                    'detailable_type'	    => $row->getTable(),
                    'detailable_id'		    => $row->id,
                    'company_id'		      => $row->goodReceive->company_id,
                    'place_id'			      => $row->place_id,
                    'warehouse_id'		    => $row->warehouse_id,
                    'item_id'			        => $row->item_id,
                    'qty_in'			        => $qty,
                    'price_in'			      => $total / $qty,
                    'total_in'			      => $total,
                    'qty_final'			      => $qty_final,
                    'price_final'		      => round($total_final / $qty_final,5),
                    'total_final'		      => $total_final,
                    'date'				        => $dateloop,
                    'type'				        => 'IN',
                    'area_id'                   => $row ->area_id ?? NULL,
                    'item_shading_id'           => $row->item_shading_id ?? NULL,
                    'production_batch_id'       => $row->productionBatch()->exists() ? $row->productionBatch->id : ($row->itemStock()->exists() ? $row->itemStock->production_batch_id : NULL),
                ]);
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $productionreceivereject = ProductionReceiveDetail::whereHas('productionReceive',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_reject_id',$item_id)->where('qty_reject','>',0)->get();

        foreach($productionreceivereject as $row){
            $qty_final = $qtyBefore + $row->qty_reject;
            $total_final = $totalBefore + 0;
            ItemCogs::create([
                'lookable_type'		        => $row->productionReceive->getTable(),
                'lookable_id'		        => $row->productionReceive->id,
                'company_id'		        => $row->productionReceive->company_id,
                'place_id'			        => $row->place_id,
                'warehouse_id'		        => $row->productionReceive->productionOrderDetail->productionScheduleDetail->bom->itemReject->warehouse(),
                'item_id'			        => $row->item_reject_id,
                'qty_in'			        => $row->qty_reject,
                'price_in'			        => 0,
                'total_in'			        => 0,
                'qty_final'			        => $qty_final,
                'price_final'		        => 0,
                'total_final'		        => $total_final,
                'date'				        => $dateloop,
                'type'				        => 'IN'
            ]);
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $productionfgreceivereject = ProductionFgReceive::whereIn('status',['2','3'])->whereDate('post_date',$dateloop)->where('qty_reject','>',0)->whereHas('productionOrderDetail',function($query)use($item_id){
            $query->whereHas('productionScheduleDetail',function($query)use($item_id){
                $query->whereHas('bom',function($query)use($item_id){
                    $query->where('item_reject_id',$item_id);
                });
            });
        })->get();

        foreach($productionfgreceivereject as $row){
            $qty_final = $qtyBefore + $row->qty_reject;
            $total_final = $totalBefore + 0;
            ItemCogs::create([
                'lookable_type'		        => $row->getTable(),
                'lookable_id'		        => $row->id,
                'company_id'		        => $row->company_id,
                'place_id'			        => $row->place_id,
                'warehouse_id'		        => $row->productionOrderDetail->productionScheduleDetail->bom->itemReject->warehouse(),
                'item_id'			        => $row->productionOrderDetail->productionScheduleDetail->bom->item_reject_id,
                'qty_in'			        => $row->qty_reject,
                'price_in'			        => 0,
                'total_in'			        => 0,
                'qty_final'			        => $qty_final,
                'price_final'		        => 0,
                'total_final'		        => $total_final,
                'date'				        => $dateloop,
                'type'				        => 'IN'
            ]);
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $productionreceive = ProductionReceiveDetail::whereHas('productionReceive',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->whereHas('productionBatch',function($query)use($production_batch_id,$bomGroup){
            if($production_batch_id && $bomGroup !== '1'){
                $query->where('id',$production_batch_id);
            }
        })->where('item_id',$item_id)->get();

        foreach($productionreceive as $row){
            if($row->productionBatch()->exists()){
                if($bomGroup == '1'){
                    foreach($row->productionBatch as $rowbatch){
                        $total = $rowbatch->total;
                        $qty = $rowbatch->qty_real;
                        $total_final = $totalBefore + $total;
                        $qty_final = $qtyBefore + $qty;
                        ItemCogs::create([
                            'lookable_type'		        => $row->productionReceive->getTable(),
                            'lookable_id'		        => $row->productionReceive->id,
                            'detailable_type'	        => $rowbatch->getTable(),
                            'detailable_id'		        => $rowbatch->id,
                            'company_id'		        => $row->productionReceive->company_id,
                            'place_id'			        => $row->place_id,
                            'warehouse_id'		        => $row->warehouse_id,
                            'item_id'			        => $row->item_id,
                            'production_batch_id'       => $rowbatch->id,
                            'qty_in'			        => $qty,
                            'price_in'			        => $qty > 0 ? $total / $qty : 0,
                            'total_in'			        => $total,
                            'qty_final'			        => $qty_final,
                            'price_final'		        => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
                            'total_final'		        => $total_final,
                            'date'				        => $dateloop,
                            'type'				        => 'IN'
                        ]);
                        foreach($rowbatch->journalDetail as $rowjournal){
                            $rowjournal->update([
                                'nominal_fc'  => $total,
                                'nominal'     => $total,
                            ]);
                        }
                        $qtyBefore = $qty_final;
                        $totalBefore = $total_final;
                    }
                }else{
                    foreach($row->productionBatch as $rowbatch){
                        $cek = ItemCogs::where('detailable_type',$rowbatch->getTable())->where('detailable_id',$rowbatch->id)->count();
                        $total = $rowbatch->total;
                        $qty = $rowbatch->qty_real;
                        $total_final = $totalBefore + $total;
                        $qty_final = $qtyBefore + $qty;
                        if($cek == 0){
                            ItemCogs::create([
                                'lookable_type'		        => $row->productionReceive->getTable(),
                                'lookable_id'		        => $row->productionReceive->id,
                                'detailable_type'	        => $rowbatch->getTable(),
                                'detailable_id'		        => $rowbatch->id,
                                'company_id'		        => $row->productionReceive->company_id,
                                'place_id'			        => $row->place_id,
                                'warehouse_id'		        => $row->warehouse_id,
                                'item_id'			        => $row->item_id,
                                'production_batch_id'       => $rowbatch->id,
                                'qty_in'			        => $qty,
                                'price_in'			        => $qty > 0 ? $total / $qty : 0,
                                'total_in'			        => $total,
                                'qty_final'			        => $qty_final,
                                'price_final'		        => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
                                'total_final'		        => $total_final,
                                'date'				        => $dateloop,
                                'type'				        => 'IN'
                            ]);
                        }
                        foreach($rowbatch->journalDetail as $rowjournal){
                            $rowjournal->update([
                                'nominal_fc'  => $total,
                                'nominal'     => $total,
                            ]);
                        }
                        $qtyBefore = $qty_final;
                        $totalBefore = $total_final;
                    }
                }
            }else{
                $total = $row->total;
                $qty = $row->qty;
                $total_final = $totalBefore + $total;
                $qty_final = $qtyBefore + $qty;
                ItemCogs::create([
                    'lookable_type'		    => $row->productionReceive->getTable(),
                    'lookable_id'		      => $row->productionReceive->id,
                    'detailable_type'	    => $row->getTable(),
                    'detailable_id'		    => $row->id,
                    'company_id'		      => $row->productionReceive->company_id,
                    'place_id'			      => $row->place_id,
                    'warehouse_id'		    => $row->warehouse_id,
                    'item_id'			        => $row->item_id,
                    'qty_in'			        => $qty,
                    'price_in'			      => $total / $qty,
                    'total_in'			      => $total,
                    'qty_final'			      => $qty_final,
                    'price_final'		      => round($total_final / $qty_final,5),
                    'total_final'		      => $total_final,
                    'date'				        => $dateloop,
                    'type'				        => 'IN'
                ]);
                foreach($row->journalDetail as $rowjournal){
                    $rowjournal->update([
                    'nominal_fc'  => $total,
                    'nominal'     => $total,
                    ]);
                }
                $qtyBefore = $qty_final;
                $totalBefore = $total_final;
            }
            if($row->productionReceive->journal()->exists()){
                $row->productionReceive->journal->journalDetail()->where('type','2')->update([
                    'nominal_fc'  => $row->productionReceive->total(),
                    'nominal'     => $row->productionReceive->total(),
                ]);
            }
        }

        $productionfgreceive = ProductionFgReceive::whereHas('productionOrderDetail',function($query)use($item_id){
            $query->whereHas('productionScheduleDetail',function($query)use($item_id){
                $query->where('item_id',$item_id);
            });
        })->whereIn('status',['2','3'])->whereDate('post_date',$dateloop)->get();

        foreach($productionfgreceive as $row){
            $total = 0;
            $qty = 0;
            foreach($row->productionFgReceiveDetail as $rowdetail){
                $qty += $rowdetail->qty;
                $total += $rowdetail->total;
            }
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->getTable(),
                'lookable_id'		      => $row->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		      => $row->company_id,
                'place_id'			      => $row->place_id,
                'warehouse_id'		    => $row->productionOrderDetail->productionScheduleDetail->item->warehouse(),
                'item_id'			        => $row->productionOrderDetail->productionScheduleDetail->item_id,
                'qty_in'			        => $qty,
                'price_in'			      => $total / $qty,
                'total_in'			      => $total,
                'qty_final'			      => $qty_final,
                'price_final'		      => round($total_final / $qty_final,5),
                'total_final'		      => $total_final,
                'date'				        => $dateloop,
                'type'				        => 'IN'
            ]);
            foreach($row->journalDetail as $rowjournal){
                $rowjournal->update([
                    'nominal_fc'  => $total,
                    'nominal'     => $total,
                ]);
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $productionhandover = ProductionHandoverDetail::whereHas('productionHandover',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_id',$item_id)->get();

        foreach($productionhandover as $row){
            $total = $row->productionFgReceiveDetail->total;
            $qty = round($row->qty * $row->productionFgReceiveDetail->conversion,3);
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            $cek = ItemCogs::where('detailable_type',$row->getTable())->where('detailable_id',$row->id)->where('production_batch_id',$row->productionBatch->id)->where('item_shading_id',$row->item_shading_id)->where('area_id',$row->area_id)->where('item_id',$row->item_id)->count();
            if($cek == 0){
                ItemCogs::create([
                    'lookable_type'		        => $row->productionHandover->getTable(),
                    'lookable_id'		        => $row->productionHandover->id,
                    'detailable_type'	        => $row->getTable(),
                    'detailable_id'		        => $row->id,
                    'company_id'		        => $row->productionHandover->company_id,
                    'place_id'			        => $row->place_id,
                    'warehouse_id'		        => $row->warehouse_id,
                    'area_id'                   => $row->area_id,
                    'item_id'			        => $row->item_id,
                    'item_shading_id'	        => $row->item_shading_id,
                    'production_batch_id'       => $row->productionBatch->id,
                    'qty_in'			        => $qty,
                    'price_in'			        => $total / $qty,
                    'total_in'			        => $total,
                    'qty_final'			        => $qty_final,
                    'price_final'		        => round($total_final / $qty_final,5),
                    'total_final'		        => $total_final,
                    'date'				        => $dateloop,
                    'type'				        => 'IN',
                ]);
            }
            foreach($row->journalDetail as $rowjournal){
                $rowjournal->update([
                    'nominal_fc'  => $total,
                    'nominal'     => $total,
                ]);
            }
            $row->update([
                'total' => $total,
            ]);
            if($row->productionBatch()->exists()){
                $row->productionBatch->update([
                    'total' => $total,
                ]);
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $productionrepack = ProductionRepackDetail::whereHas('productionRepack',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_target_id',$item_id)
        ->where(function($query)use($area_id,$item_shading_id,$production_batch_id){
            if($area_id && $item_shading_id && $production_batch_id){
                $query->whereHas('productionBatch',function($query)use($area_id,$item_shading_id,$production_batch_id){
                    $query->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('id',$production_batch_id);
                });
            }
        })->get();

        foreach($productionrepack as $row){
            $total = $row->total;
            $qty = $row->qty;
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->productionRepack->getTable(),
                'lookable_id'		    => $row->productionRepack->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		    => $row->productionRepack->company_id,
                'place_id'			    => $row->place_id,
                'warehouse_id'		    => $row->warehouse_id,
                'item_id'			    => $row->item_target_id,
                'qty_in'			    => $qty,
                'price_in'			    => $total / $qty,
                'total_in'			    => $total,
                'qty_final'			    => $qty_final,
                'price_final'		    => round($total_final / $qty_final,5),
                'total_final'		    => $total_final,
                'date'				    => $dateloop,
                'type'				    => 'IN',
                'area_id'               => $row->area_id,
                'item_shading_id'       => $row->item_shading_id,
                'production_batch_id'   => $row->production_batch_id,
            ]);
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $landedcost = LandedCostDetail::whereHas('landedCost',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_id',$item_id)->get();

        foreach($landedcost as $row){
            $rowfc = $row->nominal;
            if($row->lookable_type == 'landed_cost_details'){
                $rowfc = round($row->nominal - $row->lookable->nominal,2);
                $rowtotal = round($row->nominal * $row->landedCost->currency_rate,2) - round($row->lookable->nominal * $row->lookable->landedCost->currency_rate,2);
            }else{
                $rowtotal = round($row->nominal * $row->landedCost->currency_rate,2);
            }
            if($qtyBefore > 0){
            $total = $rowtotal;
            $qty = 0;
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->landedCost->getTable(),
                'lookable_id'		      => $row->landedCost->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		      => $row->landedCost->company_id,
                'place_id'			      => $row->place_id,
                'warehouse_id'		    => $row->warehouse_id,
                'item_id'			        => $row->item_id,
                'qty_in'			        => $qty,
                'price_in'			      => 0,
                'total_in'			      => $total,
                'qty_final'			      => $qty_final,
                'price_final'		      => round($total_final / $qty_final,5),
                'total_final'		      => $total_final,
                'date'				        => $dateloop,
                'type'				        => 'IN'
            ]);
            foreach($row->journalDetail as $rowjournal){
                $rowjournal->update([
                    'nominal_fc'  => $rowfc,
                    'nominal'     => $total,
                ]);
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
            }
        }
    
        $revaluation = InventoryRevaluationDetail::whereHas('inventoryRevaluation',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_id',$item_id)->get();

        foreach($revaluation as $row){
            $total = $row->nominal;
            $qty = 0;
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            ItemCogs::create([
            'lookable_type'		    => $row->inventoryRevaluation->getTable(),
            'lookable_id'		      => $row->inventoryRevaluation->id,
            'detailable_type'	    => $row->getTable(),
            'detailable_id'		    => $row->id,
            'company_id'		      => $row->inventoryRevaluation->company_id,
            'place_id'			      => $row->place_id,
            'warehouse_id'		    => $row->warehouse_id,
            'item_id'			        => $row->item_id,
            'qty_in'			        => 0,
            'price_in'			      => 0,
            'total_in'			      => $total,
            'qty_final'			      => $qty_final,
            'price_final'		      => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
            'total_final'		      => $total_final,
            'date'				        => $dateloop,
            'type'				        => 'IN',
            'area_id'             => $row->itemStock->area()->exists() ? $row->itemStock->area_id : NULL,
            'item_shading_id'     => $row->itemStock->itemShading()->exists() ? $row->itemStock->item_shading_id : NULL,
            'production_batch_id' => $row->itemStock->productionBatch()->exists() ? $row->itemStock->production_batch_id : NULL,
            ]);
            foreach($row->journalDetail as $rowjournal){
            $rowjournal->update([
                'nominal_fc'  => $total,
                'nominal'     => $total,
            ]);
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $goodreturn = GoodReturnPODetail::whereHas('goodReturnPO',function($query)use($dateloop,$item_id){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_id',$item_id)->get();

        foreach($goodreturn as $row){
            $total = round($row->getRowTotal() * $row->goodReceiptDetail->purchaseOrderDetail->purchaseOrder->currency_rate,2);
            $qty = $row->qty;
            $total_final = $totalBefore - $total;
            $qty_final = $qtyBefore - $qty;
            $price = $total / $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->goodReturnPO->getTable(),
                'lookable_id'		    => $row->goodReturnPO->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		    => $row->goodReturnPO->company_id,
                'place_id'			    => $row->goodReceiptDetail->place_id,
                'warehouse_id'		    => $row->goodReceiptDetail->warehouse_id,
                'item_id'			    => $row->item_id,
                'qty_out'			    => $qty,
                'price_out'			    => $price,
                'total_out'			    => $total,
                'qty_final'			    => $qty_final,
                'price_final'		    => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
                'total_final'		    => $total_final,
                'date'				    => $dateloop,
                'type'				    => 'OUT',
                'area_id'               => NULL,
                'item_shading_id'       => NULL,
                'production_batch_id'   => NULL,
            ]);
            foreach($row->journalDetail as $rowjournal){
                $rowjournal->update([
                    'nominal_fc'  => $total,
                    'nominal'     => $total,
                ]);
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $goodissue = GoodIssueDetail::whereHas('goodIssue',function($query)use($dateloop,$item_id){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->whereHas('itemStock',function($query)use($item_id,$area_id,$item_shading_id,$production_batch_id){
            $query->where('item_id',$item_id)->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id);
        })->get();

        $tempgiprice = 0;
        foreach($goodissue as $row){
            if($row->itemStock->productionBatch()->exists() && $row->itemStock->area()->exists() && $row->itemStock->itemShading()->exists()){
                $price = $row->itemStock->priceFgNow($dateloop);
            }else{
                $price = round($qtyBefore,3) > 0 ? round($totalBefore,2) / round($qtyBefore,3) : 0;
                if($tempgiprice > 0){
                   $price = $tempgiprice;
               }else{
                   $tempgiprice = $price;
               }
            }
            $total = round($row->qty * $price,2);
            $qty = $row->qty;
            $total_final = $totalBefore - $total;
            $qty_final = $qtyBefore - $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->goodIssue->getTable(),
                'lookable_id'		      => $row->goodIssue->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		      => $row->goodIssue->company_id,
                'place_id'			      => $row->itemStock->place_id,
                'warehouse_id'		    => $row->itemStock->warehouse_id,
                'item_id'			        => $row->itemStock->item_id,
                'qty_out'			        => $qty,
                'price_out'			      => $price,
                'total_out'			      => $total,
                'qty_final'			      => $qty_final,
                'price_final'		      => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
                'total_final'		      => $total_final,
                'date'				        => $dateloop,
                'type'				        => 'OUT',
                'area_id'             => $row->itemStock->area()->exists() ? $row->itemStock->area_id : NULL,
                'item_shading_id'     => $row->itemStock->itemShading()->exists() ? $row->itemStock->item_shading_id : NULL,
                'production_batch_id' => $row->itemStock->productionBatch()->exists() ? $row->itemStock->production_batch_id : NULL,
            ]);
            foreach($row->journalDetail as $rowjournal){
                $rowjournal->update([
                    'nominal_fc'  => $total,
                    'nominal'     => $total,
                ]);
            }
            $row->update([
                'price' => $price,
                'total' => $total
            ]);
            if($row->goodReturnIssueDetail()->exists()){
                foreach($row->goodReturnIssueDetail as $rowretur){
                    $rowretur->update([
                        'total'   => round($price * $rowretur->qty,2),
                    ]);
                }
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
            $gi = GoodIssue::find($row->good_issue_id);
            if($gi){
                $gi->updateGrandtotal();
            }
        }

        $goodreturnissue = GoodReturnIssueDetail::whereHas('goodReturnIssue',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_id',$item_id)->get();

        foreach($goodreturnissue as $row){
            $total = $row->total;
            $qty = $row->qty;
            $price = $total / $qty;
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            ItemCogs::create([
            'lookable_type'		    => $row->goodReturnIssue->getTable(),
            'lookable_id'		      => $row->goodReturnIssue->id,
            'detailable_type'	    => $row->getTable(),
            'detailable_id'		    => $row->id,
            'company_id'		      => $row->goodReturnIssue->company_id,
            'place_id'			      => $row->goodIssueDetail->itemStock->place_id,
            'warehouse_id'		    => $row->goodIssueDetail->itemStock->warehouse_id,
            'item_id'			        => $row->goodIssueDetail->itemStock->item_id,
            'qty_in'			        => $qty,
            'price_in'			      => $price,
            'total_in'			      => $total,
            'qty_final'			      => $qty_final,
            'price_final'		      => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
            'total_final'		      => $total_final,
            'date'				        => $dateloop,
            'type'				        => 'IN',
            'area_id'             => $row->goodIssueDetail->itemStock->area()->exists() ? $row->goodIssueDetail->itemStock->area_id : NULL,
            'item_shading_id'     => $row->goodIssueDetail->itemStock->itemShading()->exists() ? $row->goodIssueDetail->itemStock->item_shading_id : NULL,
            'production_batch_id' => $row->goodIssueDetail->itemStock->productionBatch()->exists() ? $row->goodIssueDetail->itemStock->production_batch_id : NULL,
            ]);
            foreach($row->journalDetail as $rowjournal){
            $rowjournal->update([
                'nominal_fc'  => $total,
                'nominal'     => $total,
            ]);
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
            $gri = GoodReturnIssue::find($row->good_return_issue_id);
            if($gri){
            $gri->updateGrandtotal();
            }
        }

        $goodtransferout = InventoryTransferOutDetail::whereHas('inventoryTransferOut',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_id',$item_id)->get();

        foreach($goodtransferout as $row){
            $price = $row->item->priceNow($row->itemStock->place_id,$dateloop);
            $total = round($price * $row->qty,2);
            $qty = $row->qty;
            $total_final = $totalBefore - $total;
            $qty_final = $qtyBefore - $qty;
            ItemCogs::create([
            'lookable_type'		    => $row->inventoryTransferOut->getTable(),
            'lookable_id'		      => $row->inventoryTransferOut->id,
            'detailable_type'	    => $row->getTable(),
            'detailable_id'		    => $row->id,
            'company_id'		      => $row->inventoryTransferOut->company_id,
            'place_id'			      => $row->itemStock->place_id,
            'warehouse_id'		    => $row->itemStock->warehouse_id,
            'item_id'			        => $row->itemStock->item_id,
            'qty_out'			        => $qty,
            'price_out'			      => $price,
            'total_out'			      => $total,
            'qty_final'			      => $qty_final,
            'price_final'		      => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
            'total_final'		      => $total_final,
            'date'				        => $dateloop,
            'type'				        => 'OUT',
            'area_id'             => $row->itemStock->area()->exists() ? $row->itemStock->area_id : NULL,
            'item_shading_id'     => $row->itemStock->itemShading()->exists() ? $row->itemStock->item_shading_id : NULL,
            'production_batch_id' => $row->itemStock->productionBatch()->exists() ? $row->itemStock->production_batch_id : NULL,
            ]);
            foreach($row->journalDetail as $rowjournal){
            $rowjournal->update([
                'nominal_fc'  => $total,
                'nominal'     => $total,
            ]);
            }
            $row->update([
            'price' => $price,
            'total' => $total,
            ]);
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $goodtransferin = InventoryTransferOutDetail::whereHas('inventoryTransferOut',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop)->whereHas('inventoryTransferIn');
        })->where('item_id',$item_id)->get();

        foreach($goodtransferin as $row){
            $total = $row->total;
            $qty = $row->qty;
            $price = $total / $qty;
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->inventoryTransferOut->inventoryTransferIn->getTable(),
                'lookable_id'		      => $row->inventoryTransferOut->inventoryTransferIn->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		      => $row->inventoryTransferOut->inventoryTransferIn->company_id,
                'place_id'			      => $row->inventoryTransferOut->place_to,
                'warehouse_id'		    => $row->inventoryTransferOut->warehouse_to,
                'item_id'			        => $row->item_id,
                'qty_in'			        => $qty,
                'price_in'			      => $price,
                'total_in'			      => $total,
                'qty_final'			      => $qty_final,
                'price_final'		      => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
                'total_final'		      => $total_final,
                'date'				        => $dateloop,
                'type'				        => 'IN',
                'area_id'             => $row->area_id ?? NULL,
                'item_shading_id'     => $row->itemStock->itemShading()->exists() ? $row->itemStock->item_shading_id : NULL,
                'production_batch_id' => $row->itemStock->productionBatch()->exists() ? $row->itemStock->production_batch_id : NULL,
            ]);
            foreach($row->journalDetail as $rowjournal){
            $rowjournal->update([
                'nominal_fc'  => $total,
                'nominal'     => $total,
            ]);
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $productionissue = ProductionIssueDetail::whereHas('productionIssue',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })
        ->where('lookable_type','items')->where('lookable_id',$item_id)
        ->whereNull('is_wip')
        ->where(function($query)use($production_batch_id,$bomGroup){
            if($production_batch_id && $bomGroup !== '1'){
                $query->whereHas('productionBatchUsage',function($query)use($production_batch_id){
                    $query->where('production_batch_id',$production_batch_id);
                });
            }
        })
        ->get();

        $arrProductionReceive = [];
        $arrProductionFgReceive = [];
        $arrProductionIssue = [];

        foreach($productionissue as $row){
            $total = 0;
            if(!in_array($row->productionIssue->id,$arrProductionIssue)){
                $arrProductionIssue[] = $row->productionIssue->id;
            }
            if($row->productionBatchUsage()->exists()){
                foreach($row->productionBatchUsage as $rowbatch){
                    if($bomGroup == '1'){
                        $rowprice = round($qtyBefore,3) > 0 ? round($totalBefore,2) / round($qtyBefore,3) : 0;
                        $rowtotal = round($rowbatch->qty * $rowprice,2);
                        $rowqty = $rowbatch->qty;
                        $total += $rowtotal;
                        $totalBefore -= $rowtotal;
                        $qtyBefore -= $rowqty;
                        ItemCogs::create([
                            'lookable_type'		    => $row->productionIssue->getTable(),
                            'lookable_id'		    => $row->productionIssue->id,
                            'detailable_type'	    => $rowbatch->getTable(),
                            'detailable_id'		    => $rowbatch->id,
                            'company_id'		    => $row->productionIssue->company_id,
                            'place_id'			    => $rowbatch->productionBatch->place_id,
                            'warehouse_id'		    => $rowbatch->productionBatch->warehouse_id,
                            'item_id'			    => $rowbatch->productionBatch->item_id,
                            'qty_out'			    => $rowqty,
                            'price_out'			    => $rowprice,
                            'total_out'			    => $rowtotal,
                            'qty_final'			    => $qtyBefore,
                            'price_final'		    => round($qtyBefore,3) > 0 ? round(round($totalBefore,2) / round($qtyBefore,3),5) : 0,
                            'total_final'		    => $totalBefore,
                            'date'				    => $dateloop,
                            'type'				    => 'OUT',
                            'production_batch_id'   => $rowbatch->productionBatch->id,
                        ]);
                        foreach($rowbatch->journalDetail as $rowjournal){
                            $rowjournal->update([
                                'nominal_fc'  => $rowtotal,
                                'nominal'     => $rowtotal,
                            ]);
                        }
                    }else{
                        $cek = ItemCogs::where('detailable_type',$rowbatch->getTable())->where('detailable_id',$rowbatch->id)->count();
                        $rowtotal = $rowbatch->productionBatch->totalById($rowbatch->id);
                        $rowprice = $rowtotal / $rowbatch->qty;
                        $total += $rowtotal;
                        $totalBefore -= $rowtotal;
                        $qtyBefore -= $rowbatch->qty;
                        if($cek == 0){
                            ItemCogs::create([
                                'lookable_type'		        => $row->productionIssue->getTable(),
                                'lookable_id'		        => $row->productionIssue->id,
                                'detailable_type'	        => $rowbatch->getTable(),
                                'detailable_id'		        => $rowbatch->id,
                                'company_id'		        => $row->productionIssue->company_id,
                                'place_id'			        => $rowbatch->productionBatch->place_id,
                                'warehouse_id'		        => $rowbatch->productionBatch->warehouse_id,
                                'item_id'			        => $rowbatch->productionBatch->item_id,
                                'qty_out'			        => $rowbatch->qty,
                                'price_out'			        => $rowprice,
                                'total_out'			        => $rowtotal,
                                'qty_final'			        => $qtyBefore,
                                'price_final'		        => round($qtyBefore,3) > 0 ? round(round($totalBefore,2) / round($qtyBefore,3),5) : 0,
                                'total_final'		        => $totalBefore,
                                'date'				        => $dateloop,
                                'type'				        => 'OUT',
                                'production_batch_id'       => $rowbatch->productionBatch->id,
                            ]);
                        }
                        foreach($rowbatch->journalDetail as $rowjournal){
                            $rowjournal->update([
                                'nominal_fc'  => $rowtotal,
                                'nominal'     => $rowtotal,
                            ]);
                        }
                    }
                }
            }else{
                $rowprice = round($qtyBefore,3) > 0 ? round($totalBefore,2) / round($qtyBefore,3) : 0;
                $rowtotal = round($rowprice * $row->qty,2);
                $total += $rowtotal;
                $totalBefore -= $rowtotal;
                $qtyBefore -= $row->qty;
                ItemCogs::create([
                    'lookable_type'		    => $row->productionIssue->getTable(),
                    'lookable_id'		      => $row->productionIssue->id,
                    'detailable_type'	    => $row->getTable(),
                    'detailable_id'		    => $row->id,
                    'company_id'		      => $row->productionIssue->company_id,
                    'place_id'			      => $row->itemStock->place_id,
                    'warehouse_id'		    => $row->itemStock->warehouse_id,
                    'item_id'			        => $row->itemStock->item_id,
                    'qty_out'			        => $row->qty,
                    'price_out'			      => $rowprice,
                    'total_out'			      => $rowtotal,
                    'qty_final'			      => $qtyBefore,
                    'price_final'		      => round($qtyBefore,3) > 0 ? round(round($totalBefore,2) / round($qtyBefore,3),5) : 0,
                    'total_final'		      => $totalBefore,
                    'date'				        => $dateloop,
                    'type'				        => 'OUT',
                    'item_shading_id'     => $row->itemStock->itemShading()->exists() ? $row->itemStock->item_shading_id : NULL,
                    'production_batch_id' => $row->itemStock->productionBatch()->exists() ? $row->itemStock->production_batch_id : NULL,
                ]);
                if($row->journalDetail()->exists()){
                    foreach($row->journalDetail as $rowjournal){
                        $rowjournal->update([
                            'nominal_fc'  => $rowtotal,
                            'nominal'     => $rowtotal,
                        ]);
                    }
                }
            }
            $row->update([
                'nominal' => $row->qty > 0 ? $total / $row->qty : 0,
                'total'   => $total,
            ]);
            if($row->journalDetail()->exists() && $row->productionBatchUsage()->exists() && $bomGroup !== '1' && $bomGroup){
                foreach($row->journalDetail as $rowjournal){
                    $rowjournal->update([
                        'nominal_fc'  => $total,
                        'nominal'     => $total,
                    ]);
                }
            }
            
            if($row->productionIssue->productionFgReceive()->exists()){
                $productionFgReceive = ProductionFgReceive::where('id',$row->productionIssue->productionFgReceive->id)->whereIn('status',['2','3'])->first();
                if($productionFgReceive){
                    if(!in_array($productionFgReceive->id,$arrProductionFgReceive)){
                        $arrProductionFgReceive[] = $productionFgReceive->id;
                    }
                }
            }
        }

        if(count($arrProductionIssue) > 0){
            foreach($arrProductionIssue as $row){
                $issue = ProductionIssue::find($row);
                if($issue){
                    foreach($issue->journal->journalDetail()->where('type','1')->get() as $rowjournal){
                        $rowjournal->update([
                            'nominal_fc'  => $issue->total(),
                            'nominal'     => $issue->total(),
                        ]);
                    }
                    if($issue->productionReceiveIssue()->exists()){
                        foreach($issue->productionReceiveIssue as $rowreceiveissue){
                            $productionReceive = ProductionReceive::where('id',$rowreceiveissue->production_receive_id)->whereIn('status',['2','3'])->first();
                            if($productionReceive){
                                if(!in_array($productionReceive->id,$arrProductionReceive)){
                                    $arrProductionReceive[] = $productionReceive->id;
                                }
                            }
                        }
                    }
                }
            }
        }

        if(count($arrProductionReceive) > 0){
            foreach($arrProductionReceive as $row){
                ProductionReceive::find($row)->recalculate();
            }
        }

        if(count($arrProductionFgReceive) > 0){
            foreach($arrProductionFgReceive as $row){
                $pfr = ProductionFgReceive::find($row);
                $pfr->recalculate();
                /* self::dispatch($dateloop,$pfr->company_id,$pfr->place_id,$pfr->productionOrderDetail->productionScheduleDetail->item_id,NULL,NULL,NULL); */
            }
        }

        $productionhandoverout = ProductionHandover::whereIn('status',['2','3'])
                ->whereDate('post_date',$dateloop)->whereHas('productionFgReceive', function($query)use($item_id){
                    $query->where('item_id',$item_id)->whereHas('item',function($query){
                        $query->where('item_group_id','!=',7);
                    });
                })->get();

        foreach($productionhandoverout as $row){
            $qty = $row->qtyM2();
            $total = $row->totalHandover();
            $price = round($total / $qty,5);
            $total_final = $totalBefore - $total;
            $qty_final = $qtyBefore - $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->getTable(),
                'lookable_id'		    => $row->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		    => $row->company_id,
                'place_id'			    => $row->productionFgReceive->place_id,
                'warehouse_id'		    => $row->productionFgReceive->item->warehouse(),
                'item_id'			    => $row->productionFgReceive->item_id,
                'qty_out'			    => $qty,
                'price_out'			    => $price,
                'total_out'			    => $total,
                'qty_final'			    => $qty_final,
                'price_final'		    => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
                'total_final'		    => $total_final,
                'date'				    => $dateloop,
                'type'				    => 'OUT',
            ]);
            if($row->journal()->exists()){
                foreach($row->journal->journalDetail()->whereNull('detailable_type')->whereNull('detailable_id')->get() as $rowjournal){
                    $rowjournal->update([
                        'nominal_fc'  => $total,
                        'nominal'     => $total,
                    ]);
                }
            }
            $totalBefore = $total_final;
            $qtyBefore = $qty_final;
            /* self::dispatch($dateloop,$row->productionHandover->company_id,$row->productionHandover->productionFgReceive->place_id,$row->item_id,$row->area_id,$row->item_shading_id,$row->productionBatch->id); */
        }

        $productionrepack = ProductionRepackDetail::whereHas('productionRepack',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->where('item_source_id',$item_id)
        ->where(function($query)use($area_id,$item_shading_id,$production_batch_id){
            if($area_id && $item_shading_id && $production_batch_id){
                $query->whereHas('itemStock',function($query)use($area_id,$item_shading_id,$production_batch_id){
                    $query->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id);
                });
            }
        })->get();

        foreach($productionrepack as $row){
            $total = round($row->itemStock->priceFgNow($dateloop) * $row->qty,2);
            $qty = $row->qty;
            $price = $total / $qty;
            $total_final = $totalBefore - $total;
            $qty_final = $qtyBefore - $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->productionRepack->getTable(),
                'lookable_id'		    => $row->productionRepack->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		    => $row->productionRepack->company_id,
                'place_id'			    => $row->itemStock->place_id,
                'warehouse_id'		    => $row->itemStock->warehouse_id,
                'item_id'			    => $row->itemStock->item_id,
                'qty_out'			    => $qty,
                'price_out'			    => round($price,5),
                'total_out'			    => $total,
                'qty_final'			    => $qty_final,
                'price_final'		    => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
                'total_final'		    => $total_final,
                'date'				    => $dateloop,
                'type'				    => 'OUT',
                'area_id'               => $row->itemStock->area_id,
                'item_shading_id'       => $row->itemStock->item_shading_id,
                'production_batch_id'   => $row->itemStock->production_batch_id,
            ]);
            foreach($row->journalDetail as $rowjournal){
                $rowjournal->update([
                    'nominal_fc'  => $total,
                    'nominal'     => $total,
                ]);
            }
            $row->update([
                'total' => $total
            ]);
            $row->productionBatch->update([
                'total' => $total
            ]);
            /* self::dispatch($dateloop,$row->productionRepack->company_id,$row->place_id,$row->item_target_id,$row->area_id,$row->item_shading_id,$row->production_batch_id); */
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $marketingorderdelivery = MarketingOrderDeliveryProcessDetail::whereHas('marketingOrderDeliveryProcess',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop)
                ->whereHas('marketingOrderDeliveryProcessTrack',function($query){
                    $query->whereIn('status',['2']);
                });
        })->whereHas('itemStock',function($query)use($item_id,$place_id,$area_id,$item_shading_id,$production_batch_id){
            $query->where('item_id',$item_id)
                ->where('place_id',$place_id)
                ->where('area_id',$area_id)
                ->where('item_shading_id',$item_shading_id)
                ->where('production_batch_id',$production_batch_id);
        })->get();

        foreach($marketingorderdelivery as $row){
            $total = $row->getHpp();
            $qty = $row->qty * $row->marketingOrderDeliveryDetail->marketingOrderDetail->qty_conversion;
            $price = $total / $qty;
            $total_final = $totalBefore - $total;
            $qty_final = $qtyBefore - $qty;
            ItemCogs::create([
                'lookable_type'		    => $row->marketingOrderDeliveryProcess->getTable(),
                'lookable_id'		    => $row->marketingOrderDeliveryProcess->id,
                'detailable_type'	    => $row->getTable(),
                'detailable_id'		    => $row->id,
                'company_id'		    => $row->marketingOrderDeliveryProcess->company_id,
                'place_id'			    => $row->itemStock->place_id,
                'warehouse_id'		    => $row->itemStock->warehouse_id,
                'item_id'			    => $row->itemStock->item_id,
                'qty_out'			    => $qty,
                'price_out'			    => $price,
                'total_out'			    => $total,
                'qty_final'			    => $qty_final,
                'price_final'		    => $qty_final > 0 ? round($total_final / $qty_final,5) : 0,
                'total_final'		    => $total_final,
                'date'				    => $dateloop,
                'type'				    => 'OUT',
                'area_id'               => $row->itemStock->area()->exists() ? $row->itemStock->area_id : NULL,
                'item_shading_id'       => $row->itemStock->itemShading()->exists() ? $row->itemStock->item_shading_id : NULL,
                'production_batch_id'   => $row->itemStock->productionBatch()->exists() ? $row->itemStock->production_batch_id : NULL,
            ]);
            foreach($row->journalDetail as $rowjournal){
                $rowjournal->update([
                    'nominal_fc'  => $total,
                    'nominal'     => $total,
                ]);
            }
            $row->update([
                'total' => $total
            ]);
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }
      }
      CustomHelper::accumulateCogs($this->date,$company_id,$place_id,$item_id);
      $itemstock = ItemStock::where('item_id',$item_id)->where('place_id',$place_id)->where('warehouse_id',$item->warehouse())->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id)->first();
      if($itemstock){
          $itemstock->update([
              'qty'   => $itemstock->stockByDate(date('Y-m-d')),
          ]);
      }
    }
}

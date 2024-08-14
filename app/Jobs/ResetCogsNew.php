<?php

namespace App\Jobs;

use App\Exports\ExportInventoryTransferIn;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\GoodIssue;
use App\Models\GoodIssueDetail;
use App\Models\GoodReceipt;
use App\Models\GoodReceiptDetail;
use App\Models\GoodReceive;
use App\Models\GoodReceiveDetail;
use App\Models\GoodReturnIssue;
use App\Models\GoodReturnIssueDetail;
use App\Models\GoodReturnPO;
use App\Models\InventoryRevaluation;
use App\Models\InventoryRevaluationDetail;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferOut;
use App\Models\InventoryTransferOutDetail;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\Journal;
use App\Models\LandedCost;
use App\Models\LandedCostDetail;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\MarketingOrderDeliveryStock;
use App\Models\MarketingOrderReturnDetail;
use App\Models\ProductionFgReceive;
use App\Models\ProductionFgReceiveDetail;
use App\Models\ProductionHandoverDetail;
use App\Models\ProductionIssueDetail;
use App\Models\ProductionReceive;
use App\Models\ProductionReceiveDetail;
use App\Models\PurchaseMemo;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetCogsNew implements ShouldQueue
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
      $item = Item::find($this->item_id);
      $bomPowder = $item->bomPlace($this->place_id) ? $item->bomPlace($this->place_id)->first() : NULL;
      $bomGroup = '';
      if($bomPowder){
        $bomGroup = $bomPowder->group; 
      }

      if($bomPowder && $bomGroup == '1'){
        $itemcogs = ItemCogs::where('date','>=',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->orderBy('date')->delete();
        $old_data = ItemCogs::where('date','<',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->orderByDesc('date')->orderByDesc('id')->first();
      }else{
        $itemcogs = ItemCogs::where('date','>=',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->where('area_id',$this->area_id)->where('item_shading_id',$this->item_shading_id)->where('production_batch_id',$this->production_batch_id)->delete();
        $old_data = ItemCogs::where('date','<',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->where('area_id',$this->area_id)->where('item_shading_id',$this->item_shading_id)->where('production_batch_id',$this->production_batch_id)->orderByDesc('date')->orderByDesc('id')->first();
      }
      
      $today = date('Y-m-d');

      $period = CarbonPeriod::create($this->date, $today);

      $qtyBefore = 0;
      $totalBefore = 0;
      // Iterate over the period
      foreach ($period as $key => $date) {
          $dateloop = $date->format('Y-m-d');

          if($key == 0){
            if($old_data){
              $qtyBefore = $old_data->qty_final;
              $totalBefore = $old_data->total_final;
            }
          }

          $goodreceipt = GoodReceiptDetail::whereHas('goodReceipt',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
          })->where('item_id',$this->item_id)->get();

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
              'price_final'		      => $total_final / $qty_final,
              'total_final'		      => $total_final,
              'date'				        => $dateloop,
              'type'				        => 'IN'
            ]);
            foreach($row->journalDetail as $rowjournal){
              $rowjournal->update([
                'nominal_fc'  => $row->total,
                'nominal'     => $total,
              ]);
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
          }
    
          $goodreceive = GoodReceiveDetail::whereHas('goodReceive',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
          })->where('item_id',$this->item_id)->get();

          foreach($goodreceive as $row){
            $total = $row->total;
            $qty = $row->qty;
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
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
              'price_final'		      => $total_final / $qty_final,
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

          $productionreceive = ProductionReceiveDetail::whereHas('productionReceive',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
          })->where('item_id',$this->item_id)->get();

          foreach($productionreceive as $row){
            if($row->productionBatch()->exists()){
              foreach($row->productionBatch as $rowbatch){
                $total = $rowbatch->total;
                $qty = $rowbatch->qty_real;
                $total_final = $totalBefore + $total;
                $qty_final = $qtyBefore + $qty;
                ItemCogs::create([
                  'lookable_type'		    => $row->productionReceive->getTable(),
                  'lookable_id'		      => $row->productionReceive->id,
                  'detailable_type'	    => $rowbatch->getTable(),
                  'detailable_id'		    => $rowbatch->id,
                  'company_id'		      => $row->productionReceive->company_id,
                  'place_id'			      => $row->place_id,
                  'warehouse_id'		    => $row->warehouse_id,
                  'item_id'			        => $row->item_id,
                  'production_batch_id' => $rowbatch->id,
                  'qty_in'			        => $qty,
                  'price_in'			      => $qty > 0 ? $total / $qty : 0,
                  'total_in'			      => $total,
                  'qty_final'			      => $qty_final,
                  'price_final'		      => $qty_final > 0 ? $total_final / $qty_final : 0,
                  'total_final'		      => $total_final,
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
                'price_final'		      => $total_final / $qty_final,
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
            $row->productionReceive->journal->journalDetail()->where('type','2')->update([
              'nominal_fc'  => $row->productionReceive->total(),
              'nominal'     => $row->productionReceive->total(),
            ]);
          }

          $productionfgreceive = ProductionFgReceive::whereHas('productionOrderDetail',function($query){
            $query->whereHas('productionScheduleDetail',function($query){
              $query->where('item_id',$this->item_id);
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
              'price_final'		      => $total_final / $qty_final,
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
          })->where('item_id',$this->item_id)->get();

          foreach($productionhandover as $row){
            $total = $row->total;
            $qty = round($row->qty * $row->productionFgReceiveDetail->conversion,3);
            $total_final = $totalBefore + $total;
            $qty_final = $qtyBefore + $qty;
            ItemCogs::create([
              'lookable_type'		    => $row->productionHandover->getTable(),
              'lookable_id'		      => $row->productionHandover->id,
              'detailable_type'	    => $row->getTable(),
              'detailable_id'		    => $row->id,
              'company_id'		      => $row->productionHandover->company_id,
              'place_id'			      => $row->place_id,
              'warehouse_id'		    => $row->warehouse_id,
              'item_id'			        => $row->item_id,
              'qty_in'			        => $qty,
              'price_in'			      => $total / $qty,
              'total_in'			      => $total,
              'qty_final'			      => $qty_final,
              'price_final'		      => $total_final / $qty_final,
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

          $landedcost = LandedCostDetail::whereHas('landedCost',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
          })->where('item_id',$this->item_id)->get();

          foreach($landedcost as $row){
            if($row->lookable_type == 'landed_cost_details'){
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
                'price_final'		      => $total_final / $qty_final,
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
          }
    
          $revaluation = InventoryRevaluationDetail::whereHas('inventoryRevaluation',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
          })->where('item_id',$this->item_id)->get();

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
              'price_final'		      => $qty_final > 0 ? $total_final / $qty_final : 0,
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

          $goodissue = GoodIssueDetail::whereHas('goodIssue',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
          })->whereHas('itemStock',function($query){
            $query->where('item_id',$this->item_id);
          })->get();

          foreach($goodissue as $row){
            $price = $qtyBefore > 0 ? $totalBefore / $qtyBefore : 0;
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
              'price_final'		      => $qty_final > 0 ? $total_final / $qty_final : 0,
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
          })->where('item_id',$this->item_id)->get();

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
              'price_final'		      => $qty_final > 0 ? $total_final / $qty_final : 0,
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
          })->where('item_id',$this->item_id)->get();

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
              'price_final'		      => $qty_final > 0 ? $total_final / $qty_final : 0,
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
          })->where('item_id',$this->item_id)->get();

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
              'price_final'		      => $qty_final > 0 ? $total_final / $qty_final : 0,
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
          })->where('lookable_type','items')->where('lookable_id',$this->item_id)->whereNull('is_wip')->get();

          foreach($productionissue as $row){
            $total = 0;
            if($row->productionBatchUsage()->exists()){
              foreach($row->productionBatchUsage as $rowbatch){
                if($bomGroup == '1'){
                  $rowprice = $qtyBefore > 0 ? $totalBefore / $qtyBefore : 0;
                  $rowtotal = round($rowbatch->qty * $rowprice,2);
                  $rowqty = $rowbatch->qty;
                  $total_final = $totalBefore - $rowtotal;
                  $qty_final = $qtyBefore - $rowqty;
                  $total += $rowtotal;
                  $totalBefore -= $rowtotal;
                  ItemCogs::create([
                    'lookable_type'		    => $row->productionIssue->getTable(),
                    'lookable_id'		      => $row->productionIssue->id,
                    'detailable_type'	    => $rowbatch->getTable(),
                    'detailable_id'		    => $rowbatch->id,
                    'company_id'		      => $row->productionIssue->company_id,
                    'place_id'			      => $rowbatch->productionBatch->place_id,
                    'warehouse_id'		    => $rowbatch->productionBatch->warehouse_id,
                    'item_id'			        => $rowbatch->productionBatch->item_id,
                    'qty_out'			        => $rowqty,
                    'price_out'			      => $rowprice,
                    'total_out'			      => $rowtotal,
                    'qty_final'			      => $qty_final,
                    'price_final'		      => $qty_final > 0 ? $total_final / $qty_final : 0,
                    'total_final'		      => $total_final,
                    'date'				        => $dateloop,
                    'type'				        => 'OUT',
                    'production_batch_id' => $rowbatch->productionBatch->id,
                  ]);
                  foreach($rowbatch->journalDetail as $rowjournal){
                    $rowjournal->update([
                      'nominal_fc'  => $rowtotal,
                      'nominal'     => $rowtotal,
                    ]);
                  }
                }else{
                  $rowprice = $rowbatch->productionBatch->total / $rowbatch->productionBatch->qty_real;
                  $rowtotal = round($rowprice * $rowbatch->qty,2);
                  $total += $rowtotal;
                  $totalBefore -= $rowtotal;
                  $qtyBefore -= $rowbatch->qty;
                  ItemCogs::create([
                    'lookable_type'		    => $row->productionIssue->getTable(),
                    'lookable_id'		      => $row->productionIssue->id,
                    'detailable_type'	    => $rowbatch->getTable(),
                    'detailable_id'		    => $rowbatch->id,
                    'company_id'		      => $row->productionIssue->company_id,
                    'place_id'			      => $rowbatch->productionBatch->place_id,
                    'warehouse_id'		    => $rowbatch->productionBatch->warehouse_id,
                    'item_id'			        => $rowbatch->productionBatch->item_id,
                    'qty_out'			        => $rowbatch->qty,
                    'price_out'			      => $rowprice,
                    'total_out'			      => $rowtotal,
                    'qty_final'			      => $qtyBefore,
                    'price_final'		      => $qtyBefore > 0 ? $totalBefore / $qtyBefore : 0,
                    'total_final'		      => $totalBefore,
                    'date'				        => $dateloop,
                    'type'				        => 'OUT',
                    'production_batch_id' => $rowbatch->productionBatch->id,
                  ]);
                  foreach($rowbatch->journalDetail as $rowjournal){
                    $rowjournal->update([
                      'nominal_fc'  => $rowtotal,
                      'nominal'     => $rowtotal,
                    ]);
                  }
                }
              }
            }else{
              $rowprice = $totalBefore / $qtyBefore;
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
                'price_final'		      => $qtyBefore > 0 ? $totalBefore / $qtyBefore : 0,
                'total_final'		      => $totalBefore,
                'date'				        => $dateloop,
                'type'				        => 'OUT',
                'item_shading_id'     => $row->itemStock->itemShading()->exists() ? $row->itemStock->item_shading_id : NULL,
                'production_batch_id' => $row->itemStock->productionBatch()->exists() ? $row->itemStock->production_batch_id : NULL,
              ]);
              foreach($row->journalDetail as $rowjournal){
                $rowjournal->update([
                  'nominal_fc'  => $total,
                  'nominal'     => $total,
                ]);
              }
            }
            $row->update([
              'nominal' => $total / $row->qty,
              'total'   => $total,
            ]);
            if($row->productionIssue->productionReceiveIssue()->exists()){
              $productionReceive = ProductionReceive::find($row->productionIssue->productionReceiveIssue->production_receive_id);
              if($productionReceive){
                $productionReceive->recalculate();
                foreach($productionReceive->productionReceiveDetail as $rowreceive){
                  if($rowreceive->productionBatch()->exists()){
                    foreach($rowreceive->productionBatch as $rowbatch){
                      ResetCogsNew::dispatch($dateloop,$productionReceive->company_id,$rowbatch->place_id,$rowbatch->item_id,NULL,NULL,$rowbatch->id);
                    }
                  }
                }
              }
            }
            if($row->productionIssue->productionFgReceive()->exists()){
              $productionFgReceive = ProductionFgReceive::find($row->productionIssue->productionFgReceive->id);
              if($productionFgReceive){
                $productionFgReceive->recalculate($dateloop);
                ResetCogsNew::dispatch($dateloop,$productionFgReceive->company_id,$productionFgReceive->place_id,$productionFgReceive->productionOrderDetail->productionScheduleDetail->item_id,NULL,NULL,NULL);
              }
            }
            $row->productionIssue->journal->journalDetail()->where('type','1')->update([
              'nominal_fc'  => $row->productionIssue->total(),
              'nominal'     => $row->productionIssue->total(),
            ]);
          }

          $productionhandoverout = ProductionHandoverDetail::whereHas('productionHandover',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
          })->whereHas('productionFgReceiveDetail',function($query){
            $query->where('item_id',$this->item_id);
          })->get();

          foreach($productionhandoverout as $row){
            $qty = round($row->qty * $row->productionFgReceiveDetail->conversion,3);
            $total_final = $totalBefore - $row->total;
            $qty_final = $qtyBefore - $qty;
            ItemCogs::create([
              'lookable_type'		    => $row->productionHandover->getTable(),
              'lookable_id'		      => $row->productionHandover->id,
              'detailable_type'	    => $row->getTable(),
              'detailable_id'		    => $row->id,
              'company_id'		      => $row->productionHandover->company_id,
              'place_id'			      => $row->productionHandover->productionFgReceive->place_id,
              'warehouse_id'		    => $row->productionHandover->productionFgReceive->productionOrderDetail->productionScheduleDetail->item->warehouse(),
              'item_id'			        => $row->productionHandover->productionFgReceive->productionOrderDetail->productionScheduleDetail->item_id,
              'qty_out'			        => $qty,
              'price_out'			      => $row->productionFgReceiveDetail->productionBatch->price(),
              'total_out'			      => $row->total,
              'qty_final'			      => $qty_final,
              'price_final'		      => $qty_final > 0 ? $total_final / $qty_final : 0,
              'total_final'		      => $total_final,
              'date'				        => $dateloop,
              'type'				        => 'OUT',
              'production_batch_id' => $row->productionFgReceiveDetail->productionBatch->id,
            ]);
            foreach($row->journalDetail as $rowjournal){
              $rowjournal->update([
                'nominal_fc'  => $total,
                'nominal'     => $total,
              ]);
            }
            $totalBefore = $total_final;
            $qtyBefore = $qty_final;
          }

          /* $marketingorderdelivery = MarketingOrderDeliveryStock::whereHas('marketingOrderDelivery',function($query)use($dateloop){
            $query->whereHas('marketingOrderDeliveryProcess',function($query)use($dateloop){
              $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
            });
          })->whereHas('itemStock',function($query){
            $query->where('item_id',$this->item_id);
          })->get();

          foreach($goodissue as $row){
            $price = $qtyBefore > 0 ? $totalBefore / $qtyBefore : 0;
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
              'price_final'		      => $qty_final > 0 ? $total_final / $qty_final : 0,
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

          $marketingorderreturn = MarketingOrderReturnDetail::whereHas('marketingOrderReturn',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
          })->where('item_id',$this->item_id)->get(); */
      }

      if($bomGroup == '2' || $bomGroup == '3'){
        $itemcogs2 = ItemCogs::where('date','>=',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->orderBy('date')->orderBy('id')->get();
        $old_data2 = ItemCogs::where('date','<',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->orderByDesc('date')->orderByDesc('id')->first();
  
        $total_final = 0;
        $qty_final = 0;
        $price_final = 0;
        foreach($itemcogs2 as $key => $row){
          if($key == 0){
            if($old_data2){
              if($row->type == 'IN'){
                $total_final = $old_data->total_final + $row->total_in;
                $qty_final = $old_data->qty_final + $row->qty_in;
              }elseif($row->type == 'OUT'){
                $total_final = $old_data->total_final - $row->total_out;
                $qty_final = $old_data->qty_final - $row->qty_out;
              }
  
              $price_final = $qty_final > 0 ? $total_final / $qty_final : 0;
            }else{
              if($row->type == 'IN'){
                $total_final = $row->total_in;
                $qty_final = $row->qty_in;
              }elseif($row->type == 'OUT'){
                $total_final = 0 - $row->total_out;
                $qty_final = 0 - $row->qty_out;
              }
  
              $price_final = $qty_final > 0 ? $total_final / $qty_final : 0;
            }
            $row->update([
              'price_final'	=> $price_final,
              'qty_final'		=> $qty_final,
              'total_final'	=> $total_final,
            ]);
          }else{
            if($row->type == 'IN'){
              $total_final += $row->total_in;
              $qty_final += $row->qty_in;
            }elseif($row->type == 'OUT'){
              $total_final -= $row->total_out;
              $qty_final -= $row->qty_out;
            }
            $price_final = $qty_final > 0 ? $total_final / $qty_final : 0;
            $row->update([
              'price_final'	=> $price_final,
              'qty_final'		=> $qty_final,
              'total_final'	=> $total_final,
            ]);
          }
        }
      }
    }
}

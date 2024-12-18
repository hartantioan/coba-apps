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
use App\Models\IssueGlaze;
use App\Models\IssueGlazeDetail;
use App\Models\ReceiveGlaze;
use App\Models\ReceiveGlazeDetail;
use App\Models\PurchaseMemo;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetCogsNewNonAccumulateFg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $company_id, $date,$place_id,$item_id,$area_id,$item_shading_id,$production_batch_id,$end_date;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $date = null, int $company_id = null, int $place_id = null, int $item_id = null, int $area_id = null, int $item_shading_id = null, int $production_batch_id = null, string $end_date = null)
    {
		$this->company_id = $company_id;
        $this->date = $date;
        $this->place_id = $place_id;
        $this->item_id = $item_id;
        $this->area_id = $area_id;
        $this->item_shading_id = $item_shading_id;
        $this->production_batch_id = $production_batch_id;
        $this->end_date = $end_date;
        $this->queue = 'cogsbydatenonaccumulate';
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
      $end_date = $this->end_date;
      $production_batch_id = $this->production_batch_id;
      $area_id = $this->area_id;
      $item_shading_id = $this->item_shading_id;

    $itemcogs = ItemCogs::where('date','>=',$date)->where('date','<=',$end_date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id)->delete();
    $old_data = ItemCogs::where('date','<',$date)->where('company_id',$company_id)->where('place_id',$place_id)->where('item_id',$item_id)->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id)->orderByDesc('date')->orderByDesc('id')->first();
      
      $today = $end_date;

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

                if($row->journalDetail()->exists()){
                    foreach($row->journalDetail as $rowjournal){
                        $rowjournal->update([
                            'nominal_fc'  => $total,
                            'nominal'     => $total,
                        ]);
                    }
                }
            }
            $qtyBefore = $qty_final;
            $totalBefore = $total_final;
        }

        $productionhandover = ProductionHandoverDetail::whereHas('productionHandover',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->whereHas('productionBatch',function($query)use($area_id,$item_shading_id,$production_batch_id){
            $query->where('id',$production_batch_id)->where('item_shading_id',$item_shading_id)->where('area_id',$area_id);
        })->where('item_id',$item_id)->get();

        $arrHandover = [];
        foreach($productionhandover as $row){
            if(!in_array($row->productionHandover->id,$arrHandover)){
                $arrHandover[] = $row->productionHandover->id;
            }
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

        if(count($arrHandover) > 0){
            foreach($arrHandover as $rowhandover){
                $datahandover = ProductionHandover::find($rowhandover);
                if($datahandover){
                    if($datahandover->journal()->exists()){
                        $totalhandover = $datahandover->totalHandover();
                        $datahandover->journal->journalDetail()->whereNull('detailable_type')->whereNull('detailable_id')->update([
                            'nominal'       => $totalhandover,
                            'nominal_fc'    => $totalhandover,
                        ]);
                    }
                }
            }
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
    
        $revaluation = InventoryRevaluationDetail::whereHas('inventoryRevaluation',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })->whereHas('itemStock',function($query)use($item_id,$area_id,$item_shading_id,$production_batch_id){
            $query->where('item_id',$item_id)->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id);
        })->get();

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
                /* if($tempgiprice > 0){
                   $price = $tempgiprice;
               }else{
                   $tempgiprice = $price;
               } */
            }
            $total = round($row->qty * $price,2);
            $qty = $row->qty;
            $total_final = round($totalBefore,2) - $total;
            $qty_final = round($qtyBefore,3) - $qty;
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

        $goodtransferout = InventoryTransferOutDetail::whereHas('inventoryTransferOut',function($query)use($dateloop){
            $query->whereIn('status',['2','3'])->whereDate('post_date',$dateloop);
        })
        ->whereHas('itemStock',function($query)use($item_id,$area_id,$item_shading_id,$production_batch_id){
            $query->where('item_id',$item_id)->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id);
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
        })
        ->whereHas('itemStock',function($query)use($item_id,$area_id,$item_shading_id,$production_batch_id){
            $query->where('item_id',$item_id)->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id);
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
            self::dispatch($dateloop,$row->productionRepack->company_id,$row->place_id,$row->item_target_id,$row->area_id,$row->item_shading_id,$row->production_batch_id,$end_date);
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
      $itemstock = ItemStock::where('item_id',$item_id)->where('place_id',$place_id)->where('warehouse_id',$item->warehouse())->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id)->first();
      if($itemstock){
        $itemstock->update([
            'qty'   => $itemstock->stockByDate(date('Y-m-d')),
        ]);
      }
    }
}

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

class CountStockPerBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $company_id, $date,$place_id,$item_id,$area_id,$item_shading_id,$production_batch_id,$end_date;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $company_id = null, int $place_id = null, int $item_id = null, int $area_id = null, int $item_shading_id = null, int $production_batch_id = null)
    {
		$this->company_id = $company_id;
        $this->place_id = $place_id;
        $this->item_id = $item_id;
        $this->area_id = $area_id;
        $this->item_shading_id = $item_shading_id;
        $this->production_batch_id = $production_batch_id;
        $this->queue = 'countstockperbatch';
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
      $production_batch_id = $this->production_batch_id;
      $area_id = $this->area_id;
      $item_shading_id = $this->item_shading_id;
      $itemstock = ItemStock::where('item_id',$item_id)->where('place_id',$place_id)->where('warehouse_id',$item->warehouse())->where('area_id',$area_id)->where('item_shading_id',$item_shading_id)->where('production_batch_id',$production_batch_id)->first();
      if($itemstock){
        $itemstock->update([
            'qty'   => $itemstock->stockByDate(date('Y-m-d')),
        ]);
      }
    }
}

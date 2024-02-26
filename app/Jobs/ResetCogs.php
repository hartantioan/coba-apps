<?php

namespace App\Jobs;

use App\Helpers\CustomHelper;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReceive;
use App\Models\GoodReturnIssue;
use App\Models\GoodReturnPO;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferOut;
use App\Models\ItemCogs;
use App\Models\Journal;
use App\Models\LandedCost;
use App\Models\MarketingOrderDeliveryProcess;
use App\Models\PurchaseMemo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetCogs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $date,$place_id,$item_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $date = null, int $place_id = null)
    {
        $this->date = $date;
        $this->place_id = $place_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$itemcogs = ItemCogs::where('post_date','>=',$this->date)->where('place_id',$this->place_id)->get();

		foreach($itemcogs as $row){
			$journal = Journal::where('lookable_type',$row->lookable_type)->where('lookable_id',$row->lookable_id)->get();
			if($journal){
				foreach($journal as $rowjournal){
					$rowjournal->journalDetail()->delete();
					$rowjournal->delete;
				}
			}
			$qty = $row->type == 'IN' ? $row->qty_in : $row->qty_out;
			CustomHelper::resetStock($row->place_id,$row->warehouse_id,$row->area_id,$row->item_id,$row->item_shading_i,$qty,$row->type);
			$row->delete();
		}

		$gr = GoodReceipt::whereIn('status',['2','3'])->whereDate('post_date','>=',$this->date)->get();
		$grcv = GoodReceive::whereIn('status',['2','3'])->whereDate('post_date','>=',$this->date)->get();
		$lc = LandedCost::whereIn('status',['2','3'])->whereDate('post_date','>=',$this->date)->get();
		$gi = GoodIssue::whereIn('status',['2','3'])->whereDate('post_date','>=',$this->date)->get();
		$grt = GoodReturnPO::whereIn('status',['2','3'])->whereDate('post_date','>=',$this->date)->get();
		$gri = GoodReturnIssue::whereIn('status',['2','3'])->whereDate('post_date','>=',$this->date)->get();
		$pm = PurchaseMemo::whereIn('status',['2','3'])->whereDate('post_date','>=',$this->date)->get();

		$data = [];

		foreach($gr as $row){
			$data[] = [
				'type'          => 0,
				'date'          => $row->post_date,
				'lookable_type' => $row->getTable(),
				'lookable_id'   => $row->id,
			];
		}

		foreach($grt as $row){
			$data[] = [
				'type'          => 1,
				'date'          => $row->post_date,
				'lookable_type' => $row->getTable(),
				'lookable_id'   => $row->id,
			];
		}

		foreach($grcv as $row){
			$data[] = [
				'type'          => 0,
				'date'          => $row->post_date,
				'lookable_type' => $row->getTable(),
				'lookable_id'   => $row->id,
			];
		}

		foreach($lc as $row){
			$data[] = [
				'type'          => 1,
				'date'          => $row->post_date,
				'lookable_type' => $row->getTable(),
				'lookable_id'   => $row->id,
			];
		}

		foreach($gi as $row){
			$data[] = [
				'type'          => 2,
				'date'          => $row->post_date,
				'lookable_type' => $row->getTable(),
				'lookable_id'   => $row->id,
			];
		}

		foreach($gri as $row){
			$data[] = [
				'type'          => 3,
				'date'          => $row->post_date,
				'lookable_type' => $row->getTable(),
				'lookable_id'   => $row->id,
			];
		}

		foreach($pm as $row){
			$data[] = [
				'type'          => 4,
				'date'          => $row->post_date,
				'lookable_type' => $row->getTable(),
				'lookable_id'   => $row->id,
			];
		}

		$collection = collect($data)->sortBy(function($item) {
						return [$item['date'], $item['type']];
					})->values();

		foreach($collection as $row){
			CustomHelper::sendCogsFromReset($row['lookable_type'],$row['lookable_id']);
		}
    }
}

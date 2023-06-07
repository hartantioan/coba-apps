<?php

namespace App\Jobs;

use App\Helpers\CustomHelper;
use App\Models\GoodIssue;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferOut;
use App\Models\ItemCogs;
use App\Models\Journal;
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
    public function __construct(string $date = null, int $place_id = null, int $item_id = null)
    {
        $this->date = $date;
        $this->place_id = $place_id;
        $this->item_id = $item_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = ItemCogs::where('place_id',$this->place_id)->where('item_id',$this->item_id)->whereDate('date','>=',$this->date)->orderBy('date')->orderBy('id')->get();
		$databefore = ItemCogs::where('place_id',$this->place_id)->where('item_id',$this->item_id)->whereDate('date','<',$this->date)->orderByDesc('date')->orderByDesc('id')->first();

		foreach($data as $key => $row){
			if($key == 0){
				if($row->type == 'IN'){
					if($databefore){
						$finalprice = round(($databefore->total_final + $row->total_in) / ($databefore->qty_final + $row->qty_in),2);
						$totalprice = round($databefore->total_final + $row->total_in,2);
						$qtyfinal = $databefore->qty_final + $row->qty_in;
					}else{
						$finalprice = $row->qty_in > 0 ? round($row->total_in / $row->qty_in,2) : 0;
						$totalprice = round($finalprice * $row->qty_in,2);
						$qtyfinal = $row->qty_in;
					}
					$row->update([
						'qty_final' 	=> $qtyfinal,
						'price_final'	=> $finalprice,
						'total_final'	=> $totalprice
					]);
				}
			}else{
				$prevqty = $data[$key-1]->qty_final;
				$prevtotal = $data[$key-1]->total_final;
				if($row->type == 'IN'){
					if($row->lookable_type == 'inventory_transfer_ins'){
						$it = InventoryTransferIn::find($row->lookable_id);
						$it->updateJournal();
					}

					$finalprice = round(($prevtotal + $row->total_in) / ($prevqty + $row->qty_in),2);
					$totalprice = $prevtotal + $row->total_in;
					$row->update([
						'qty_final' 	=> $prevqty + $row->qty_in,
						'price_final'	=> $finalprice,
						'total_final'	=> $totalprice
					]);
				}elseif($row->type == 'OUT'){
					if($row->lookable_type == 'good_issues' || $row->lookable_type == 'inventory_transfer_outs'){
						$prevprice = $data[$key-1]->price_final;
						if($row->lookable_type == 'good_issues'){
							$gi = GoodIssue::find($row->lookable_id);
							$journal = Journal::where('lookable_type',$row->lookable_type)->where('lookable_id',$row->lookable_id)->first();
							foreach($gi->goodIssueDetail()->where('item_id',$row->item_id)->get() as $rowgid){
								$rowgid->update([
									'price'	=> $prevprice,
									'total'	=> $prevprice * $rowgid->qty,
								]);
								if($journal){
									foreach($journal->journalDetail()->where('item_id',$rowgid->item_id)->get() as $rowupdate){
										$rowupdate->update([
											'nominal'   => $prevprice * $rowgid->qty
										]);
									}
								}
							}
							GoodIssue::find($row->lookable_id)->updateGrandtotal();
						}elseif($row->lookable_type == 'inventory_transfer_outs'){
							$it = InventoryTransferOut::find($row->lookable_id);
							$it->updateJournal();
						}
						$finalprice = $prevprice;
						$totalprice = $prevtotal - $row->total_out;
						$row->update([
							'price_out'		=> $finalprice,
							'total_out'		=> $finalprice * $row->qty_out,
							'qty_final' 	=> $prevqty - $row->qty_out,
							'price_final'	=> $finalprice,
							'total_final'	=> $totalprice
						]);
					}else{
						$prevprice = round(($prevtotal - $row->total_out) / ($prevqty - $row->qty_out),2);
						$finalprice = $prevprice;
						$totalprice = $prevtotal - $row->total_out;
						$row->update([
							'qty_final' 	=> $prevqty - $row->qty_out,
							'price_final'	=> $finalprice,
							'total_final'	=> $totalprice
						]);
					}
				}
			}
		}
    }
}

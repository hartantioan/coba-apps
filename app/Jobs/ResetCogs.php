<?php

namespace App\Jobs;

use App\Helpers\CustomHelper;
use App\Models\ItemCogs;
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

		foreach($data as $key => $row){
			if($key == 0){
				if($row->type == 'IN'){
					$finalprice = $row->total_in / $row->qty_in;
					$totalprice = $finalprice * $row->qty_in;
					$row->update([
						'qty_final' 	=> $row->qty_in,
						'price_final'	=> $finalprice,
						'total_final'	=> $totalprice
					]);
				}
			}else{
				$prevqty = $data[$key-1]->qty_final;
				$prevtotal = $data[$key-1]->total_final;
				if($row->type == 'IN'){
					$finalprice = ($prevtotal + $row->total_in) / ($prevqty + $row->qty_in);
					$totalprice = $finalprice * ($prevqty + $row->qty_in);
					$row->update([
						'qty_final' 	=> $prevqty + $row->qty_in,
						'price_final'	=> $finalprice,
						'total_final'	=> $totalprice
					]);
				}elseif($row->type == 'OUT'){
					if($row->lookable_type == 'good_issues' || $row->lookable_type == 'inventory_transfers'){
						$prevprice = $data[$key-1]->price_final;
						if($row->lookable_type == 'good_issues'){
							$gi = $row->lookable;
							foreach($gi->goodIssueDetail()->where('item_id',$row->item_id)->get() as $rowgid){
								$rowgid->update([
									'price'	=> $prevprice,
									'total'	=> $prevprice * $rowgid->qty,
								]);
							}
							$gi->updateGrandtotal();
						}
						$finalprice = $prevprice;
						$totalprice = $finalprice * ($prevqty - $row->qty_out);
						$row->update([
							'price_out'		=> $finalprice,
							'total_out'		=> $finalprice * $row->qty_out,
							'qty_final' 	=> $prevqty - $row->qty_out,
							'price_final'	=> $finalprice,
							'total_final'	=> $totalprice
						]);
					}else{
						$prevprice = ($prevtotal - $row->total_out) / ($prevqty - $row->qty_out);
						$finalprice = $prevprice;
						$totalprice = $finalprice * ($prevqty - $row->qty_out);
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

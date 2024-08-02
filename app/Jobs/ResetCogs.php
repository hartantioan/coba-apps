<?php

namespace App\Jobs;

use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Models\GoodIssue;
use App\Models\GoodReceipt;
use App\Models\GoodReceive;
use App\Models\GoodReturnIssue;
use App\Models\GoodReturnPO;
use App\Models\InventoryTransferIn;
use App\Models\InventoryTransferOut;
use App\Models\Item;
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
			$itemcogs = ItemCogs::where('date','>=',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->orderBy('date')->orderBy('id')->get();
			$old_data = ItemCogs::where('date','<',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->orderByDesc('date')->orderByDesc('id')->first();
		}else{
			$itemcogs = ItemCogs::where('date','>=',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->where('area_id',$this->area_id)->where('item_shading_id',$this->item_shading_id)->where('production_batch_id',$this->production_batch_id)->orderBy('date')->orderBy('id')->get();
			$old_data = ItemCogs::where('date','<',$this->date)->where('company_id',$this->company_id)->where('place_id',$this->place_id)->where('item_id',$this->item_id)->where('area_id',$this->area_id)->where('item_shading_id',$this->item_shading_id)->where('production_batch_id',$this->production_batch_id)->orderByDesc('date')->orderByDesc('id')->first();
		}
		
		$total_final = 0;
		$qty_final = 0;
		$price_final = 0;
		foreach($itemcogs as $key => $row){
			if($key == 0){
				if($old_data){
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

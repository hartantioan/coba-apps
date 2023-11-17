<?php

namespace App\Jobs;

use App\Models\ItemStock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResetStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $place_id,$warehouse_id,$item_id,$qty,$type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($place_id,$warehouse_id,$area_id,$item_id,$qty,$type)
    {
        $this->place_id = $place_id;
        $this->warehouse_id = $warehouse_id;
        $this->area_id = $area_id;
        $this->item_id = $item_id;
        $this->qty = $qty;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = ItemStock::where('place_id',$this->place_id)->where('warehouse_id',$this->warehouse_id)->where('area_id',$this->area_id)->where('item_id',$this->item_id)->first();

		if($data){
			$data->update([
				'qty' => $this->type == 'IN' ? $data->qty - $this->qty : $data->qty + $this->qty,
			]);
		}else{
			ItemStock::create([
				'place_id'		=> $this->place_id,
				'warehouse_id'	=> $this->warehouse_id,
				'item_id'		=> $this->item_id,
				'qty'			=> $this->type == 'IN' ? 0 - $this->qty : $this->qty,
			]);
		}
    }
}

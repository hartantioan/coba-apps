<?php

namespace App\Jobs;

use App\Exports\ExportInventoryTransferIn;
use App\Helpers\CustomHelper;
use App\Helpers\PrintHelper;
use App\Helpers\ResetCogsHelper;
use App\Models\Item;
use App\Models\ItemCogs;
use App\Models\PurchaseMemo;
use Carbon\CarbonPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AccumulateCogsByItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $company_id, $date,$place_id,$item_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $date = null, int $company_id = null, int $place_id = null, int $item_id = null)
    {
		$this->company_id = $company_id;
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
        $company_id = $this->company_id;
        $place_id = $this->place_id;
        $item_id = $this->item_id;
        CustomHelper::accumulateCogs($this->date,$company_id,$place_id,$item_id);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\PurchaseRequest;
use Illuminate\Console\Command;

class AllCustomScript extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customscript:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All cron job and custom script goes here.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        #close pr that has no po and expired
        $pr = PurchaseRequest::whereHas('purchaseRequestDetail',function($query){
            $query->whereDoesntHave('purchaseOrderDetail');
        })
        ->whereIn('status',['1','2'])->whereDate('due_date','<',date("Y-m-d"))
        ->update([
            'status'    => '5',
            'void_note' => 'Ditutup otomatis oleh sistem.',
            'void_date' => date('Y-m-d H:i:s'),
        ]);
        #end close pr that has no po and expired
    }
}

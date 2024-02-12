<?php

namespace App\Console\Commands;

use App\Models\LockPeriod;
use App\Models\LockPeriodDetail;
use App\Models\PurchaseRequest;
use App\Models\User;
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
        /* $pr = PurchaseRequest::whereHas('purchaseRequestDetail',function($query){
            $query->whereDoesntHave('purchaseOrderDetail');
        })
        ->whereIn('status',['1','2'])->whereDate('due_date','<',date("Y-m-d"))
        ->update([
            'status'    => '5',
            'void_note' => 'Ditutup otomatis oleh sistem.',
            'void_date' => date('Y-m-d H:i:s'),
        ]); */
        #end close pr that has no po and expired

        #start make lock period for 1 year

        $thisyear = date('Y');

        for($i=1;$i<=12;$i++){
            $month = $thisyear.'-'.str_pad($i,2,"0",STR_PAD_LEFT);
            $dataLock = LockPeriod::where('month',$month)->where('status','3')->first();
            if(!$dataLock){
                $query = LockPeriod::create([
                    'code'          => LockPeriod::generateCode('LOPR-'.date('y').'P1'),
                    'company_id'    => 1,
                    'post_date'     => date('Y-m-d'),
                    'month'         => $month,
                    'status_closing'=> '1',
                    'status'        => '3',
                    'note'          => 'Dibuat oleh sistem.',
                ]);

                $dataspecial = User::whereNotNull('is_special_lock_user')->pluck('id');

                foreach($dataspecial as $key => $row){
                    $countCheck = LockPeriodDetail::where('lock_period_id',$query->id)->where('user_id',$row)->count();
                    if($countCheck == 0){
                        LockPeriodDetail::create([
                            'lock_period_id'        => $query->id,
                            'user_id'               => $row,
                        ]);
                    }
                }

                activity()
                    ->performedOn(new LockPeriod())
                    ->withProperties($query)
                    ->log('Add lock period by system.');
            }
        }

        #end make lock period for 1 year
    }
}

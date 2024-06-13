<?php

namespace App\Console\Commands;

use App\Models\GoodIssueRequest;
use App\Models\LockPeriod;
use App\Models\LockPeriodDetail;
use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

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

                $dataspecial = User::whereNotNull('is_special_lock_user')->where('type','1')->pluck('id');

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
            }else{
                $dataspecial = User::whereNotNull('is_special_lock_user')->where('type','1')->pluck('id');
                $dataLock->lockPeriodDetail()->whereNotIn('user_id',$dataspecial)->delete();
                foreach($dataspecial as $row){
                    $cekAvailable = $dataLock->lockPeriodDetail()->where('user_id',$row)->first();
                    if(!$cekAvailable){
                        LockPeriodDetail::create([
                            'lock_period_id'        => $dataLock->id,
                            'user_id'               => $row,
                        ]);
                    }
                }
            }
        }

        #end make lock period for 1 year

        #close and void good issue request

        $gir = GoodIssueRequest::where('status','2')->get();

        foreach($gir as $row){
            $datedoc = date('Y-m-d',strtotime($row->updated_at));
            $datenow = date('Y-m-d');
            $diff = round((strtotime($datenow) - strtotime($datedoc)) / 86400);
            if($diff >= 2){
                if($row->hasChildDocument()){
                    $row->update([
                        'status'    => '3',
                        'note'      => $row->note.' - DITUTUP OLEH SISTEM',
                    ]);
                }else{
                    $row->update([
                        'status'    => '5',
                        'void_note' => 'Ditutup oleh sistem karena aturan masa tenggat 2 hari.',
                        'void_date' => date('Y-m-d H:i:s'),
                    ]);
                }
                activity()
                    ->performedOn(new GoodIssueRequest())
                    ->withProperties($row)
                    ->log('Close good issue request by system.');
            }
        }
    }
}

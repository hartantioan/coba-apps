<?php

namespace App\Console\Commands;

use App\Models\EmployeeLeaveQuotas;
use App\Models\EmployeeTransfer;
use App\Models\User;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB as FacadesDB;

class LeaveQuotaRenew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leavequotas:renew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renewing leave quotas today joined user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::now();
        $year_later = Carbon::now()->addYear();
        $todayWithoutYear = $today->format('m-d');

        $oldestTransfers = EmployeeTransfer::select('account_id', FacadesDB::raw('MIN(post_date) as post_date'))
            ->groupBy('account_id')
            ->get();
        //mengambil transfer dengan postdate terlama per user
        
        foreach($oldestTransfers as $row_transfer){
            //mengolah apa bila ada postdate yang sama dengan tanggal hari ini maka akan membuat quota baru
            if(Carbon::parse($row_transfer->post_date)->format('m-d') == $todayWithoutYear ){
                EmployeeLeaveQuotas::create([
                    'user_id'			=> $row_transfer->account_id,
                    'leave_type_id'		=> 1,
                    'paid_leave_quotas'	=> 12,
                    'start_date'		=> strval($today->format('Y-m-d')),
                    'end_date'			=> strval($year_later->format('Y-m-d')),
                ]);
            }
            
        }

    }
}

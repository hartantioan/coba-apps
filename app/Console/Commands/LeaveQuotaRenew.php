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
            $postDate = Carbon::parse($row_transfer->post_date);

            // Check if the difference in years is greater than or equal to 2
            if ($postDate->diffInYears(Carbon::now()) >= 2) {
                // Check if there's an existing leave quota for the user and the current year
                $existingQuota = EmployeeLeaveQuotas::where('user_id', $row_transfer->account_id)
                ->whereYear('start_date', Carbon::now()->year)
                ->first();

                if (!$existingQuota) {
                    // Create a new leave quota for the user for the current year
                    EmployeeLeaveQuotas::create([
                        'user_id'           => $row_transfer->account_id,
                        'leave_type_id'     => 1,
                        'paid_leave_quotas' => 12,
                        'start_date'        => Carbon::now()->firstOfYear()->format('Y-m-d'),
                        'end_date'          => Carbon::now()->lastOfYear()->format('Y-m-d'),
                    ]);
                }
            } else {
                $monthDifference = $row_transfer->post_date->monthDiff($row_transfer->post_date->copy()->endOfYear());

                // Adjust the difference based on the day of the month
                if ($row_transfer->post_date->day < 16) {
                    $resultDifference = $monthDifference;
                } else {
                    $resultDifference = max(0, $monthDifference - 1);
                }
                if ($postDate->format('m-d') == $todayWithoutYear) {
                    EmployeeLeaveQuotas::create([
                        'user_id'           => $row_transfer->account_id,
                        'leave_type_id'     => 1,
                        'paid_leave_quotas' => $resultDifference,
                        'start_date'        => strval($today->format('Y-m-d')),
                        'end_date'          => strval($year_later->format('Y-m-d')),
                    ]);
                }
            }
            
        }

    }
}

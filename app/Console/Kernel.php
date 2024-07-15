<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('leavequotas:renew')
                ->everyMinute()->withoutOverlapping();
        $schedule->command('salestaxseries:set')
                ->everyMinute()->withoutOverlapping();
        $schedule->command('customscript:run')
                ->everyMinute()->withoutOverlapping();
        $schedule->command('app:fetch-currency-rates')->dailyAt('06:00');
        $schedule->command('weight-histories:delete-old')
                ->dailyAt('00:00')->withoutOverlapping(); 
        // $schedule->command('inspire')->hourly();
        /* $schedule->command('queue:work')->everyMinute()->withoutOverlapping()->runInBackground(); */
        $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
        /* $schedule->command('websocket:init')->everyMinute()->withoutOverlapping()->runInBackground(); */
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        Commands\LeaveQuotaRenew::class;

        require base_path('routes/console.php');
    }
}

<?php

namespace App\Console\Commands;


use App\Mail\SendMailProcurementOutstandPR;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportProcurementOutstandPR;

class MailReportProcurementOutstandPR extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailprocurementoutstandpr:run';

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
        $recipient = ['edp@superior.co.id'];
        Excel::store(new ExportProcurementOutstandPR, 'AutoEmail/OutstandPR.xlsx', 'local');
        Mail::to($recipient)->send(new SendMailProcurementOutstandPR());
    }
}

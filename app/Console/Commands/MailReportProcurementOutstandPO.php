<?php

namespace App\Console\Commands;


use App\Mail\SendMailProcurementOutstandPO;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportProcurementOutstandPO;

class MailReportProcurementOutstandPO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailprocurementoutstandpo:run';

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
        $recipient = ['edp@superior.co.id','heny@superior.co.id','livia@superior.co.id','rmpurch@superiorporcelain.co.id'];
        Excel::store(new ExportProcurementOutstandPO, 'public/AutoEmail/OutstandPO.xlsx', 'local');
        Mail::to($recipient)->send(new SendMailProcurementOutstandPO());
    }
}

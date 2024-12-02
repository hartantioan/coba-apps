<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportDeliveryCustomer;
use App\Mail\SendMailDeliveryCustomer;
use Illuminate\Support\Facades\DB;

class MailReportDeliveryCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emaildeliverycustomer:run';

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
        $recipient = ['edp@superior.co.id','marisa@superiorporcelain.co.id'];

        $data = [];

        $date = date('d');

        if ($date == '02') {
            $tanggal1 = date('Y-m-01', strtotime("-2 day"));
            $tanggal2 = date('Y-m-d', strtotime("-2 day"));
        } else {
        }

        $customer = '961';

        //kirim setiap tanggal 2
        if ($date == '02') {
            Excel::store(new ExportDeliveryCustomer($tanggal1, $tanggal2, $customer), 'public/auto_email/delivery_report.xlsx', 'local');
            Mail::to($recipient)->send(new SendMailDeliveryCustomer());
        }
    }
}

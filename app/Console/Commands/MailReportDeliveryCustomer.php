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
        //$recipient = ['edp@superior.co.id','marisa@superiorporcelain.co.id'];

        $data = [];

        $date = date('d');

        if ($date == '03') {
            $tanggal1 = date('Y-m-01', strtotime("-3 day"));
            $tanggal2 = date('Y-m-d', strtotime("-3 day"));
        } else {
        }

        $customer = ['1140', '1141'];
        //manual
        // $tanggal1 = '2024-10-01';
        //$tanggal2 = '2024-10-31';

        //kirim setiap tanggal 2
        foreach ($customer as $row) {
            if ($date == '03') {
                if ($row == '1140') {
                    $recipient = ['edp@superior.co.id', 'marisa@superiorporcelain.co.id', 'diah.christian@abp.co.id', 'weni.anugrah@abp-jatim.co.id', 'tan.oesiung@abp-jatim.co.id'];
                   // $recipient = ['edp@superior.co.id'];
                }
                if ($row == '1141') {
                    $recipient = ['edp@superior.co.id', 'marisa@superiorporcelain.co.id', 'santika.bela@rima.co.id', 'hani.susanti@rima-jatim.co.id', 'tan.oesiung@abp-jatim.co.id'];
                    //$recipient = ['edp@superior.co.id'];
                }
                Excel::store(new ExportDeliveryCustomer($tanggal1, $tanggal2, $row), 'public/auto_email/delivery_report.xlsx', 'local');
                Mail::to($recipient)->send(new SendMailDeliveryCustomer());
            }
        }
    }
}

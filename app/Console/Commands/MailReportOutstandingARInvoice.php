<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportIncomingPaymentAR;
use App\Mail\SendMailIncomingPaymentAR;
use Illuminate\Support\Facades\DB;

class MailReportOutstandingARInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emailoutstandingarinvoice:run';

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


        $query = DB::select("SELECT CONCAT(' 01 - ',DATE_FORMAT('$tanggal2','%d %M %Y')) AS tanggal, coalesce(SUM(b.subtotal),0) AS total FROM incoming_payments a
        LEFT JOIN incoming_payment_details b ON a.id=b.incoming_payment_id AND b.deleted_at IS null
        WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.account_id IS NOT NULL AND a.coa_id <>20 and
        a.post_date>='$tanggal1' AND a.post_date<='$tanggal2'
        UNION ALL
        SELECT DATE_FORMAT('$tanggal2','%d %M %Y'), coalesce(SUM(b.subtotal),0) AS total FROM incoming_payments a
        LEFT JOIN incoming_payment_details b ON a.id=b.incoming_payment_id AND b.deleted_at IS null
        WHERE a.void_date IS NULL AND a.deleted_at IS NULL AND a.account_id IS NOT NULL AND a.coa_id <>20 and
        a.post_date='$tanggal2'
        ");



        foreach ($query as $row) {
            $data[] = [
                'tanggal'  => $row->tanggal,
                'total'  => $row->total,
            ];
        }

        $obj = json_decode(json_encode($data));


        //Excel::store(new ExportIncomingPaymentAR($tanggal1, $tanggal2), 'public/auto_email/incoming_payment.xlsx', 'local');
        Mail::to($recipient)->send(new SendMailIncomingPaymentAR($obj));
    }
}

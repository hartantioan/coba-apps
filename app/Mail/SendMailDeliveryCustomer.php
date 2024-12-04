<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendMailDeliveryCustomer extends Mailable
{
    use Queueable, SerializesModels;


    public function __construct() {}


    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        $month = date('F', strtotime("-2 day"));
        $year = date('Y', strtotime("-2 day"));


        return $this->from('porcelain@superiorporcelain.co.id')
            //->subject('Laporan Delivery ' . $month . ' '.$year)
            ->subject('Laporan Delivery Oktober 2024')
            ->view('admin.mail.report_delivery_customer')

            ->attach(
                storage_path('app/public/auto_email/delivery_report.xlsx'),
                [
                    'as' => 'delivery_report.xlsx',
                    'mime' => 'application/xlsx',
                ]
            )
        ;
    }
}

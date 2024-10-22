<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailReportMarketingDelivery extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    protected $data2;
    protected $data3;

    public function __construct($data, $data2, $data3)
    {
        $this->data = $data;
        $this->data2 = $data2;
        $this->data3 = $data3;
    }

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->from('porcelain@superior.co.id')
            ->subject('[SUPERIOR PORCELAIN SUKSES] Report Delivery')
            ->view('admin.mail.report_marketing_delivery')
            ->with(
                'data',
                $this->data
            )
            ->with(
                'data2',
                $this->data2
            )
            ->with(
                'data3',
                $this->data3
            );
    }
}

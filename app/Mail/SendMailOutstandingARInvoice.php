<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendMailOutstandingARInvoice extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }


    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->from('porcelain@superiorporcelain.co.id')
            ->subject('Invoice Jatuh Tempo')
            ->view('admin.mail.report_incoming_payment_ar')
            ->with(
                'data',
                $this->data
            )

        ;
    }
}

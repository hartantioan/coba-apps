<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendMailOutstandingARInvoiceInternal extends Mailable
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
            ->subject('Outstanding AR Invoice')
            ->view('admin.mail.report_outstanding_ar_invoice_internal')
            ->with(
                'data',
                $this->data
            )

        ;
    }
}

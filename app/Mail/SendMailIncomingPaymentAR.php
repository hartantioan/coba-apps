<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendMailIncomingPaymentAR extends Mailable
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
        ->subject('AR Incoming Payment Report')
        ->view('admin.mail.report_incoming_payment_ar')
        ->with(
            'data',
            $this->data
        )
        ->attach(storage_path('app/public/auto_email/incoming_payment.xlsx'), [
           'as' => 'Incoming Payment.xlsx',
         'mime' => 'application/xlsx',
        ]
        )
        ;
      
        
    }
}
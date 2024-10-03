<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMailMarketingScale extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    protected $data2;

    public function __construct($data,$data2)
    {
        $this->data = $data;
        $this->data2 = $data2;
    }

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->from('reminder@superior.co.id')
        ->subject('Report Timbangan Marketing')
        ->view('admin.mail.report_marketing_scale')
        ->with(
         'data', $this->data)
         ->with(
            'data2', $this->data2);
        
    }
}
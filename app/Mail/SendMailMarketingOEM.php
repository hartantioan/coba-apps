<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailMarketingOEM extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    protected $data2;
   
    
    public function __construct($data, $data2)
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
        return $this->from('porcelain@superior.co.id')
            ->subject('[SUPERIOR PORCELAIN SUKSES] Report Marketing OEM')
            ->view('admin.mail.report_marketing_oem')
            ->with(
                'data',
                $this->data
            )->with(
                'data2',
                $this->data2
            );
    }
}

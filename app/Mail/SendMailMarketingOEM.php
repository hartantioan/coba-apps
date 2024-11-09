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
    protected $data3;
    protected $data4;
     protected $data5;
    
    public function __construct($data, $data2,$data3,$data4,$data5)
    {
        $this->data = $data;
        $this->data2 = $data2;
        $this->data3 = $data3;
        $this->data4 = $data4;
     $this->data5 = $data5;
     
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
            )->with(
                'data3',
                $this->data3
            )->with(
                'data4',
                $this->data4
            )->with(
                'data5',
                $this->data5
            );
    }
}

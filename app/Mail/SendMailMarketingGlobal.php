<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailMarketingGlobal extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
    protected $data2;
    protected $data3;
    protected $data4;
    public function __construct($data, $data2, $data3, $data4)
    {
        $this->data = $data;
        $this->data2 = $data2;
        $this->data3 = $data3;
        $this->data4 = $data4;
    }

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->from('porcelain@superior.co.id')
            ->subject('[SUPERIOR PORCELAIN SUKSES] Report Marketing GLobal')
            ->view('admin.mail.report_marketing_global')
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
            );
    }
}

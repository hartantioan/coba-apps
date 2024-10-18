<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendMailOutstandARInvoiceToCustomer extends Mailable
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
        return $this->from('ar-superiorporcelainsukses@superior.co.id')
        ->subject('[SUPERIOR PORCELAIN SUKSES] Report Outstand AR Invoice')
        ->view('admin.mail.report_outstand_ar_invoice_to_customer')
        ->with(
         'data', $this->data);
        
    }
}
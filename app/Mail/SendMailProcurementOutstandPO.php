<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendMailProcurementOutstandPO extends Mailable
{
    use Queueable, SerializesModels;

  

   

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->from('reminder@superior.co.id')
        ->subject('Report Outstand PO Procurement')
        ->view('admin.mail.report_procurement_outstand_po')
        ->attach(Storage::url('public/AutoEmail/OutstandPO.xlsx'), [
            'as' => 'Outstand PO.xlsx',
            'mime' => 'application/xlsx',
        ]);
      
        
    }
}
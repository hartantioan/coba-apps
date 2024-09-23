<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendMailProcurementOutstandPR extends Mailable
{
    use Queueable, SerializesModels;

  

   

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        return $this->from('reminder@superior.co.id')
        ->subject('Report Outstand PR Procurement')
        ->view('admin.mail.report_procurement_outstand_pr')
        ->attach(Storage::url('public/AutoEmail/OutstandPR.xlsx'), [
            'as' => 'Outstand PR.xlsx',
            'mime' => 'application/xlsx',
        ]);
      
        
    }
}
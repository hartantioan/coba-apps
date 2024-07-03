<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: $this->data['subject'],
    //     );
    // }

    // public function content(): Content
    // {
    //     return new Content(
    //         view: $this->data['view']
    //     );
    // }

    public function attachments(): array
    {
        return [];
    }

    public function build()
    {
        if($this->data['attachmentPath']){
            return $this->subject($this->data['subject'])
                ->view($this->data['view'])
                ->with([
                    'url'    => url('/'),
                    'data'   => $this->data,
                    'result' => $this->data['result'],
                ])
                ->attach($this->data['attachmentPath'], [
                    'as' => $this->data['attachmentName'],
                ]);
        }else{
            return $this->subject($this->data['subject'])
                ->view($this->data['view'])
                ->with([
                    'url'    => url('/'),
                    'data'   => $this->data,
                    'result' => $this->data['result'],
                ]);
        }
        
    }
}
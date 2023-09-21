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

    /* public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->data['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->data['view']
        );
    }

    public function attachments(): array
    {
        return [];
    } */

    public function build()
    {
        return $this->view($this->data['view'])
            ->with([
                'url'   => 'https://superiorporcelain.co.id',
                'data'  => $this->data,
            ])
            ->subject($this->data['subject']);
    }
}
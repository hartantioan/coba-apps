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
                if($this->data['newAttachmentPath'] && $this->data['attachmentPath']){
                    $email =$this->subject($this->data['subject'])
                    ->view($this->data['view'])
                    ->with([
                        'url'    => url('/'),
                        'data'   => $this->data,
                        'result' => $this->data['result'],
                    ])
                    ->attach($this->data['attachmentPath'], [
                        'as' => $this->data['attachmentName'],
                    ])->attach($this->data['newAttachmentPath'], [
                        'as' => $this->data['newAttachmentName'],
                    ]);
                }elseif($this->data['attachmentPath']){
                    info($this->data['attachmentPath']);
                    $email = $this->subject($this->data['subject'])
                    ->view($this->data['view'])
                    ->with([
                        'url'    => url('/'),
                        'data'   => $this->data,
                        'result' => $this->data['result'],
                    ]);
                    if (!empty($this->data['attachmentPath']) && is_array($this->data['attachmentPath'])) {
                        foreach ($this->data['attachmentPath'] as $index => $path) {
                            if (!empty($path)) { // Ensure the file path is not empty
                                $email->attach($path, [
                                    'as' => $this->data['attachmentName'][$index] ?? basename($path), // Use the given name or fallback to file name
                                ]);
                            }
                        }
                    } elseif (!empty($this->data['attachmentPath']) && is_string($this->data['attachmentPath'])) {
                        $email->attach($this->data['attachmentPath'], [
                            'as' => $this->data['attachmentName'] ?? basename($this->data['attachmentPath']),
                        ]);
                    }
                }

                $email->from(config('mail.from.address'), 'PT Superior Porcelain Sukses');
                return $email;
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

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageContent;
    public $subjectLine;

    public function __construct($messageContent, $subjectLine = 'Notification')
    {
        $this->messageContent = $messageContent;
        $this->subjectLine = $subjectLine;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
                    ->view('emails.generic')
                    ->with(['content' => $this->messageContent]);
    }
}

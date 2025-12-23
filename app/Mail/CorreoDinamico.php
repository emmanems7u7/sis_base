<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CorreoDinamico extends Mailable
{
    use Queueable, SerializesModels;

    public string $subjectText;
    public string $bodyHtml;

    /**
     * Create a new message instance.
     *
     * @param string $subjectText
     * @param string $bodyHtml
     */
    public function __construct(string $subjectText, string $bodyHtml)
    {
        $this->subjectText = $subjectText;
        $this->bodyHtml = $bodyHtml;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subjectText)
            ->html($this->bodyHtml);
    }
}

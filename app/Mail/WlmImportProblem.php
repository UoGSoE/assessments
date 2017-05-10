<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WlmImportProblem extends Mailable
{
    use Queueable, SerializesModels;

    public $exceptionMessage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($exceptionMessage)
    {
        $this->exceptionMessage = $exceptionMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
//        return $this->markdown('emails.wlm.error');
    }
}

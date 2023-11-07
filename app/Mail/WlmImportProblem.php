<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
     */
    public function build(): static
    {
        return $this->from('noreply@eng.gla.ac.uk')->markdown('emails.wlm.error');
    }
}

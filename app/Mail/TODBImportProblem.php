<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TODBImportProblem extends Mailable
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
        return $this->from('noreply@eng.gla.ac.uk')->markdown('emails.todb.error');
    }
}
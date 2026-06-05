<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class WelcomeUserMail extends Mailable
{
    public string $setupUrl;

    public function __construct(string $setupUrl)
    {
        $this->setupUrl = $setupUrl;
    }

    public function build()
    {
        return $this->subject(
                'Welcome to DOCSYSTEM'
            )
            ->view('emails.welcome')
            ->with([
                'setupUrl' => $this->setupUrl,
            ]);
    }
}
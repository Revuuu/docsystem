<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class LoginOtpMail extends Mailable
{
    public string $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function build()
    {
        return $this->subject(
                'DOCSYSTEM Login Verification Code'
            )
            ->view('emails.login-otp')
            ->with([
                'otp' => $this->otp
            ]);
    }
}
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $code;

    public function __construct($user, $code = null)
    {
        $this->user = $user;
        $this->code = $code;
    }

    public function build()
    {
        return $this->to($this->user->email)
            ->subject('Reset Password')
            ->view('mail.reset_password', ['user' => $this->user, 'code' => $this->code]);
    }
}

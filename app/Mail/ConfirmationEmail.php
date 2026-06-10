<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmationEmail extends Mailable
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
            ->subject('Confirmation Email')
            ->view('mail.mobile_confirmation_message', ['user' => $this->user, 'code' => $this->code]);
    }
}

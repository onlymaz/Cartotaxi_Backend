<?php

namespace App\Jobs;

use App\Mail\ConfirmationEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user;
    public $code;

    public function __construct($user, $code = null)
    {
        $this->user = $user;
        $this->code = $code;
    }

    public function handle()
    {
        Mail::send(new ConfirmationEmail($this->user, $this->code));
    }
}

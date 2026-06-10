<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use App\Mail\AssignRider;

class SendAssignRider implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user;
    public $order;
    public $userCR;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$order,$userCR)
    {
        $this->user = $user;
        $this->order = $order;
        $this->userCR = $userCR;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::send(new AssignRider($this->user,$this->order,$this->userCR));
    }
}

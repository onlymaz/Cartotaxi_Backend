<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\UpdateStatus;
use Mail;
class SendUpdateStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user;
    public $order;
    public $status;
    public $role;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$order,$status,$role)
    {
        $this->user = $user;
        $this->order = $order;
        $this->status = $status;
        $this->role = $role;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::send(new UpdateStatus($this->user,$this->order,$this->status,$this->role));
    }
}

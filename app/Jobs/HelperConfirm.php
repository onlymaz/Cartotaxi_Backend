<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\HelperConfirmMail;
use Mail;


class HelperConfirm implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user;
    public $helper_info;
    public $helpers;
    public $h_hours;
    public $h_start_end_time;
    public $helper_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user,$helper_info,$helpers,$h_hours,$h_start_end_time,$helper_id)
    {
        $this->user = $user;
        $this->helper_info = $helper_info;
        $this->helpers = $helpers;
        $this->h_hours = $h_hours;
        $this->h_start_end_time=$h_start_end_time;
        $this->helper_id=$helper_id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::send(new HelperConfirmMail($this->user,$this->helper_info,$this->helpers,$this->h_hours,$this->h_start_end_time,$this->helper_id));
    }
}

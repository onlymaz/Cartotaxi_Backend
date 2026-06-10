<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewHelper extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $helper_info;
    public $helpers;
    public $h_hours;
    public $h_start_end_time;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user,$helper_info,$helpers,$h_hours ,$h_start_end_time)
    {
        $this->user = $user;
        $this->helper_info = $helper_info;
        $this->helpers = $helpers;
        $this->h_hours = $h_hours;
        $this->h_start_end_time=$h_start_end_time;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        
        if($this->user->role_id==1){
         return $this->to($this->user->email)
            ->subject('Helper Created')
            ->view('mail.booking.create_helper')->with(['helper_info',$this->helper_info,'helpers',$this->helpers,'h_hours',$this->h_hours,$this->h_start_end_time]);
        }else{
         return $this->to($this->user->email)
            ->subject('Helper Created')
            ->view('mail.booking.create_helper')->with(['helper_info',$this->helper_info,'helpers',$this->helpers,'h_hours',$this->h_hours,$this->h_start_end_time]);
        }
    }
}

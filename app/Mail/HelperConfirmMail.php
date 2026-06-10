<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HelperConfirmMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $helper_info;
    public $helpers;
    public $h_hours;
    public $h_start_end_time;
    public $helper_id;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user,$helper_info,$helpers,$h_hours ,$h_start_end_time,$helper_id)
    {
        $this->user = $user;
        $this->helper_info = $helper_info;
        $this->helpers = $helpers;
        $this->h_hours = $h_hours;
        $this->h_start_end_time=$h_start_end_time;
        $this->helper_id=$helper_id;
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
            ->view('mail.booking.helper_confirm')->with(['helper_info',$this->helper_info,'helpers',$this->helpers,'h_hours',$this->h_hours,$this->h_start_end_time,'helper_id',$this->helper_id]);
        }else{
         return $this->to($this->user->email)
            ->subject('Helper Created')
            ->view('mail.booking.helper_confirm')->with(['helper_info',$this->helper_info,'helpers',$this->helpers,'h_hours',$this->h_hours,$this->h_start_end_time,'helper_id',$this->helper_id]);
        }
    }
}

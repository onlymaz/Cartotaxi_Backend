<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AssignRider extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $order;
    public $userCR;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $user , $order , $userCR)
    {
        $this->user = $user;
        $this->order = $order;
        $this->userCR = $userCR;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        if($this->user->role_id==3){
         return $this->to($this->user->email)
            ->subject('Assign to Rider')
            ->view('mail.booking.assign_rider');
        }else{
         return $this->to($this->user->email)
            ->subject('Assign a Booking Order')
            ->view('mail.booking.rider_assign');

        }
    }
}

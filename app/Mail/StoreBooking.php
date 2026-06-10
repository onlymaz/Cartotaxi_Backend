<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StoreBooking extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $user , $order)
    {
        $this->user = $user;
        $this->order = $order;
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
            ->subject('Order Booked')
            ->view('mail.booking.admin_create_new_booking');
        }else{
         return $this->to($this->user->email)
            ->subject('Order Booked')
            ->view('mail.booking.create_booking');
        }
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UpdateStatus extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $order;
    public $status;
    public $role;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $user , $order ,$status,$role)
    {
        $this->user = $user;
        $this->order = $order;
        $this->status = $status;
        $this->role = $role;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->role=="Rider"){
             return $this->to($this->user->email)
                ->subject('Update Status')
                ->view('mail.booking.update_status');
        }else{
             return $this->to($this->user->email)
                ->subject('Admin Update Status')
            ->view('mail.booking.admin_status_update');
        }
    }
}

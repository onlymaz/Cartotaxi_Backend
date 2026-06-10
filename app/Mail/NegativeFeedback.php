<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NegativeFeedback extends Mailable
{
    use Queueable, SerializesModels;

    public $review;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reviews)
    {
        $this->review      =   $reviews;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to('wilayats@gmail.com')
            ->subject('Driver Negative Feedback')
            ->view('mail.negative');
    }
}

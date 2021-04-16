<?php

namespace App\Mail;

use App\Models\Invites;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Pin extends Mailable
{
    use Queueable, SerializesModels;

    public int $pin;

    public function __construct(int $pin)
    {
        $this->pin = $pin;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->from('admin@example-app.test')
                    ->subject('Your PIN!')
                    ->view('emails.pin');
    }
}

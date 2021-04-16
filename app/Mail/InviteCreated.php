<?php

namespace App\Mail;

use App\Models\Invites;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteCreated extends Mailable
{
    use Queueable, SerializesModels;

    public object $invite;

    /**
     * Create a new message instance.
     * @return void
     */
    public function __construct(Invites $invite)
    {
        $this->invite = $invite;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->from('admin@example-app.test')
                    ->subject('You Been Invited!')
                    ->view('emails.invite');
    }
}

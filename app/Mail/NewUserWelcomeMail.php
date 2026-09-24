<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewUserWelcomeMail extends Mailable implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user, public string $plainPassword) {}

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to WAVEX CRM - Your Account Credentials')
            ->view('emails.new-user-welcome')
            ->with([
                'user' => $this->user,
                'plainPassword' => $this->plainPassword,
                'dashboardUrl' => url('/admin'),
            ]);
    }
}

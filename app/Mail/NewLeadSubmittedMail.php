<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NewLeadSubmittedMail extends Mailable
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Lead $lead) {}

    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->subject('New Lead Submitted: #'.$this->lead->lead_number)
            ->view('emails.new-lead-submitted')
            ->with([
                'lead' => $this->lead,
                'dashboardUrl' => url('/admin'),
            ]);
    }
}

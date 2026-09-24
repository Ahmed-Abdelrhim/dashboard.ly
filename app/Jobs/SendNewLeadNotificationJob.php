<?php

namespace App\Jobs;

use App\Mail\NewLeadSubmittedMail;
use App\Models\EmailNotification;
use App\Models\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendNewLeadNotificationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Lead $lead) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $recipientEmails = EmailNotification::query()
            ->where('type', 'new_lead_submitted')
            ->pluck('email')
            ->filter()
            ->unique();

        if ($recipientEmails->isEmpty()) {
            Log::info('No notification recipients found for type: new_lead_submitted.', [
                'lead_id' => $this->lead->id,
                'lead_number' => $this->lead->lead_number,
            ]);

            return;
        }

        // Eager load campaign if not loaded
        if ($this->lead->campaign_id !== null && ! $this->lead->relationLoaded('campaign')) {
            $this->lead->load('campaign');
        }

        foreach ($recipientEmails as $email) {
            try {
                Mail::to($email)->send(new NewLeadSubmittedMail($this->lead));

                Log::info("Sent new lead notification email to [{$email}] for lead [{$this->lead->lead_number}].");
            } catch (Throwable $e) {
                Log::error("Failed to send new lead notification to [{$email}]: ".$e->getMessage(), [
                    'lead_id' => $this->lead->id,
                    'lead_number' => $this->lead->lead_number,
                    'exception' => $e,
                ]);
            }
        }
    }
}

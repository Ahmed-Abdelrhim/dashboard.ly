<?php

namespace Tests\Feature;

use App\Jobs\SendNewLeadNotificationJob;
use App\Mail\NewLeadSubmittedMail;
use App\Models\EmailNotification;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SendNewLeadNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_fetches_recipients_from_email_notifications_and_sends_mail(): void
    {
        Mail::fake();

        // Create target recipients
        EmailNotification::create([
            'type' => 'new_lead_submitted',
            'email' => 'manager1@wavex.fit',
        ]);
        EmailNotification::create([
            'type' => 'new_lead_submitted',
            'email' => 'manager2@wavex.fit',
        ]);

        // Create other unrelated notification recipient
        EmailNotification::create([
            'type' => 'daily_report',
            'email' => 'unrelated@wavex.fit',
        ]);

        $lead = Lead::factory()->create([
            'name' => 'Michael Scott',
            'email' => 'michael@dundermifflin.com',
            'phone' => '+201012345678',
        ]);

        (new SendNewLeadNotificationJob($lead))->handle();

        Mail::assertSent(NewLeadSubmittedMail::class, function (NewLeadSubmittedMail $mail) use ($lead) {
            return $mail->hasTo('manager1@wavex.fit')
                && $mail->lead->id === $lead->id;
        });

        Mail::assertSent(NewLeadSubmittedMail::class, function (NewLeadSubmittedMail $mail) use ($lead) {
            return $mail->hasTo('manager2@wavex.fit')
                && $mail->lead->id === $lead->id;
        });

        Mail::assertNotSent(NewLeadSubmittedMail::class, function (NewLeadSubmittedMail $mail) {
            return $mail->hasTo('unrelated@wavex.fit');
        });
    }

    public function test_job_handles_empty_recipients_gracefully(): void
    {
        Mail::fake();

        $lead = Lead::factory()->create([
            'name' => 'Dwight Schrute',
            'email' => 'dwight@dundermifflin.com',
        ]);

        (new SendNewLeadNotificationJob($lead))->handle();

        Mail::assertNothingSent();
    }

    public function test_mail_template_renders_correctly_with_lead_info_and_dashboard_button(): void
    {
        $lead = Lead::factory()->create([
            'lead_number' => 'LD-260920-WAVEX',
            'name' => 'Jim Halpert',
            'email' => 'jim@dundermifflin.com',
            'phone' => '+201112223334',
        ]);

        $mailable = new NewLeadSubmittedMail($lead);

        $mailable->assertSeeInHtml('LD-260920-WAVEX');
        $mailable->assertSeeInHtml('Jim Halpert');
        $mailable->assertSeeInHtml('+201112223334');
        $mailable->assertSeeInHtml('jim@dundermifflin.com');
        $mailable->assertSeeInHtml(url('/admin'));
        $mailable->assertSeeInHtml('Open Dashboard');
    }

    public function test_store_lead_endpoint_dispatches_notification_job(): void
    {
        Queue::fake();

        $payload = [
            'name' => 'Pam Beesly',
            'email' => 'pam@dundermifflin.com',
            'phone' => '+201212345678',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertCreated();

        Queue::assertPushed(SendNewLeadNotificationJob::class, function (SendNewLeadNotificationJob $job) {
            return $job->lead->email === 'pam@dundermifflin.com'
                && $job->lead->name === 'Pam Beesly';
        });
    }
}

<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Campaign;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreLeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_successfully_store_a_lead_with_full_valid_data(): void
    {
        $campaign = Campaign::factory()->create([
            'name' => 'Spring Aquatic Promo',
            'platform' => 'Instagram',
            'utm_campaign' => 'spring_aqua_2026',
        ]);

        $payload = [
            'name' => 'Sarah Connor',
            'email' => 'sarah@example.com',
            'phone' => '+201001234567',
            'age' => 29,
            'wavex_user_id' => 450,
            'country_id' => 1,
            'branch_id' => 2,
            'campaign_id' => $campaign->id,
            'tried_aqua_fitness' => true,
            'interested_in' => ['Weight Loss', 'Aqua Cycling'],
            'sessions_considering' => '12 sessions',
            'preferred_days' => ['Monday', 'Wednesday', 'Friday'],
            'preferred_times' => ['Morning (8AM - 12PM)'],
            'utm_source' => 'instagram',
            'utm_medium' => 'paid_social',
            'utm_campaign' => 'spring_aqua_2026',
            'utm_term' => 'fitness',
            'utm_content' => 'carousel_ad_1',
            'fbclid' => 'fb_click_token_123',
            'gclid' => null,
            'ttclid' => null,
            'landing_page_url' => 'https://wavex.example.com/promo',
            'referrer_url' => 'https://instagram.com/',
            'notes' => 'Looking to start next month.',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 201)
            ->assertJsonPath('message', 'Lead registered successfully.')
            ->assertJsonPath('data.name', 'Sarah Connor')
            ->assertJsonPath('data.email', 'sarah@example.com')
            ->assertJsonPath('data.phone', '+201001234567')
            ->assertJsonPath('data.age', 29)
            ->assertJsonPath('data.status.value', LeadStatus::New->value)
            ->assertJsonPath('data.campaign.id', $campaign->id)
            ->assertJsonPath('data.form.tried_aqua_fitness', true)
            ->assertJsonPath('data.form.interested_in', ['Weight Loss', 'Aqua Cycling'])
            ->assertJsonPath('data.form.preferred_days', ['Monday', 'Wednesday', 'Friday'])
            ->assertJsonPath('data.attribution.utm_source', 'instagram');

        $this->assertDatabaseHas('leads', [
            'name' => 'Sarah Connor',
            'email' => 'sarah@example.com',
            'phone' => '+201001234567',
            'campaign_id' => $campaign->id,
            'status' => LeadStatus::New->value,
        ]);

        $lead = Lead::where('email', 'sarah@example.com')->firstOrFail();
        $this->assertNotNull($lead->lead_number);
        $this->assertStringStartsWith('LD-', $lead->lead_number);
    }

    public function test_fails_validation_when_name_is_missing(): void
    {
        $payload = [
            'email' => 'john@example.com',
            'phone' => '+201099999999',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_fails_validation_when_neither_email_nor_phone_is_provided(): void
    {
        $payload = [
            'name' => 'Jane Doe',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'phone']);
    }

    public function test_fails_validation_when_phone_is_missing(): void
    {
        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_fails_validation_when_email_is_missing(): void
    {
        $payload = [
            'name' => 'John Doe',
            'phone' => '+201112223334',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_fails_validation_with_invalid_email_format(): void
    {
        $payload = [
            'name' => 'Invalid Email Guy',
            'email' => 'not-an-email',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_fails_validation_with_invalid_phone_format(): void
    {
        $payload = [
            'name' => 'Invalid Phone Guy',
            'phone' => '12345',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_fails_validation_when_campaign_id_does_not_exist(): void
    {
        $payload = [
            'name' => 'Test Lead',
            'email' => 'test@example.com',
            'campaign_id' => 999999,
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['campaign_id']);
    }

    public function test_auto_resolves_campaign_id_from_utm_campaign_if_campaign_id_omitted(): void
    {
        $campaign = Campaign::factory()->create([
            'name' => 'Summer Aquabike Blitz',
            'utm_campaign' => 'aquabike_summer',
        ]);

        $payload = [
            'name' => 'Automated Campaign Match',
            'email' => 'autocfg@example.com',
            'phone' => '+201012345678',
            'utm_campaign' => 'aquabike_summer',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('data.campaign_id', $campaign->id)
            ->assertJsonPath('data.campaign.id', $campaign->id);

        $this->assertDatabaseHas('leads', [
            'email' => 'autocfg@example.com',
            'campaign_id' => $campaign->id,
        ]);
    }

    public function test_public_user_cannot_override_status_assigned_user_or_timestamps(): void
    {
        $payload = [
            'name' => 'Hacker Lead',
            'email' => 'hacker@example.com',
            'phone' => '+201098765432',
            'status' => 'won',
            'assigned_to' => 1234,
            'won_at' => now()->toISOString(),
            'lead_number' => 'CUSTOM-FAKE-NUMBER',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('data.status.value', LeadStatus::New->value);

        $lead = Lead::where('email', 'hacker@example.com')->firstOrFail();
        $this->assertEquals(LeadStatus::New, $lead->status);
        $this->assertNull($lead->assigned_to);
        $this->assertNull($lead->won_at);
        $this->assertNotEquals('CUSTOM-FAKE-NUMBER', $lead->lead_number);
    }

    public function test_can_decode_json_string_arrays(): void
    {
        $payload = [
            'name' => 'Json String Lead',
            'email' => 'jsonstr@example.com',
            'phone' => '+201056781234',
            'interested_in' => json_encode(['Cardio', 'Post Rehab']),
            'preferred_days' => json_encode(['Tuesday', 'Thursday']),
            'preferred_times' => json_encode(['Evening (4PM - 8PM)']),
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertCreated()
            ->assertJsonPath('data.form.interested_in', ['Cardio', 'Post Rehab'])
            ->assertJsonPath('data.form.preferred_days', ['Tuesday', 'Thursday'])
            ->assertJsonPath('data.form.preferred_times', ['Evening (4PM - 8PM)']);

        $lead = Lead::where('email', 'jsonstr@example.com')->firstOrFail();
        $this->assertEquals(['Cardio', 'Post Rehab'], $lead->interested_in);
    }

    public function test_fails_validation_when_email_is_duplicate(): void
    {
        Lead::factory()->create([
            'email' => 'duplicate@example.com',
            'phone' => '+201011112222',
        ]);

        $payload = [
            'name' => 'Duplicate Email Tester',
            'email' => 'duplicate@example.com',
            'phone' => '+201033334444',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'A lead with this email address already exists.');
    }

    public function test_fails_validation_when_phone_is_duplicate(): void
    {
        Lead::factory()->create([
            'email' => 'unique1@example.com',
            'phone' => '+201055556666',
        ]);

        // Attempt submission with formatted duplicate phone number
        $payload = [
            'name' => 'Duplicate Phone Tester',
            'email' => 'unique2@example.com',
            'phone' => '+20 10 5555 6666',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone'])
            ->assertJsonPath('errors.phone.0', 'A lead with this phone number already exists.');
    }

    public function test_endpoint_has_throttling_headers(): void
    {
        $payload = [
            'name' => 'Throttled User',
            'email' => 'throttled@example.com',
            'phone' => '+201088889999',
        ];

        $response = $this->postJson(route('api.leads.store'), $payload);

        $response->assertCreated();
        $response->assertHeader('X-RateLimit-Limit', '3');
        $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
    }

    public function test_throttled_response_has_custom_json_format(): void
    {
        // Route has throttle:3,1. Exhaust the 3 allowed requests.
        for ($i = 0; $i < 3; $i++) {
            $this->postJson(route('api.leads.store'), [
                'name' => "Rate Limit User {$i}",
                'email' => "ratelimit{$i}@example.com",
                'phone' => "+20101111000{$i}",
            ]);
        }

        // The 4th request must trigger ThrottleRequestsException with our custom formatted JSON response
        $response = $this->postJson(route('api.leads.store'), [
            'name' => 'Excess User',
            'email' => 'excess@example.com',
            'phone' => '+201099999999',
        ]);

        $response->assertStatus(429)
            ->assertJsonPath('success', false)
            ->assertJsonPath('status', 429)
            ->assertJsonPath('errors', 'Too Many Attempts.')
            ->assertJsonStructure([
                'success',
                'status',
                'message',
                'errors',
            ]);

        $this->assertStringContainsString('Too many requests', $response->json('message'));
    }
}

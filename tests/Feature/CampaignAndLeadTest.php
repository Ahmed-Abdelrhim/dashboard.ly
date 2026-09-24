<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\User;
use Database\Seeders\CampaignSeeder;
use Database\Seeders\LeadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignAndLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_campaign_can_be_created_with_factory(): void
    {
        $campaign = Campaign::factory()->create([
            'name' => 'Summer Fitness 2026',
            'platform' => 'Instagram',
            'utm_campaign' => 'summer_2026',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'name' => 'Summer Fitness 2026',
            'platform' => 'Instagram',
            'utm_campaign' => 'summer_2026',
            'is_active' => true,
        ]);

        $this->assertTrue($campaign->is_active);
    }

    public function test_campaign_scopes_work(): void
    {
        Campaign::factory()->create(['platform' => 'Facebook', 'is_active' => true]);
        Campaign::factory()->create(['platform' => 'Instagram', 'is_active' => true]);
        Campaign::factory()->inactive()->create(['platform' => 'Facebook']);

        $this->assertSame(2, Campaign::active()->count());
        $this->assertSame(2, Campaign::platform('Facebook')->count());
        $this->assertSame(1, Campaign::active()->platform('Facebook')->count());
    }

    public function test_lead_auto_generates_lead_number_on_creation(): void
    {
        $lead = Lead::factory()->create([
            'lead_number' => null,
            'name' => 'John Doe',
        ]);

        $this->assertNotNull($lead->lead_number);
        $this->assertStringStartsWith('LD-', $lead->lead_number);
    }

    public function test_lead_casts_attributes_properly(): void
    {
        $lead = Lead::factory()->create([
            'age' => 28,
            'tried_aqua_fitness' => true,
            'interested_in' => ['Weight Loss', 'Cardio'],
            'preferred_days' => ['Monday', 'Wednesday'],
            'preferred_times' => ['Morning (8AM - 12PM)'],
            'status' => LeadStatus::Contacted,
            'first_contacted_at' => now(),
        ]);

        $lead->refresh();

        $this->assertSame(28, $lead->age);
        $this->assertTrue($lead->tried_aqua_fitness);
        $this->assertSame(['Weight Loss', 'Cardio'], $lead->interested_in);
        $this->assertSame(['Monday', 'Wednesday'], $lead->preferred_days);
        $this->assertSame(['Morning (8AM - 12PM)'], $lead->preferred_times);
        $this->assertInstanceOf(LeadStatus::class, $lead->status);
        $this->assertSame(LeadStatus::Contacted, $lead->status);
        $this->assertNotNull($lead->first_contacted_at);
    }

    public function test_lead_relationships_with_campaign_and_user(): void
    {
        $user = User::factory()->create();
        $campaign = Campaign::factory()->create();

        $lead = Lead::factory()->create([
            'campaign_id' => $campaign->id,
            'assigned_to' => $user->id,
        ]);

        $this->assertTrue($lead->campaign->is($campaign));
        $this->assertTrue($lead->assignedUser->is($user));
        $this->assertTrue($campaign->leads->contains($lead));
        $this->assertTrue($user->assignedLeads->contains($lead));
    }

    public function test_lead_scopes_work(): void
    {
        $user = User::factory()->create();

        $lead1 = Lead::factory()->create([
            'status' => LeadStatus::New,
            'assigned_to' => null,
        ]);

        $lead2 = Lead::factory()->create([
            'status' => LeadStatus::Won,
            'assigned_to' => $user->id,
        ]);

        $this->assertSame(1, Lead::status(LeadStatus::New)->count());
        $this->assertSame(1, Lead::status(LeadStatus::Won)->count());
        $this->assertSame(1, Lead::unassigned()->count());
        $this->assertSame(1, Lead::assignedTo($user)->count());
        $this->assertTrue(Lead::unassigned()->first()->is($lead1));
        $this->assertTrue(Lead::assignedTo($user)->first()->is($lead2));
    }

    public function test_campaign_and_lead_seeders_run_successfully(): void
    {
        User::factory()->count(2)->create();

        $this->seed(CampaignSeeder::class);
        $this->assertGreaterThan(0, Campaign::count());

        $this->seed(LeadSeeder::class);
        $this->assertGreaterThan(0, Lead::count());
    }
}

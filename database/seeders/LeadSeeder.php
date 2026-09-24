<?php

namespace Database\Seeders;

use App\Enums\LeadStatus;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = Campaign::all();
        if ($campaigns->isEmpty()) {
            $this->call(CampaignSeeder::class);
            $campaigns = Campaign::all();
        }

        $agents = User::all();

        // Create sample leads across various stages
        foreach ($campaigns as $campaign) {
            Lead::factory()->count(3)->create([
                'campaign_id' => $campaign->id,
                'assigned_to' => $agents->isNotEmpty() ? $agents->random()->id : null,
                'status' => LeadStatus::New,
            ]);

            Lead::factory()->count(2)->contacted()->create([
                'campaign_id' => $campaign->id,
                'assigned_to' => $agents->isNotEmpty() ? $agents->random()->id : null,
            ]);

            Lead::factory()->count(1)->won()->create([
                'campaign_id' => $campaign->id,
                'assigned_to' => $agents->isNotEmpty() ? $agents->random()->id : null,
            ]);

            Lead::factory()->count(1)->lost()->create([
                'campaign_id' => $campaign->id,
                'assigned_to' => $agents->isNotEmpty() ? $agents->random()->id : null,
            ]);
        }
    }
}

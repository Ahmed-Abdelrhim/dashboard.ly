<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $campaigns = [
            [
                'name' => 'Summer Aqua Promo 2026',
                'platform' => 'Instagram',
                'utm_campaign' => 'summer_aqua_2026',
                'is_active' => true,
            ],
            [
                'name' => 'Facebook Lead Gen Aqua Fitness',
                'platform' => 'Facebook',
                'utm_campaign' => 'fb_leadgen_aqua',
                'is_active' => true,
            ],
            [
                'name' => 'TikTok Fitness Challengers',
                'platform' => 'TikTok',
                'utm_campaign' => 'tiktok_fitness_challenge',
                'is_active' => true,
            ],
            [
                'name' => 'Google Search - Hydrotherapy',
                'platform' => 'Google Ads',
                'utm_campaign' => 'google_hydrotherapy_cairo',
                'is_active' => true,
            ],
            [
                'name' => 'Direct Website Inquiries',
                'platform' => 'Website',
                'utm_campaign' => 'website_organic',
                'is_active' => true,
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::firstOrCreate(
                ['utm_campaign' => $campaign['utm_campaign']],
                $campaign
            );
        }
    }
}

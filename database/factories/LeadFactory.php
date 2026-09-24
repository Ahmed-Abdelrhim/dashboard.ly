<?php

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Campaign;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_number' => Lead::generateLeadNumber(),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'age' => fake()->numberBetween(18, 65),
            'wavex_user_id' => null,
            'country_id' => null,
            'branch_id' => null,
            'campaign_id' => Campaign::factory(),
            'tried_aqua_fitness' => fake()->boolean(),
            'interested_in' => fake()->randomElements(['Weight Loss', 'Cardio', 'Rehabilitation', 'General Fitness'], 2),
            'sessions_considering' => fake()->randomElement(['1-2 times/week', '3-4 times/week', 'Unlimited']),
            'preferred_days' => fake()->randomElements(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'], 2),
            'preferred_times' => fake()->randomElements(['Morning (8AM - 12PM)', 'Afternoon (12PM - 4PM)', 'Evening (4PM - 8PM)'], 1),
            'utm_source' => fake()->randomElement(['instagram', 'facebook', 'google', 'tiktok']),
            'utm_medium' => fake()->randomElement(['cpc', 'social', 'paid']),
            'utm_campaign' => fake()->slug(),
            'utm_term' => fake()->word(),
            'utm_content' => fake()->word(),
            'fbclid' => null,
            'gclid' => null,
            'ttclid' => null,
            'landing_page_url' => fake()->url(),
            'referrer_url' => fake()->url(),
            'assigned_to' => User::factory(),
            'status' => LeadStatus::New,
            'first_contacted_at' => null,
            'last_contacted_at' => null,
            'won_at' => null,
            'lost_at' => null,
            'lost_reason' => null,
            'notes' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the lead is unassigned.
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'assigned_to' => null,
        ]);
    }

    /**
     * Indicate that the lead was won.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeadStatus::Won,
            'won_at' => now(),
        ]);
    }

    /**
     * Indicate that the lead was lost.
     */
    public function lost(?string $reason = 'Price too high'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeadStatus::Lost,
            'lost_at' => now(),
            'lost_reason' => $reason,
        ]);
    }

    /**
     * Indicate that the lead was contacted.
     */
    public function contacted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeadStatus::Contacted,
            'first_contacted_at' => now(),
            'last_contacted_at' => now(),
        ]);
    }
}

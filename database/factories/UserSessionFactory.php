<?php

namespace Database\Factories;

use App\Enums\AuthenticationType;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserSession>
 */
class UserSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'sanctum_token_id' => null,
            'session_identifier' => (string) Str::uuid(),
            'authentication_type' => AuthenticationType::SessionCookie,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'device_name' => fake()->randomElement(['MacBook Pro', 'iPhone 15', 'Dell XPS', 'Pixel 8']),
            'device_type' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
            'os_name' => fake()->randomElement(['macOS', 'iOS', 'Windows', 'Android']),
            'os_version' => fake()->randomElement(['14.4', '17.2', '11.0', '14.0']),
            'browser_name' => fake()->randomElement(['Chrome', 'Safari', 'Firefox', 'Edge']),
            'browser_version' => fake()->randomElement(['122.0', '17.3', '123.0', '122.0']),
            'country' => fake()->country(),
            'country_code' => fake()->countryCode(),
            'city' => fake()->city(),
            'login_at' => now(),
            'last_activity_at' => now(),
            'logout_at' => null,
            'last_activity_ip' => fake()->ipv4(),
            'is_active' => true,
            'revoked_at' => null,
        ];
    }

    /**
     * Indicate that the session was authenticated via Bearer Token.
     */
    public function bearerToken(?int $sanctumTokenId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'authentication_type' => AuthenticationType::BearerToken,
            'sanctum_token_id' => $sanctumTokenId,
        ]);
    }

    /**
     * Indicate that the session was authenticated via Session Cookie.
     */
    public function sessionCookie(): static
    {
        return $this->state(fn (array $attributes) => [
            'authentication_type' => AuthenticationType::SessionCookie,
            'sanctum_token_id' => null,
        ]);
    }

    /**
     * Indicate that the session is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'revoked_at' => now(),
        ]);
    }

    /**
     * Indicate that the session is logged out.
     */
    public function loggedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'logout_at' => now(),
        ]);
    }
}

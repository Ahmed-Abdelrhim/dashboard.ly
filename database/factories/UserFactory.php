<?php

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'type' => UserType::SalesAgent,
            'is_active' => true,
            '2fa_enabled' => false,
            '2fa_secret' => null,
            '2fa_confirmed_at' => null,
            'last_login_at' => null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserType::SuperAdmin,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserType::Admin,
        ]);
    }

    /**
     * Indicate that the user is a sales agent.
     */
    public function salesAgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => UserType::SalesAgent,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the user has two-factor authentication enabled.
     */
    public function withTwoFactor(bool $confirmed = true): static
    {
        return $this->state(fn (array $attributes) => [
            '2fa_enabled' => true,
            '2fa_secret' => Str::random(32),
            '2fa_confirmed_at' => $confirmed ? now() : null,
        ]);
    }
}

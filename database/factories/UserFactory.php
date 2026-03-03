<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
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
            'phone' => '+1555'.fake()->unique()->numerify('#######'),
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => 'guardian',
            'status' => 'active',
            'verification_status' => 'unverified',
            'verification_type' => null,
            'verified_at' => null,
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is pending phone verification.
     */
    public function pendingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_verification',
            'phone_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }

    /**
     * Indicate that the user is a tutor.
     */
    public function tutor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'tutor',
        ]);
    }

    /**
     * Indicate that the user is a guardian.
     */
    public function guardian(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'guardian',
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'status' => 'active',
            'phone' => null,
            'phone_verified_at' => now(),
        ]);
    }
}

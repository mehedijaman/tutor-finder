<?php

namespace Database\Factories;

use App\Models\GuardianProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuardianProfile>
 */
class GuardianProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->guardian(),
            'phone_alt' => fake()->optional()->phoneNumber(),
            'guardian_name' => fake()->name(),
            'address' => fake()->address(),
            'occupation' => fake()->jobTitle(),
            'notes' => fake()->optional()->sentence(),
            'status' => GuardianProfile::STATUS_ACTIVE,
        ];
    }
}

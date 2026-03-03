<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VerificationRequest>
 */
class VerificationRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = fake()->randomElement([VerificationRequest::ROLE_TUTOR, VerificationRequest::ROLE_GUARDIAN]);

        return [
            'user_id' => User::factory()->state(['role' => $role]),
            'role' => $role,
            'status' => VerificationRequest::STATUS_PENDING,
            'fee_amount' => 500,
            'currency' => 'BDT',
            'submitted_at' => now(),
            'reviewed_by' => null,
            'reviewed_at' => null,
            'decision_reason' => null,
            'metadata' => null,
        ];
    }
}

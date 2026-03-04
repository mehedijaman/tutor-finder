<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
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
        $role = fake()->randomElement([VerificationRole::Tutor, VerificationRole::Guardian]);

        $userRole = $role === VerificationRole::Tutor ? UserRole::Tutor : UserRole::Guardian;

        return [
            'user_id' => User::factory()->state(['role' => $userRole]),
            'role' => $role,
            'status' => VerificationStatus::Pending,
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

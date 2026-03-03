<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RefundRequest>
 */
class RefundRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_assignment_id' => TuitionJobAssignment::factory(),
            'requested_by_user_id' => User::factory()->tutor(),
            'reason_text' => fake()->sentence(),
            'requested_at' => now(),
            'status' => RefundRequest::STATUS_PENDING,
            'amount' => fake()->randomFloat(2, 100, 4000),
            'currency' => 'BDT',
            'decision_by_admin_id' => null,
            'decision_note' => null,
            'decided_at' => null,
            'paid_at' => null,
            'payment_id' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state([
            'status' => RefundRequest::STATUS_PAID,
            'decision_by_admin_id' => User::factory()->admin(),
            'decided_at' => now(),
            'paid_at' => now(),
            'payment_id' => Payment::factory(),
        ]);
    }
}

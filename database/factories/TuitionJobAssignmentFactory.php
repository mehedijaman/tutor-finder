<?php

namespace Database\Factories;

use App\Enums\DurationType;
use App\Enums\FeePaymentMode;
use App\Models\TuitionJob;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TuitionJobAssignment>
 */
class TuitionJobAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => TuitionJob::factory()->live(),
            'tutor_user_id' => User::factory()->tutor(),
            'appointed_at' => now(),
            'confirmed_at' => now(),
            'cancelled_at' => null,
            'cancelled_by' => null,
            'fault' => null,
            'cancel_reason' => null,
            'reported_within_24h' => false,
            'duration_type' => DurationType::LongTerm,
            'short_term_months' => null,
            'service_fee_rate' => null,
            'service_fee_amount' => null,
            'fee_currency' => 'BDT',
            'fee_due_at' => null,
            'fee_payment_mode' => FeePaymentMode::PayBefore,
            'month1_escrow_required' => false,
            'month1_escrow_paid_at' => null,
            'first_month_received_at' => null,
            'month1_ended_at' => null,
            'month1_settled_at' => null,
            'notes' => null,
            'metadata' => null,
        ];
    }
}

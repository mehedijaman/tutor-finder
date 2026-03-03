<?php

namespace Database\Factories;

use App\Models\TuitionJob;
use App\Models\TuitionJobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TuitionJobApplication>
 */
class TuitionJobApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_id' => TuitionJob::factory(),
            'tutor_user_id' => User::factory()->tutor(),
            'cover_letter' => fake()->paragraph(),
            'expected_salary_amount' => fake()->numberBetween(5000, 30000),
            'salary_currency' => 'BDT',
            'status' => TuitionJobApplication::STATUS_APPLIED,
            'cancel_reason' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the application is shortlisted.
     */
    public function shortlisted(): static
    {
        return $this->state(fn (): array => [
            'status' => TuitionJobApplication::STATUS_SHORTLISTED,
        ]);
    }

    /**
     * Indicate that the application is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (): array => [
            'status' => TuitionJobApplication::STATUS_CONFIRMED,
        ]);
    }
}

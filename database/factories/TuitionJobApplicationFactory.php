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
            'tuition_job_id' => TuitionJob::factory(),
            'tutor_id' => User::factory()->tutor(),
            'cover_letter' => fake()->paragraph(),
            'expected_salary' => fake()->numberBetween(5000, 30000),
            'status' => TuitionJobApplication::STATUS_PENDING,
            'guardian_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }

    /**
     * Indicate that the application is shortlisted.
     */
    public function shortlisted(): static
    {
        return $this->state(fn (): array => [
            'status' => TuitionJobApplication::STATUS_SHORTLISTED,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that the application is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => TuitionJobApplication::STATUS_REJECTED,
            'reviewed_at' => now(),
        ]);
    }
}

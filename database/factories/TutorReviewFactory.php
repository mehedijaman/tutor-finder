<?php

namespace Database\Factories;

use App\Models\TuitionJobAssignment;
use App\Models\TutorReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TutorReview>
 */
class TutorReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tutor_user_id' => User::factory()->tutor(),
            'guardian_user_id' => User::factory()->guardian(),
            'job_assignment_id' => TuitionJobAssignment::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional(0.8)->paragraph(),
        ];
    }

    /**
     * Set a specific rating.
     */
    public function rating(int $rating): static
    {
        return $this->state(fn () => ['rating' => $rating]);
    }

    /**
     * Review without a comment.
     */
    public function withoutComment(): static
    {
        return $this->state(fn () => ['comment' => null]);
    }
}

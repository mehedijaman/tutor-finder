<?php

namespace Database\Factories;

use App\Models\TutorEducation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TutorEducation>
 */
class TutorEducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->tutor(),
            'degree' => fake()->randomElement(['BSc', 'MSc', 'BA']),
            'institute' => fake()->company().' University',
            'department' => fake()->optional()->word(),
            'graduation_year' => (int) fake()->year(),
            'result' => fake()->optional()->randomElement(['3.80/4.00', 'First Class']),
            'is_current' => false,
            'sort_order' => 0,
        ];
    }
}

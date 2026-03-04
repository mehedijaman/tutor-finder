<?php

namespace Database\Factories;

use App\Enums\ProfileStatus;
use App\Models\TutorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TutorProfile>
 */
class TutorProfileFactory extends Factory
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
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'date_of_birth' => fake()->date(),
            'present_address' => fake()->address(),
            'permanent_address' => fake()->address(),
            'nid_no' => fake()->numerify('##########'),
            'bio' => fake()->paragraph(),
            'preferred_tuition_types' => [1, 2],
            'preferred_categories' => [1],
            'preferred_classes' => [1, 2],
            'preferred_subjects' => [1, 2, 3],
            'preferred_locations' => [1],
            'expected_salary_min' => 5000,
            'expected_salary_max' => 10000,
            'available_days' => ['sat', 'mon', 'wed'],
            'available_time' => '5 PM - 8 PM',
            'status' => ProfileStatus::Active,
        ];
    }
}

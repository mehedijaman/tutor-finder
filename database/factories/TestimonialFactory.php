<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Testimonial>
 */
class TestimonialFactory extends Factory
{
    public function definition(): array
    {
        $roles = ['Guardian', 'Tutor', 'Parent', 'Student'];
        $names = [
            'Ahmed Hasan', 'Fatema Begum', 'Rahman Chowdhury', 'Nusrat Sultana',
            'Mahbubur Rahman', 'Salma Khatun', 'Kamal Hossain', 'Jahanara Begum',
            'Tariq Ahmed', 'Rashida Begum', 'Imran Khan', 'Farida Yasmin',
        ];

        return [
            'user_id' => User::query()->where('role', 'guardian')->inRandomOrder()->first()?->id,
            'name' => fake()->randomElement($names),
            'role' => fake()->randomElement($roles),
            'avatar_url' => null,
            'content' => fake()->paragraphs(2, true),
            'rating' => fake()->randomElement([4, 5, 5, 5, 4, 5]),
            'status' => 'active',
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}

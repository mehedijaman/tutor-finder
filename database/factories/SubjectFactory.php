<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'class_id' => SchoolClass::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'status' => Subject::STATUS_ACTIVE,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}

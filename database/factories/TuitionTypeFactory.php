<?php

namespace Database\Factories;

use App\Enums\TaxonomyStatus;
use App\Models\TuitionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TuitionType>
 */
class TuitionTypeFactory extends Factory
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
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'status' => TaxonomyStatus::Active,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}

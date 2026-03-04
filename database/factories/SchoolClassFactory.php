<?php

namespace Database\Factories;

use App\Enums\TaxonomyStatus;
use App\Models\Category;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SchoolClass>
 */
class SchoolClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->bothify('Class ## ??');

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'status' => TaxonomyStatus::Active,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}

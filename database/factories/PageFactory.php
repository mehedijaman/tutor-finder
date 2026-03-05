<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'status' => fake()->randomElement([PageStatus::Active, PageStatus::Inactive]),
            'is_system' => false,
            'meta_title' => fake()->optional()->sentence(6),
            'meta_description' => fake()->optional()->sentence(12),
        ];
    }

    /**
     * Mark the page as a system page.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_system' => true,
        ]);
    }

    /**
     * Mark the page as active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PageStatus::Active,
        ]);
    }

    /**
     * Mark the page as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PageStatus::Inactive,
        ]);
    }
}

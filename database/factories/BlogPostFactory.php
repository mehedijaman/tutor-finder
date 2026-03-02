<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->paragraph(),
            'content' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'status' => fake()->randomElement(['draft', 'published']),
            'published_at' => now()->subDay(),
            'author_admin_id' => User::factory()->admin(),
            'meta_title' => fake()->sentence(7),
            'meta_description' => fake()->sentence(14),
        ];
    }
}

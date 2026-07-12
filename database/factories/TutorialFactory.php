<?php

namespace Database\Factories;

use App\Enums\TutorialAudience;
use App\Models\Tutorial;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tutorial>
 */
class TutorialFactory extends Factory
{
    protected $model = Tutorial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'video_url' => 'https://www.youtube.com/watch?v='.fake()->regexify('[A-Za-z0-9]{11}'),
            'audience' => fake()->randomElement(TutorialAudience::cases()),
            'description' => fake()->optional()->sentence(12),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function forTutor(): static
    {
        return $this->state(fn (): array => ['audience' => TutorialAudience::Tutor]);
    }

    public function forGuardian(): static
    {
        return $this->state(fn (): array => ['audience' => TutorialAudience::Guardian]);
    }

    public function forAll(): static
    {
        return $this->state(fn (): array => ['audience' => TutorialAudience::All]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}

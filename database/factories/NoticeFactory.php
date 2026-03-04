<?php

namespace Database\Factories;

use App\Enums\NoticeAudience;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notice>
 */
class NoticeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Notice>
     */
    protected $model = Notice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(6),
            'body' => fake()->paragraphs(3, true),
            'audience' => fake()->randomElement(NoticeAudience::cases()),
            'expires_at' => fake()->optional(0.5)->dateTimeBetween('+1 week', '+3 months'),
            'published_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'created_by_user_id' => User::factory(),
            'is_active' => true,
        ];
    }

    /**
     * Set the notice as expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => fake()->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Set the notice as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Set the notice audience to tutor only.
     */
    public function forTutors(): static
    {
        return $this->state(fn (array $attributes): array => [
            'audience' => NoticeAudience::Tutor,
        ]);
    }

    /**
     * Set the notice audience to guardian only.
     */
    public function forGuardians(): static
    {
        return $this->state(fn (array $attributes): array => [
            'audience' => NoticeAudience::Guardian,
        ]);
    }

    /**
     * Set the notice audience to both.
     */
    public function forAll(): static
    {
        return $this->state(fn (array $attributes): array => [
            'audience' => NoticeAudience::Both,
        ]);
    }

    /**
     * Set the notice to never expire.
     */
    public function neverExpires(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => null,
        ]);
    }
}

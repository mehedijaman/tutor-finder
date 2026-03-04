<?php

namespace Database\Factories;

use App\Enums\FaqAudience;
use App\Enums\FaqStatus;
use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => fake()->sentence(8),
            'answer' => '<p>'.fake()->paragraph(3).'</p>',
            'audience' => fake()->randomElement([
                FaqAudience::Tutor,
                FaqAudience::Guardian,
                FaqAudience::Both,
            ]),
            'status' => fake()->randomElement([FaqStatus::Active, FaqStatus::Inactive]),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}

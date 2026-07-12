<?php

namespace Database\Factories;

use App\Models\SmsSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsSetting>
 */
class SmsSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Gateway '.fake()->unique()->numerify('###'),
            'provider' => 'Ssl',
            'credentials' => [
                'api_token' => fake()->bothify('token-####'),
                'sid' => fake()->bothify('SID##'),
                'csms_id' => fake()->bothify('SENDER##'),
            ],
            'is_default' => false,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that this setting is the default gateway.
     */
    public function defaultSetting(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that this setting is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_default' => false,
            'is_active' => false,
        ]);
    }
}

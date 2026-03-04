<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SmtpSetting>
 */
class SmtpSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'SMTP Gateway '.fake()->unique()->numerify('###'),
            'driver' => 'smtp',
            'from_address' => fake()->unique()->safeEmail(),
            'from_name' => fake()->company(),
            'credentials' => [
                'host' => 'smtp.'.fake()->domainName(),
                'port' => fake()->randomElement(['25', '465', '587', '2525']),
                'username' => fake()->safeEmail(),
                'password' => fake()->password(12, 16),
                'encryption' => fake()->randomElement(['tls', 'ssl', '']),
            ],
            'is_default' => false,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that this setting is the default mailer.
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

    /**
     * Configure for log driver (no credentials needed).
     */
    public function logDriver(): static
    {
        return $this->state(fn (array $attributes): array => [
            'driver' => 'log',
            'credentials' => [],
        ]);
    }

    /**
     * Configure for sendmail driver.
     */
    public function sendmailDriver(): static
    {
        return $this->state(fn (array $attributes): array => [
            'driver' => 'sendmail',
            'credentials' => [
                'path' => '/usr/sbin/sendmail -bs -i',
            ],
        ]);
    }
}

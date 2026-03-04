<?php

namespace Database\Factories;

use App\Enums\PaymentGatewayType;
use App\Enums\TaxonomyStatus;
use App\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGateway>
 */
class PaymentGatewayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gateway' => fake()->randomElement([
                PaymentGatewayType::Bkash,
                PaymentGatewayType::Sslcommerz,
                PaymentGatewayType::Manual,
            ]),
            'name' => fake()->company(),
            'status' => TaxonomyStatus::Active,
            'credentials' => [],
            'notes' => null,
        ];
    }

    public function bkash(): static
    {
        return $this->state(fn (): array => [
            'gateway' => PaymentGatewayType::Bkash,
            'name' => 'bKash',
        ]);
    }

    public function sslcommerz(): static
    {
        return $this->state(fn (): array => [
            'gateway' => PaymentGatewayType::Sslcommerz,
            'name' => 'SSLCommerz',
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (): array => [
            'gateway' => PaymentGatewayType::Manual,
            'name' => 'Manual',
            'credentials' => [],
            'notes' => 'Manual payment requires admin approval.',
        ]);
    }
}

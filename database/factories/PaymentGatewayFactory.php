<?php

namespace Database\Factories;

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
                PaymentGateway::GATEWAY_BKASH,
                PaymentGateway::GATEWAY_SSLCOMMERZ,
                PaymentGateway::GATEWAY_MANUAL,
            ]),
            'name' => fake()->company(),
            'status' => PaymentGateway::STATUS_ACTIVE,
            'credentials' => [],
            'notes' => null,
        ];
    }

    public function bkash(): static
    {
        return $this->state(fn (): array => [
            'gateway' => PaymentGateway::GATEWAY_BKASH,
            'name' => 'bKash',
        ]);
    }

    public function sslcommerz(): static
    {
        return $this->state(fn (): array => [
            'gateway' => PaymentGateway::GATEWAY_SSLCOMMERZ,
            'name' => 'SSLCommerz',
        ]);
    }

    public function manual(): static
    {
        return $this->state(fn (): array => [
            'gateway' => PaymentGateway::GATEWAY_MANUAL,
            'name' => 'Manual',
            'credentials' => [],
            'notes' => 'Manual payment requires admin approval.',
        ]);
    }
}

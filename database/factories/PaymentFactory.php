<?php

namespace Database\Factories;

use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'gateway' => fake()->randomElement([
                PaymentGatewayType::Bkash,
                PaymentGatewayType::Sslcommerz,
                PaymentGatewayType::Manual,
            ]),
            'provider_txn_id' => strtoupper(fake()->bothify('TXN#######')),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'status' => PaymentStatus::Pending,
            'provider_payload' => null,
        ];
    }
}

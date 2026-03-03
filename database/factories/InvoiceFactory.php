<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $payer = User::factory()->guardian();

        return [
            'invoice_no' => 'INV-'.strtoupper(fake()->unique()->bothify('########')),
            'invoiceable_type' => VerificationRequest::class,
            'invoiceable_id' => VerificationRequest::factory(),
            'user_id' => $payer,
            'payer_user_id' => $payer,
            'payee_user_id' => User::factory()->state([
                'role' => 'platform',
                'status' => 'active',
            ]),
            'type' => Invoice::TYPE_TUTOR_VERIFICATION_FEE,
            'job_assignment_id' => null,
            'amount' => 500,
            'currency' => 'BDT',
            'status' => Invoice::STATUS_UNPAID,
            'due_at' => now()->addDays(7),
            'expires_at' => now()->addDays(7),
            'issued_by' => null,
            'issued_at' => now(),
            'paid_at' => null,
            'payment_gateway' => null,
            'payment_method' => null,
            'payment_reference' => null,
            'transaction_id' => null,
            'gateway_payload' => null,
            'notes' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Invoice $invoice): void {
            if ($invoice->invoiceable_type !== VerificationRequest::class) {
                return;
            }

            /** @var VerificationRequest|null $verificationRequest */
            $verificationRequest = $invoice->invoiceable()->first();

            if (! $verificationRequest instanceof VerificationRequest) {
                return;
            }

            $invoice->forceFill([
                'user_id' => $verificationRequest->user_id,
                'payer_user_id' => $verificationRequest->user_id,
                'type' => $verificationRequest->role === VerificationRequest::ROLE_GUARDIAN
                    ? Invoice::TYPE_GUARDIAN_VERIFICATION_FEE
                    : Invoice::TYPE_TUTOR_VERIFICATION_FEE,
            ])->save();
        });
    }
}

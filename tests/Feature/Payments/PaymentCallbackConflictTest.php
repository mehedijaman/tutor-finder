<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\VerificationRequest;
use Ihasan\Bkash\Facades\Bkash;

function configureBkashGatewayForCallbackTests(): void
{
    PaymentGateway::query()->updateOrCreate(
        ['gateway' => PaymentGateway::GATEWAY_BKASH],
        [
            'name' => 'bKash',
            'status' => PaymentGateway::STATUS_ACTIVE,
            'credentials' => [
                'app_key' => 'bkash-app-key',
                'app_secret' => 'bkash-app-secret',
                'username' => 'bkash-user',
                'password' => 'bkash-password',
                'base_url' => 'https://tokenized.sandbox.bka.sh',
            ],
            'notes' => null,
        ],
    );
}

it('treats callback as idempotent when invoice is already paid with same provider transaction', function () {
    configureBkashGatewayForCallbackTests();

    $tutor = User::factory()->tutor()->create();
    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_VERIFIED,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $tutor->id,
        'payer_user_id' => $tutor->id,
        'status' => Invoice::STATUS_PAID,
        'payment_gateway' => Invoice::GATEWAY_BKASH,
        'payment_reference' => 'PID-IDEMPOTENT-1',
        'transaction_id' => 'BKASH-TXN-1',
        'amount' => 500,
        'currency' => 'BDT',
        'paid_at' => now(),
    ]);

    Payment::query()->create([
        'invoice_id' => $invoice->id,
        'gateway' => Invoice::GATEWAY_BKASH,
        'provider_txn_id' => 'BKASH-TXN-1',
        'amount' => 500,
        'status' => Payment::STATUS_PAID,
        'provider_payload' => null,
    ]);

    Bkash::shouldReceive('queryPayment')
        ->once()
        ->with('PID-IDEMPOTENT-1')
        ->andReturn([
            'trxID' => 'BKASH-TXN-1',
            'transactionStatus' => 'Completed',
            'merchantInvoiceNumber' => $invoice->invoice_no,
            'currency' => 'BDT',
            'amount' => '500',
        ]);

    $this->get(route('payment.bkash.callback', [
        'paymentID' => 'PID-IDEMPOTENT-1',
        'status' => 'success',
    ]))->assertRedirect('/tutor/verification');

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_PAID);
    expect($invoice->fresh()->transaction_id)->toBe('BKASH-TXN-1');
    expect(Payment::query()->where('invoice_id', $invoice->id)->count())->toBe(1);
});

it('records failed attempt on callback conflict and keeps paid invoice unchanged', function () {
    configureBkashGatewayForCallbackTests();

    $tutor = User::factory()->tutor()->create();
    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_VERIFIED,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $tutor->id,
        'payer_user_id' => $tutor->id,
        'status' => Invoice::STATUS_PAID,
        'payment_gateway' => Invoice::GATEWAY_BKASH,
        'payment_reference' => 'PID-CONFLICT-1',
        'transaction_id' => 'BKASH-TXN-OLD',
        'amount' => 500,
        'currency' => 'BDT',
        'paid_at' => now(),
    ]);

    Payment::query()->create([
        'invoice_id' => $invoice->id,
        'gateway' => Invoice::GATEWAY_BKASH,
        'provider_txn_id' => 'BKASH-TXN-OLD',
        'amount' => 500,
        'status' => Payment::STATUS_PAID,
        'provider_payload' => null,
    ]);

    Bkash::shouldReceive('queryPayment')
        ->once()
        ->with('PID-CONFLICT-1')
        ->andReturn([
            'trxID' => 'BKASH-TXN-NEW',
            'transactionStatus' => 'Completed',
            'merchantInvoiceNumber' => $invoice->invoice_no,
            'currency' => 'BDT',
            'amount' => '500',
        ]);

    $this->get(route('payment.bkash.callback', [
        'paymentID' => 'PID-CONFLICT-1',
        'status' => 'success',
    ]))
        ->assertRedirect('/tutor/verification')
        ->assertSessionHasErrors('payment');

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_PAID);
    expect($invoice->fresh()->transaction_id)->toBe('BKASH-TXN-OLD');

    $failedAttempt = Payment::query()
        ->where('invoice_id', $invoice->id)
        ->where('status', Payment::STATUS_FAILED)
        ->latest('id')
        ->first();

    expect($failedAttempt)->not->toBeNull();
    expect($failedAttempt?->provider_payload['conflict_provider_txn_id'] ?? null)->toBe('BKASH-TXN-NEW');
});

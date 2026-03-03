<?php

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\VerificationRequest;
use Ihasan\Bkash\Facades\Bkash;
use Raziul\Sslcommerz\Data\PaymentResponse;
use Raziul\Sslcommerz\Facades\Sslcommerz;

function configureGatewaySettings(): void
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

    PaymentGateway::query()->updateOrCreate(
        ['gateway' => PaymentGateway::GATEWAY_SSLCOMMERZ],
        [
            'name' => 'SSLCommerz',
            'status' => PaymentGateway::STATUS_ACTIVE,
            'credentials' => [
                'store_id' => 'ssl-store-id',
                'store_password' => 'ssl-store-password',
                'mode' => 'sandbox',
            ],
            'notes' => null,
        ],
    );

    PaymentGateway::query()->updateOrCreate(
        ['gateway' => PaymentGateway::GATEWAY_MANUAL],
        [
            'name' => 'Manual',
            'status' => PaymentGateway::STATUS_ACTIVE,
            'credentials' => [],
            'notes' => 'Manual payment requires admin approval.',
        ],
    );

    config([
        'sslcommerz.store.id' => 'ssl-store-id',
        'sslcommerz.store.password' => 'ssl-store-password',
        'sslcommerz.sandbox' => true,
        'sslcommerz.route.success' => 'payment.sslcommerz.success',
        'sslcommerz.route.failure' => 'payment.sslcommerz.fail',
        'sslcommerz.route.cancel' => 'payment.sslcommerz.cancel',
        'sslcommerz.route.ipn' => 'payment.sslcommerz.ipn',
    ]);
}

it('completes bKash payment and verifies tutor after validated callback', function () {
    configureGatewaySettings();

    $tutor = User::factory()->tutor()->create([
        'verification_status' => User::VERIFICATION_STATUS_INVOICED,
        'verification_type' => VerificationRequest::ROLE_TUTOR,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_INVOICED,
        'currency' => 'BDT',
        'fee_amount' => 500,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $tutor->id,
        'status' => Invoice::STATUS_UNPAID,
        'amount' => 500,
        'currency' => 'BDT',
        'expires_at' => now()->addDays(2),
    ]);

    Bkash::shouldReceive('createPayment')
        ->once()
        ->andReturn([
            'bkashURL' => 'https://sandbox.bkash.test/pay',
            'paymentID' => 'PID-1001',
        ]);

    $this->actingAs($tutor)
        ->post(route('payment.bkash.start', $invoice))
        ->assertRedirect('https://sandbox.bkash.test/pay');

    $invoice->refresh();

    expect($invoice->status)->toBe(Invoice::STATUS_UNPAID);
    expect($invoice->payment_gateway)->toBe(Invoice::GATEWAY_BKASH);
    expect($invoice->payment_reference)->toBe('PID-1001');
    expect($invoice->payments()->where('status', Payment::STATUS_PENDING)->count())->toBe(1);

    Bkash::shouldReceive('queryPayment')
        ->once()
        ->with('PID-1001')
        ->andReturn([
            'trxID' => 'BKASH-TRX-1001',
            'transactionStatus' => 'Completed',
            'merchantInvoiceNumber' => $invoice->invoice_no,
            'currency' => 'BDT',
            'amount' => '500',
        ]);

    $this->get(route('payment.bkash.callback', [
        'paymentID' => 'PID-1001',
        'status' => 'success',
    ]))->assertRedirect('/tutor/verification');

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_PAID);
    expect($invoice->fresh()->transaction_id)->toBe('BKASH-TRX-1001');
    expect($invoice->payments()->where('status', Payment::STATUS_PAID)->count())->toBe(1);
    expect($verificationRequest->fresh()->status)->toBe(VerificationRequest::STATUS_VERIFIED);

    $tutor->refresh();

    expect($tutor->verification_status)->toBe(User::VERIFICATION_STATUS_VERIFIED);
    expect($tutor->verified_at)->not->toBeNull();
});

it('completes SSLCommerz payment and supports idempotent IPN handling', function () {
    configureGatewaySettings();

    $guardian = User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_INVOICED,
        'verification_type' => VerificationRequest::ROLE_GUARDIAN,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $guardian->id,
        'role' => VerificationRequest::ROLE_GUARDIAN,
        'status' => VerificationRequest::STATUS_INVOICED,
        'currency' => 'BDT',
        'fee_amount' => 500,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $guardian->id,
        'status' => Invoice::STATUS_UNPAID,
        'amount' => 500,
        'currency' => 'BDT',
        'expires_at' => now()->addDays(2),
    ]);

    $gatewayResponse = new PaymentResponse([
        'status' => 'SUCCESS',
        'GatewayPageURL' => 'https://sandbox.sslcommerz.com/gwprocess/v4',
        'sessionkey' => 'ssl-session',
    ]);

    Sslcommerz::shouldReceive('setCallbackUrls')->once()->andReturnSelf();
    Sslcommerz::shouldReceive('setOrder')->once()->andReturnSelf();
    Sslcommerz::shouldReceive('setCustomer')->once()->andReturnSelf();
    Sslcommerz::shouldReceive('setShippingInfo')->once()->andReturnSelf();
    Sslcommerz::shouldReceive('makePayment')->once()->andReturn($gatewayResponse);

    $this->actingAs($guardian)
        ->post(route('payment.sslcommerz.start', $invoice))
        ->assertRedirect('https://sandbox.sslcommerz.com/gwprocess/v4');

    $invoice->refresh();

    expect($invoice->status)->toBe(Invoice::STATUS_UNPAID);
    expect($invoice->payment_gateway)->toBe(Invoice::GATEWAY_SSLCOMMERZ);
    expect($invoice->payment_reference)->toBe($invoice->invoice_no);
    expect($invoice->payments()->where('status', Payment::STATUS_PENDING)->count())->toBe(1);

    Sslcommerz::shouldReceive('verifyHash')->twice()->andReturnTrue();
    Sslcommerz::shouldReceive('validatePayment')->twice()->andReturnTrue();

    $payload = [
        'tran_id' => $invoice->invoice_no,
        'bank_tran_id' => 'SSLC-BANK-1001',
        'val_id' => 'VAL-1001',
        'verify_sign' => 'signature',
        'verify_key' => 'key',
    ];

    $this->get(route('payment.sslcommerz.success', $payload))
        ->assertRedirect('/guardian/verification');

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_PAID);
    expect($invoice->fresh()->transaction_id)->toBe('SSLC-BANK-1001');
    expect($invoice->payments()->where('status', Payment::STATUS_PAID)->count())->toBe(1);
    expect($verificationRequest->fresh()->status)->toBe(VerificationRequest::STATUS_VERIFIED);
    expect($guardian->fresh()->verification_status)->toBe(User::VERIFICATION_STATUS_VERIFIED);

    $this->post(route('payment.sslcommerz.ipn'), $payload)
        ->assertOk()
        ->assertJson([
            'ok' => true,
        ]);
});

it('rejects tampered bKash callback and does not verify user', function () {
    configureGatewaySettings();

    $tutor = User::factory()->tutor()->create([
        'verification_status' => User::VERIFICATION_STATUS_INVOICED,
        'verification_type' => VerificationRequest::ROLE_TUTOR,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_INVOICED,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $tutor->id,
        'status' => Invoice::STATUS_UNPAID,
        'payment_gateway' => Invoice::GATEWAY_BKASH,
        'payment_reference' => 'PID-TAMPER-1',
        'amount' => 500,
        'currency' => 'BDT',
        'expires_at' => now()->addDay(),
    ]);

    Bkash::shouldReceive('queryPayment')
        ->once()
        ->with('PID-TAMPER-1')
        ->andReturn([
            'trxID' => 'TRX-TAMPER-1',
            'transactionStatus' => 'Completed',
            'merchantInvoiceNumber' => $invoice->invoice_no,
            'currency' => 'BDT',
            'amount' => '250',
        ]);

    $this->get(route('payment.bkash.callback', [
        'paymentID' => 'PID-TAMPER-1',
        'status' => 'success',
    ]))->assertRedirect('/tutor/verification');

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_UNPAID);
    expect($invoice->payments()->where('status', Payment::STATUS_FAILED)->exists())->toBeTrue();
    expect($verificationRequest->fresh()->status)->toBe(VerificationRequest::STATUS_INVOICED);
    expect($tutor->fresh()->verification_status)->toBe(User::VERIFICATION_STATUS_INVOICED);
    expect($tutor->fresh()->verified_at)->toBeNull();
});

it('rejects payment initiation for expired invoice', function () {
    configureGatewaySettings();

    $tutor = User::factory()->tutor()->create();

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_INVOICED,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $tutor->id,
        'status' => Invoice::STATUS_UNPAID,
        'expires_at' => now()->subMinute(),
    ]);

    $this->actingAs($tutor)
        ->from('/tutor/verification')
        ->post(route('payment.bkash.start', $invoice))
        ->assertRedirect('/tutor/verification')
        ->assertSessionHasErrors('payment');

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_UNPAID);
});

it('uses atomic transition so second payment start is blocked while a pending attempt exists', function () {
    configureGatewaySettings();

    $tutor = User::factory()->tutor()->create();

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $tutor->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_INVOICED,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $tutor->id,
        'status' => Invoice::STATUS_UNPAID,
        'expires_at' => now()->addDay(),
    ]);

    Bkash::shouldReceive('createPayment')
        ->once()
        ->andReturn([
            'bkashURL' => 'https://sandbox.bkash.test/pay-2',
            'paymentID' => 'PID-RACE-1',
        ]);

    $this->actingAs($tutor)
        ->post(route('payment.bkash.start', $invoice))
        ->assertRedirect('https://sandbox.bkash.test/pay-2');

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_UNPAID);
    expect($invoice->payments()->where('status', Payment::STATUS_PENDING)->count())->toBe(1);

    $this->actingAs($tutor)
        ->from('/tutor/verification')
        ->post(route('payment.bkash.start', $invoice))
        ->assertRedirect('/tutor/verification')
        ->assertSessionHasErrors('payment');
});

it('records failed attempt on SSLCommerz fail callback and keeps user unverified', function () {
    configureGatewaySettings();

    $guardian = User::factory()->guardian()->create([
        'verification_status' => User::VERIFICATION_STATUS_INVOICED,
        'verification_type' => VerificationRequest::ROLE_GUARDIAN,
    ]);

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $guardian->id,
        'role' => VerificationRequest::ROLE_GUARDIAN,
        'status' => VerificationRequest::STATUS_INVOICED,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $guardian->id,
        'status' => Invoice::STATUS_UNPAID,
        'payment_gateway' => Invoice::GATEWAY_SSLCOMMERZ,
        'payment_reference' => 'INV-FAIL-1001',
    ]);

    $this->get(route('payment.sslcommerz.fail', [
        'tran_id' => 'INV-FAIL-1001',
    ]))->assertRedirect('/guardian/verification');

    expect($invoice->fresh()->status)->toBe(Invoice::STATUS_UNPAID);
    expect($invoice->payments()->where('status', Payment::STATUS_FAILED)->exists())->toBeTrue();
    expect($verificationRequest->fresh()->status)->toBe(VerificationRequest::STATUS_INVOICED);
    expect($guardian->fresh()->verification_status)->toBe(User::VERIFICATION_STATUS_INVOICED);
    expect($guardian->fresh()->verified_at)->toBeNull();
});

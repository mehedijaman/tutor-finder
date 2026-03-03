<?php

use App\Models\Invoice;
use App\Models\User;
use App\Models\VerificationRequest;

it('forbids user from initiating payment for another users invoice', function () {
    $owner = User::factory()->tutor()->create();
    $otherUser = User::factory()->tutor()->create();

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $owner->id,
        'role' => VerificationRequest::ROLE_TUTOR,
        'status' => VerificationRequest::STATUS_INVOICED,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $owner->id,
        'status' => Invoice::STATUS_UNPAID,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs($otherUser)
        ->post(route('payment.bkash.start', $invoice))
        ->assertForbidden();

    $this->actingAs($otherUser)
        ->post(route('payment.sslcommerz.start', $invoice))
        ->assertForbidden();
});

it('requires authentication for payment initiation routes', function () {
    $owner = User::factory()->guardian()->create();

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $owner->id,
        'role' => VerificationRequest::ROLE_GUARDIAN,
        'status' => VerificationRequest::STATUS_INVOICED,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $owner->id,
        'status' => Invoice::STATUS_UNPAID,
    ]);

    $this->post(route('payment.bkash.start', $invoice))
        ->assertRedirect(route('login', absolute: false));

    $this->post(route('payment.sslcommerz.start', $invoice))
        ->assertRedirect(route('login', absolute: false));
});

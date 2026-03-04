<?php

use App\Enums\InvoiceStatus;
use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Models\VerificationRequest;

it('allows payer to pay their own invoice', function () {
    $user = User::factory()->tutor()->create();

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $user->id,
        'role' => VerificationRole::Tutor,
        'status' => VerificationStatus::Invoiced,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $user->id,
        'status' => InvoiceStatus::Unpaid,
    ]);

    expect($user->can('pay', $invoice))->toBeTrue();
});

it('forbids user from paying another users invoice', function () {
    $owner = User::factory()->tutor()->create();
    $otherUser = User::factory()->tutor()->create();

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $owner->id,
        'role' => VerificationRole::Tutor,
        'status' => VerificationStatus::Invoiced,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $owner->id,
        'status' => InvoiceStatus::Unpaid,
    ]);

    expect($otherUser->can('pay', $invoice))->toBeFalse();
});

it('allows payer to view their own invoice', function () {
    $user = User::factory()->guardian()->create();

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $user->id,
        'role' => VerificationRole::Guardian,
        'status' => VerificationStatus::Invoiced,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $user->id,
        'status' => InvoiceStatus::Unpaid,
    ]);

    expect($user->can('view', $invoice))->toBeTrue();
});

it('forbids user from viewing another users invoice', function () {
    $owner = User::factory()->guardian()->create();
    $otherUser = User::factory()->guardian()->create();

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $owner->id,
        'role' => VerificationRole::Guardian,
        'status' => VerificationStatus::Invoiced,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $owner->id,
        'status' => InvoiceStatus::Unpaid,
    ]);

    expect($otherUser->can('view', $invoice))->toBeFalse();
});

it('allows payer by payer_user_id to pay the invoice', function () {
    $payer = User::factory()->tutor()->create();
    $user = User::factory()->guardian()->create();

    $verificationRequest = VerificationRequest::factory()->create([
        'user_id' => $user->id,
        'role' => VerificationRole::Guardian,
        'status' => VerificationStatus::Invoiced,
    ]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => VerificationRequest::class,
        'invoiceable_id' => $verificationRequest->id,
        'user_id' => $user->id,
        'status' => InvoiceStatus::Unpaid,
    ]);

    // Override payer after factory afterCreating callback
    $invoice->forceFill(['payer_user_id' => $payer->id])->save();
    $invoice->refresh();

    expect($payer->can('pay', $invoice))->toBeTrue();
    expect($user->can('pay', $invoice))->toBeFalse();
});

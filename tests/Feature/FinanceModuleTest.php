<?php

use App\Enums\InvoiceStatus;
use App\Enums\LedgerEntryType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use App\Models\WalletLedgerEntry;
use App\Notifications\PaymentNotification;
use App\Policies\PaymentPolicy;
use Database\Seeders\AdminRolesAndPermissionsSeeder;

// ============================================================
// Invoice formatted_amount accessor tests
// ============================================================

it('formats invoice amount with BDT currency symbol', function (): void {
    $invoice = Invoice::factory()->make([
        'amount' => 1500.50,
        'currency' => 'BDT',
    ]);

    expect($invoice->formatted_amount)->toBe('৳1,500.50');
});

it('formats invoice amount with USD currency symbol', function (): void {
    $invoice = Invoice::factory()->make([
        'amount' => 99.99,
        'currency' => 'USD',
    ]);

    expect($invoice->formatted_amount)->toBe('$99.99');
});

it('formats invoice amount with unknown currency code', function (): void {
    $invoice = Invoice::factory()->make([
        'amount' => 250.00,
        'currency' => 'JPY',
    ]);

    expect($invoice->formatted_amount)->toBe('JPY 250.00');
});

// ============================================================
// User wallet balance tests
// ============================================================

it('calculates zero wallet balance when no ledger entries', function (): void {
    $user = User::factory()->tutor()->create();

    expect($user->getWalletBalance())->toBe(0.0);
});

it('calculates wallet balance from ledger entries', function (): void {
    $user = User::factory()->tutor()->create();

    WalletLedgerEntry::factory()->create([
        'owner_user_id' => $user->id,
        'type' => LedgerEntryType::Credit,
        'amount' => 5000.00,
        'currency' => 'BDT',
    ]);

    WalletLedgerEntry::factory()->create([
        'owner_user_id' => $user->id,
        'type' => LedgerEntryType::Debit,
        'amount' => 1500.00,
        'currency' => 'BDT',
    ]);

    expect($user->getWalletBalance())->toBe(3500.0);
});

it('formats wallet balance with currency symbol', function (): void {
    $user = User::factory()->tutor()->create();

    WalletLedgerEntry::factory()->create([
        'owner_user_id' => $user->id,
        'type' => LedgerEntryType::Credit,
        'amount' => 2500.50,
        'currency' => 'BDT',
    ]);

    expect($user->getFormattedWalletBalance())->toBe('৳2,500.50');
});

it('calculates wallet balance per currency', function (): void {
    $user = User::factory()->tutor()->create();

    WalletLedgerEntry::factory()->create([
        'owner_user_id' => $user->id,
        'type' => LedgerEntryType::Credit,
        'amount' => 1000.00,
        'currency' => 'BDT',
    ]);

    WalletLedgerEntry::factory()->create([
        'owner_user_id' => $user->id,
        'type' => LedgerEntryType::Credit,
        'amount' => 50.00,
        'currency' => 'USD',
    ]);

    expect($user->getWalletBalance('BDT'))->toBe(1000.0)
        ->and($user->getWalletBalance('USD'))->toBe(50.0);
});

// ============================================================
// PaymentPolicy tests
// ============================================================

it('allows admin to view any payments', function (): void {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $admin = User::factory()->admin()->create();

    $policy = new PaymentPolicy;

    expect($policy->viewAny($admin))->toBeTrue();
});

it('denies non-admin viewing any payments by default', function (): void {
    $tutor = User::factory()->tutor()->create();

    $policy = new PaymentPolicy;

    expect($policy->viewAny($tutor))->toBeFalse();
});

it('allows user to view their own payment', function (): void {
    $tutor = User::factory()->tutor()->create();
    $assignment = TuitionJobAssignment::factory()->create(['tutor_user_id' => $tutor->id]);

    $invoice = Invoice::factory()->create([
        'invoiceable_type' => TuitionJobAssignment::class,
        'invoiceable_id' => $assignment->id,
        'payer_user_id' => $tutor->id,
        'user_id' => $tutor->id,
    ]);

    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    $policy = new PaymentPolicy;

    expect($policy->view($tutor, $payment))->toBeTrue();
});

it('denies user viewing payment belonging to another user', function (): void {
    $tutor = User::factory()->tutor()->create();
    $otherUser = User::factory()->tutor()->create();

    $invoice = Invoice::factory()->create([
        'payer_user_id' => $otherUser->id,
        'user_id' => $otherUser->id,
    ]);

    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    $policy = new PaymentPolicy;

    expect($policy->view($tutor, $payment))->toBeFalse();
});

it('allows admin to view any payment', function (): void {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $admin = User::factory()->admin()->create();
    $tutor = User::factory()->tutor()->create();

    $invoice = Invoice::factory()->create([
        'payer_user_id' => $tutor->id,
    ]);

    $payment = Payment::factory()->create([
        'invoice_id' => $invoice->id,
    ]);

    $policy = new PaymentPolicy;

    expect($policy->view($admin, $payment))->toBeTrue();
});

// ============================================================
// PaymentNotification tests
// ============================================================

it('creates payment notification for payment success event', function (): void {
    $user = User::factory()->tutor()->create();

    $invoice = Invoice::factory()->create([
        'payer_user_id' => $user->id,
        'status' => InvoiceStatus::Paid,
        'amount' => 500,
        'currency' => 'BDT',
    ]);

    $notification = new PaymentNotification($invoice, 'payment.success');

    $mailMessage = $notification->toMail($user);

    expect($mailMessage->subject)->toContain('Payment Successful')
        ->and($mailMessage->subject)->toContain($invoice->invoice_no);
});

it('creates payment notification array with event metadata', function (): void {
    $user = User::factory()->tutor()->create();

    $invoice = Invoice::factory()->create([
        'payer_user_id' => $user->id,
        'amount' => 1000,
        'currency' => 'BDT',
    ]);

    $notification = new PaymentNotification($invoice, 'invoice.created');
    $data = $notification->toArray($user);

    expect($data['event'])->toBe('invoice.created')
        ->and($data['title'])->toBe('New Invoice')
        ->and($data['meta']['invoice_id'])->toBe($invoice->id)
        ->and($data['meta']['amount'])->toBe($invoice->amount);
});

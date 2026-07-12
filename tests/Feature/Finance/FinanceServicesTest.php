<?php

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\LedgerEntryType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use App\Models\WalletLedgerEntry;
use App\Services\Finance\InvoiceLifecycleService;
use App\Services\Finance\LedgerJournalService;

// ============================================================
// LedgerJournalService tests
// ============================================================

describe('LedgerJournalService::postDoubleEntry', function (): void {
    it('creates exactly two balanced ledger entries', function (): void {
        $payer = User::factory()->tutor()->create();
        $payee = User::factory()->create(['role' => 'platform', 'status' => 'active']);

        $service = app(LedgerJournalService::class);

        $journalUuid = $service->postDoubleEntry(
            ownerDebitUserId: $payer->id,
            ownerCreditUserId: $payee->id,
            amount: 500.00,
            currency: 'BDT',
            referenceType: 'test',
            referenceId: 1,
        );

        $entries = WalletLedgerEntry::query()->where('journal_uuid', $journalUuid)->get();

        expect($entries)->toHaveCount(2);

        $debit = $entries->firstWhere('type', LedgerEntryType::Debit);
        $credit = $entries->firstWhere('type', LedgerEntryType::Credit);

        expect($debit->owner_user_id)->toBe($payer->id)
            ->and($debit->amount)->toBe('500.00')
            ->and($credit->owner_user_id)->toBe($payee->id)
            ->and($credit->amount)->toBe('500.00');
    });

    it('throws exception when amount is zero', function (): void {
        $payer = User::factory()->tutor()->create();
        $payee = User::factory()->create(['role' => 'platform', 'status' => 'active']);

        $service = app(LedgerJournalService::class);

        $service->postDoubleEntry(
            ownerDebitUserId: $payer->id,
            ownerCreditUserId: $payee->id,
            amount: 0,
            currency: 'BDT',
            referenceType: 'test',
            referenceId: 1,
        );
    })->throws(DomainException::class, 'Ledger amount must be greater than zero');

    it('throws exception when amount is negative', function (): void {
        $payer = User::factory()->tutor()->create();
        $payee = User::factory()->create(['role' => 'platform', 'status' => 'active']);

        $service = app(LedgerJournalService::class);

        $service->postDoubleEntry(
            ownerDebitUserId: $payer->id,
            ownerCreditUserId: $payee->id,
            amount: -100,
            currency: 'BDT',
            referenceType: 'test',
            referenceId: 1,
        );
    })->throws(DomainException::class, 'Ledger amount must be greater than zero');

    it('throws exception when payer and payee are same user', function (): void {
        $user = User::factory()->tutor()->create();

        $service = app(LedgerJournalService::class);

        $service->postDoubleEntry(
            ownerDebitUserId: $user->id,
            ownerCreditUserId: $user->id,
            amount: 500.00,
            currency: 'BDT',
            referenceType: 'test',
            referenceId: 1,
        );
    })->throws(DomainException::class, 'Ledger counterparty users must be different');

    it('normalizes currency to uppercase', function (): void {
        $payer = User::factory()->tutor()->create();
        $payee = User::factory()->create(['role' => 'platform', 'status' => 'active']);

        $service = app(LedgerJournalService::class);

        $journalUuid = $service->postDoubleEntry(
            ownerDebitUserId: $payer->id,
            ownerCreditUserId: $payee->id,
            amount: 100.00,
            currency: 'bdt',
            referenceType: 'test',
            referenceId: 1,
        );

        $entry = WalletLedgerEntry::query()->where('journal_uuid', $journalUuid)->first();

        expect($entry->currency)->toBe('BDT');
    });

    it('marks reversal entries correctly', function (): void {
        $payer = User::factory()->tutor()->create();
        $payee = User::factory()->create(['role' => 'platform', 'status' => 'active']);

        $service = app(LedgerJournalService::class);

        $originalJournalUuid = $service->postDoubleEntry(
            ownerDebitUserId: $payer->id,
            ownerCreditUserId: $payee->id,
            amount: 500.00,
            currency: 'BDT',
            referenceType: 'test',
            referenceId: 1,
        );

        $reversalJournalUuid = $service->postDoubleEntry(
            ownerDebitUserId: $payee->id,
            ownerCreditUserId: $payer->id,
            amount: 500.00,
            currency: 'BDT',
            referenceType: 'test',
            referenceId: 1,
            isReversal: true,
            reversesJournalUuid: $originalJournalUuid,
        );

        $reversalEntry = WalletLedgerEntry::query()
            ->where('journal_uuid', $reversalJournalUuid)
            ->first();

        expect($reversalEntry->is_reversal)->toBeTrue()
            ->and($reversalEntry->reverses_journal_uuid)->toBe($originalJournalUuid);
    });
});

describe('LedgerJournalService::postInvoicePayment', function (): void {
    it('posts payment journal entries for invoice', function (): void {
        $tutor = User::factory()->tutor()->create();
        $platform = User::factory()->create(['role' => 'platform', 'status' => 'active']);
        $assignment = TuitionJobAssignment::factory()->create(['tutor_user_id' => $tutor->id]);

        $invoice = Invoice::factory()->create([
            'invoiceable_type' => TuitionJobAssignment::class,
            'invoiceable_id' => $assignment->id,
            'payer_user_id' => $tutor->id,
            'payee_user_id' => $platform->id,
            'amount' => 1000.00,
            'currency' => 'BDT',
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 1000.00,
        ]);

        $service = app(LedgerJournalService::class);
        $journalUuid = $service->postInvoicePayment($invoice, $payment);

        expect($journalUuid)->toBeString()->not->toBeEmpty();

        $entries = WalletLedgerEntry::query()->where('journal_uuid', $journalUuid)->get();

        expect($entries)->toHaveCount(2);
    });

    it('throws exception when payer is missing', function (): void {
        $platform = User::factory()->create(['role' => 'platform', 'status' => 'active']);

        $invoice = Invoice::factory()->make([
            'payer_user_id' => null,
            'user_id' => null,
            'payee_user_id' => $platform->id,
        ]);

        $payment = Payment::factory()->make(['amount' => 500.00]);

        $service = app(LedgerJournalService::class);
        $service->postInvoicePayment($invoice, $payment);
    })->throws(DomainException::class, 'Invoice payer/payee must be set');
});

// ============================================================
// InvoiceLifecycleService tests
// ============================================================

describe('InvoiceLifecycleService::issue', function (): void {
    it('issues an invoice with generated invoice number', function (): void {
        $payer = User::factory()->tutor()->create();
        $payee = User::factory()->create(['role' => 'platform', 'status' => 'active']);
        $assignment = TuitionJobAssignment::factory()->create(['tutor_user_id' => $payer->id]);

        $service = app(InvoiceLifecycleService::class);

        $invoice = $service->issue([
            'invoiceable_type' => TuitionJobAssignment::class,
            'invoiceable_id' => $assignment->id,
            'payer_user_id' => $payer->id,
            'payee_user_id' => $payee->id,
            'type' => InvoiceType::TutorVerificationFee,
            'amount' => 500.00,
            'currency' => 'BDT',
            'status' => InvoiceStatus::Unpaid,
            'due_at' => now()->addDays(7),
        ]);

        expect($invoice->id)->not->toBeNull()
            ->and($invoice->invoice_no)->toStartWith('INV-')
            ->and($invoice->payer_user_id)->toBe($payer->id);
    });

    it('throws exception when payer is missing', function (): void {
        $payee = User::factory()->create(['role' => 'platform', 'status' => 'active']);

        $service = app(InvoiceLifecycleService::class);

        $service->issue([
            'payee_user_id' => $payee->id,
            'type' => InvoiceType::TutorVerificationFee,
            'amount' => 500.00,
        ]);
    })->throws(DomainException::class, 'Invoice requires valid payer and payee');

    it('throws exception when payee account is suspended', function (): void {
        $payer = User::factory()->tutor()->create();
        $payee = User::factory()->create(['role' => 'platform', 'status' => 'suspended']);

        $service = app(InvoiceLifecycleService::class);

        $service->issue([
            'invoiceable_type' => User::class,
            'invoiceable_id' => $payer->id,
            'payer_user_id' => $payer->id,
            'payee_user_id' => $payee->id,
            'type' => InvoiceType::TutorVerificationFee,
            'amount' => 500.00,
        ]);
    })->throws(DomainException::class, 'Invoice payee account must be active');
});

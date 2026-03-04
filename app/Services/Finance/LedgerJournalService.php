<?php

namespace App\Services\Finance;

use App\Enums\LedgerEntryType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\WalletLedgerEntry;
use DomainException;
use Illuminate\Support\Str;

class LedgerJournalService
{
    /**
     * Post invoice payment journal entries.
     */
    public function postInvoicePayment(Invoice $invoice, Payment $payment): string
    {
        $payerUserId = (int) ($invoice->payer_user_id ?? $invoice->user_id);
        $payeeUserId = (int) $invoice->payee_user_id;

        if ($payerUserId <= 0 || $payeeUserId <= 0) {
            throw new DomainException('Invoice payer/payee must be set before posting ledger entries.');
        }

        return $this->postDoubleEntry(
            ownerDebitUserId: $payerUserId,
            ownerCreditUserId: $payeeUserId,
            amount: (float) $payment->amount,
            currency: (string) $invoice->currency,
            referenceType: 'invoice',
            referenceId: (int) $invoice->id,
            metadata: [
                'payment_id' => $payment->id,
                'invoice_type' => $invoice->type,
            ],
        );
    }

    /**
     * Post refund payout journal entries.
     */
    public function postRefundPayout(
        RefundRequest $refundRequest,
        Payment $payment,
        Invoice $invoice,
        ?string $reversesJournalUuid = null,
    ): string {
        $platformUserId = (int) $invoice->payee_user_id;
        $tutorUserId = (int) ($invoice->payer_user_id ?? $invoice->user_id);

        if ($platformUserId <= 0 || $tutorUserId <= 0) {
            throw new DomainException('Refund ledger posting requires platform and tutor account owners.');
        }

        return $this->postDoubleEntry(
            ownerDebitUserId: $platformUserId,
            ownerCreditUserId: $tutorUserId,
            amount: (float) $payment->amount,
            currency: (string) $refundRequest->currency,
            referenceType: 'refund_request',
            referenceId: (int) $refundRequest->id,
            metadata: [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
            ],
            isReversal: true,
            reversesJournalUuid: $reversesJournalUuid,
        );
    }

    /**
     * Post exactly one balanced debit/credit journal.
     */
    public function postDoubleEntry(
        int $ownerDebitUserId,
        int $ownerCreditUserId,
        float $amount,
        string $currency,
        string $referenceType,
        int $referenceId,
        array $metadata = [],
        bool $isReversal = false,
        ?string $reversesJournalUuid = null,
    ): string {
        if ($amount <= 0) {
            throw new DomainException('Ledger amount must be greater than zero.');
        }

        if ($ownerDebitUserId === $ownerCreditUserId) {
            throw new DomainException('Ledger counterparty users must be different.');
        }

        $journalUuid = Str::uuid()->toString();
        $postedAt = now();
        $normalizedCurrency = strtoupper(trim($currency));

        WalletLedgerEntry::query()->create([
            'journal_uuid' => $journalUuid,
            'owner_user_id' => $ownerDebitUserId,
            'type' => LedgerEntryType::Debit,
            'amount' => $amount,
            'currency' => $normalizedCurrency,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'counterparty_user_id' => $ownerCreditUserId,
            'posted_at' => $postedAt,
            'is_reversal' => $isReversal,
            'reverses_journal_uuid' => $reversesJournalUuid,
            'metadata' => $metadata,
        ]);

        WalletLedgerEntry::query()->create([
            'journal_uuid' => $journalUuid,
            'owner_user_id' => $ownerCreditUserId,
            'type' => LedgerEntryType::Credit,
            'amount' => $amount,
            'currency' => $normalizedCurrency,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'counterparty_user_id' => $ownerDebitUserId,
            'posted_at' => $postedAt,
            'is_reversal' => $isReversal,
            'reverses_journal_uuid' => $reversesJournalUuid,
            'metadata' => $metadata,
        ]);

        return $journalUuid;
    }
}

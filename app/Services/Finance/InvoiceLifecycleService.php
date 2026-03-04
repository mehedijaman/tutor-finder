<?php

namespace App\Services\Finance;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\VerificationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\User;
use App\Models\VerificationRequest;
use DomainException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

class InvoiceLifecycleService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly InvoiceNumberGenerator $invoiceNumberGenerator,
        private readonly LedgerJournalService $ledgerJournalService,
    ) {}

    /**
     * Issue a new invoice and guarantee invoice number.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function issue(array $attributes): Invoice
    {
        $payerUserId = (int) ($attributes['payer_user_id'] ?? $attributes['user_id'] ?? 0);
        $payeeUserId = (int) ($attributes['payee_user_id'] ?? 0);

        if ($payerUserId <= 0 || $payeeUserId <= 0) {
            throw new DomainException('Invoice requires valid payer and payee users.');
        }

        $payeeIsActive = User::query()
            ->whereKey($payeeUserId)
            ->where('status', 'active')
            ->exists();

        if (! $payeeIsActive) {
            throw new DomainException('Invoice payee account must be active.');
        }

        $invoiceNo = trim((string) ($attributes['invoice_no'] ?? ''));

        if ($invoiceNo === '') {
            $attributes['invoice_no'] = 'TMP-'.Str::upper(Str::random(26));
        }

        /** @var Invoice $invoice */
        $invoice = Invoice::query()->create($attributes);
        $resolvedInvoiceNo = $this->invoiceNumberGenerator->generateFor($invoice);

        $invoice->forceFill([
            'invoice_no' => $resolvedInvoiceNo,
            'user_id' => $attributes['payer_user_id'] ?? $attributes['user_id'] ?? $invoice->user_id,
        ]);

        $invoice->syncLegacyUserReference();
        $invoice->save();

        return $invoice->fresh();
    }

    /**
     * Mark an invoice paid and apply side effects.
     *
     * @param  array<string, mixed>  $context
     */
    public function markPaid(Invoice $invoice, Payment $payment, array $context = []): Invoice
    {
        return $this->database->transaction(function () use ($invoice, $payment, $context): Invoice {
            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::query()
                ->with('invoiceable')
                ->lockForUpdate()
                ->findOrFail($invoice->getKey());

            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::query()
                ->lockForUpdate()
                ->findOrFail($payment->getKey());

            if ($lockedInvoice->status === InvoiceStatus::Paid) {
                return $lockedInvoice->fresh(['invoiceable', 'payer', 'payee']);
            }

            if (! in_array($lockedInvoice->status, [
                InvoiceStatus::Unpaid,
                InvoiceStatus::Draft,
                InvoiceStatus::Void,
                InvoiceStatus::Failed,
                InvoiceStatus::Cancelled,
            ], true)) {
                throw new DomainException('Invoice cannot be marked paid in its current state.');
            }

            $gatewayPayload = is_array($lockedInvoice->gateway_payload) ? $lockedInvoice->gateway_payload : [];
            $gatewayPayload = array_merge($gatewayPayload, $context['gateway_payload'] ?? []);

            $lockedInvoice->forceFill([
                'status' => InvoiceStatus::Paid,
                'paid_at' => $context['paid_at'] ?? now(),
                'payment_gateway' => $context['payment_gateway'] ?? $lockedInvoice->payment_gateway,
                'payment_method' => $context['payment_method'] ?? $lockedInvoice->payment_method,
                'payment_reference' => $context['payment_reference'] ?? $lockedInvoice->payment_reference,
                'transaction_id' => $context['transaction_id'] ?? $lockedInvoice->transaction_id ?? $lockedPayment->provider_txn_id,
                'gateway_payload' => $gatewayPayload,
            ]);

            if ($lockedInvoice->payer_user_id === null) {
                $lockedInvoice->payer_user_id = $lockedInvoice->user_id;
            }

            $lockedInvoice->syncLegacyUserReference();
            $lockedInvoice->save();

            if ($lockedPayment->status !== PaymentStatus::Paid) {
                $lockedPayment->forceFill([
                    'status' => PaymentStatus::Paid,
                    'provider_txn_id' => $context['transaction_id'] ?? $lockedPayment->provider_txn_id,
                ])->save();
            }

            if ($lockedInvoice->invoiceable instanceof VerificationRequest) {
                $this->finalizeVerificationInvoice($lockedInvoice, $context['reviewer'] ?? null);
            }

            $journalUuid = $this->ledgerJournalService->postInvoicePayment($lockedInvoice, $lockedPayment);

            $lockedInvoice->forceFill([
                'gateway_payload' => array_merge($gatewayPayload, [
                    'ledger_journal_uuid' => $journalUuid,
                ]),
            ])->save();

            return $lockedInvoice->fresh(['invoiceable', 'payer', 'payee']);
        });
    }

    /**
     * Void an unpaid or draft invoice.
     */
    public function markVoid(Invoice $invoice): Invoice
    {
        return $this->database->transaction(function () use ($invoice): Invoice {
            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->getKey());

            if (in_array($lockedInvoice->status, [InvoiceStatus::Paid, InvoiceStatus::Refunded], true)) {
                throw new DomainException('Paid or refunded invoice cannot be voided.');
            }

            $lockedInvoice->forceFill([
                'status' => InvoiceStatus::Void,
            ])->save();

            return $lockedInvoice->fresh();
        });
    }

    /**
     * Mark a paid invoice as refunded and create reversal postings.
     */
    public function markRefunded(
        Invoice $invoice,
        RefundRequest $refundRequest,
        Payment $refundPayment,
        ?string $reversesJournalUuid = null,
    ): Invoice {
        return $this->database->transaction(function () use ($invoice, $refundRequest, $refundPayment, $reversesJournalUuid): Invoice {
            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->getKey());

            if ($lockedInvoice->status === InvoiceStatus::Refunded) {
                return $lockedInvoice->fresh();
            }

            if ($lockedInvoice->status !== InvoiceStatus::Paid) {
                throw new DomainException('Only paid invoices can be marked refunded.');
            }

            $lockedInvoice->forceFill([
                'status' => InvoiceStatus::Refunded,
            ])->save();

            if ($refundPayment->status !== PaymentStatus::Refunded) {
                $refundPayment->forceFill([
                    'status' => PaymentStatus::Refunded,
                ])->save();
            }

            $journalUuid = $this->ledgerJournalService->postRefundPayout(
                refundRequest: $refundRequest,
                payment: $refundPayment,
                invoice: $lockedInvoice,
                reversesJournalUuid: $reversesJournalUuid,
            );

            $gatewayPayload = is_array($lockedInvoice->gateway_payload) ? $lockedInvoice->gateway_payload : [];
            $gatewayPayload['refund_journal_uuid'] = $journalUuid;

            $lockedInvoice->forceFill([
                'gateway_payload' => $gatewayPayload,
            ])->save();

            return $lockedInvoice->fresh();
        });
    }

    private function finalizeVerificationInvoice(Invoice $invoice, ?User $reviewer = null): void
    {
        if (! $invoice->invoiceable instanceof VerificationRequest) {
            return;
        }

        /** @var VerificationRequest $verificationRequest */
        $verificationRequest = VerificationRequest::query()
            ->lockForUpdate()
            ->findOrFail($invoice->invoiceable->getKey());

        $user = User::query()
            ->lockForUpdate()
            ->findOrFail((int) ($invoice->payer_user_id ?? $invoice->user_id));

        $verificationRequest->markVerified($reviewer);

        $user->forceFill([
            'verified_at' => now(),
            'verification_status' => VerificationStatus::Verified,
            'verification_type' => $verificationRequest->role,
        ])->save();
    }
}

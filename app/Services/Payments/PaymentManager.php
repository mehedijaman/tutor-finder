<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\Finance\InvoiceLifecycleService;
use App\Services\Finance\PaymentAttemptService;
use DomainException;
use Illuminate\Http\Request;

class PaymentManager
{
    public function __construct(
        private readonly PaymentAttemptService $paymentAttemptService,
        private readonly InvoiceLifecycleService $invoiceLifecycleService,
    ) {}

    /**
     * Create and initialize payment for an invoice.
     *
     * @return array{redirect_url: string, reference: string, payload: array<string, mixed>}
     */
    public function createPayment(Invoice $invoice, string $gateway): array
    {
        return $this->paymentAttemptService->createPayment($invoice, $gateway);
    }

    /**
     * Handle bKash callback payload and finalize payment if validated.
     */
    public function handleBkashCallback(Request $request): ?Invoice
    {
        return $this->paymentAttemptService->handleBkashCallback($request);
    }

    /**
     * Handle SSLCommerz success or IPN callbacks.
     */
    public function handleSslValidation(Request $request): ?Invoice
    {
        return $this->paymentAttemptService->handleSslValidation($request);
    }

    /**
     * Mark an invoice as failed/cancelled/void.
     */
    public function markInvoiceFailure(Invoice $invoice, string $status, array $payload = []): void
    {
        if (! in_array($status, [Invoice::STATUS_FAILED, Invoice::STATUS_CANCELLED, Invoice::STATUS_VOID], true)) {
            throw new DomainException('Invalid invoice failure status.');
        }

        $attemptStatus = $status === Invoice::STATUS_CANCELLED
            ? Payment::STATUS_CANCELLED
            : Payment::STATUS_FAILED;

        $gateway = $invoice->payment_gateway;

        if (! is_string($gateway) || trim($gateway) === '') {
            return;
        }

        $this->paymentAttemptService->markInvoiceAttemptFailure(
            invoice: $invoice,
            gateway: $gateway,
            status: $attemptStatus,
            payload: $payload,
        );
    }

    /**
     * Mark invoice as paid manually by admin.
     */
    public function markPaidManually(Invoice $invoice, array $data, User $admin): void
    {
        if (! in_array($invoice->status, [
            Invoice::STATUS_UNPAID,
            Invoice::STATUS_DRAFT,
            Invoice::STATUS_VOID,
            Invoice::STATUS_FAILED,
            Invoice::STATUS_CANCELLED,
        ], true)) {
            throw new DomainException('Invoice cannot be marked paid in its current state.');
        }

        $payment = Payment::query()->create([
            'invoice_id' => $invoice->id,
            'gateway' => $data['payment_gateway'] ?? Invoice::GATEWAY_MANUAL,
            'provider_txn_id' => $data['payment_reference'] ?? ('MANUAL-'.$invoice->invoice_no),
            'amount' => $invoice->amount,
            'status' => Payment::STATUS_PAID,
            'provider_payload' => [
                'manual_override' => [
                    'admin_id' => $admin->getKey(),
                ],
            ],
        ]);

        $this->invoiceLifecycleService->markPaid($invoice, $payment, [
            'payment_gateway' => $data['payment_gateway'] ?? Invoice::GATEWAY_MANUAL,
            'payment_method' => $data['payment_method'] ?? 'manual',
            'payment_reference' => $data['payment_reference'] ?? $invoice->payment_reference,
            'transaction_id' => $payment->provider_txn_id,
            'paid_at' => $data['paid_at'] ?? now(),
            'gateway_payload' => [
                'manual_override' => [
                    'admin_id' => $admin->getKey(),
                    'notes' => $data['notes'] ?? null,
                ],
            ],
            'reviewer' => $admin,
        ]);
    }
}

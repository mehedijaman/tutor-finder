<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\User;
use App\Models\VerificationRequest;
use DomainException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PaymentManager
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly BkashService $bkashService,
        private readonly SslCommerzService $sslCommerzService,
    ) {}

    /**
     * Create and initialize payment for an invoice.
     *
     * @return array{redirect_url: string, reference: string, payload: array<string, mixed>}
     */
    public function createPayment(Invoice $invoice, string $gateway): array
    {
        $normalizedGateway = strtolower(trim($gateway));

        if (! in_array($normalizedGateway, [Invoice::GATEWAY_BKASH, Invoice::GATEWAY_SSLCOMMERZ], true)) {
            throw new DomainException('Unsupported payment gateway selected.');
        }

        if ($invoice->invoiceable_type !== VerificationRequest::class) {
            throw new DomainException('Only verification invoices support online payment.');
        }

        $affected = Invoice::query()
            ->whereKey($invoice->getKey())
            ->where('status', Invoice::STATUS_UNPAID)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->update([
                'status' => Invoice::STATUS_PROCESSING,
                'payment_gateway' => $normalizedGateway,
                'updated_at' => now(),
            ]);

        if ($affected !== 1) {
            throw new DomainException('Invoice is not payable or already being processed.');
        }

        $invoice->refresh();

        try {
            $response = match ($normalizedGateway) {
                Invoice::GATEWAY_BKASH => $this->bkashService->initiate($invoice),
                Invoice::GATEWAY_SSLCOMMERZ => $this->sslCommerzService->initiate($invoice),
                default => throw new DomainException('Unsupported payment gateway selected.'),
            };
        } catch (\Throwable $exception) {
            Invoice::query()
                ->whereKey($invoice->getKey())
                ->where('status', Invoice::STATUS_PROCESSING)
                ->update([
                    'status' => Invoice::STATUS_UNPAID,
                    'payment_gateway' => null,
                    'updated_at' => now(),
                ]);

            throw $exception;
        }

        $invoice->forceFill([
            'payment_reference' => $response['reference'],
            'gateway_payload' => [
                'initiation' => $response['payload'] ?? [],
            ],
        ])->save();

        return $response;
    }

    /**
     * Handle bKash callback payload and finalize payment if validated.
     */
    public function handleBkashCallback(Request $request): ?Invoice
    {
        $paymentId = trim((string) $request->input('paymentID'));

        if ($paymentId === '') {
            return null;
        }

        $invoice = Invoice::query()
            ->with(['invoiceable', 'user'])
            ->where('payment_gateway', Invoice::GATEWAY_BKASH)
            ->where('payment_reference', $paymentId)
            ->first();

        if (! $invoice instanceof Invoice) {
            return null;
        }

        if ($invoice->status === Invoice::STATUS_PAID) {
            return $invoice;
        }

        try {
            $result = $this->bkashService->validateAndCapture($request, $invoice);
        } catch (\Throwable $exception) {
            $this->markInvoiceFailure($invoice, Invoice::STATUS_FAILED, [
                'callback' => $request->all(),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $this->finalizeVerifiedInvoice($invoice, [
            'payment_gateway' => Invoice::GATEWAY_BKASH,
            'payment_method' => 'bkash',
            'payment_reference' => $paymentId,
            'transaction_id' => $result['transaction_id'],
            'gateway_payload' => [
                'callback' => $request->all(),
                'validated' => $result['payload'],
            ],
        ]);

        return $invoice->fresh(['invoiceable', 'user']);
    }

    /**
     * Handle SSLCommerz success or IPN callbacks.
     */
    public function handleSslValidation(Request $request): ?Invoice
    {
        $tranId = trim((string) $request->input('tran_id'));

        if ($tranId === '') {
            return null;
        }

        $invoice = Invoice::query()
            ->with(['invoiceable', 'user'])
            ->where('payment_gateway', Invoice::GATEWAY_SSLCOMMERZ)
            ->where('payment_reference', $tranId)
            ->first();

        if (! $invoice instanceof Invoice) {
            return null;
        }

        if ($invoice->status === Invoice::STATUS_PAID) {
            return $invoice;
        }

        try {
            $result = $this->sslCommerzService->validateCallback($request->all(), $invoice);
        } catch (\Throwable $exception) {
            $this->markInvoiceFailure($invoice, Invoice::STATUS_FAILED, [
                'callback' => $request->all(),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $this->finalizeVerifiedInvoice($invoice, [
            'payment_gateway' => Invoice::GATEWAY_SSLCOMMERZ,
            'payment_method' => strtolower((string) Arr::get($result['payload'], 'card_type', 'sslcommerz')),
            'payment_reference' => $tranId,
            'transaction_id' => $result['transaction_id'],
            'gateway_payload' => [
                'callback' => $request->all(),
                'validated' => $result['payload'],
            ],
        ]);

        return $invoice->fresh(['invoiceable', 'user']);
    }

    /**
     * Mark an invoice as failed/cancelled/void.
     */
    public function markInvoiceFailure(Invoice $invoice, string $status, array $payload = []): void
    {
        if (! in_array($status, [Invoice::STATUS_FAILED, Invoice::STATUS_CANCELLED, Invoice::STATUS_VOID], true)) {
            throw new DomainException('Invalid invoice failure status.');
        }

        if ($invoice->status === Invoice::STATUS_PAID) {
            return;
        }

        if (! in_array($invoice->status, [Invoice::STATUS_UNPAID, Invoice::STATUS_PROCESSING], true)) {
            return;
        }

        $gatewayPayload = is_array($invoice->gateway_payload) ? $invoice->gateway_payload : [];

        $invoice->forceFill([
            'status' => $status,
            'gateway_payload' => array_merge($gatewayPayload, [
                'failure' => $payload,
            ]),
        ])->save();
    }

    /**
     * Mark invoice as paid manually by admin.
     */
    public function markPaidManually(Invoice $invoice, array $data, User $admin): void
    {
        if (! in_array($invoice->status, [Invoice::STATUS_UNPAID, Invoice::STATUS_PROCESSING, Invoice::STATUS_FAILED, Invoice::STATUS_CANCELLED], true)) {
            throw new DomainException('Invoice cannot be marked paid in its current state.');
        }

        $this->finalizeVerifiedInvoice($invoice, [
            'payment_gateway' => $data['payment_gateway'] ?? Invoice::GATEWAY_MANUAL,
            'payment_method' => $data['payment_method'] ?? 'manual',
            'payment_reference' => $data['payment_reference'] ?? $invoice->payment_reference,
            'transaction_id' => $data['payment_reference'] ?? $invoice->transaction_id ?? ('MANUAL-'.$invoice->invoice_no),
            'paid_at' => $data['paid_at'] ?? now(),
            'notes' => $data['notes'] ?? null,
            'gateway_payload' => [
                'manual_override' => [
                    'admin_id' => $admin->getKey(),
                ],
            ],
            'reviewer' => $admin,
        ]);
    }

    /**
     * Complete invoice + request + user verification in one transaction.
     *
     * @param  array<string, mixed>  $context
     */
    private function finalizeVerifiedInvoice(Invoice $invoice, array $context): void
    {
        $this->database->transaction(function () use ($invoice, $context): void {
            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::query()
                ->with('invoiceable')
                ->lockForUpdate()
                ->findOrFail($invoice->getKey());

            if ($lockedInvoice->status === Invoice::STATUS_PAID) {
                return;
            }

            if ($lockedInvoice->isExpired() && ($context['reviewer'] ?? null) === null) {
                throw new DomainException('Invoice has expired and cannot be paid online.');
            }

            if (! $lockedInvoice->invoiceable instanceof VerificationRequest) {
                throw new DomainException('Invoice does not belong to a verification request.');
            }

            $verificationRequest = VerificationRequest::query()
                ->lockForUpdate()
                ->findOrFail($lockedInvoice->invoiceable->getKey());

            $user = User::query()->lockForUpdate()->findOrFail($lockedInvoice->user_id);

            $mergedPayload = is_array($lockedInvoice->gateway_payload) ? $lockedInvoice->gateway_payload : [];
            $mergedPayload = array_merge($mergedPayload, $context['gateway_payload'] ?? []);

            $lockedInvoice->forceFill([
                'status' => Invoice::STATUS_PAID,
                'paid_at' => $context['paid_at'] ?? now(),
                'payment_gateway' => $context['payment_gateway'] ?? $lockedInvoice->payment_gateway,
                'payment_method' => $context['payment_method'] ?? $lockedInvoice->payment_method,
                'payment_reference' => $context['payment_reference'] ?? $lockedInvoice->payment_reference,
                'transaction_id' => $context['transaction_id'] ?? $lockedInvoice->transaction_id,
                'notes' => $context['notes'] ?? $lockedInvoice->notes,
                'gateway_payload' => $mergedPayload,
            ])->save();

            $verificationRequest->markVerified($context['reviewer'] ?? null);

            $user->forceFill([
                'verified_at' => now(),
                'verification_status' => User::VERIFICATION_STATUS_VERIFIED,
                'verification_type' => $verificationRequest->role,
            ])->save();
        });
    }
}

<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payments\BkashService;
use App\Services\Payments\SslCommerzService;
use DomainException;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PaymentAttemptService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly BkashService $bkashService,
        private readonly SslCommerzService $sslCommerzService,
        private readonly InvoiceLifecycleService $invoiceLifecycleService,
    ) {}

    /**
     * Create a payment attempt and initialize selected gateway.
     *
     * @return array{redirect_url: string, reference: string, payload: array<string, mixed>}
     */
    public function createPayment(Invoice $invoice, string $gateway): array
    {
        $normalizedGateway = strtolower(trim($gateway));

        if (! in_array($normalizedGateway, [Invoice::GATEWAY_BKASH, Invoice::GATEWAY_SSLCOMMERZ], true)) {
            throw new DomainException('Unsupported payment gateway selected.');
        }

        /** @var Payment $payment */
        $payment = $this->database->transaction(function () use ($invoice, $normalizedGateway): Payment {
            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->getKey());

            if (! $lockedInvoice->isPayable()) {
                throw new DomainException('Invoice is not payable in its current state.');
            }

            $pendingExists = Payment::query()
                ->where('invoice_id', $lockedInvoice->id)
                ->where('status', Payment::STATUS_PENDING)
                ->lockForUpdate()
                ->exists();

            if ($pendingExists) {
                throw new DomainException('A payment attempt is already in progress for this invoice.');
            }

            return Payment::query()->create([
                'invoice_id' => $lockedInvoice->id,
                'gateway' => $normalizedGateway,
                'provider_txn_id' => null,
                'amount' => $lockedInvoice->amount,
                'status' => Payment::STATUS_PENDING,
                'provider_payload' => null,
            ]);
        });

        $invoice->loadMissing('user');

        try {
            $response = match ($normalizedGateway) {
                Invoice::GATEWAY_BKASH => $this->bkashService->initiate($invoice),
                Invoice::GATEWAY_SSLCOMMERZ => $this->sslCommerzService->initiate($invoice),
                default => throw new DomainException('Unsupported payment gateway selected.'),
            };
        } catch (\Throwable $exception) {
            $this->database->transaction(function () use ($payment): void {
                $payment->forceFill([
                    'status' => Payment::STATUS_FAILED,
                ])->save();
            });

            throw $exception;
        }

        $this->database->transaction(function () use ($invoice, $payment, $normalizedGateway, $response): void {
            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->getKey());
            /** @var Payment $lockedPayment */
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());

            $gatewayPayload = is_array($lockedInvoice->gateway_payload) ? $lockedInvoice->gateway_payload : [];
            $gatewayPayload['initiation'] = $response['payload'] ?? [];

            $lockedInvoice->forceFill([
                'payment_gateway' => $normalizedGateway,
                'payment_reference' => $response['reference'],
                'gateway_payload' => $gatewayPayload,
            ])->save();

            $lockedPayment->forceFill([
                'provider_payload' => [
                    'initiation' => $response['payload'] ?? [],
                    'invoice_reference' => $response['reference'],
                ],
            ])->save();
        });

        return $response;
    }

    /**
     * Handle bKash callback payload and finalize payment.
     */
    public function handleBkashCallback(Request $request): ?Invoice
    {
        $paymentId = trim((string) $request->input('paymentID'));

        if ($paymentId === '') {
            return null;
        }

        $invoice = $this->resolveInvoiceByReference(Invoice::GATEWAY_BKASH, $paymentId);

        if (! $invoice instanceof Invoice) {
            return null;
        }

        try {
            $result = $this->bkashService->validateAndCapture($request, $invoice);
        } catch (\Throwable $exception) {
            $this->markInvoiceAttemptFailure(
                invoice: $invoice,
                gateway: Invoice::GATEWAY_BKASH,
                status: Payment::STATUS_FAILED,
                payload: [
                    'callback' => $request->all(),
                    'error' => $exception->getMessage(),
                ],
            );

            throw $exception;
        }

        return $this->finalizeSuccessfulAttempt(
            invoice: $invoice,
            gateway: Invoice::GATEWAY_BKASH,
            reference: $paymentId,
            providerTransactionId: (string) $result['transaction_id'],
            paymentMethod: 'bkash',
            payload: [
                'callback' => $request->all(),
                'validated' => $result['payload'],
            ],
        );
    }

    /**
     * Handle SSLCommerz success/IPN callback payload and finalize payment.
     */
    public function handleSslValidation(Request $request): ?Invoice
    {
        $tranId = trim((string) $request->input('tran_id'));

        if ($tranId === '') {
            return null;
        }

        $invoice = $this->resolveInvoiceByReference(Invoice::GATEWAY_SSLCOMMERZ, $tranId);

        if (! $invoice instanceof Invoice) {
            return null;
        }

        try {
            $result = $this->sslCommerzService->validateCallback($request->all(), $invoice);
        } catch (\Throwable $exception) {
            $this->markInvoiceAttemptFailure(
                invoice: $invoice,
                gateway: Invoice::GATEWAY_SSLCOMMERZ,
                status: Payment::STATUS_FAILED,
                payload: [
                    'callback' => $request->all(),
                    'error' => $exception->getMessage(),
                ],
            );

            throw $exception;
        }

        return $this->finalizeSuccessfulAttempt(
            invoice: $invoice,
            gateway: Invoice::GATEWAY_SSLCOMMERZ,
            reference: $tranId,
            providerTransactionId: (string) $result['transaction_id'],
            paymentMethod: strtolower((string) Arr::get($result['payload'], 'card_type', 'sslcommerz')),
            payload: [
                'callback' => $request->all(),
                'validated' => $result['payload'],
            ],
        );
    }

    /**
     * Mark active payment attempt as failed/cancelled.
     *
     * @param  array<string, mixed>  $payload
     */
    public function markInvoiceAttemptFailure(
        Invoice $invoice,
        string $gateway,
        string $status = Payment::STATUS_FAILED,
        array $payload = [],
    ): void {
        if (! in_array($status, [Payment::STATUS_FAILED, Payment::STATUS_CANCELLED], true)) {
            throw new DomainException('Invalid payment attempt status.');
        }

        $this->database->transaction(function () use ($invoice, $gateway, $status, $payload): void {
            /** @var Invoice $lockedInvoice */
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->getKey());

            if ($lockedInvoice->status === Invoice::STATUS_PAID) {
                return;
            }

            $activeAttempt = Payment::query()
                ->where('invoice_id', $lockedInvoice->id)
                ->where('gateway', $gateway)
                ->where('status', Payment::STATUS_PENDING)
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $activeAttempt instanceof Payment) {
                Payment::query()->create([
                    'invoice_id' => $lockedInvoice->id,
                    'gateway' => $gateway,
                    'provider_txn_id' => null,
                    'amount' => $lockedInvoice->amount,
                    'status' => $status,
                    'provider_payload' => [
                        'failure' => $payload,
                    ],
                ]);

                return;
            }

            $existingPayload = is_array($activeAttempt->provider_payload) ? $activeAttempt->provider_payload : [];
            $existingPayload['failure'] = $payload;

            $activeAttempt->forceFill([
                'status' => $status,
                'provider_payload' => $existingPayload,
            ])->save();
        });
    }

    private function resolveInvoiceByReference(string $gateway, string $reference): ?Invoice
    {
        return Invoice::query()
            ->with(['invoiceable', 'payer', 'payee', 'user'])
            ->where('payment_gateway', $gateway)
            ->where('payment_reference', $reference)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function finalizeSuccessfulAttempt(
        Invoice $invoice,
        string $gateway,
        string $reference,
        string $providerTransactionId,
        string $paymentMethod,
        array $payload = [],
    ): Invoice {
        try {
            return $this->database->transaction(function () use ($invoice, $gateway, $reference, $providerTransactionId, $paymentMethod, $payload): Invoice {
                /** @var Invoice $lockedInvoice */
                $lockedInvoice = Invoice::query()
                    ->with('invoiceable')
                    ->lockForUpdate()
                    ->findOrFail($invoice->getKey());

                /** @var Payment|null $paymentByProviderTxn */
                $paymentByProviderTxn = Payment::query()
                    ->where('gateway', $gateway)
                    ->where('provider_txn_id', $providerTransactionId)
                    ->lockForUpdate()
                    ->first();

                if ($lockedInvoice->status === Invoice::STATUS_PAID) {
                    if ($paymentByProviderTxn instanceof Payment
                        && (int) $paymentByProviderTxn->invoice_id === (int) $lockedInvoice->id
                        && $paymentByProviderTxn->status === Payment::STATUS_PAID) {
                        return $lockedInvoice->fresh(['invoiceable', 'payer', 'payee']);
                    }

                    throw new DomainException('callback_conflict_paid_invoice');
                }

                if ($paymentByProviderTxn instanceof Payment
                    && (int) $paymentByProviderTxn->invoice_id !== (int) $lockedInvoice->id) {
                    throw new DomainException('callback_conflict_linked_invoice');
                }

                $activeAttempt = $paymentByProviderTxn;

                if (! $activeAttempt instanceof Payment) {
                    $activeAttempt = Payment::query()
                        ->where('invoice_id', $lockedInvoice->id)
                        ->where('gateway', $gateway)
                        ->where('status', Payment::STATUS_PENDING)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();
                }

                if (! $activeAttempt instanceof Payment) {
                    $activeAttempt = Payment::query()->create([
                        'invoice_id' => $lockedInvoice->id,
                        'gateway' => $gateway,
                        'provider_txn_id' => null,
                        'amount' => $lockedInvoice->amount,
                        'status' => Payment::STATUS_PENDING,
                        'provider_payload' => null,
                    ]);
                }

                $attemptPayload = is_array($activeAttempt->provider_payload) ? $activeAttempt->provider_payload : [];
                $attemptPayload['success'] = $payload;

                $activeAttempt->forceFill([
                    'provider_txn_id' => $providerTransactionId,
                    'status' => Payment::STATUS_PAID,
                    'provider_payload' => $attemptPayload,
                ])->save();

                return $this->invoiceLifecycleService->markPaid($lockedInvoice, $activeAttempt, [
                    'payment_gateway' => $gateway,
                    'payment_method' => $paymentMethod,
                    'payment_reference' => $reference,
                    'transaction_id' => $providerTransactionId,
                    'gateway_payload' => $payload,
                ]);
            });
        } catch (DomainException $exception) {
            if (in_array($exception->getMessage(), [
                'callback_conflict_paid_invoice',
                'callback_conflict_linked_invoice',
            ], true)) {
                $this->recordCallbackConflict($invoice, $gateway, $providerTransactionId, $payload);

                throw new DomainException($exception->getMessage() === 'callback_conflict_paid_invoice'
                    ? 'Invoice is already paid with a different transaction.'
                    : 'Payment transaction is already linked to a different invoice.');
            }

            throw $exception;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function recordCallbackConflict(
        Invoice $invoice,
        string $gateway,
        string $providerTransactionId,
        array $payload = [],
    ): void {
        report(new DomainException(sprintf(
            'Payment callback conflict on invoice %d for gateway %s and provider txn %s.',
            (int) $invoice->id,
            $gateway,
            $providerTransactionId,
        )));

        Payment::query()->create([
            'invoice_id' => $invoice->id,
            'gateway' => $gateway,
            'provider_txn_id' => null,
            'amount' => $invoice->amount,
            'status' => Payment::STATUS_FAILED,
            'provider_payload' => [
                'conflict_provider_txn_id' => $providerTransactionId,
                'callback_payload' => $payload,
            ],
        ]);
    }
}

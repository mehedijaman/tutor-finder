<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\PaymentGateway;
use DomainException;
use Raziul\Sslcommerz\Facades\Sslcommerz;

class SslCommerzService
{
    /**
     * Initialize SSLCommerz payment and return redirect context.
     *
     * @return array{redirect_url: string, reference: string, payload: array<string, mixed>}
     */
    public function initiate(Invoice $invoice): array
    {
        $this->configureCredentials();

        $response = Sslcommerz::setCallbackUrls(
            route('payment.sslcommerz.success'),
            route('payment.sslcommerz.fail'),
            route('payment.sslcommerz.cancel'),
            route('payment.sslcommerz.ipn'),
        )
            ->setOrder((float) $invoice->amount, $invoice->invoice_no, 'Verification Fee')
            ->setCustomer(
                (string) ($invoice->user?->name ?? 'Tutor Finder User'),
                (string) ($invoice->user?->email ?? 'no-reply@example.com'),
                (string) ($invoice->user?->phone ?? ' '),
            )
            ->setShippingInfo(1, 'N/A')
            ->makePayment();

        $redirectUrl = (string) $response->gatewayPageURL();

        if (! $response->success() || $redirectUrl === '') {
            throw new DomainException($response->failedReason() ?: 'Failed to initialize SSLCommerz payment.');
        }

        return [
            'redirect_url' => $redirectUrl,
            'reference' => $invoice->invoice_no,
            'payload' => $response->toArray() ?? [],
        ];
    }

    /**
     * Validate SSLCommerz callback/IPN payload and capture payment details.
     *
     * @param  array<string, mixed>  $payload
     * @return array{transaction_id: string, payload: array<string, mixed>}
     */
    public function validateCallback(array $payload, Invoice $invoice): array
    {
        $this->configureCredentials();

        $tranId = trim((string) ($payload['tran_id'] ?? ''));

        if ($tranId === '' || $tranId !== (string) $invoice->payment_reference || $tranId !== $invoice->invoice_no) {
            throw new DomainException('SSLCommerz invoice reference mismatch.');
        }

        if (! Sslcommerz::verifyHash($payload)) {
            throw new DomainException('SSLCommerz hash verification failed.');
        }

        $isValid = Sslcommerz::validatePayment($payload, $tranId, (float) $invoice->amount, $invoice->currency);

        if (! $isValid) {
            throw new DomainException('SSLCommerz payment validation failed.');
        }

        $transactionId = trim((string) ($payload['bank_tran_id'] ?? $payload['tran_id'] ?? ''));

        if ($transactionId === '') {
            throw new DomainException('SSLCommerz transaction ID is missing.');
        }

        return [
            'transaction_id' => $transactionId,
            'payload' => $payload,
        ];
    }

    /**
     * Configure SSLCommerz credentials from site settings.
     */
    private function configureCredentials(): void
    {
        $paymentGateway = PaymentGateway::active(PaymentGateway::GATEWAY_SSLCOMMERZ);

        if (! $paymentGateway instanceof PaymentGateway) {
            throw new DomainException('SSLCommerz gateway is not active.');
        }

        $credentials = is_array($paymentGateway->credentials) ? $paymentGateway->credentials : [];
        $storeId = trim((string) ($credentials['store_id'] ?? ''));
        $storePassword = trim((string) ($credentials['store_password'] ?? ''));
        $mode = strtolower(trim((string) ($credentials['mode'] ?? 'sandbox')));

        if (
            $storeId === ''
            || $storePassword === ''
        ) {
            throw new DomainException('SSLCommerz credentials are not configured.');
        }

        config([
            'sslcommerz.store.id' => $storeId,
            'sslcommerz.store.password' => $storePassword,
            'sslcommerz.sandbox' => $mode !== 'live',
            'sslcommerz.route.success' => 'payment.sslcommerz.success',
            'sslcommerz.route.failure' => 'payment.sslcommerz.fail',
            'sslcommerz.route.cancel' => 'payment.sslcommerz.cancel',
            'sslcommerz.route.ipn' => 'payment.sslcommerz.ipn',
        ]);
    }
}

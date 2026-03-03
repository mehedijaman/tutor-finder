<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\PaymentGateway;
use DomainException;
use Ihasan\Bkash\Facades\Bkash;
use Illuminate\Http\Request;

class BkashService
{
    /**
     * Initialize bKash payment and return redirect context.
     *
     * @return array{redirect_url: string, reference: string, payload: array<string, mixed>}
     */
    public function initiate(Invoice $invoice): array
    {
        $this->configureCredentials();

        $response = Bkash::createPayment([
            'amount' => (string) $invoice->amount,
            'currency' => $invoice->currency,
            'payer_reference' => (string) $invoice->user_id,
            'callback_url' => route('payment.bkash.callback'),
            'merchant_invoice_number' => $invoice->invoice_no,
        ]);

        $redirectUrl = (string) ($response['bkashURL'] ?? '');
        $paymentId = (string) ($response['paymentID'] ?? '');

        if ($redirectUrl === '' || $paymentId === '') {
            throw new DomainException('Failed to initialize bKash payment.');
        }

        return [
            'redirect_url' => $redirectUrl,
            'reference' => $paymentId,
            'payload' => $response,
        ];
    }

    /**
     * Validate callback payload by querying bKash API.
     *
     * @return array{transaction_id: string, payload: array<string, mixed>}
     */
    public function validateAndCapture(Request $request, Invoice $invoice): array
    {
        $this->configureCredentials();

        $paymentId = trim((string) $request->input('paymentID'));

        if ($paymentId === '' || $paymentId !== (string) $invoice->payment_reference) {
            throw new DomainException('Invalid bKash payment reference.');
        }

        $payload = Bkash::queryPayment($paymentId);

        $transactionId = trim((string) ($payload['trxID'] ?? ''));

        if ($transactionId === '') {
            $payload = Bkash::executePayment($paymentId);
            $transactionId = trim((string) ($payload['trxID'] ?? ''));
        }

        if ($transactionId === '') {
            throw new DomainException('bKash transaction could not be validated.');
        }

        $transactionStatus = strtolower(trim((string) ($payload['transactionStatus'] ?? '')));

        if (! in_array($transactionStatus, ['completed'], true)) {
            throw new DomainException('bKash payment is not completed.');
        }

        $merchantInvoice = trim((string) ($payload['merchantInvoiceNumber'] ?? ''));

        if ($merchantInvoice !== $invoice->invoice_no) {
            throw new DomainException('bKash invoice reference mismatch.');
        }

        $currency = strtoupper(trim((string) ($payload['currency'] ?? '')));

        if ($currency !== strtoupper($invoice->currency)) {
            throw new DomainException('bKash currency mismatch detected.');
        }

        $amount = (float) ($payload['amount'] ?? 0);

        if (abs($amount - (float) $invoice->amount) > 0.01) {
            throw new DomainException('bKash amount mismatch detected.');
        }

        return [
            'transaction_id' => $transactionId,
            'payload' => $payload,
        ];
    }

    /**
     * Configure runtime credentials from site settings singleton.
     */
    private function configureCredentials(): void
    {
        $paymentGateway = PaymentGateway::active(PaymentGateway::GATEWAY_BKASH);

        if (! $paymentGateway instanceof PaymentGateway) {
            throw new DomainException('bKash gateway is not active.');
        }

        $credentials = is_array($paymentGateway->credentials) ? $paymentGateway->credentials : [];
        $appKey = trim((string) ($credentials['app_key'] ?? ''));
        $appSecret = trim((string) ($credentials['app_secret'] ?? ''));
        $username = trim((string) ($credentials['username'] ?? ''));
        $password = trim((string) ($credentials['password'] ?? ''));
        $baseUrl = trim((string) ($credentials['base_url'] ?? 'https://tokenized.sandbox.bka.sh'));

        if (
            $appKey === ''
            || $appSecret === ''
            || $username === ''
            || $password === ''
        ) {
            throw new DomainException('bKash credentials are not configured.');
        }

        $isSandbox = str_contains(strtolower($baseUrl), 'sandbox');

        config([
            'bkash.sandbox' => $isSandbox,
            'bkash.credentials.app_key' => $appKey,
            'bkash.credentials.app_secret' => $appSecret,
            'bkash.credentials.username' => $username,
            'bkash.credentials.password' => $password,
            'bkash.sandbox_base_url' => $baseUrl,
            'bkash.live_base_url' => $baseUrl,
        ]);
    }
}

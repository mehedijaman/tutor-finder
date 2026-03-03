<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Payments\PaymentManager;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Start bKash payment flow for an invoice.
     */
    public function startBkash(Request $request, Invoice $invoice, PaymentManager $paymentManager): RedirectResponse
    {
        $payerUserId = (int) ($invoice->payer_user_id ?? $invoice->user_id);

        if ((int) $request->user()?->getKey() !== $payerUserId) {
            abort(403);
        }

        try {
            $response = $paymentManager->createPayment($invoice, Invoice::GATEWAY_BKASH);
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->back()->withErrors([
                'payment' => 'Failed to start payment. Please try again.',
            ]);
        }

        return redirect()->away($response['redirect_url']);
    }

    /**
     * Handle bKash callback and finalize payment if valid.
     */
    public function bkashCallback(Request $request, PaymentManager $paymentManager): RedirectResponse
    {
        $status = strtolower(trim((string) $request->input('status')));
        $invoice = $this->resolveBkashInvoice($request);

        if ($status === 'failure' || $status === 'cancel') {
            if ($invoice instanceof Invoice) {
                $paymentManager->markInvoiceFailure(
                    $invoice,
                    $status === 'cancel' ? Invoice::STATUS_CANCELLED : Invoice::STATUS_FAILED,
                    ['callback' => $request->all()],
                );
            }

            return redirect($this->verificationRedirectPath($invoice))->withErrors([
                'payment' => $status === 'cancel' ? 'Payment was cancelled.' : 'Payment failed. Please try again.',
            ]);
        }

        try {
            $resolvedInvoice = $paymentManager->handleBkashCallback($request);

            if (! $resolvedInvoice instanceof Invoice) {
                throw new DomainException('Unable to match bKash callback with an active invoice.');
            }
        } catch (DomainException $exception) {
            return redirect($this->verificationRedirectPath($invoice))->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect($this->verificationRedirectPath($invoice))->withErrors([
                'payment' => 'Payment validation failed.',
            ]);
        }

        return redirect($this->verificationRedirectPath($resolvedInvoice))->with('status', 'Payment confirmed successfully.');
    }

    /**
     * Start SSLCommerz payment flow for an invoice.
     */
    public function startSslCommerz(Request $request, Invoice $invoice, PaymentManager $paymentManager): RedirectResponse
    {
        $payerUserId = (int) ($invoice->payer_user_id ?? $invoice->user_id);

        if ((int) $request->user()?->getKey() !== $payerUserId) {
            abort(403);
        }

        try {
            $response = $paymentManager->createPayment($invoice, Invoice::GATEWAY_SSLCOMMERZ);
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->back()->withErrors([
                'payment' => 'Failed to start payment. Please try again.',
            ]);
        }

        return redirect()->away($response['redirect_url']);
    }

    /**
     * Handle SSLCommerz success callback.
     */
    public function sslSuccess(Request $request, PaymentManager $paymentManager): RedirectResponse
    {
        $invoice = $this->resolveSslInvoice($request);

        try {
            $resolvedInvoice = $paymentManager->handleSslValidation($request);

            if (! $resolvedInvoice instanceof Invoice) {
                throw new DomainException('Unable to match SSLCommerz callback with an active invoice.');
            }
        } catch (DomainException $exception) {
            return redirect($this->verificationRedirectPath($invoice))->withErrors([
                'payment' => $exception->getMessage(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return redirect($this->verificationRedirectPath($invoice))->withErrors([
                'payment' => 'Payment validation failed.',
            ]);
        }

        return redirect($this->verificationRedirectPath($resolvedInvoice))->with('status', 'Payment confirmed successfully.');
    }

    /**
     * Handle SSLCommerz failed callback.
     */
    public function sslFail(Request $request, PaymentManager $paymentManager): RedirectResponse
    {
        $invoice = $this->resolveSslInvoice($request);

        if ($invoice instanceof Invoice) {
            $paymentManager->markInvoiceFailure($invoice, Invoice::STATUS_FAILED, [
                'callback' => $request->all(),
            ]);
        }

        return redirect($this->verificationRedirectPath($invoice))->withErrors([
            'payment' => 'Payment failed. Please try again.',
        ]);
    }

    /**
     * Handle SSLCommerz cancel callback.
     */
    public function sslCancel(Request $request, PaymentManager $paymentManager): RedirectResponse
    {
        $invoice = $this->resolveSslInvoice($request);

        if ($invoice instanceof Invoice) {
            $paymentManager->markInvoiceFailure($invoice, Invoice::STATUS_CANCELLED, [
                'callback' => $request->all(),
            ]);
        }

        return redirect($this->verificationRedirectPath($invoice))->withErrors([
            'payment' => 'Payment was cancelled.',
        ]);
    }

    /**
     * Handle SSLCommerz IPN callback.
     */
    public function sslIpn(Request $request, PaymentManager $paymentManager): JsonResponse
    {
        try {
            $invoice = $paymentManager->handleSslValidation($request);

            if (! $invoice instanceof Invoice) {
                throw new DomainException('Invoice could not be resolved from callback payload.');
            }
        } catch (DomainException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
        ]);
    }

    /**
     * Resolve SSL invoice by trusted transaction reference.
     */
    private function resolveSslInvoice(Request $request): ?Invoice
    {
        $tranId = trim((string) $request->input('tran_id'));

        if ($tranId === '') {
            return null;
        }

        return Invoice::query()
            ->with(['user', 'payer'])
            ->where('payment_gateway', Invoice::GATEWAY_SSLCOMMERZ)
            ->where('payment_reference', $tranId)
            ->first();
    }

    /**
     * Resolve bKash invoice by trusted payment reference.
     */
    private function resolveBkashInvoice(Request $request): ?Invoice
    {
        $paymentId = trim((string) $request->input('paymentID'));

        if ($paymentId === '') {
            return null;
        }

        return Invoice::query()
            ->with(['user', 'payer'])
            ->where('payment_gateway', Invoice::GATEWAY_BKASH)
            ->where('payment_reference', $paymentId)
            ->first();
    }

    /**
     * Resolve role-based redirect after payment callbacks.
     */
    private function verificationRedirectPath(?Invoice $invoice): string
    {
        if (! $invoice instanceof Invoice) {
            return '/login';
        }

        $payer = $invoice->payer ?? $invoice->user;

        if (! $payer) {
            return '/login';
        }

        if ($invoice->isVerificationInvoice()) {
            return $payer->role === 'tutor'
                ? '/tutor/verification'
                : '/guardian/verification';
        }

        return $payer->role === 'tutor'
            ? '/tutor/finance/invoices'
            : '/guardian/finance/invoices';
    }
}

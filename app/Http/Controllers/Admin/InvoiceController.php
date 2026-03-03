<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceMarkPaidRequest;
use App\Models\Invoice;
use App\Services\Payments\PaymentManager;
use DomainException;
use Illuminate\Http\RedirectResponse;

class InvoiceController extends Controller
{
    /**
     * Manually mark invoice paid.
     */
    public function markPaid(
        InvoiceMarkPaidRequest $request,
        Invoice $invoice,
        PaymentManager $paymentManager,
    ): RedirectResponse {
        try {
            $paymentManager->markPaidManually($invoice, $request->validated(), $request->user());
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors([
                'invoice' => $exception->getMessage(),
            ]);
        }

        return redirect()->back()->with('status', 'Invoice marked as paid and verification finalized.');
    }
}

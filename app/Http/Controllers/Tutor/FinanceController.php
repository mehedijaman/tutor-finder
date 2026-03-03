<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Response;

class FinanceController extends Controller
{
    /**
     * Display tutor invoice center.
     */
    public function invoices(Request $request): Response
    {
        $status = strtolower(trim($request->string('status')->toString()));

        if (! in_array($status, Invoice::statuses(), true)) {
            $status = '';
        }

        $items = Invoice::query()
            ->with(['latestPayment:id,invoice_id,gateway,provider_txn_id,status,created_at'])
            ->where('payer_user_id', $request->user()?->getAuthIdentifier())
            ->when($status !== '', fn (Builder $builder): Builder => $builder->where('status', $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Invoice $invoice): array => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'type' => $invoice->type,
                'status' => $invoice->status,
                'amount' => $invoice->amount,
                'currency' => $invoice->currency,
                'due_at' => $invoice->due_at?->toDateTimeString(),
                'paid_at' => $invoice->paid_at?->toDateTimeString(),
                'latest_payment' => $invoice->latestPayment ? [
                    'gateway' => $invoice->latestPayment->gateway,
                    'status' => $invoice->latestPayment->status,
                    'provider_txn_id' => $invoice->latestPayment->provider_txn_id,
                    'created_at' => $invoice->latestPayment->created_at?->toDateTimeString(),
                ] : null,
            ]);

        return inertia('tutor/finance/Invoices', [
            'items' => $items,
            'filters' => [
                'status' => $status,
            ],
            'statusOptions' => Invoice::statuses(),
        ]);
    }
}

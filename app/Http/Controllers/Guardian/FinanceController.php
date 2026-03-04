<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Response;

class FinanceController extends Controller
{
    /**
     * Display guardian invoice center.
     */
    public function invoices(Request $request): Response
    {
        $status = strtolower(trim($request->string('status')->toString()));

        if ($status !== '' && InvoiceStatus::tryFrom($status) === null) {
            $status = '';
        }

        $items = Invoice::query()
            ->with(['latestPayment' => fn ($query) => $query->select([
                'payments.id',
                'payments.invoice_id',
                'payments.gateway',
                'payments.provider_txn_id',
                'payments.status',
                'payments.created_at',
            ])])
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

        return inertia('guardian/finance/Invoices', [
            'items' => $items,
            'filters' => [
                'status' => $status,
            ],
            'statusOptions' => Invoice::statuses(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display finance invoices for admin.
     */
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));
        $type = strtolower(trim($request->string('type')->toString()));

        if (! in_array($status, Invoice::statuses(), true)) {
            $status = '';
        }

        if (! in_array($type, Invoice::types(), true)) {
            $type = '';
        }

        $items = Invoice::query()
            ->with([
                'payer:id,name,email,role',
                'payee:id,name,email,role',
                'assignment:id,job_id,tutor_user_id',
                'assignment.job:id,title,slug',
                'latestPayment:id,invoice_id,gateway,provider_txn_id,status,created_at',
            ])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('invoice_no', 'like', "%{$search}%")
                        ->orWhereHas('payer', fn (Builder $payerQuery): Builder => $payerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('assignment.job', fn (Builder $jobQuery): Builder => $jobQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn (Builder $builder): Builder => $builder->where('status', $status))
            ->when($type !== '', fn (Builder $builder): Builder => $builder->where('type', $type))
            ->latest('id')
            ->paginate(20)
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
                'created_at' => $invoice->created_at?->toDateTimeString(),
                'payer' => [
                    'id' => $invoice->payer?->id,
                    'name' => $invoice->payer?->name,
                    'email' => $invoice->payer?->email,
                    'role' => $invoice->payer?->role,
                ],
                'payee' => [
                    'id' => $invoice->payee?->id,
                    'name' => $invoice->payee?->name,
                    'email' => $invoice->payee?->email,
                    'role' => $invoice->payee?->role,
                ],
                'job' => [
                    'id' => $invoice->assignment?->job?->id,
                    'title' => $invoice->assignment?->job?->title,
                    'slug' => $invoice->assignment?->job?->slug,
                ],
                'latest_payment' => $invoice->latestPayment ? [
                    'id' => $invoice->latestPayment->id,
                    'gateway' => $invoice->latestPayment->gateway,
                    'provider_txn_id' => $invoice->latestPayment->provider_txn_id,
                    'status' => $invoice->latestPayment->status,
                    'created_at' => $invoice->latestPayment->created_at?->toDateTimeString(),
                ] : null,
            ]);

        return inertia('admin/finance/Invoices', [
            'items' => $items,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'type' => $type,
            ],
            'statusOptions' => Invoice::statuses(),
            'typeOptions' => Invoice::types(),
        ]);
    }
}

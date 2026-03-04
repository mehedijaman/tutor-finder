<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Display finance payment attempts for admin.
     */
    public function index(Request $request): Response
    {
        $search = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));
        $gateway = strtolower(trim($request->string('gateway')->toString()));

        if ($status !== '' && PaymentStatus::tryFrom($status) === null) {
            $status = '';
        }

        if ($gateway !== '' && PaymentGatewayType::tryFrom($gateway) === null) {
            $gateway = '';
        }

        $items = Payment::query()
            ->with([
                'invoice:id,invoice_no,status,type,payer_user_id',
                'invoice.payer:id,name,email,role',
            ])
            ->when($search !== '', function (Builder $builder) use ($search): void {
                $builder->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('provider_txn_id', 'like', "%{$search}%")
                        ->orWhereHas('invoice', fn (Builder $invoiceQuery): Builder => $invoiceQuery
                            ->where('invoice_no', 'like', "%{$search}%"))
                        ->orWhereHas('invoice.payer', fn (Builder $payerQuery): Builder => $payerQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn (Builder $builder): Builder => $builder->where('status', $status))
            ->when($gateway !== '', fn (Builder $builder): Builder => $builder->where('gateway', $gateway))
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Payment $payment): array => [
                'id' => $payment->id,
                'gateway' => $payment->gateway,
                'provider_txn_id' => $payment->provider_txn_id,
                'amount' => $payment->amount,
                'status' => $payment->status,
                'created_at' => $payment->created_at?->toDateTimeString(),
                'invoice' => [
                    'id' => $payment->invoice?->id,
                    'invoice_no' => $payment->invoice?->invoice_no,
                    'status' => $payment->invoice?->status,
                    'type' => $payment->invoice?->type,
                    'payer_name' => $payment->invoice?->payer?->name,
                    'payer_email' => $payment->invoice?->payer?->email,
                ],
            ]);

        return inertia('admin/finance/Payments', [
            'items' => $items,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'gateway' => $gateway,
            ],
            'statusOptions' => Payment::statuses(),
            'gatewayOptions' => PaymentGatewayType::cases(),
        ]);
    }
}

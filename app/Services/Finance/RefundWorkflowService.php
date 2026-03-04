<?php

namespace App\Services\Finance;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RefundRequest;
use App\Models\TuitionJobAssignment;
use App\Models\User;
use DomainException;
use Illuminate\Database\DatabaseManager;

class RefundWorkflowService
{
    public function __construct(
        private readonly DatabaseManager $database,
        private readonly InvoiceLifecycleService $invoiceLifecycleService,
    ) {}

    /**
     * Submit refund request by tutor.
     */
    public function submit(TuitionJobAssignment $assignment, User $tutor, string $reasonText): RefundRequest
    {
        return $this->database->transaction(function () use ($assignment, $tutor, $reasonText): RefundRequest {
            /** @var TuitionJobAssignment $lockedAssignment */
            $lockedAssignment = TuitionJobAssignment::query()
                ->lockForUpdate()
                ->findOrFail($assignment->getKey());

            if ((int) $lockedAssignment->tutor_user_id !== (int) $tutor->getKey()) {
                throw new DomainException('Only assigned tutor can request a refund.');
            }

            $serviceFeeInvoice = Invoice::query()
                ->where('job_assignment_id', $lockedAssignment->id)
                ->where('type', InvoiceType::PlatformServiceFee)
                ->where('status', InvoiceStatus::Paid)
                ->lockForUpdate()
                ->first();

            if (! $serviceFeeInvoice instanceof Invoice) {
                throw new DomainException('Refund requires a paid platform service fee invoice.');
            }

            $hasPending = RefundRequest::query()
                ->where('job_assignment_id', $lockedAssignment->id)
                ->where('status', RefundStatus::Pending)
                ->lockForUpdate()
                ->exists();

            if ($hasPending) {
                throw new DomainException('A pending refund request already exists for this assignment.');
            }

            return RefundRequest::query()->create([
                'job_assignment_id' => $lockedAssignment->id,
                'requested_by_user_id' => $tutor->getKey(),
                'reason_text' => $reasonText,
                'requested_at' => now(),
                'status' => RefundStatus::Pending,
                'amount' => $serviceFeeInvoice->amount,
                'currency' => $serviceFeeInvoice->currency,
                'decision_by_admin_id' => null,
                'decision_note' => null,
                'decided_at' => null,
                'paid_at' => null,
                'payment_id' => null,
            ]);
        });
    }

    /**
     * Approve pending refund request.
     */
    public function approve(RefundRequest $refundRequest, User $admin, ?string $decisionNote = null): RefundRequest
    {
        return $this->database->transaction(function () use ($refundRequest, $admin, $decisionNote): RefundRequest {
            /** @var RefundRequest $lockedRequest */
            $lockedRequest = RefundRequest::query()
                ->lockForUpdate()
                ->findOrFail($refundRequest->getKey());

            if ($lockedRequest->status !== RefundStatus::Pending) {
                throw new DomainException('Only pending refund requests can be approved.');
            }

            $lockedRequest->forceFill([
                'status' => RefundStatus::Approved,
                'decision_by_admin_id' => $admin->getKey(),
                'decision_note' => $decisionNote,
                'decided_at' => now(),
            ])->save();

            return $lockedRequest->fresh(['assignment', 'requester']);
        });
    }

    /**
     * Reject pending refund request.
     */
    public function reject(RefundRequest $refundRequest, User $admin, string $decisionNote): RefundRequest
    {
        return $this->database->transaction(function () use ($refundRequest, $admin, $decisionNote): RefundRequest {
            /** @var RefundRequest $lockedRequest */
            $lockedRequest = RefundRequest::query()
                ->lockForUpdate()
                ->findOrFail($refundRequest->getKey());

            if ($lockedRequest->status !== RefundStatus::Pending) {
                throw new DomainException('Only pending refund requests can be rejected.');
            }

            $lockedRequest->forceFill([
                'status' => RefundStatus::Rejected,
                'decision_by_admin_id' => $admin->getKey(),
                'decision_note' => $decisionNote,
                'decided_at' => now(),
            ])->save();

            return $lockedRequest->fresh(['assignment', 'requester']);
        });
    }

    /**
     * Mark approved refund request as paid and post reversal ledger.
     *
     * @param  array<string, mixed>  $paymentContext
     */
    public function markPaid(RefundRequest $refundRequest, User $admin, array $paymentContext = []): RefundRequest
    {
        return $this->database->transaction(function () use ($refundRequest, $admin, $paymentContext): RefundRequest {
            /** @var RefundRequest $lockedRequest */
            $lockedRequest = RefundRequest::query()
                ->lockForUpdate()
                ->findOrFail($refundRequest->getKey());

            if ($lockedRequest->status !== RefundStatus::Approved) {
                throw new DomainException('Only approved refund requests can be marked paid.');
            }

            $serviceFeeInvoice = Invoice::query()
                ->where('job_assignment_id', $lockedRequest->job_assignment_id)
                ->where('type', InvoiceType::PlatformServiceFee)
                ->where('status', InvoiceStatus::Paid)
                ->lockForUpdate()
                ->first();

            if (! $serviceFeeInvoice instanceof Invoice) {
                throw new DomainException('Related paid service fee invoice not found.');
            }

            $payoutPayment = Payment::query()->create([
                'invoice_id' => $serviceFeeInvoice->id,
                'gateway' => $paymentContext['gateway'] ?? PaymentGatewayType::Manual,
                'provider_txn_id' => $paymentContext['provider_txn_id'] ?? ('REFUND-'.$lockedRequest->id.'-'.now()->timestamp),
                'amount' => $lockedRequest->amount ?? $serviceFeeInvoice->amount,
                'status' => PaymentStatus::Refunded,
                'provider_payload' => [
                    'admin_id' => $admin->getKey(),
                    'note' => $paymentContext['note'] ?? null,
                ],
            ]);

            $lockedRequest->forceFill([
                'status' => RefundStatus::Paid,
                'decision_by_admin_id' => $admin->getKey(),
                'decision_note' => $paymentContext['note'] ?? $lockedRequest->decision_note,
                'decided_at' => $lockedRequest->decided_at ?? now(),
                'paid_at' => now(),
                'payment_id' => $payoutPayment->id,
            ])->save();

            $this->invoiceLifecycleService->markRefunded(
                invoice: $serviceFeeInvoice,
                refundRequest: $lockedRequest,
                refundPayment: $payoutPayment,
            );

            return $lockedRequest->fresh(['assignment', 'requester', 'decisionBy', 'payment']);
        });
    }
}

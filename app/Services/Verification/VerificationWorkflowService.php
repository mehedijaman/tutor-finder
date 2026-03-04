<?php

namespace App\Services\Verification;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use App\Models\Invoice;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Services\Finance\InvoiceLifecycleService;
use DomainException;
use Illuminate\Support\Facades\DB;

class VerificationWorkflowService
{
    public function __construct(
        private readonly InvoiceLifecycleService $invoiceLifecycleService,
    ) {}

    /**
     * Approve a pending verification request.
     */
    public function approve(VerificationRequest $verificationRequest, User $admin): void
    {
        DB::transaction(function () use ($verificationRequest, $admin): void {
            $lockedRequest = VerificationRequest::query()->lockForUpdate()->findOrFail($verificationRequest->getKey());
            $lockedUser = User::query()->lockForUpdate()->findOrFail($lockedRequest->user_id);

            $lockedRequest->markApproved($admin);

            $lockedUser->forceFill([
                'verification_status' => VerificationStatus::Approved,
                'verification_type' => $lockedRequest->role,
                'verified_at' => null,
            ])->save();
        });
    }

    /**
     * Reject or cancel a verification request.
     *
     * @param  array{decision_status: string, reason: string}  $data
     */
    public function reject(VerificationRequest $verificationRequest, User $admin, array $data): void
    {
        DB::transaction(function () use ($verificationRequest, $admin, $data): void {
            $lockedRequest = VerificationRequest::query()->lockForUpdate()->findOrFail($verificationRequest->getKey());
            $lockedUser = User::query()->lockForUpdate()->findOrFail($lockedRequest->user_id);

            $status = VerificationStatus::from($data['decision_status']);
            $lockedRequest->markDecision($status, (string) $data['reason'], $admin);

            $userStatus = $status === VerificationStatus::Rejected
                ? VerificationStatus::Rejected
                : VerificationStatus::Cancelled;

            $lockedUser->forceFill([
                'verification_status' => $userStatus,
                'verified_at' => null,
            ])->save();
        });
    }

    /**
     * Generate an invoice for a verification request.
     *
     * @param  array{amount?: float|string|null, currency?: string|null, due_at?: string|null, expires_at?: string|null, notes?: string|null}  $data
     */
    public function issueInvoice(VerificationRequest $verificationRequest, User $admin, array $data): void
    {
        DB::transaction(function () use ($verificationRequest, $admin, $data): void {
            $lockedRequest = VerificationRequest::query()->with('invoice')->lockForUpdate()->findOrFail($verificationRequest->getKey());
            $lockedUser = User::query()->lockForUpdate()->findOrFail($lockedRequest->user_id);
            $siteSetting = SiteSetting::current();
            $platformOwnerUserId = $siteSetting->platformOwnerUserId();

            if (! in_array($lockedRequest->status, [VerificationStatus::Pending, VerificationStatus::Approved], true)) {
                throw new DomainException('Invoice can only be generated for pending or approved requests.');
            }

            if ($platformOwnerUserId === null) {
                throw new DomainException('Platform finance account is not configured. Please update site settings.');
            }

            if ($lockedRequest->invoice instanceof Invoice) {
                if (! in_array($lockedRequest->invoice->status, Invoice::recoverableStatuses(), true)) {
                    throw new DomainException('An active invoice already exists for this verification request.');
                }

                $lockedRequest->invoice->delete();
            }

            $amount = isset($data['amount']) ? (float) $data['amount'] : (float) $lockedRequest->fee_amount;
            $currency = $data['currency'] ?? $lockedRequest->currency;
            $invoiceType = $lockedRequest->role === VerificationRole::Guardian
                ? InvoiceType::GuardianVerificationFee
                : InvoiceType::TutorVerificationFee;

            $this->invoiceLifecycleService->issue([
                'invoice_no' => null,
                'invoiceable_type' => VerificationRequest::class,
                'invoiceable_id' => $lockedRequest->getKey(),
                'user_id' => $lockedUser->getKey(),
                'payer_user_id' => $lockedUser->getKey(),
                'payee_user_id' => $platformOwnerUserId,
                'type' => $invoiceType,
                'job_assignment_id' => null,
                'amount' => $amount,
                'currency' => $currency,
                'status' => InvoiceStatus::Unpaid,
                'due_at' => $data['due_at'] ?? now()->addDays(7),
                'expires_at' => $data['expires_at'] ?? ($data['due_at'] ?? now()->addDays(7)),
                'issued_by' => $admin->getKey(),
                'issued_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $lockedRequest->markInvoiced($admin);

            $lockedUser->forceFill([
                'verification_status' => VerificationStatus::Invoiced,
                'verification_type' => $lockedRequest->role,
            ])->save();
        });
    }
}

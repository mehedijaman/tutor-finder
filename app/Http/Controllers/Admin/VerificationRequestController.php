<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceCreateRequest;
use App\Http\Requests\Admin\VerificationDecisionRequest;
use App\Models\GuardianProfile;
use App\Models\Invoice;
use App\Models\TutorProfile;
use App\Models\User;
use App\Models\VerificationRequest;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class VerificationRequestController extends Controller
{
    /**
     * Display pending profile verification queue.
     */
    public function pendingProfiles(Request $request): Response
    {
        return $this->profileVerificationIndex($request, 'pending');
    }

    /**
     * Display unverified profiles.
     */
    public function unverifiedProfiles(Request $request): Response
    {
        return $this->profileVerificationIndex($request, 'unverified');
    }

    /**
     * Display verified profiles.
     */
    public function verifiedProfiles(Request $request): Response
    {
        return $this->profileVerificationIndex($request, 'verified');
    }

    /**
     * Display verification requests listing.
     */
    public function index(Request $request): Response
    {
        $query = trim($request->string('q')->toString());
        $status = strtolower(trim($request->string('status')->toString()));
        $role = strtolower(trim($request->string('role')->toString()));

        if (! in_array($status, $this->statusOptions(), true)) {
            $status = '';
        }

        if (! in_array($role, [VerificationRequest::ROLE_TUTOR, VerificationRequest::ROLE_GUARDIAN], true)) {
            $role = '';
        }

        $items = VerificationRequest::query()
            ->with(['user:id,name,email,phone,verification_status', 'invoice'])
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->whereHas('user', fn ($userQuery) => $userQuery
                            ->where('name', 'like', "%{$query}%")
                            ->orWhere('email', 'like', "%{$query}%")
                            ->orWhere('phone', 'like', "%{$query}%"));
                });
            })
            ->when($status !== '', fn ($builder) => $builder->where('status', $status))
            ->when($role !== '', fn ($builder) => $builder->where('role', $role))
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (VerificationRequest $verificationRequest): array => [
                'id' => $verificationRequest->id,
                'user_id' => $verificationRequest->user_id,
                'user_name' => $verificationRequest->user?->name,
                'user_email' => $verificationRequest->user?->email,
                'role' => $verificationRequest->role,
                'status' => $verificationRequest->status,
                'fee_amount' => $verificationRequest->fee_amount,
                'currency' => $verificationRequest->currency,
                'submitted_at' => $verificationRequest->submitted_at?->toDateTimeString(),
                'invoice_status' => $verificationRequest->invoice?->status,
                'invoice_no' => $verificationRequest->invoice?->invoice_no,
                'invoice_id' => $verificationRequest->invoice?->id,
            ]);

        return inertia('admin/verifications/Index', [
            'items' => $items,
            'filters' => [
                'q' => $query,
                'status' => $status,
                'role' => $role,
            ],
            'statusOptions' => $this->statusOptions(),
            'roleOptions' => [VerificationRequest::ROLE_TUTOR, VerificationRequest::ROLE_GUARDIAN],
        ]);
    }

    /**
     * Show specific verification request details.
     */
    public function show(VerificationRequest $verificationRequest): Response
    {
        $verificationRequest->load([
            'user:id,name,email,phone,role,verification_status,verified_at',
            'reviewer:id,name',
            'invoice',
        ]);

        $profileSnapshot = $verificationRequest->role === VerificationRequest::ROLE_TUTOR
            ? TutorProfile::query()->where('user_id', $verificationRequest->user_id)->first()
            : GuardianProfile::query()->where('user_id', $verificationRequest->user_id)->first();

        $educationSnapshot = $verificationRequest->role === VerificationRequest::ROLE_TUTOR
            ? $verificationRequest->user?->tutorEducations()->get()
            : collect();

        return inertia('admin/verifications/Show', [
            'verification' => [
                'id' => $verificationRequest->id,
                'status' => $verificationRequest->status,
                'role' => $verificationRequest->role,
                'fee_amount' => $verificationRequest->fee_amount,
                'currency' => $verificationRequest->currency,
                'submitted_at' => $verificationRequest->submitted_at?->toDateTimeString(),
                'reviewed_at' => $verificationRequest->reviewed_at?->toDateTimeString(),
                'reviewed_by' => $verificationRequest->reviewer?->name,
                'decision_reason' => $verificationRequest->decision_reason,
                'metadata' => $verificationRequest->metadata,
                'user' => [
                    'id' => $verificationRequest->user?->id,
                    'name' => $verificationRequest->user?->name,
                    'email' => $verificationRequest->user?->email,
                    'phone' => $verificationRequest->user?->phone,
                    'verification_status' => $verificationRequest->user?->verification_status,
                    'verified_at' => $verificationRequest->user?->verified_at?->toDateTimeString(),
                ],
                'invoice' => $verificationRequest->invoice ? [
                    'id' => $verificationRequest->invoice->id,
                    'invoice_no' => $verificationRequest->invoice->invoice_no,
                    'status' => $verificationRequest->invoice->status,
                    'amount' => $verificationRequest->invoice->amount,
                    'currency' => $verificationRequest->invoice->currency,
                    'issued_at' => $verificationRequest->invoice->issued_at?->toDateTimeString(),
                    'due_at' => $verificationRequest->invoice->due_at?->toDateTimeString(),
                    'expires_at' => $verificationRequest->invoice->expires_at?->toDateTimeString(),
                    'paid_at' => $verificationRequest->invoice->paid_at?->toDateTimeString(),
                    'payment_gateway' => $verificationRequest->invoice->payment_gateway,
                    'payment_reference' => $verificationRequest->invoice->payment_reference,
                    'transaction_id' => $verificationRequest->invoice->transaction_id,
                ] : null,
            ],
            'profileSnapshot' => $profileSnapshot,
            'educationSnapshot' => $educationSnapshot,
        ]);
    }

    /**
     * Approve verification request.
     */
    public function approve(VerificationDecisionRequest $request, VerificationRequest $verificationRequest): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $verificationRequest): void {
                $admin = $request->user();

                $lockedRequest = VerificationRequest::query()->lockForUpdate()->findOrFail($verificationRequest->getKey());
                $lockedUser = User::query()->lockForUpdate()->findOrFail($lockedRequest->user_id);

                $lockedRequest->markApproved($admin);

                $lockedUser->forceFill([
                    'verification_status' => User::VERIFICATION_STATUS_APPROVED,
                    'verification_type' => $lockedRequest->role,
                    'verified_at' => null,
                ])->save();
            });
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['verification' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Verification request approved successfully.');
    }

    /**
     * Reject or cancel verification request.
     */
    public function reject(VerificationDecisionRequest $request, VerificationRequest $verificationRequest): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($request, $verificationRequest, $validated): void {
                $admin = $request->user();
                $lockedRequest = VerificationRequest::query()->lockForUpdate()->findOrFail($verificationRequest->getKey());
                $lockedUser = User::query()->lockForUpdate()->findOrFail($lockedRequest->user_id);

                $lockedRequest->markDecision((string) $validated['decision_status'], (string) $validated['reason'], $admin);

                $userStatus = $validated['decision_status'] === VerificationRequest::STATUS_REJECTED
                    ? User::VERIFICATION_STATUS_REJECTED
                    : User::VERIFICATION_STATUS_CANCELLED;

                $lockedUser->forceFill([
                    'verification_status' => $userStatus,
                    'verified_at' => null,
                ])->save();
            });
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['verification' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Verification decision saved successfully.');
    }

    /**
     * Generate invoice for verification request.
     */
    public function createInvoice(InvoiceCreateRequest $request, VerificationRequest $verificationRequest): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($request, $verificationRequest, $validated): void {
                $admin = $request->user();
                $lockedRequest = VerificationRequest::query()->with('invoice')->lockForUpdate()->findOrFail($verificationRequest->getKey());
                $lockedUser = User::query()->lockForUpdate()->findOrFail($lockedRequest->user_id);

                if (! in_array($lockedRequest->status, [VerificationRequest::STATUS_PENDING, VerificationRequest::STATUS_APPROVED], true)) {
                    throw new DomainException('Invoice can only be generated for pending or approved requests.');
                }

                if ($lockedRequest->invoice instanceof Invoice) {
                    if (! in_array($lockedRequest->invoice->status, Invoice::recoverableStatuses(), true)) {
                        throw new DomainException('An active invoice already exists for this verification request.');
                    }

                    $lockedRequest->invoice->delete();
                }

                $amount = isset($validated['amount']) ? (float) $validated['amount'] : (float) $lockedRequest->fee_amount;
                $currency = $validated['currency'] ?? $lockedRequest->currency;

                $lockedRequest->invoice()->create([
                    'invoice_no' => $this->generateInvoiceNumber(),
                    'user_id' => $lockedUser->getKey(),
                    'amount' => $amount,
                    'currency' => $currency,
                    'status' => Invoice::STATUS_UNPAID,
                    'due_at' => $validated['due_at'] ?? now()->addDays(7),
                    'expires_at' => $validated['expires_at'] ?? ($validated['due_at'] ?? now()->addDays(7)),
                    'issued_by' => $admin?->getKey(),
                    'issued_at' => now(),
                    'notes' => $validated['notes'] ?? null,
                ]);

                $lockedRequest->markInvoiced($admin);

                $lockedUser->forceFill([
                    'verification_status' => User::VERIFICATION_STATUS_INVOICED,
                    'verification_type' => $lockedRequest->role,
                ])->save();
            });
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['invoice' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Invoice generated successfully.');
    }

    /**
     * @return list<string>
     */
    private function statusOptions(): array
    {
        return [
            VerificationRequest::STATUS_PENDING,
            VerificationRequest::STATUS_APPROVED,
            VerificationRequest::STATUS_INVOICED,
            VerificationRequest::STATUS_VERIFIED,
            VerificationRequest::STATUS_REJECTED,
            VerificationRequest::STATUS_CANCELLED,
        ];
    }

    /**
     * Generate unique invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        do {
            $candidate = 'INV-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Invoice::query()->where('invoice_no', $candidate)->exists());

        return $candidate;
    }

    /**
     * Display users by profile verification bucket.
     */
    private function profileVerificationIndex(Request $request, string $bucket): Response
    {
        $query = trim($request->string('q')->toString());
        $role = strtolower(trim($request->string('role')->toString()));

        if (! in_array($role, [VerificationRequest::ROLE_TUTOR, VerificationRequest::ROLE_GUARDIAN], true)) {
            $role = '';
        }

        $bucketStatuses = match ($bucket) {
            'pending' => [
                User::VERIFICATION_STATUS_PENDING,
                User::VERIFICATION_STATUS_APPROVED,
                User::VERIFICATION_STATUS_INVOICED,
            ],
            'unverified' => [User::VERIFICATION_STATUS_UNVERIFIED],
            'verified' => [User::VERIFICATION_STATUS_VERIFIED],
            default => abort(404),
        };

        $pageMeta = match ($bucket) {
            'pending' => [
                'title' => 'Pending Profile Verification',
                'description' => 'Profiles currently waiting for verification completion.',
            ],
            'unverified' => [
                'title' => 'Unverified Profiles',
                'description' => 'Profiles that have not started verification yet.',
            ],
            'verified' => [
                'title' => 'Verified Profiles',
                'description' => 'Profiles with completed verification.',
            ],
            default => abort(404),
        };

        $items = User::query()
            ->whereIn('role', [VerificationRequest::ROLE_TUTOR, VerificationRequest::ROLE_GUARDIAN])
            ->whereIn('verification_status', $bucketStatuses)
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($subQuery) use ($query): void {
                    $subQuery
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%");
                });
            })
            ->when($role !== '', fn ($builder) => $builder->where('role', $role))
            ->with(['latestVerificationRequest.invoice'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(function (User $user): array {
                $latestVerificationRequest = $user->latestVerificationRequest;

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'verification_status' => $user->verification_status,
                    'verified_at' => $user->verified_at?->toDateTimeString(),
                    'request_id' => $latestVerificationRequest?->id,
                    'request_status' => $latestVerificationRequest?->status,
                    'submitted_at' => $latestVerificationRequest?->submitted_at?->toDateTimeString(),
                    'invoice_status' => $latestVerificationRequest?->invoice?->status,
                    'invoice_no' => $latestVerificationRequest?->invoice?->invoice_no,
                ];
            });

        return inertia('admin/verifications/ProfileVerificationIndex', [
            'items' => $items,
            'filters' => [
                'q' => $query,
                'role' => $role,
            ],
            'roleOptions' => [VerificationRequest::ROLE_TUTOR, VerificationRequest::ROLE_GUARDIAN],
            'bucket' => $bucket,
            'title' => $pageMeta['title'],
            'description' => $pageMeta['description'],
        ]);
    }
}

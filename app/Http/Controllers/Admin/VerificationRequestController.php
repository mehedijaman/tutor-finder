<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InvoiceCreateRequest;
use App\Http\Requests\Admin\VerificationDecisionRequest;
use App\Models\GuardianProfile;
use App\Models\TutorProfile;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Services\Verification\VerificationWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        if (! in_array($role, [VerificationRole::Tutor->value, VerificationRole::Guardian->value], true)) {
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
            'roleOptions' => [VerificationRole::Tutor, VerificationRole::Guardian],
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

        $profileSnapshot = $verificationRequest->role === VerificationRole::Tutor
            ? TutorProfile::query()->where('user_id', $verificationRequest->user_id)->first()
            : GuardianProfile::query()->where('user_id', $verificationRequest->user_id)->first();

        $educationSnapshot = $verificationRequest->role === VerificationRole::Tutor
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
    public function approve(
        VerificationDecisionRequest $request,
        VerificationRequest $verificationRequest,
        VerificationWorkflowService $workflowService,
    ): RedirectResponse {
        try {
            $workflowService->approve($verificationRequest, $request->user());
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['verification' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Verification request approved successfully.');
    }

    /**
     * Reject or cancel verification request.
     */
    public function reject(
        VerificationDecisionRequest $request,
        VerificationRequest $verificationRequest,
        VerificationWorkflowService $workflowService,
    ): RedirectResponse {
        try {
            $workflowService->reject($verificationRequest, $request->user(), $request->validated());
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors(['verification' => $exception->getMessage()]);
        }

        return redirect()->back()->with('status', 'Verification decision saved successfully.');
    }

    /**
     * Generate invoice for verification request.
     */
    public function createInvoice(
        InvoiceCreateRequest $request,
        VerificationRequest $verificationRequest,
        VerificationWorkflowService $workflowService,
    ): RedirectResponse {
        try {
            $workflowService->issueInvoice($verificationRequest, $request->user(), $request->validated());
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
            VerificationStatus::Pending->value,
            VerificationStatus::Approved->value,
            VerificationStatus::Invoiced->value,
            VerificationStatus::Verified->value,
            VerificationStatus::Rejected->value,
            VerificationStatus::Cancelled->value,
        ];
    }

    /**
     * Display users by profile verification bucket.
     */
    private function profileVerificationIndex(Request $request, string $bucket): Response
    {
        $query = trim($request->string('q')->toString());
        $role = strtolower(trim($request->string('role')->toString()));

        if (! in_array($role, [VerificationRole::Tutor->value, VerificationRole::Guardian->value], true)) {
            $role = '';
        }

        $bucketStatuses = match ($bucket) {
            'pending' => [
                VerificationStatus::Pending,
                VerificationStatus::Approved,
                VerificationStatus::Invoiced,
            ],
            'unverified' => [VerificationStatus::Unverified],
            'verified' => [VerificationStatus::Verified],
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
            ->whereIn('role', [VerificationRole::Tutor, VerificationRole::Guardian])
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
            'roleOptions' => [VerificationRole::Tutor, VerificationRole::Guardian],
            'bucket' => $bucket,
            'title' => $pageMeta['title'],
            'description' => $pageMeta['description'],
        ]);
    }
}

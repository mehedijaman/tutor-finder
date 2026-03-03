<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerificationRequestStoreRequest;
use App\Models\User;
use App\Models\VerificationRequest;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class GuardianVerificationController extends Controller
{
    /**
     * Show guardian verification status page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        $verificationRequest = VerificationRequest::query()
            ->with('invoice')
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        return inertia('guardian/Verification/Show', [
            'verification' => $verificationRequest ? [
                'id' => $verificationRequest->id,
                'status' => $verificationRequest->status,
                'role' => $verificationRequest->role,
                'fee_amount' => $verificationRequest->fee_amount,
                'currency' => $verificationRequest->currency,
                'submitted_at' => $verificationRequest->submitted_at?->toDateTimeString(),
                'reviewed_at' => $verificationRequest->reviewed_at?->toDateTimeString(),
                'decision_reason' => $verificationRequest->decision_reason,
                'invoice' => $verificationRequest->invoice ? [
                    'id' => $verificationRequest->invoice->id,
                    'invoice_no' => $verificationRequest->invoice->invoice_no,
                    'amount' => $verificationRequest->invoice->amount,
                    'currency' => $verificationRequest->invoice->currency,
                    'status' => $verificationRequest->invoice->status,
                    'due_at' => $verificationRequest->invoice->due_at?->toDateTimeString(),
                    'expires_at' => $verificationRequest->invoice->expires_at?->toDateTimeString(),
                    'paid_at' => $verificationRequest->invoice->paid_at?->toDateTimeString(),
                    'payment_gateway' => $verificationRequest->invoice->payment_gateway,
                ] : null,
            ] : null,
            'verificationStatus' => $user->verification_status,
            'verifiedAt' => $user->verified_at?->toDateTimeString(),
        ]);
    }

    /**
     * Store guardian verification request.
     */
    public function store(VerificationRequestStoreRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            DB::transaction(function () use ($user, $request): void {
                /** @var User $lockedUser */
                $lockedUser = User::query()
                    ->whereKey($user->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedUser->verification_status === User::VERIFICATION_STATUS_VERIFIED || $lockedUser->verified_at !== null) {
                    throw new DomainException('Your profile is already verified.');
                }

                $hasActiveRequest = VerificationRequest::query()
                    ->where('user_id', $lockedUser->getKey())
                    ->whereIn('status', VerificationRequest::activeStatuses())
                    ->lockForUpdate()
                    ->exists();

                if ($hasActiveRequest) {
                    throw new DomainException('An active verification request already exists.');
                }

                VerificationRequest::query()->create([
                    'user_id' => $lockedUser->getKey(),
                    'role' => $request->requestedRole(),
                    'status' => VerificationRequest::STATUS_PENDING,
                    'fee_amount' => 500,
                    'currency' => 'BDT',
                    'submitted_at' => now(),
                    'metadata' => $request->validated('metadata') ?? null,
                ]);

                $lockedUser->forceFill([
                    'verification_status' => User::VERIFICATION_STATUS_PENDING,
                    'verification_type' => $request->requestedRole(),
                    'verified_at' => null,
                ])->save();
            });
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() === '23000') {
                return redirect()->back()->withErrors([
                    'verification' => 'An active verification request already exists.',
                ]);
            }

            throw $exception;
        } catch (DomainException $exception) {
            return redirect()->back()->withErrors([
                'verification' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('guardian.verification.show')->with('status', 'Verification request submitted successfully.');
    }
}

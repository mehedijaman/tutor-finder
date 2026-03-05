<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\VerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\VerificationRequestStoreRequest;
use App\Models\User;
use App\Models\VerificationRequest;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class GuardianVerificationController extends Controller
{
    /**
     * Redirect to guardian profile page (verification is now a tab).
     */
    public function show(): RedirectResponse
    {
        return redirect()->route('guardian.profile.edit');
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

                if ($lockedUser->verification_status === VerificationStatus::Verified || $lockedUser->verified_at !== null) {
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
                    'status' => VerificationStatus::Pending,
                    'fee_amount' => 500,
                    'currency' => 'BDT',
                    'submitted_at' => now(),
                    'metadata' => $request->validated('metadata') ?? null,
                ]);

                $lockedUser->forceFill([
                    'verification_status' => VerificationStatus::Pending,
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

        return redirect()->route('guardian.profile.edit')->with('status', 'Verification request submitted successfully.');
    }
}

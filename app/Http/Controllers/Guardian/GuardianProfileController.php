<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\TaxonomyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\GuardianProfileUpdateRequest;
use App\Models\GuardianProfile;
use App\Models\VerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class GuardianProfileController extends Controller
{
    /**
     * Show guardian profile edit page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profile = $user->guardianProfile()->first();

        $verificationRequest = VerificationRequest::query()
            ->with('invoice')
            ->where('user_id', $user->getKey())
            ->latest('id')
            ->first();

        return inertia('guardian/Profile/Edit', [
            'profile' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'phone_alt' => $profile?->phone_alt,
                'emergency_contact' => $profile?->emergency_contact,
                'guardian_name' => $profile?->guardian_name,
                'relationship_to_student' => $profile?->relationship_to_student,
                'address' => $profile?->address,
                'city' => $profile?->city,
                'area' => $profile?->area,
                'occupation' => $profile?->occupation,
                'notes' => $profile?->notes,
                'preferred_contact_time' => $profile?->preferred_contact_time,
                'status' => $profile?->status ?? TaxonomyStatus::Active->value,
            ],
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
     * Update guardian profile.
     */
    public function update(GuardianProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($validated, $user): void {
            $user->forceFill([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
            ])->save();

            $profileData = Arr::except($validated, ['name', 'phone']);

            $profile = GuardianProfile::query()->firstOrNew([
                'user_id' => $user->getKey(),
            ]);

            $profile->fill($profileData);
            $profile->status = $profileData['status'] ?? TaxonomyStatus::Active;
            $profile->save();
        });

        return redirect()
            ->route('guardian.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}

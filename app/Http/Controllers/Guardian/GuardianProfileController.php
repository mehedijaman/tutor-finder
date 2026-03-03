<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guardian\GuardianProfileUpdateRequest;
use App\Models\GuardianProfile;
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

        return inertia('guardian/Profile/Edit', [
            'profile' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'phone_alt' => $profile?->phone_alt,
                'guardian_name' => $profile?->guardian_name,
                'address' => $profile?->address,
                'occupation' => $profile?->occupation,
                'notes' => $profile?->notes,
                'status' => $profile?->status ?? GuardianProfile::STATUS_ACTIVE,
            ],
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
            $profile->status = $profileData['status'] ?? GuardianProfile::STATUS_ACTIVE;
            $profile->save();
        });

        return redirect()
            ->route('guardian.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }
}

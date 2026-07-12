<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\Otp\OtpService;
use App\Support\Auth\RoleRedirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class VerifyOtpController extends Controller
{
    /**
     * Show the OTP verification page.
     */
    public function create(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return redirect('/login');
        }

        if ($user->role === UserRole::Admin) {
            return redirect('/admin/dashboard');
        }

        if ($user->status === UserStatus::Active) {
            return redirect(RoleRedirector::destinationFor($user));
        }

        if ($user->status === UserStatus::Suspended) {
            abort(403);
        }

        $localOtp = null;

        if ((string) config('app.env') === 'local') {
            $payload = $request->session()->get('otp.local_debug');

            if (is_array($payload) && ($payload['phone'] ?? null) === $user->phone && ($payload['purpose'] ?? null) === 'register') {
                $code = $payload['code'] ?? null;
                $localOtp = is_string($code) ? $code : null;
            }
        }

        return inertia('auth/VerifyOtp', [
            'phone' => $user->phone,
            'status' => $request->session()->get('status'),
            'localOtp' => $localOtp,
        ]);
    }

    /**
     * Verify the submitted OTP code.
     */
    public function store(VerifyOtpRequest $request, OtpService $otpService): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! in_array($user->role, [UserRole::Guardian, UserRole::Tutor], true)) {
            return redirect('/login');
        }

        if ($user->status === UserStatus::Active) {
            return redirect(RoleRedirector::destinationFor($user));
        }

        if ($user->status === UserStatus::Suspended) {
            abort(403);
        }

        if (! $user->phone) {
            throw ValidationException::withMessages([
                'code' => 'A phone number is required to complete verification.',
            ]);
        }

        $isValid = $otpService->verifyPhoneCode(
            $user->phone,
            'register',
            (string) $request->string('code')
        );

        if (! $isValid) {
            throw ValidationException::withMessages([
                'code' => 'The provided verification code is invalid or expired.',
            ]);
        }

        $user->forceFill([
            'phone_verified_at' => now(),
            'status' => UserStatus::Active,
        ])->save();

        $request->session()->forget('otp.local_debug');

        return redirect(RoleRedirector::destinationFor($user));
    }

    /**
     * Resend the OTP code.
     */
    public function resend(Request $request, OtpService $otpService): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return redirect('/login');
        }

        $otpService->issueForRegistration($user, $request, false);

        return redirect()->route('otp.verify')->with('status', 'A new verification code has been sent to your phone.');
    }
}

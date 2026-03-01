<?php

namespace App\Http\Responses\Auth;

use App\Models\User;
use App\Services\Otp\OtpService;
use App\Support\Auth\RoleRedirector;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function __construct(private OtpService $otpService) {}

    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return redirect('/login');
        }

        if ($user->status === 'pending_verification' && in_array($user->role, ['guardian', 'tutor'], true)) {
            $this->otpService->issueForRegistration($user, $request, true);
        }

        return redirect(RoleRedirector::destinationFor($user));
    }
}

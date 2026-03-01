<?php

namespace App\Http\Responses\Auth;

use App\Models\User;
use App\Services\Otp\OtpService;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function __construct(private OtpService $otpService) {}

    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request)
    {
        $user = $request->user();

        if ($user instanceof User && in_array($user->role, ['guardian', 'tutor'], true)) {
            $this->otpService->issueForRegistration($user, $request, false);

            return redirect('/verify-otp');
        }

        return redirect('/dashboard');
    }
}

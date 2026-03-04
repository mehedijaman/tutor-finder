<?php

namespace App\Support\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;

class RoleRedirector
{
    /**
     * Resolve a dashboard path or verification path for the authenticated user.
     */
    public static function destinationFor(User $user): string
    {
        if ($user->status === UserStatus::PendingVerification) {
            return '/verify-otp';
        }

        return match ($user->role) {
            UserRole::Tutor => '/tutor/dashboard',
            UserRole::Admin => '/admin/dashboard',
            default => '/guardian/dashboard',
        };
    }
}

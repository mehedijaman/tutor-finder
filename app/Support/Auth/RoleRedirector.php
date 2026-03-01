<?php

namespace App\Support\Auth;

use App\Models\User;

class RoleRedirector
{
    /**
     * Resolve a dashboard path or verification path for the authenticated user.
     */
    public static function destinationFor(User $user): string
    {
        if ($user->status === 'pending_verification') {
            return '/verify-otp';
        }

        return match ($user->role) {
            'tutor' => '/tutor/dashboard',
            'admin' => '/admin/dashboard',
            default => '/guardian/dashboard',
        };
    }
}

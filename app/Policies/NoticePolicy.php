<?php

namespace App\Policies;

use App\Models\Notice;
use App\Models\User;

class NoticePolicy
{
    /**
     * Determine whether the user can view any notices.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the notice.
     */
    public function view(User $user, Notice $notice): bool
    {
        if (! $notice->is_active) {
            return false;
        }

        if ($notice->published_at && $notice->published_at->isFuture()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can create notices.
     */
    public function create(User $user): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can update the notice.
     */
    public function update(User $user, Notice $notice): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can delete the notice.
     */
    public function delete(User $user, Notice $notice): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can restore the notice.
     */
    public function restore(User $user, Notice $notice): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can permanently delete the notice.
     */
    public function forceDelete(User $user, Notice $notice): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }
}

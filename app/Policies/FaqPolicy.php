<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    /**
     * Determine whether the user can view any FAQs.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the FAQ.
     */
    public function view(User $user, Faq $faq): bool
    {
        if ($faq->status->isActive()) {
            return true;
        }

        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can create FAQs.
     */
    public function create(User $user): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can update the FAQ.
     */
    public function update(User $user, Faq $faq): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can delete the FAQ.
     */
    public function delete(User $user, Faq $faq): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can restore the FAQ.
     */
    public function restore(User $user, Faq $faq): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can permanently delete the FAQ.
     */
    public function forceDelete(User $user, Faq $faq): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }
}

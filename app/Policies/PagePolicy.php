<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    /**
     * Determine whether the user can view any pages.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the page.
     */
    public function view(User $user, Page $page): bool
    {
        if ($page->status->isPublished()) {
            return true;
        }

        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can create pages.
     */
    public function create(User $user): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can update the page.
     */
    public function update(User $user, Page $page): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can delete the page.
     */
    public function delete(User $user, Page $page): bool
    {
        if ($page->is_system) {
            return false;
        }

        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can restore the page.
     */
    public function restore(User $user, Page $page): bool
    {
        return $user->role === \App\Enums\UserRole::Admin;
    }

    /**
     * Determine whether the user can permanently delete the page.
     */
    public function forceDelete(User $user, Page $page): bool
    {
        if ($page->is_system) {
            return false;
        }

        return $user->role === \App\Enums\UserRole::Admin;
    }
}

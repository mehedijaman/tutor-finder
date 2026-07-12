<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    /**
     * Determine whether the user can view any blog posts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the blog post.
     */
    public function view(User $user, BlogPost $blogPost): bool
    {
        if ($blogPost->status->isPublished()) {
            return true;
        }

        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can create blog posts.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can update the blog post.
     */
    public function update(User $user, BlogPost $blogPost): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can delete the blog post.
     */
    public function delete(User $user, BlogPost $blogPost): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can restore the blog post.
     */
    public function restore(User $user, BlogPost $blogPost): bool
    {
        return $user->role === UserRole::Admin;
    }

    /**
     * Determine whether the user can permanently delete the blog post.
     */
    public function forceDelete(User $user, BlogPost $blogPost): bool
    {
        return $user->role === UserRole::Admin;
    }
}

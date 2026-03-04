<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TuitionJob;
use App\Models\User;

class TuitionJobPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Guardian;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TuitionJob $tuitionJob): bool
    {
        return $this->isOwner($user, $tuitionJob);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Guardian;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TuitionJob $tuitionJob): bool
    {
        return $this->isOwner($user, $tuitionJob);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TuitionJob $tuitionJob): bool
    {
        return $this->isOwner($user, $tuitionJob);
    }

    /**
     * Determine whether the guardian can manage applications for this job.
     */
    public function manageApplications(User $user, TuitionJob $tuitionJob): bool
    {
        return $this->isOwner($user, $tuitionJob);
    }

    /**
     * Check whether the user is the guardian owner of the job.
     */
    private function isOwner(User $user, TuitionJob $tuitionJob): bool
    {
        return $user->role === UserRole::Guardian
            && (int) $tuitionJob->guardian_id === (int) $user->getAuthIdentifier();
    }
}

<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\TuitionJobApplication;
use App\Models\User;

class TuitionJobApplicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Tutor
            || $user->role === UserRole::Guardian;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TuitionJobApplication $tuitionJobApplication): bool
    {
        return $this->isTutorOwner($user, $tuitionJobApplication)
            || $this->isGuardianJobOwner($user, $tuitionJobApplication);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Tutor;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TuitionJobApplication $tuitionJobApplication): bool
    {
        return $this->isTutorOwner($user, $tuitionJobApplication)
            || $this->isGuardianJobOwner($user, $tuitionJobApplication);
    }

    /**
     * Determine whether the tutor can withdraw the application.
     */
    public function withdraw(User $user, TuitionJobApplication $tuitionJobApplication): bool
    {
        return $this->isTutorOwner($user, $tuitionJobApplication);
    }

    /**
     * Check if the user is the tutor who submitted this application.
     */
    private function isTutorOwner(User $user, TuitionJobApplication $tuitionJobApplication): bool
    {
        return $user->role === UserRole::Tutor
            && (int) $tuitionJobApplication->tutor_user_id === (int) $user->getAuthIdentifier();
    }

    /**
     * Check if the user is the guardian who owns the parent job.
     */
    private function isGuardianJobOwner(User $user, TuitionJobApplication $tuitionJobApplication): bool
    {
        if ($user->role !== UserRole::Guardian) {
            return false;
        }

        $tuitionJobApplication->loadMissing('tuitionJob');

        return $tuitionJobApplication->tuitionJob !== null
            && (int) $tuitionJobApplication->tuitionJob->guardian_id === (int) $user->getAuthIdentifier();
    }
}

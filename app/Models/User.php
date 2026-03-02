<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate as ImpersonateModel;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, ImpersonateModel, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'phone_verified_at',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the user can impersonate another account.
     */
    public function canImpersonate(): bool
    {
        return $this->role === 'admin' && $this->status === 'active';
    }

    /**
     * Determine whether this user account can be impersonated.
     */
    public function canBeImpersonated(): bool
    {
        return $this->status === 'active'
            && in_array($this->role, ['admin', 'tutor', 'guardian'], true);
    }

    /**
     * Get jobs posted by this guardian.
     */
    public function guardianJobs(): HasMany
    {
        return $this->hasMany(TuitionJob::class, 'guardian_id');
    }

    /**
     * Get jobs created by this admin.
     */
    public function createdTuitionJobs(): HasMany
    {
        return $this->hasMany(TuitionJob::class, 'created_by');
    }

    /**
     * Get jobs updated by this admin.
     */
    public function updatedTuitionJobs(): HasMany
    {
        return $this->hasMany(TuitionJob::class, 'updated_by');
    }

    /**
     * Get jobs confirmed by this admin.
     */
    public function confirmedTuitionJobs(): HasMany
    {
        return $this->hasMany(TuitionJob::class, 'confirmed_by');
    }

    /**
     * Get tutor applications submitted by this tutor.
     */
    public function tutorJobApplications(): HasMany
    {
        return $this->hasMany(TuitionJobApplication::class, 'tutor_id');
    }

    /**
     * Get tutor applications reviewed by this guardian/admin.
     */
    public function reviewedJobApplications(): HasMany
    {
        return $this->hasMany(TuitionJobApplication::class, 'reviewed_by');
    }
}

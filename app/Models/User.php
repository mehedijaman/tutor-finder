<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate as ImpersonateModel;
use Laravel\Fortify\TwoFactorAuthenticatable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasPushSubscriptions, HasRoles, ImpersonateModel, InteractsWithMedia, LogsActivity, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

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
        'verified_at',
        'verification_status',
        'verification_type',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'photo_url',
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
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['password', 'remember_token'])
            ->dontSubmitEmptyLogs()
            ->useLogName('users');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'status' => UserStatus::class,
            'verification_status' => VerificationStatus::class,
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Determine whether the user can impersonate another account.
     */
    public function canImpersonate(): bool
    {
        return $this->role === UserRole::Admin && $this->status === UserStatus::Active;
    }

    /**
     * Determine whether this user account can be impersonated.
     */
    public function canBeImpersonated(): bool
    {
        return $this->status === UserStatus::Active
            && in_array($this->role, UserRole::cases(), true);
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
        return $this->hasMany(TuitionJobApplication::class, 'tutor_user_id');
    }

    /**
     * Get tutor assignments where this user is selected as tutor.
     */
    public function tutorJobAssignments(): HasMany
    {
        return $this->hasMany(TuitionJobAssignment::class, 'tutor_user_id');
    }

    /**
     * Get reviews received by this tutor.
     */
    public function tutorReviews(): HasMany
    {
        return $this->hasMany(TutorReview::class, 'tutor_user_id');
    }

    /**
     * Get reviews written by this guardian.
     */
    public function givenReviews(): HasMany
    {
        return $this->hasMany(TutorReview::class, 'guardian_user_id');
    }

    /**
     * Get tutor profile for this user.
     */
    public function tutorProfile(): HasOne
    {
        return $this->hasOne(TutorProfile::class);
    }

    /**
     * Get tutor educations for this user.
     */
    public function tutorEducations(): HasMany
    {
        return $this->hasMany(TutorEducation::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get guardian profile for this user.
     */
    public function guardianProfile(): HasOne
    {
        return $this->hasOne(GuardianProfile::class);
    }

    /**
     * Get verification requests of this user.
     */
    public function verificationRequests(): HasMany
    {
        return $this->hasMany(VerificationRequest::class);
    }

    /**
     * Get the latest verification request of this user.
     */
    public function latestVerificationRequest(): HasOne
    {
        return $this->hasOne(VerificationRequest::class)->latestOfMany();
    }

    /**
     * Get invoices of this user.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get invoices where user is payer.
     */
    public function payerInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'payer_user_id');
    }

    /**
     * Get invoices where user is payee.
     */
    public function payeeInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'payee_user_id');
    }

    /**
     * Get ledger entries where user is owner.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(WalletLedgerEntry::class, 'owner_user_id');
    }

    /**
     * Calculate wallet balance for a given currency.
     */
    public function getWalletBalance(string $currency = 'BDT'): float
    {
        $entries = $this->ledgerEntries()
            ->where('currency', $currency)
            ->selectRaw('type, SUM(amount) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $credits = (float) ($entries[\App\Enums\LedgerEntryType::Credit->value] ?? 0);
        $debits = (float) ($entries[\App\Enums\LedgerEntryType::Debit->value] ?? 0);

        return $credits - $debits;
    }

    /**
     * Get formatted wallet balance with currency symbol.
     */
    public function getFormattedWalletBalance(string $currency = 'BDT'): string
    {
        $balance = $this->getWalletBalance($currency);
        $symbol = match ($currency) {
            'BDT' => '৳',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $currency.' ',
        };

        return $symbol.number_format($balance, 2);
    }

    /**
     * Get refund requests submitted by this user.
     */
    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class, 'requested_by_user_id');
    }

    /**
     * Get support tickets created by this user.
     */
    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Get support tickets assigned to this admin.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class, 'assigned_to');
    }

    /**
     * Register media collections.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_photo')
            ->singleFile();
    }

    /**
     * Get the profile photo URL.
     */
    public function getPhotoUrlAttribute(): string
    {
        $mediaUrl = $this->getFirstMediaUrl('profile_photo');

        if ($mediaUrl !== '') {
            return $mediaUrl;
        }

        $emailHash = md5(strtolower(trim($this->email ?? '')) ?: 'default');

        return 'https://www.gravatar.com/avatar/'.$emailHash.'?s=200&d=mp';
    }
}

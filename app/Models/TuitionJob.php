<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TuitionJob extends Model
{
    /** @use HasFactory<\Database\Factories\TuitionJobFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_LIVE = 'live';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_CLOSED = 'closed';

    public const GENDER_MALE = 'male';

    public const GENDER_FEMALE = 'female';

    public const GENDER_ANY = 'any';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'tuition_type_id',
        'category_id',
        'class_id',
        'country_id',
        'city_id',
        'area_id',
        'guardian_id',
        'selected_tutor_id',
        'selected_application_id',
        'location',
        'latitude',
        'longitude',
        'student_gender',
        'tutor_gender',
        'tuition_days',
        'days_per_week',
        'tuition_time',
        'tuition_duration',
        'no_of_students',
        'salary_amount',
        'salary_currency',
        'salary_negotiable',
        'status',
        'cancellation_reason',
        'published_at',
        'expires_at',
        'created_by',
        'updated_by',
        'confirmed_by',
        'confirmed_at',
        'view_count',
    ];

    /**
     * Get casts for model attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tuition_days' => 'array',
            'salary_amount' => 'decimal:2',
            'salary_negotiable' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'days_per_week' => 'integer',
            'no_of_students' => 'integer',
            'view_count' => 'integer',
        ];
    }

    /**
     * Get guardian who created the job.
     */
    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    /**
     * Get selected tutor for confirmed engagement.
     */
    public function selectedTutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_tutor_id');
    }

    /**
     * Get selected application for confirmed engagement.
     */
    public function selectedApplication(): BelongsTo
    {
        return $this->belongsTo(TuitionJobApplication::class, 'selected_application_id');
    }

    /**
     * Get tuition type of this job.
     */
    public function tuitionType(): BelongsTo
    {
        return $this->belongsTo(TuitionType::class, 'tuition_type_id');
    }

    /**
     * Get category of this job.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get class of this job.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get country of this job.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get city of this job.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get area of this job.
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Get subjects associated with this job.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'tuition_job_subjects', 'job_id', 'subject_id')
            ->withTimestamps();
    }

    /**
     * Get tutor applications submitted for this job.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(TuitionJobApplication::class, 'tuition_job_id');
    }

    /**
     * Get admin user who created this job entry.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get admin user who last updated this job entry.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get admin user who confirmed this job.
     */
    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Scope only live jobs.
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_LIVE);
    }

    /**
     * Scope only pending jobs.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope only confirmed jobs.
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope active live jobs which are not expired.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_LIVE)
            ->where(function (Builder $builder): void {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope expired jobs.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
    }

    /**
     * Determine if job is live.
     */
    public function isLive(): bool
    {
        return $this->status === self::STATUS_LIVE;
    }

    /**
     * Determine if job is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Approve pending job and mark as live.
     */
    public function markLive(User $admin): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new DomainException('Only pending jobs can be approved.');
        }

        $this->forceFill([
            'status' => self::STATUS_LIVE,
            'published_at' => $this->published_at ?? now(),
            'updated_by' => $admin->getKey(),
            'cancellation_reason' => null,
            'selected_tutor_id' => null,
            'selected_application_id' => null,
        ])->save();
    }

    /**
     * Mark live job as confirmed after tutor engagement.
     */
    public function markConfirmed(User $admin): void
    {
        if ($this->status !== self::STATUS_LIVE) {
            throw new DomainException('Only live jobs can be confirmed.');
        }

        $this->forceFill([
            'status' => self::STATUS_CONFIRMED,
            'confirmed_by' => $admin->getKey(),
            'confirmed_at' => now(),
            'updated_by' => $admin->getKey(),
        ])->save();
    }

    /**
     * Alias method for future engagement confirmation flow.
     */
    public function confirmEngagement(User $admin, ?TuitionJobApplication $application = null): void
    {
        $this->markConfirmed($admin);

        if ($application !== null) {
            $this->forceFill([
                'selected_tutor_id' => $application->tutor_id,
                'selected_application_id' => $application->getKey(),
            ])->save();
        }
    }

    /**
     * Mark pending or live job as cancelled.
     */
    public function markCancelled(?string $reason, ?User $admin = null): void
    {
        if (! in_array($this->status, [self::STATUS_PENDING, self::STATUS_LIVE], true)) {
            throw new DomainException('Only pending or live jobs can be cancelled.');
        }

        $this->forceFill([
            'status' => self::STATUS_CANCELLED,
            'cancellation_reason' => $reason,
            'updated_by' => $admin?->getKey(),
        ])->save();
    }

    /**
     * Mark live or confirmed job as closed.
     */
    public function markClosed(?User $admin = null): void
    {
        if (! in_array($this->status, [self::STATUS_LIVE, self::STATUS_CONFIRMED], true)) {
            throw new DomainException('Only live or confirmed jobs can be closed.');
        }

        $this->forceFill([
            'status' => self::STATUS_CLOSED,
            'updated_by' => $admin?->getKey(),
        ])->save();
    }

    /**
     * Get all available statuses.
     *
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_LIVE,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
            self::STATUS_CLOSED,
        ];
    }

    /**
     * Get supported genders.
     *
     * @return list<string>
     */
    public static function genders(): array
    {
        return [
            self::GENDER_MALE,
            self::GENDER_FEMALE,
            self::GENDER_ANY,
        ];
    }
}

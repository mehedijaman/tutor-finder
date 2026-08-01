<?php

namespace App\Models;

use App\Enums\JobGender;
use App\Enums\JobStatus;
use Database\Factories\TuitionJobFactory;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TuitionJob extends Model
{
    /** @use HasFactory<TuitionJobFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

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
        'requested_tutor_id',
        'requested_at',
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
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('tuition-jobs');
    }

    /**
     * Get casts for model attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => JobStatus::class,
            'student_gender' => JobGender::class,
            'tutor_gender' => JobGender::class,
            'tuition_days' => 'array',
            'salary_amount' => 'decimal:2',
            'salary_negotiable' => 'boolean',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'requested_at' => 'datetime',
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
     * Get the specific tutor requested for this job.
     */
    public function requestedTutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_tutor_id');
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
        return $this->hasMany(TuitionJobApplication::class, 'job_id');
    }

    /**
     * Get selected assignment snapshot for this job.
     */
    public function assignment(): HasOne
    {
        return $this->hasOne(TuitionJobAssignment::class, 'job_id');
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
        return $query->where('status', JobStatus::Live);
    }

    /**
     * Scope only pending jobs.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', JobStatus::Pending);
    }

    /**
     * Scope only confirmed jobs.
     */
    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', JobStatus::Confirmed);
    }

    /**
     * Scope active live jobs which are not expired.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', JobStatus::Live)
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
        return $this->status === JobStatus::Live;
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
        if ($this->status !== JobStatus::Pending) {
            throw new DomainException('Only pending jobs can be approved.');
        }

        $this->forceFill([
            'status' => JobStatus::Live,
            'published_at' => $this->published_at ?? now(),
            'updated_by' => $admin->getKey(),
            'cancellation_reason' => null,
            'confirmed_by' => null,
            'confirmed_at' => null,
        ])->save();
    }

    /**
     * Mark live job as confirmed after tutor engagement.
     */
    public function markConfirmed(User $admin): void
    {
        $this->markConfirmedAt($admin, now());
    }

    /**
     * Mark live job as confirmed using a specific timestamp.
     */
    public function markConfirmedAt(User $admin, \DateTimeInterface $confirmedAt): void
    {
        if ($this->status !== JobStatus::Live) {
            throw new DomainException('Only live jobs can be confirmed.');
        }

        $this->forceFill([
            'status' => JobStatus::Confirmed,
            'confirmed_by' => $admin->getKey(),
            'confirmed_at' => $confirmedAt,
            'updated_by' => $admin->getKey(),
        ])->save();
    }

    /**
     * Mark pending or live job as cancelled.
     */
    public function markCancelled(?string $reason, ?User $admin = null): void
    {
        if (! in_array($this->status, [JobStatus::Pending, JobStatus::Live], true)) {
            throw new DomainException('Only pending or live jobs can be cancelled.');
        }

        $this->forceFill([
            'status' => JobStatus::Cancelled,
            'cancellation_reason' => $reason,
            'updated_by' => $admin?->getKey(),
        ])->save();
    }

    /**
     * Mark live or confirmed job as closed.
     */
    public function markClosed(?User $admin = null): void
    {
        if (! in_array($this->status, [JobStatus::Live, JobStatus::Confirmed], true)) {
            throw new DomainException('Only live or confirmed jobs can be closed.');
        }

        $this->forceFill([
            'status' => JobStatus::Closed,
            'updated_by' => $admin?->getKey(),
        ])->save();
    }

    /**
     * Get all available statuses.
     *
     * @return list<JobStatus>
     */
    public static function statuses(): array
    {
        return JobStatus::cases();
    }

    /**
     * Get supported genders.
     *
     * @return list<JobGender>
     */
    public static function genders(): array
    {
        return JobGender::cases();
    }

    /**
     * Retrieve the model for a bound value (supports both ID and slug).
     */
    public function resolveRouteBinding($value, $field = null): ?self
    {
        if (is_numeric($value)) {
            return $this->where('id', (int) $value)->first();
        }

        return $this->where('slug', $value)->first();
    }
}

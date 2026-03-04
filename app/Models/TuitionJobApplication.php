<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuitionJobApplication extends Model
{
    /** @use HasFactory<\Database\Factories\TuitionJobApplicationFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_id',
        'tutor_user_id',
        'cover_letter',
        'expected_salary_amount',
        'salary_currency',
        'status',
        'cancel_reason',
        'metadata',
    ];

    /**
     * Get casts for model attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'expected_salary_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    /**
     * Get related tuition job.
     */
    public function tuitionJob(): BelongsTo
    {
        return $this->belongsTo(TuitionJob::class, 'job_id');
    }

    /**
     * Get tutor who submitted this application.
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_user_id');
    }

    /**
     * Mark application as shortlisted by guardian.
     */
    public function markShortlisted(): void
    {
        if ($this->status !== ApplicationStatus::Applied) {
            throw new DomainException('Only applied applications can be shortlisted.');
        }

        $this->forceFill([
            'status' => ApplicationStatus::Shortlisted,
            'cancel_reason' => null,
        ])->save();
    }

    /**
     * Mark application as cancelled by guardian/admin.
     */
    public function markCancelled(?string $reason = null): void
    {
        if (! in_array($this->status, ApplicationStatus::cancellableStatuses(), true)) {
            throw new DomainException('Only active applications can be cancelled.');
        }

        $this->forceFill([
            'status' => ApplicationStatus::Cancelled,
            'cancel_reason' => $reason,
        ])->save();
    }

    /**
     * Mark application as confirmed for a selected hire.
     */
    public function markConfirmed(): void
    {
        if ($this->status !== ApplicationStatus::Shortlisted) {
            throw new DomainException('Only shortlisted applications can be confirmed.');
        }

        $this->forceFill([
            'status' => ApplicationStatus::Confirmed,
            'cancel_reason' => null,
        ])->save();
    }

    /**
     * Mark application back to applied state when tutor re-applies.
     */
    public function markApplied(?string $coverLetter, int|float|string|null $expectedSalaryAmount): void
    {
        if ($this->status !== ApplicationStatus::Cancelled) {
            throw new DomainException('Only cancelled applications can be re-applied.');
        }

        $this->forceFill([
            'status' => ApplicationStatus::Applied,
            'cover_letter' => $coverLetter,
            'expected_salary_amount' => $expectedSalaryAmount,
            'salary_currency' => $this->salary_currency ?: 'BDT',
            'cancel_reason' => null,
        ])->save();
    }

    /**
     * Get all available application statuses.
     *
     * @return list<ApplicationStatus>
     */
    public static function statuses(): array
    {
        return ApplicationStatus::cases();
    }
}

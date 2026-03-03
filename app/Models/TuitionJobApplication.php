<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TuitionJobApplication extends Model
{
    /** @use HasFactory<\Database\Factories\TuitionJobApplicationFactory> */
    use HasFactory;

    public const STATUS_APPLIED = 'applied';

    public const STATUS_SHORTLISTED = 'shortlisted';

    public const STATUS_APPOINTED = 'appointed';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

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
        if ($this->status !== self::STATUS_APPLIED) {
            throw new DomainException('Only applied applications can be shortlisted.');
        }

        $this->forceFill([
            'status' => self::STATUS_SHORTLISTED,
            'cancel_reason' => null,
        ])->save();
    }

    /**
     * Mark application as cancelled by guardian/admin.
     */
    public function markCancelled(?string $reason = null): void
    {
        if (! in_array($this->status, [
            self::STATUS_APPLIED,
            self::STATUS_SHORTLISTED,
            self::STATUS_APPOINTED,
            self::STATUS_CONFIRMED,
        ], true)) {
            throw new DomainException('Only active applications can be cancelled.');
        }

        $this->forceFill([
            'status' => self::STATUS_CANCELLED,
            'cancel_reason' => $reason,
        ])->save();
    }

    /**
     * Mark application as confirmed for a selected hire.
     */
    public function markConfirmed(): void
    {
        if ($this->status !== self::STATUS_SHORTLISTED) {
            throw new DomainException('Only shortlisted applications can be confirmed.');
        }

        $this->forceFill([
            'status' => self::STATUS_CONFIRMED,
            'cancel_reason' => null,
        ])->save();
    }

    /**
     * Mark application back to applied state when tutor re-applies.
     */
    public function markApplied(?string $coverLetter, int|float|string|null $expectedSalaryAmount): void
    {
        if ($this->status !== self::STATUS_CANCELLED) {
            throw new DomainException('Only cancelled applications can be re-applied.');
        }

        $this->forceFill([
            'status' => self::STATUS_APPLIED,
            'cover_letter' => $coverLetter,
            'expected_salary_amount' => $expectedSalaryAmount,
            'salary_currency' => $this->salary_currency ?: 'BDT',
            'cancel_reason' => null,
        ])->save();
    }

    /**
     * Get all available application statuses.
     *
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_APPLIED,
            self::STATUS_SHORTLISTED,
            self::STATUS_APPOINTED,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
        ];
    }
}

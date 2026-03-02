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

    public const STATUS_PENDING = 'pending';

    public const STATUS_SHORTLISTED = 'shortlisted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WITHDRAWN = 'withdrawn';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'tuition_job_id',
        'tutor_id',
        'cover_letter',
        'expected_salary',
        'status',
        'guardian_note',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Get casts for model attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected_salary' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Get related tuition job.
     */
    public function tuitionJob(): BelongsTo
    {
        return $this->belongsTo(TuitionJob::class, 'tuition_job_id');
    }

    /**
     * Get tutor who submitted this application.
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    /**
     * Get guardian who reviewed this application.
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Mark application as shortlisted by guardian.
     */
    public function markShortlisted(User $guardian, ?string $guardianNote = null): void
    {
        if ($this->status === self::STATUS_WITHDRAWN) {
            throw new DomainException('Withdrawn application cannot be shortlisted.');
        }

        $this->forceFill([
            'status' => self::STATUS_SHORTLISTED,
            'guardian_note' => $guardianNote,
            'reviewed_by' => $guardian->getKey(),
            'reviewed_at' => now(),
        ])->save();
    }

    /**
     * Mark application as rejected by guardian.
     */
    public function markRejected(User $guardian, ?string $guardianNote = null): void
    {
        if ($this->status === self::STATUS_WITHDRAWN) {
            throw new DomainException('Withdrawn application cannot be rejected.');
        }

        $this->forceFill([
            'status' => self::STATUS_REJECTED,
            'guardian_note' => $guardianNote,
            'reviewed_by' => $guardian->getKey(),
            'reviewed_at' => now(),
        ])->save();
    }

    /**
     * Mark application as withdrawn by tutor.
     */
    public function markWithdrawn(): void
    {
        if (in_array($this->status, [self::STATUS_REJECTED, self::STATUS_WITHDRAWN], true)) {
            throw new DomainException('Rejected or withdrawn application cannot be withdrawn again.');
        }

        $this->forceFill([
            'status' => self::STATUS_WITHDRAWN,
            'guardian_note' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
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
            self::STATUS_PENDING,
            self::STATUS_SHORTLISTED,
            self::STATUS_REJECTED,
            self::STATUS_WITHDRAWN,
        ];
    }
}

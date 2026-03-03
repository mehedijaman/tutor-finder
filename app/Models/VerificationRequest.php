<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationRequest extends Model
{
    /** @use HasFactory<\Database\Factories\VerificationRequestFactory> */
    use HasFactory, SoftDeletes;

    public const ROLE_TUTOR = 'tutor';

    public const ROLE_GUARDIAN = 'guardian';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_INVOICED = 'invoiced';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'role',
        'status',
        'fee_amount',
        'currency',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'decision_reason',
        'metadata',
    ];

    /**
     * Get casts for attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fee_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get request owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get reviewer.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get invoice of this request.
     */
    public function invoice(): MorphOne
    {
        return $this->morphOne(Invoice::class, 'invoiceable');
    }

    /**
     * Mark request approved.
     */
    public function markApproved(User $admin): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            throw new DomainException('Only pending requests can be approved.');
        }

        $this->forceFill([
            'status' => self::STATUS_APPROVED,
            'reviewed_by' => $admin->getKey(),
            'reviewed_at' => now(),
            'decision_reason' => null,
        ])->save();
    }

    /**
     * Mark request rejected or cancelled.
     */
    public function markDecision(string $status, string $reason, User $admin): void
    {
        if (! in_array($status, [self::STATUS_REJECTED, self::STATUS_CANCELLED], true)) {
            throw new DomainException('Invalid verification decision status.');
        }

        if (in_array($this->status, [self::STATUS_VERIFIED, self::STATUS_CANCELLED], true)) {
            throw new DomainException('This request can no longer be updated.');
        }

        $this->forceFill([
            'status' => $status,
            'reviewed_by' => $admin->getKey(),
            'reviewed_at' => now(),
            'decision_reason' => $reason,
        ])->save();
    }

    /**
     * Mark request as invoiced.
     */
    public function markInvoiced(User $admin): void
    {
        if (! in_array($this->status, [self::STATUS_PENDING, self::STATUS_APPROVED], true)) {
            throw new DomainException('Invoice can be generated only for pending or approved requests.');
        }

        $this->forceFill([
            'status' => self::STATUS_INVOICED,
            'reviewed_by' => $admin->getKey(),
            'reviewed_at' => now(),
        ])->save();
    }

    /**
     * Mark request as verified.
     */
    public function markVerified(?User $reviewer = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_VERIFIED,
            'reviewed_by' => $reviewer?->getKey() ?? $this->reviewed_by,
            'reviewed_at' => now(),
        ])->save();
    }

    /**
     * Get active statuses for duplicate guard.
     *
     * @return list<string>
     */
    public static function activeStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_INVOICED,
        ];
    }
}

<?php

namespace App\Models;

use App\Enums\VerificationRole;
use App\Enums\VerificationStatus;
use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VerificationRequest extends Model
{
    /** @use HasFactory<\Database\Factories\VerificationRequestFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

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
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['metadata'])
            ->dontSubmitEmptyLogs()
            ->useLogName('verification');
    }

    /**
     * Get casts for attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => VerificationRole::class,
            'status' => VerificationStatus::class,
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
        if ($this->status !== VerificationStatus::Pending) {
            throw new DomainException('Only pending requests can be approved.');
        }

        $this->forceFill([
            'status' => VerificationStatus::Approved,
            'reviewed_by' => $admin->getKey(),
            'reviewed_at' => now(),
            'decision_reason' => null,
        ])->save();
    }

    /**
     * Mark request rejected or cancelled.
     */
    public function markDecision(VerificationStatus $status, string $reason, User $admin): void
    {
        if (! in_array($status, [VerificationStatus::Rejected, VerificationStatus::Cancelled], true)) {
            throw new DomainException('Invalid verification decision status.');
        }

        if (in_array($this->status, [VerificationStatus::Verified, VerificationStatus::Cancelled], true)) {
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
        if (! in_array($this->status, [VerificationStatus::Pending, VerificationStatus::Approved], true)) {
            throw new DomainException('Invoice can be generated only for pending or approved requests.');
        }

        $this->forceFill([
            'status' => VerificationStatus::Invoiced,
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
            'status' => VerificationStatus::Verified,
            'reviewed_by' => $reviewer?->getKey() ?? $this->reviewed_by,
            'reviewed_at' => now(),
        ])->save();
    }

    /**
     * Get active statuses for duplicate guard.
     *
     * @return list<VerificationStatus>
     */
    public static function activeStatuses(): array
    {
        return VerificationStatus::activeStatuses();
    }
}

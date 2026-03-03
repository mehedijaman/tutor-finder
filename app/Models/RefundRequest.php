<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    /** @use HasFactory<\Database\Factories\RefundRequestFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PAID = 'paid';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_assignment_id',
        'requested_by_user_id',
        'reason_text',
        'requested_at',
        'status',
        'amount',
        'currency',
        'decision_by_admin_id',
        'decision_note',
        'decided_at',
        'paid_at',
        'payment_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'amount' => 'decimal:2',
            'decided_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get assignment associated with this refund request.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TuitionJobAssignment::class, 'job_assignment_id');
    }

    /**
     * Get request owner user.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    /**
     * Get admin decision maker.
     */
    public function decisionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decision_by_admin_id');
    }

    /**
     * Get payout payment record.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_PAID,
        ];
    }
}

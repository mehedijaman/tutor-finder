<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    /** @use HasFactory<\Database\Factories\RefundRequestFactory> */
    use HasFactory;

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
            'status' => RefundStatus::class,
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
     * @return list<RefundStatus>
     */
    public static function statuses(): array
    {
        return RefundStatus::cases();
    }
}

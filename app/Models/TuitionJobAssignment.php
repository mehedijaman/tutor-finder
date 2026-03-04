<?php

namespace App\Models;

use App\Enums\CancellationActor;
use App\Enums\CancellationFault;
use App\Enums\DurationType;
use App\Enums\FeePaymentMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TuitionJobAssignment extends Model
{
    /**
     * Phase 1 note:
     * during initial assignment-first cutover, appointed_at and confirmed_at are written
     * to the same timestamp. A later split flow may diverge those lifecycle moments.
     */
    /** @use HasFactory<\Database\Factories\TuitionJobAssignmentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_id',
        'tutor_user_id',
        'appointed_at',
        'confirmed_at',
        'cancelled_at',
        'cancelled_by',
        'fault',
        'cancel_reason',
        'reported_within_24h',
        'duration_type',
        'short_term_months',
        'salary_base_amount',
        'salary_base_source',
        'service_fee_rate',
        'service_fee_amount',
        'fee_currency',
        'fee_due_at',
        'fee_payment_mode',
        'month1_escrow_required',
        'month1_escrow_paid_at',
        'first_month_received_at',
        'month1_ended_at',
        'month1_settled_at',
        'notes',
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
            'duration_type' => DurationType::class,
            'cancelled_by' => CancellationActor::class,
            'fault' => CancellationFault::class,
            'fee_payment_mode' => FeePaymentMode::class,
            'appointed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'reported_within_24h' => 'boolean',
            'short_term_months' => 'integer',
            'salary_base_amount' => 'decimal:2',
            'service_fee_rate' => 'decimal:5',
            'service_fee_amount' => 'decimal:2',
            'fee_due_at' => 'datetime',
            'month1_escrow_required' => 'boolean',
            'month1_escrow_paid_at' => 'datetime',
            'first_month_received_at' => 'datetime',
            'month1_ended_at' => 'datetime',
            'month1_settled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the assigned tuition job.
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(TuitionJob::class, 'job_id');
    }

    /**
     * Get the assigned tutor user.
     */
    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_user_id');
    }

    /**
     * Get invoices attached to this assignment.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'job_assignment_id');
    }

    /**
     * Get refund requests raised for this assignment.
     */
    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class, 'job_assignment_id');
    }
}

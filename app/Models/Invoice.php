<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (self $invoice): void {
            $invoice->syncLegacyUserReference();
        });
    }

    public const STATUS_DRAFT = 'draft';

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PAID = 'paid';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_VOID = 'void';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const GATEWAY_BKASH = 'bkash';

    public const GATEWAY_SSLCOMMERZ = 'sslcommerz';

    public const GATEWAY_MANUAL = 'manual';

    public const TYPE_TUTOR_VERIFICATION_FEE = 'tutor_verification_fee';

    public const TYPE_GUARDIAN_VERIFICATION_FEE = 'guardian_verification_fee';

    public const TYPE_PLATFORM_SERVICE_FEE = 'platform_service_fee';

    public const TYPE_ONLINE_MONTH1_ESCROW = 'online_month1_escrow';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'invoice_no',
        'invoiceable_type',
        'invoiceable_id',
        'user_id',
        'payer_user_id',
        'payee_user_id',
        'type',
        'job_assignment_id',
        'amount',
        'currency',
        'status',
        'due_at',
        'expires_at',
        'issued_by',
        'issued_at',
        'paid_at',
        'payment_gateway',
        'payment_method',
        'payment_reference',
        'transaction_id',
        'gateway_payload',
        'notes',
    ];

    /**
     * Get casts for attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_at' => 'datetime',
            'expires_at' => 'datetime',
            'issued_at' => 'datetime',
            'paid_at' => 'datetime',
            'gateway_payload' => 'array',
        ];
    }

    /**
     * Get invoice payer.
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }

    /**
     * Get invoice payee.
     */
    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_user_id');
    }

    /**
     * Get owner user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get issuing admin.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get invoiceable relation.
     */
    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get assignment associated with this invoice.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TuitionJobAssignment::class, 'job_assignment_id');
    }

    /**
     * Get payment attempts for this invoice.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest payment attempt.
     */
    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Determine if invoice can accept payment attempts.
     */
    public function isPayable(): bool
    {
        if ($this->status !== self::STATUS_UNPAID) {
            return false;
        }

        if ($this->expires_at === null) {
            return true;
        }

        return $this->expires_at->isFuture();
    }

    /**
     * Determine if this invoice belongs to verification flow.
     */
    public function isVerificationInvoice(): bool
    {
        return in_array($this->type, [
            self::TYPE_TUTOR_VERIFICATION_FEE,
            self::TYPE_GUARDIAN_VERIFICATION_FEE,
        ], true);
    }

    /**
     * Mirror legacy compatibility user_id to payer_user_id.
     */
    public function syncLegacyUserReference(): void
    {
        if ($this->payer_user_id !== null && $this->user_id !== $this->payer_user_id) {
            $this->user_id = $this->payer_user_id;
        }
    }

    /**
     * Determine if invoice has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Return statuses that represent terminal failure/cancel paths.
     *
     * @return list<string>
     */
    public static function recoverableStatuses(): array
    {
        return [
            self::STATUS_VOID,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_UNPAID,
            self::STATUS_PAID,
            self::STATUS_REFUNDED,
            self::STATUS_VOID,
        ];
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return [
            self::TYPE_TUTOR_VERIFICATION_FEE,
            self::TYPE_GUARDIAN_VERIFICATION_FEE,
            self::TYPE_PLATFORM_SERVICE_FEE,
            self::TYPE_ONLINE_MONTH1_ESCROW,
        ];
    }
}

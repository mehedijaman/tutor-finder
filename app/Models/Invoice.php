<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, SoftDeletes;

    public const STATUS_UNPAID = 'unpaid';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_VOID = 'void';

    public const GATEWAY_BKASH = 'bkash';

    public const GATEWAY_SSLCOMMERZ = 'sslcommerz';

    public const GATEWAY_MANUAL = 'manual';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'invoice_no',
        'invoiceable_type',
        'invoiceable_id',
        'user_id',
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
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
            self::STATUS_VOID,
        ];
    }
}

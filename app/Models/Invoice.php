<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentGatewayType;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (self $invoice): void {
            $invoice->syncLegacyUserReference();
        });
    }

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
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['gateway_payload'])
            ->dontSubmitEmptyLogs()
            ->useLogName('invoices');
    }

    /**
     * Get casts for attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => InvoiceType::class,
            'status' => InvoiceStatus::class,
            'payment_gateway' => PaymentGatewayType::class,
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
        if ($this->status !== InvoiceStatus::Unpaid) {
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
        return $this->type->isVerification();
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
     * Get formatted amount with currency symbol.
     */
    public function getFormattedAmountAttribute(): string
    {
        $currency = $this->currency ?? 'BDT';
        $symbol = match ($currency) {
            'BDT' => '৳',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $currency.' ',
        };

        return $symbol.number_format((float) $this->amount, 2);
    }

    /**
     * Return statuses that represent terminal failure/cancel paths.
     *
     * @return list<InvoiceStatus>
     */
    public static function recoverableStatuses(): array
    {
        return InvoiceStatus::recoverableStatuses();
    }

    /**
     * @return list<InvoiceStatus>
     */
    public static function statuses(): array
    {
        return InvoiceStatus::cases();
    }

    /**
     * @return list<InvoiceType>
     */
    public static function types(): array
    {
        return InvoiceType::cases();
    }
}

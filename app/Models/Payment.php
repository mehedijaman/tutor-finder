<?php

namespace App\Models;

use App\Enums\PaymentGatewayType;
use App\Enums\PaymentStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'invoice_id',
        'gateway',
        'provider_txn_id',
        'amount',
        'status',
        'provider_payload',
    ];

    /**
     * Configure activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['provider_payload'])
            ->dontSubmitEmptyLogs()
            ->useLogName('payments');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'gateway' => PaymentGatewayType::class,
            'amount' => 'decimal:2',
            'provider_payload' => 'array',
        ];
    }

    /**
     * Get invoice associated with this payment.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * @return list<PaymentStatus>
     */
    public static function statuses(): array
    {
        return PaymentStatus::cases();
    }
}

<?php

namespace App\Models;

use App\Enums\PaymentGatewayType;
use App\Enums\TaxonomyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PaymentGateway extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentGatewayFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'gateway',
        'name',
        'status',
        'credentials',
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
            ->logExcept(['credentials'])
            ->dontSubmitEmptyLogs()
            ->useLogName('settings');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gateway' => PaymentGatewayType::class,
            'status' => TaxonomyStatus::class,
            'credentials' => 'encrypted:array',
        ];
    }

    /**
     * Resolve active gateway row by key.
     */
    public static function active(string $gateway): ?self
    {
        return static::query()
            ->where('gateway', strtolower(trim($gateway)))
            ->where('status', TaxonomyStatus::Active)
            ->first();
    }

    /**
     * Ensure rows exist for supported gateways.
     */
    public static function ensureDefaults(): void
    {
        foreach ([
            PaymentGatewayType::Bkash->value => 'bKash',
            PaymentGatewayType::Sslcommerz->value => 'SSLCommerz',
            PaymentGatewayType::Manual->value => 'Manual',
        ] as $gateway => $name) {
            $paymentGateway = static::query()->withTrashed()->firstOrNew([
                'gateway' => $gateway,
            ]);

            if ($paymentGateway->trashed()) {
                $paymentGateway->restore();
            }

            $paymentGateway->fill([
                'name' => $name,
                'status' => TaxonomyStatus::Active,
                'credentials' => is_array($paymentGateway->credentials) ? $paymentGateway->credentials : [],
            ])->save();
        }
    }
}

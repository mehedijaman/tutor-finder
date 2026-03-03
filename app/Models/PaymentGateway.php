<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentGateway extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentGatewayFactory> */
    use HasFactory, SoftDeletes;

    public const GATEWAY_BKASH = 'bkash';

    public const GATEWAY_SSLCOMMERZ = 'sslcommerz';

    public const GATEWAY_MANUAL = 'manual';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
            ->where('status', self::STATUS_ACTIVE)
            ->first();
    }

    /**
     * Ensure rows exist for supported gateways.
     */
    public static function ensureDefaults(): void
    {
        foreach ([
            self::GATEWAY_BKASH => 'bKash',
            self::GATEWAY_SSLCOMMERZ => 'SSLCommerz',
            self::GATEWAY_MANUAL => 'Manual',
        ] as $gateway => $name) {
            $paymentGateway = static::query()->withTrashed()->firstOrNew([
                'gateway' => $gateway,
            ]);

            if ($paymentGateway->trashed()) {
                $paymentGateway->restore();
            }

            $paymentGateway->fill([
                'name' => $name,
                'status' => self::STATUS_ACTIVE,
                'credentials' => is_array($paymentGateway->credentials) ? $paymentGateway->credentials : [],
            ])->save();
        }
    }
}

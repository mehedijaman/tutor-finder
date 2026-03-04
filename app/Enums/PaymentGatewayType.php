<?php

namespace App\Enums;

enum PaymentGatewayType: string
{
    case Bkash = 'bkash';
    case Sslcommerz = 'sslcommerz';
    case Manual = 'manual';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Bkash => 'bKash',
            self::Sslcommerz => 'SSLCommerz',
            self::Manual => 'Manual',
        };
    }
}

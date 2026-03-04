<?php

namespace App\Enums;

enum FeePaymentMode: string
{
    case PayBefore = 'pay_before';
    case PayAfterFirstMonth = 'pay_after_first_month';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PayBefore => 'Pay Before',
            self::PayAfterFirstMonth => 'Pay After First Month',
        };
    }
}

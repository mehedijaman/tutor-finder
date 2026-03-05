<?php

namespace App\Enums;

enum TicketCategory: string
{
    case General = 'general';
    case Billing = 'billing';
    case Technical = 'technical';
    case Account = 'account';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::General => 'General',
            self::Billing => 'Billing',
            self::Technical => 'Technical',
            self::Account => 'Account',
        };
    }
}

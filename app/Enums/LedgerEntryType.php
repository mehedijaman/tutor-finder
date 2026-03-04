<?php

namespace App\Enums;

enum LedgerEntryType: string
{
    case Debit = 'debit';
    case Credit = 'credit';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Debit => 'Debit',
            self::Credit => 'Credit',
        };
    }
}

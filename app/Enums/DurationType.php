<?php

namespace App\Enums;

enum DurationType: string
{
    case LongTerm = 'long_term';
    case ShortTerm = 'short_term';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::LongTerm => 'Long Term',
            self::ShortTerm => 'Short Term',
        };
    }
}

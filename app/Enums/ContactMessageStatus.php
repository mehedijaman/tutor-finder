<?php

namespace App\Enums;

enum ContactMessageStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
        };
    }
}

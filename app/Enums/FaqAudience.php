<?php

namespace App\Enums;

enum FaqAudience: string
{
    case Tutor = 'tutor';
    case Guardian = 'guardian';
    case Both = 'both';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Tutor => 'Tutor',
            self::Guardian => 'Guardian',
            self::Both => 'Both',
        };
    }
}

<?php

namespace App\Enums;

enum VerificationRole: string
{
    case Tutor = 'tutor';
    case Guardian = 'guardian';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Tutor => 'Tutor',
            self::Guardian => 'Guardian',
        };
    }
}

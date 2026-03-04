<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Tutor = 'tutor';
    case Guardian = 'guardian';
    case Platform = 'platform';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Tutor => 'Tutor',
            self::Guardian => 'Guardian',
            self::Platform => 'Platform',
        };
    }
}

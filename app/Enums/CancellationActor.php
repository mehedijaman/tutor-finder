<?php

namespace App\Enums;

enum CancellationActor: string
{
    case Tutor = 'tutor';
    case Guardian = 'guardian';
    case Admin = 'admin';
    case System = 'system';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Tutor => 'Tutor',
            self::Guardian => 'Guardian',
            self::Admin => 'Admin',
            self::System => 'System',
        };
    }
}

<?php

namespace App\Enums;

enum ProfileStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
        };
    }
}

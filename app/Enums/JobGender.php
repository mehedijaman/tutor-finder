<?php

namespace App\Enums;

enum JobGender: string
{
    case Male = 'male';
    case Female = 'female';
    case Any = 'any';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Male => 'Male',
            self::Female => 'Female',
            self::Any => 'Any',
        };
    }
}

<?php

namespace App\Enums;

enum TutorialAudience: string
{
    case All = 'all';
    case Tutor = 'tutor';
    case Guardian = 'guardian';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Tutor => 'For Tutor',
            self::Guardian => 'For Guardian/Student',
        };
    }
}

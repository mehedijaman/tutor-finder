<?php

namespace App\Enums;

enum CancellationFault: string
{
    case TutorFault = 'tutor_fault';
    case GuardianFault = 'guardian_fault';
    case Mutual = 'mutual';
    case ValidOther = 'valid_other';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::TutorFault => 'Tutor Fault',
            self::GuardianFault => 'Guardian Fault',
            self::Mutual => 'Mutual',
            self::ValidOther => 'Other Valid Reason',
        };
    }
}

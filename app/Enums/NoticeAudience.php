<?php

namespace App\Enums;

enum NoticeAudience: string
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
            self::Tutor => 'Tutors Only',
            self::Guardian => 'Guardians Only',
            self::Both => 'All Users',
        };
    }

    /**
     * Check if this audience includes the given role.
     */
    public function includesRole(UserRole $role): bool
    {
        if ($this === self::Both) {
            return in_array($role, [UserRole::Tutor, UserRole::Guardian], true);
        }

        return match ($this) {
            self::Tutor => $role === UserRole::Tutor,
            self::Guardian => $role === UserRole::Guardian,
            default => false,
        };
    }
}

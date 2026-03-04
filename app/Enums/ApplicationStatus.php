<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Applied = 'applied';
    case Shortlisted = 'shortlisted';
    case Appointed = 'appointed';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Applied => 'Applied',
            self::Shortlisted => 'Shortlisted',
            self::Appointed => 'Appointed',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Get valid transition targets from this status.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Applied => [self::Shortlisted, self::Cancelled],
            self::Shortlisted => [self::Confirmed, self::Cancelled],
            self::Appointed => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::Cancelled],
            self::Cancelled => [self::Applied],
        };
    }

    /**
     * Determine if this status can transition to the given target.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    /**
     * Get statuses that can be cancelled.
     *
     * @return list<self>
     */
    public static function cancellableStatuses(): array
    {
        return [
            self::Applied,
            self::Shortlisted,
            self::Appointed,
            self::Confirmed,
        ];
    }
}

<?php

namespace App\Enums;

enum JobStatus: string
{
    case Pending = 'pending';
    case Live = 'live';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Closed = 'closed';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Live => 'Live',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
            self::Closed => 'Closed',
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
            self::Pending => [self::Live, self::Cancelled],
            self::Live => [self::Confirmed, self::Cancelled, self::Closed],
            self::Confirmed => [self::Closed],
            self::Cancelled, self::Closed => [],
        };
    }

    /**
     * Determine if this status can transition to the given target.
     */
    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }
}

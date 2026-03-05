<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Closed = 'closed';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Closed => 'Closed',
        };
    }

    /**
     * Get badge color class for display.
     */
    public function color(): string
    {
        return match ($this) {
            self::Open => 'default',
            self::InProgress => 'warning',
            self::Closed => 'secondary',
        };
    }
}

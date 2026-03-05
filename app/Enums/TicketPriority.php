<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Low => 'Low',
            self::Medium => 'Medium',
            self::High => 'High',
            self::Urgent => 'Urgent',
        };
    }

    /**
     * Get badge color class for display.
     */
    public function color(): string
    {
        return match ($this) {
            self::Low => 'secondary',
            self::Medium => 'default',
            self::High => 'warning',
            self::Urgent => 'destructive',
        };
    }
}

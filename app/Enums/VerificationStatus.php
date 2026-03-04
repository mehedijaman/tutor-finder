<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case Unverified = 'unverified';
    case Pending = 'pending';
    case Approved = 'approved';
    case Invoiced = 'invoiced';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Unverified => 'Unverified',
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Invoiced => 'Invoiced',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Get statuses that represent an active verification lifecycle.
     *
     * @return list<self>
     */
    public static function activeStatuses(): array
    {
        return [
            self::Pending,
            self::Approved,
            self::Invoiced,
        ];
    }
}

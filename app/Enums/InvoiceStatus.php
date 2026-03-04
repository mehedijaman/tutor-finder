<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Unpaid = 'unpaid';
    case Paid = 'paid';
    case Refunded = 'refunded';
    case Void = 'void';
    case Processing = 'processing';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Unpaid => 'Unpaid',
            self::Paid => 'Paid',
            self::Refunded => 'Refunded',
            self::Void => 'Void',
            self::Processing => 'Processing',
            self::Failed => 'Failed',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * Statuses that represent terminal failure/cancel paths.
     *
     * @return list<self>
     */
    public static function recoverableStatuses(): array
    {
        return [
            self::Void,
            self::Failed,
            self::Cancelled,
        ];
    }
}

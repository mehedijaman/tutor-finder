<?php

namespace App\Enums;

enum InvoiceType: string
{
    case TutorVerificationFee = 'tutor_verification_fee';
    case GuardianVerificationFee = 'guardian_verification_fee';
    case PlatformServiceFee = 'platform_service_fee';
    case OnlineMonth1Escrow = 'online_month1_escrow';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::TutorVerificationFee => 'Tutor Verification Fee',
            self::GuardianVerificationFee => 'Guardian Verification Fee',
            self::PlatformServiceFee => 'Platform Service Fee',
            self::OnlineMonth1Escrow => 'Online Month 1 Escrow',
        };
    }

    /**
     * Determine if this is a verification-related invoice type.
     */
    public function isVerification(): bool
    {
        return in_array($this, [self::TutorVerificationFee, self::GuardianVerificationFee], true);
    }
}

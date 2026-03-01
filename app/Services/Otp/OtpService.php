<?php

namespace App\Services\Otp;

use App\Jobs\SendOtpSmsJob;
use App\Models\OtpRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpService
{
    /**
     * Issue and dispatch an OTP for pending registration verification.
     */
    public function issueForRegistration(User $user, Request $request, bool $respectCooldown = true): bool
    {
        if (! $user->phone) {
            return false;
        }

        $purpose = 'register';

        if ($respectCooldown && $this->isCooldownActive($user->phone, $request->ip(), $purpose)) {
            return false;
        }

        if ($respectCooldown) {
            $this->hitCooldown($user->phone, $request->ip(), $purpose);
        }

        $code = $this->generateCode();
        $sentCount = $this->nextSentCount($user->phone, $purpose);

        $expiresAt = now()->addMinutes((int) config('otp.expires_in_minutes', 5));

        OtpRequest::query()->create([
            'phone' => $user->phone,
            'purpose' => $purpose,
            'otp_hash' => Hash::make($code),
            'expires_at' => $expiresAt,
            'attempts' => 0,
            'sent_count' => $sentCount,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($this->shouldExposeLocalOtp() && $request->hasSession()) {
            $request->session()->put('otp.local_debug', [
                'phone' => $user->phone,
                'purpose' => $purpose,
                'code' => $code,
                'expires_at' => $expiresAt->toIso8601String(),
            ]);
        }

        SendOtpSmsJob::dispatch($user->phone, "Your verification code is {$code}.")
            ->onQueue(config('otp.queue', 'default'));

        return true;
    }

    /**
     * Verify an OTP code for a phone / purpose pair.
     */
    public function verifyPhoneCode(string $phone, string $purpose, string $otp): bool
    {
        $otpRequest = OtpRequest::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otpRequest) {
            return false;
        }

        if ($otpRequest->attempts >= (int) config('otp.max_attempts', 5)) {
            return false;
        }

        $otpRequest->increment('attempts');

        if (! Hash::check($otp, $otpRequest->otp_hash)) {
            return false;
        }

        $this->invalidate($phone, $purpose);

        return true;
    }

    /**
     * Ensure a verification code can be sent, otherwise throw validation error.
     */
    public function ensureCanSend(string $phone, Request $request, string $purpose): void
    {
        if (! $this->isCooldownActive($phone, $request->ip(), $purpose)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->cooldownKey($phone, $request->ip(), $purpose));

        throw ValidationException::withMessages([
            'email' => ["Please wait {$seconds} seconds before requesting another code."],
        ]);
    }

    /**
     * Remove all OTP entries for a phone / purpose pair.
     */
    public function invalidate(string $phone, string $purpose): void
    {
        OtpRequest::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->delete();
    }

    /**
     * Generate a numeric OTP code.
     */
    public function generateCode(): string
    {
        if (app()->environment('testing')) {
            return (string) config('otp.testing_code', '123456');
        }

        $length = (int) config('otp.code_length', 6);
        $max = (10 ** $length) - 1;

        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Determine if resend cooldown is active.
     */
    protected function isCooldownActive(string $phone, ?string $ip, string $purpose): bool
    {
        return RateLimiter::tooManyAttempts($this->cooldownKey($phone, $ip, $purpose), 1);
    }

    /**
     * Start resend cooldown timer.
     */
    protected function hitCooldown(string $phone, ?string $ip, string $purpose): void
    {
        RateLimiter::hit(
            $this->cooldownKey($phone, $ip, $purpose),
            (int) config('otp.resend_cooldown_seconds', 60)
        );
    }

    /**
     * Build the resend cooldown cache key.
     */
    protected function cooldownKey(string $phone, ?string $ip, string $purpose): string
    {
        return 'otp-send:'.$purpose.'|'.$phone.'|'.($ip ?? 'unknown');
    }

    /**
     * Get the next sent_count for storage.
     */
    protected function nextSentCount(string $phone, string $purpose): int
    {
        $latest = OtpRequest::query()
            ->where('phone', $phone)
            ->where('purpose', $purpose)
            ->latest('id')
            ->first();

        return $latest ? $latest->sent_count + 1 : 1;
    }

    /**
     * Determine whether local debug OTP should be exposed in UI.
     */
    protected function shouldExposeLocalOtp(): bool
    {
        return (string) config('app.env') === 'local';
    }
}

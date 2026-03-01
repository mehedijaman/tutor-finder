<?php

namespace App\Services\Sms;

use App\Contracts\SmsSender;
use App\Models\SmsSetting;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;
use Xenon\LaravelBDSms\Sender;

class GatewaySmsSender implements SmsSender
{
    /**
     * Send OTP SMS via the default active laravelbdsms provider.
     */
    public function send(string $phone, string $message): void
    {
        $smsSetting = SmsSetting::query()
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();

        if (! $smsSetting) {
            throw new RuntimeException('No active default SMS setting is configured.');
        }

        $credentials = $smsSetting->credentials;

        if (! is_array($credentials) || $credentials === []) {
            throw new RuntimeException('Default SMS setting credentials are missing or invalid.');
        }

        try {
            $sender = Sender::getInstance();
            $sender->setProvider($smsSetting->provider);
            $sender->setConfig($credentials);
            $sender->setQueue(false);
            $sender->setMobile($phone);
            $sender->setMessage($message);
            $sender->send();
        } catch (Throwable $exception) {
            Log::error('Failed to send SMS via laravelbdsms gateway.', [
                'sms_setting_id' => $smsSetting->id,
                'provider' => $smsSetting->provider,
                'phone' => $phone,
                'error' => $exception->getMessage(),
            ]);

            throw new RuntimeException('Failed to send SMS via configured gateway.', previous: $exception);
        }
    }
}

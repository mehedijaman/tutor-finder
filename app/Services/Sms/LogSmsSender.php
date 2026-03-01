<?php

namespace App\Services\Sms;

use App\Contracts\SmsSender;
use Illuminate\Support\Facades\Log;

class LogSmsSender implements SmsSender
{
    /**
     * Log the OTP payload for local and development environments.
     */
    public function send(string $phone, string $message): void
    {
        Log::channel(config('logging.default'))->info('OTP SMS dispatched', [
            'phone' => $phone,
            'message' => $message,
        ]);
    }
}

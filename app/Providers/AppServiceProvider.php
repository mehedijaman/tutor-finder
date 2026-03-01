<?php

namespace App\Providers;

use App\Contracts\SmsSender;
use App\Services\Sms\GatewaySmsSender;
use App\Services\Sms\LogSmsSender;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SmsSender::class, function (): SmsSender {
            return match (config('otp.sms_driver')) {
                'gateway' => new GatewaySmsSender,
                default => new LogSmsSender,
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure custom rate limiters used by OTP endpoints.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('otp-verify', function ($request) {
            $userId = $request->user()?->id ?? 'guest';

            return Limit::perMinute(10)->by('otp-verify:'.$userId.'|'.$request->ip());
        });
    }
}

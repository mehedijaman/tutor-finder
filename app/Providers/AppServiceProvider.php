<?php

namespace App\Providers;

use App\Contracts\SmsSender;
use App\Services\Sms\GatewaySmsSender;
use App\Services\Sms\LogSmsSender;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        $this->configureLogViewerAuthorization();
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
        RateLimiter::for('otp-verify', function (Request $request) {
            $userId = $request->user()?->id ?? 'guest';

            return Limit::perMinute(10)
                ->by('otp-verify:'.$userId.'|'.$request->ip())
                ->response(function (Request $request, array $headers): RedirectResponse {
                    $retryAfter = max(1, (int) ($headers['Retry-After'] ?? 60));

                    return redirect()
                        ->back(303)
                        ->withErrors([
                            'code' => "Too many verification attempts. Please wait {$retryAfter} seconds and try again.",
                        ]);
                });
        });

        RateLimiter::for('otp-resend', function (Request $request) {
            $userId = $request->user()?->id ?? 'guest';

            return Limit::perMinute(3)
                ->by('otp-resend:'.$userId.'|'.$request->ip())
                ->response(function (Request $request, array $headers): RedirectResponse {
                    $retryAfter = max(1, (int) ($headers['Retry-After'] ?? 60));

                    return redirect()
                        ->back(303)
                        ->withErrors([
                            'resend' => "Please wait {$retryAfter} seconds before requesting another code.",
                        ]);
                });
        });

        RateLimiter::for('contact-form', function (Request $request) {
            $identifier = trim((string) $request->input('email'));

            if ($identifier === '') {
                $identifier = trim((string) $request->input('phone'));
            }

            if ($identifier === '') {
                $identifier = 'anonymous';
            }

            return Limit::perMinute(5)->by('contact-form:'.$request->ip().'|'.$identifier);
        });
    }

    /**
     * Configure Log Viewer gates using application permissions.
     */
    protected function configureLogViewerAuthorization(): void
    {
        if (! class_exists(\Opcodes\LogViewer\Facades\LogViewer::class)) {
            return;
        }

        Gate::define('viewLogViewer', fn (mixed $user): bool => $this->canAccessPermission($user, 'log-viewer-view'));
        Gate::define('downloadLogFile', fn (mixed $user, mixed $file = null): bool => $this->canAccessPermission($user, 'log-viewer-download'));
        Gate::define('downloadLogFolder', fn (mixed $user, mixed $folder = null): bool => $this->canAccessPermission($user, 'log-viewer-download'));
        Gate::define('deleteLogFile', fn (mixed $user, mixed $file = null): bool => $this->canAccessPermission($user, 'log-viewer-delete'));
        Gate::define('deleteLogFolder', fn (mixed $user, mixed $folder = null): bool => $this->canAccessPermission($user, 'log-viewer-delete'));
    }

    /**
     * Check whether the given authenticated user has a permission.
     */
    protected function canAccessPermission(mixed $user, string $permission): bool
    {
        return is_object($user)
            && method_exists($user, 'can')
            && (bool) $user->can($permission);
    }
}

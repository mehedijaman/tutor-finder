<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\SiteSettingsResolver;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Lab404\Impersonate\Services\ImpersonateManager;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $isImpersonating = $user instanceof User
            && method_exists($user, 'isImpersonated')
            && (bool) $user->isImpersonated();

        $impersonator = null;

        if ($isImpersonating) {
            $impersonator = app(ImpersonateManager::class)->getImpersonator();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'impersonation' => [
                    'is_impersonating' => $isImpersonating,
                    'impersonator_id' => $impersonator?->getAuthIdentifier(),
                    'impersonator_name' => $impersonator?->name,
                ],
            ],
            'notificationCounts' => fn (): array => $this->notificationCounts($user),
            'siteSettings' => fn (): array => app(SiteSettingsResolver::class)->publicPayload(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'vapidPublicKey' => config('webpush.vapid.public_key'),
        ];
    }

    /**
     * Get notification count payload for shared sidebar usage.
     *
     * @return array{unread: int}
     */
    private function notificationCounts(?User $user): array
    {
        if (! $user instanceof User || ! in_array($user->role, [UserRole::Tutor, UserRole::Guardian], true)) {
            return ['unread' => 0, 'recent' => []];
        }

        return [
            'unread' => $user->unreadNotifications()->count(),
            'recent' => $user->unreadNotifications()
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? 'notification',
                    'title' => $n->data['title'] ?? 'Notification',
                    'data' => $n->data,
                    'created_at' => $n->created_at->toISOString(),
                ]),
        ];
    }
}

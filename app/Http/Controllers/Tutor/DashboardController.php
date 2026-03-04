<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the tutor dashboard.
     */
    public function __invoke(): Response
    {
        $notices = Notice::query()
            ->active()
            ->forAudience(UserRole::Tutor)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get(['id', 'title', 'body', 'published_at', 'expires_at']);

        return inertia('tutor/Dashboard', [
            'notices' => $notices,
        ]);
    }
}

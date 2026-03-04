<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the guardian dashboard.
     */
    public function __invoke(): Response
    {
        $notices = Notice::query()
            ->active()
            ->forAudience(UserRole::Guardian)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get(['id', 'title', 'body', 'published_at', 'expires_at']);

        return inertia('guardian/Dashboard', [
            'notices' => $notices,
        ]);
    }
}

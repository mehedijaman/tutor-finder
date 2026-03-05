<?php

namespace App\Http\Controllers\Tutor;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\TuitionJobApplication;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the tutor dashboard.
     */
    public function __invoke(Request $request): Response
    {
        $applicationCounts = TuitionJobApplication::query()
            ->where('tutor_user_id', $request->user()?->getAuthIdentifier())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $notices = Notice::query()
            ->active()
            ->forAudience(UserRole::Tutor)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get(['id', 'title', 'body', 'published_at', 'expires_at']);

        return inertia('tutor/Dashboard', [
            'notices' => $notices,
            'applicationStats' => [
                'applied' => (int) ($applicationCounts[ApplicationStatus::Applied->value] ?? 0),
                'shortlisted' => (int) ($applicationCounts[ApplicationStatus::Shortlisted->value] ?? 0),
                'appointed' => (int) ($applicationCounts[ApplicationStatus::Appointed->value] ?? 0),
                'confirmed' => (int) ($applicationCounts[ApplicationStatus::Confirmed->value] ?? 0),
                'cancelled' => (int) ($applicationCounts[ApplicationStatus::Cancelled->value] ?? 0),
            ],
        ]);
    }
}

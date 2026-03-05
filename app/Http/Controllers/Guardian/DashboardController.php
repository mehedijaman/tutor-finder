<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\JobStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\TuitionJob;
use Illuminate\Http\Request;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the guardian dashboard.
     */
    public function __invoke(Request $request): Response
    {
        $jobCounts = TuitionJob::query()
            ->where('guardian_id', $request->user()?->getAuthIdentifier())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $notices = Notice::query()
            ->active()
            ->forAudience(UserRole::Guardian)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get(['id', 'title', 'body', 'published_at', 'expires_at']);

        return inertia('guardian/Dashboard', [
            'notices' => $notices,
            'jobStats' => [
                'pending' => (int) ($jobCounts[JobStatus::Pending->value] ?? 0),
                'live' => (int) ($jobCounts[JobStatus::Live->value] ?? 0),
                'confirmed' => (int) ($jobCounts[JobStatus::Confirmed->value] ?? 0),
                'cancelled' => (int) ($jobCounts[JobStatus::Cancelled->value] ?? 0),
                'closed' => (int) ($jobCounts[JobStatus::Closed->value] ?? 0),
            ],
        ]);
    }
}

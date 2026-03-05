<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\TutorialAudience;
use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Inertia\Response;

class TutorialController extends Controller
{
    public function index(): Response
    {
        $tutorials = Tutorial::query()
            ->where('is_active', true)
            ->whereIn('audience', [TutorialAudience::All, TutorialAudience::Guardian])
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Tutorial $tutorial): array => [
                'id' => $tutorial->id,
                'title' => $tutorial->title,
                'slug' => $tutorial->slug,
                'video_url' => $tutorial->video_url,
                'audience' => $tutorial->audience->value,
                'thumbnail_url' => $tutorial->getFirstMediaUrl('thumbnail') ?: null,
            ]);

        return inertia('guardian/Tutorials', [
            'tutorials' => $tutorials,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Enums\TutorialAudience;
use App\Models\Tutorial;
use Inertia\Response;

class TutorialController extends Controller
{
    public function index(): Response
    {
        $tutorials = Tutorial::query()
            ->where('is_active', true)
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

        $audienceOptions = array_map(
            fn (TutorialAudience $audience): array => [
                'value' => $audience->value,
                'label' => $audience->label(),
            ],
            TutorialAudience::cases(),
        );

        return inertia('Tutorials', [
            'tutorials' => $tutorials,
            'audienceOptions' => $audienceOptions,
        ]);
    }
}

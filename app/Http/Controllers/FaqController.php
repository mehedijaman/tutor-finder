<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Response;
use Laravel\Fortify\Features;

class FaqController extends Controller
{
    /**
     * Display active FAQ entries with optional audience filtering.
     */
    public function index(Request $request): Response
    {
        $audience = strtolower(trim($request->string('audience')->toString()));

        if (! in_array($audience, [Faq::AUDIENCE_TUTOR, Faq::AUDIENCE_GUARDIAN], true)) {
            $audience = '';
        }

        $items = Faq::query()
            ->where('status', Faq::STATUS_ACTIVE)
            ->when($audience !== '', function ($builder) use ($audience): void {
                $builder->whereIn('audience', [$audience, Faq::AUDIENCE_BOTH]);
            })
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Faq $faq): array => [
                'id' => $faq->id,
                'question' => $faq->question,
                'answer' => $faq->answer,
                'audience' => $faq->audience,
                'sort_order' => $faq->sort_order,
            ])
            ->values()
            ->all();

        $metaTitle = 'FAQ | '.config('app.name');
        $metaDescription = 'Find answers to common questions for tutors and guardians.';

        if ($audience === Faq::AUDIENCE_TUTOR) {
            $metaTitle = 'Tutor FAQ | '.config('app.name');
            $metaDescription = 'Frequently asked questions for tutors using our platform.';
        }

        if ($audience === Faq::AUDIENCE_GUARDIAN) {
            $metaTitle = 'Guardian FAQ | '.config('app.name');
            $metaDescription = 'Frequently asked questions for guardians using our platform.';
        }

        return inertia('Faq', [
            'canRegister' => Features::enabled(Features::registration()),
            'faqs' => $items,
            'filters' => [
                'audience' => $audience,
            ],
            'meta' => [
                'title' => $metaTitle,
                'description' => $metaDescription,
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\PageStatus;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Inertia\Response;

class TermsOfServiceController extends Controller
{
    public function __invoke(): Response
    {
        $page = Page::query()
            ->where('slug', 'terms-of-service')
            ->where('status', PageStatus::Active)
            ->first();

        return inertia('guardian/TermsOfService', [
            'page' => $page ? $this->transformPage($page) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPage(Page $page): array
    {
        return [
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'updated_at' => $page->updated_at?->toDateTimeString(),
        ];
    }
}

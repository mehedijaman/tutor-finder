<?php

namespace App\Http\Controllers;

use App\Enums\JobStatus;
use App\Enums\PageStatus;
use App\Enums\TaxonomyStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Category;
use App\Models\Page;
use App\Models\Testimonial;
use App\Models\TuitionJob;
use App\Models\User;
use Inertia\Response;
use Laravel\Fortify\Features;

class SiteController extends Controller
{
    public function index(): Response
    {
        return inertia('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'heroStats' => $this->heroStats(),
            'testimonials' => $this->homepageTestimonials(),
            'tuitionMethods' => $this->tuitionMethods(),
        ]);
    }

    public function jobs(): Response
    {
        return inertia('JobBoard');
    }

    public function faq(): Response
    {
        return inertia('Faq');
    }

    public function blog(): Response
    {
        return inertia('Blog');
    }

    public function contact(): Response
    {
        return inertia('Contact');
    }

    public function privacy(): Response
    {
        $page = Page::query()
            ->where('slug', 'privacy-policy')
            ->where('status', PageStatus::Active)
            ->first();

        if ($page) {
            return inertia('Page', ['page' => $this->transformPage($page)]);
        }

        return inertia('PrivacyPolicy');
    }

    public function terms(): Response
    {
        $page = Page::query()
            ->where('slug', 'terms-of-service')
            ->where('status', PageStatus::Active)
            ->first();

        if ($page) {
            return inertia('Page', ['page' => $this->transformPage($page)]);
        }

        return inertia('TermsOfService');
    }

    public function refundPolicy(): Response
    {
        $page = Page::query()
            ->where('slug', 'refund-policy')
            ->where('status', PageStatus::Active)
            ->first();

        if ($page) {
            return inertia('Page', ['page' => $this->transformPage($page)]);
        }

        return inertia('RefundPolicy');
    }

    public function showPage(string $slug): Response
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('status', PageStatus::Active)
            ->firstOrFail();

        return inertia('Page', ['page' => $this->transformPage($page)]);
    }

    /**
     * Transform a page model for the frontend.
     *
     * @return array<string, mixed>
     */
    private function transformPage(Page $page): array
    {
        return [
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'featured_image_url' => $page->getFirstMediaUrl('featured_image') ?: null,
            'updated_at' => $page->updated_at?->toDateTimeString(),
        ];
    }

    /**
     * Build hero section stat payload from persisted database records.
     *
     * @return array{
     *     active_tutors: int,
     *     families_served: int,
     *     average_rating: float|null
     * }
     */
    private function heroStats(): array
    {
        $activeTutors = User::query()
            ->where('role', UserRole::Tutor)
            ->where('status', UserStatus::Active)
            ->count();

        $familiesServed = TuitionJob::query()
            ->where('status', JobStatus::Confirmed)
            ->distinct('guardian_id')
            ->count('guardian_id');

        $averageRating = Testimonial::query()
            ->where('status', TaxonomyStatus::Active)
            ->avg('rating');

        return [
            'active_tutors' => $activeTutors,
            'families_served' => $familiesServed,
            'average_rating' => $averageRating !== null ? round((float) $averageRating, 1) : null,
        ];
    }

    /**
     * Build active testimonials payload for homepage carousel.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     role: string|null,
     *     avatar_url: string|null,
     *     content: string,
     *     rating: int
     * }>
     */
    private function homepageTestimonials(): array
    {
        return Testimonial::query()
            ->with('media')
            ->where('status', TaxonomyStatus::Active)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->limit(12)
            ->get(['id', 'name', 'role', 'avatar_url', 'content', 'rating'])
            ->map(fn (Testimonial $testimonial): array => [
                'id' => $testimonial->id,
                'name' => $testimonial->name,
                'role' => $testimonial->role,
                'avatar_url' => $testimonial->getFirstMediaUrl('avatar') ?: $testimonial->avatar_url,
                'content' => $testimonial->content,
                'rating' => (int) $testimonial->rating,
            ])
            ->values()
            ->all();
    }

    /**
     * Build tuition method cards from category taxonomy.
     *
     * @return array<int, array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     description: string|null
     * }>
     */
    private function tuitionMethods(): array
    {
        return Category::query()
            ->where('status', TaxonomyStatus::Active)
            ->ordered()
            ->get(['id', 'name', 'slug', 'description'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ])
            ->values()
            ->all();
    }
}

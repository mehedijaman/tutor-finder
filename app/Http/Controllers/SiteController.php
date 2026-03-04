<?php

namespace App\Http\Controllers;

use App\Enums\JobStatus;
use App\Enums\TaxonomyStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
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
        return inertia('PrivacyPolicy');
    }

    public function terms(): Response
    {
        return inertia('TermsOfService');
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
}

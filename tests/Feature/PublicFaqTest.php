<?php

use App\Enums\FaqAudience;
use App\Enums\FaqStatus;
use App\Models\Faq;
use Inertia\Testing\AssertableInertia as Assert;

it('shows only active non-trashed faqs on public faq page', function () {
    $visibleFaq = Faq::factory()->create([
        'question' => 'Visible FAQ',
        'status' => FaqStatus::Active,
        'audience' => FaqAudience::Both,
    ]);

    Faq::factory()->create([
        'question' => 'Inactive FAQ',
        'status' => FaqStatus::Inactive,
        'audience' => FaqAudience::Both,
    ]);

    $trashedFaq = Faq::factory()->create([
        'question' => 'Trashed FAQ',
        'status' => FaqStatus::Active,
        'audience' => FaqAudience::Both,
    ]);
    $trashedFaq->delete();

    $this->get(route('faq.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Faq')
            ->has('faqs', 1)
            ->where('faqs.0.id', $visibleFaq->id));
});

it('applies audience filter including both audience records', function () {
    $tutorFaq = Faq::factory()->create([
        'status' => FaqStatus::Active,
        'audience' => FaqAudience::Tutor,
        'sort_order' => 0,
        'created_at' => now()->subDay(),
    ]);

    $bothFaq = Faq::factory()->create([
        'status' => FaqStatus::Active,
        'audience' => FaqAudience::Both,
        'sort_order' => 0,
        'created_at' => now(),
    ]);

    Faq::factory()->create([
        'status' => FaqStatus::Active,
        'audience' => FaqAudience::Guardian,
        'sort_order' => 0,
    ]);

    $this->get(route('faq.index', ['audience' => 'tutor']))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Faq')
            ->where('filters.audience', 'tutor')
            ->has('faqs', 2)
            ->where('faqs.0.id', $bothFaq->id)
            ->where('faqs.1.id', $tutorFaq->id));
});

it('orders faqs by sort_order asc then created_at desc', function () {
    $oldest = Faq::factory()->create([
        'status' => FaqStatus::Active,
        'audience' => FaqAudience::Both,
        'sort_order' => 0,
        'created_at' => now()->subDays(3),
    ]);

    $newer = Faq::factory()->create([
        'status' => FaqStatus::Active,
        'audience' => FaqAudience::Both,
        'sort_order' => 0,
        'created_at' => now()->subDay(),
    ]);

    $after = Faq::factory()->create([
        'status' => FaqStatus::Active,
        'audience' => FaqAudience::Both,
        'sort_order' => 2,
        'created_at' => now(),
    ]);

    $this->get(route('faq.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Faq')
            ->where('faqs.0.id', $newer->id)
            ->where('faqs.1.id', $oldest->id)
            ->where('faqs.2.id', $after->id));
});

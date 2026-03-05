<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;

it('tutor can view terms of service page with fallback content', function () {
    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($tutor)->get(route('tutor.terms-of-service'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('tutor/TermsOfService')
        ->where('page', null)
    );
});

it('tutor sees page model content when terms of service page exists', function () {
    $tutor = User::factory()->tutor()->create();

    Page::factory()->create([
        'title' => 'Terms of Service',
        'slug' => 'terms-of-service',
        'status' => PageStatus::Active,
        'content' => '<p>Custom terms content</p>',
    ]);

    $response = $this->actingAs($tutor)->get(route('tutor.terms-of-service'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('tutor/TermsOfService')
        ->where('page.title', 'Terms of Service')
        ->where('page.slug', 'terms-of-service')
    );
});

it('guardian can view terms of service page with fallback content', function () {
    $guardian = User::factory()->guardian()->create();

    $response = $this->actingAs($guardian)->get(route('guardian.terms-of-service'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('guardian/TermsOfService')
        ->where('page', null)
    );
});

it('guardian sees page model content when terms of service page exists', function () {
    $guardian = User::factory()->guardian()->create();

    Page::factory()->create([
        'title' => 'Terms of Service',
        'slug' => 'terms-of-service',
        'status' => PageStatus::Active,
        'content' => '<p>Custom terms content</p>',
    ]);

    $response = $this->actingAs($guardian)->get(route('guardian.terms-of-service'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('guardian/TermsOfService')
        ->where('page.title', 'Terms of Service')
        ->where('page.slug', 'terms-of-service')
    );
});

it('unauthenticated user cannot access tutor terms of service', function () {
    $response = $this->get(route('tutor.terms-of-service'));

    $response->assertRedirect();
});

it('unauthenticated user cannot access guardian terms of service', function () {
    $response = $this->get(route('guardian.terms-of-service'));

    $response->assertRedirect();
});

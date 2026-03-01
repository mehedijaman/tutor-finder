<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('guardian.dashboard', absolute: false));
});

test('admin base route points to admin dashboard', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $response = $this->get('/admin');

    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('tutor base route points to tutor dashboard', function () {
    $tutor = User::factory()->tutor()->create();
    $this->actingAs($tutor);

    $response = $this->get('/tutor');

    $response->assertRedirect(route('tutor.dashboard', absolute: false));
});

test('guardian base route points to guardian dashboard', function () {
    $guardian = User::factory()->guardian()->create();
    $this->actingAs($guardian);

    $response = $this->get('/guardian');

    $response->assertRedirect(route('guardian.dashboard', absolute: false));
});

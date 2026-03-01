<?php

use App\Models\User;

it('tutor cannot access guardian dashboard', function () {
    $tutor = User::factory()->tutor()->create();

    $response = $this->actingAs($tutor)->get(route('guardian.dashboard'));

    $response->assertForbidden();
});

it('guardian cannot access tutor dashboard', function () {
    $guardian = User::factory()->guardian()->create();

    $response = $this->actingAs($guardian)->get(route('tutor.dashboard'));

    $response->assertForbidden();
});

it('non admin cannot access admin dashboard', function () {
    $guardian = User::factory()->guardian()->create();

    $response = $this->actingAs($guardian)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

it('suspended user cannot access own dashboard', function () {
    $tutor = User::factory()->tutor()->suspended()->create();

    $response = $this->actingAs($tutor)->get(route('tutor.dashboard'));

    $response->assertForbidden();
});

it('pending user is redirected to verify otp from dashboard route', function () {
    $guardian = User::factory()->guardian()->pendingVerification()->create();

    $response = $this->actingAs($guardian)->get(route('dashboard'));

    $response->assertRedirect('/verify-otp');
});

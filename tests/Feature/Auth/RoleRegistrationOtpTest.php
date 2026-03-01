<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('tutor can register verify otp and land on tutor dashboard', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Tutor One',
        'phone' => '+15550000101',
        'email' => 'tutor1@example.com',
        'role' => 'tutor',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/verify-otp');
    $this->assertAuthenticated();

    /** @var User $user */
    $user = User::query()->where('email', 'tutor1@example.com')->firstOrFail();

    expect($user->status)->toBe('pending_verification');
    expect($user->phone_verified_at)->toBeNull();

    $verifyResponse = $this->post(route('otp.verify.store'), [
        'code' => config('otp.testing_code'),
    ]);

    $verifyResponse->assertRedirect(route('tutor.dashboard', absolute: false));

    $user->refresh();

    expect($user->status)->toBe('active');
    expect($user->phone_verified_at)->not->toBeNull();
});

it('guardian can register verify otp and land on guardian dashboard', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Guardian One',
        'phone' => '+15550000102',
        'email' => 'guardian1@example.com',
        'role' => 'guardian',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/verify-otp');
    $this->assertAuthenticated();

    $verifyResponse = $this->post(route('otp.verify.store'), [
        'code' => config('otp.testing_code'),
    ]);

    $verifyResponse->assertRedirect(route('guardian.dashboard', absolute: false));
});

it('shows local debug otp on verify page when app env is local', function () {
    config()->set('app.env', 'local');

    $response = $this->post(route('register.store'), [
        'name' => 'Local Guardian',
        'phone' => '+15550000103',
        'email' => 'local-guardian@example.com',
        'role' => 'guardian',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/verify-otp');

    $this->get(route('otp.verify'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('auth/VerifyOtp')
            ->where('localOtp', config('otp.testing_code')),
        );
});

<?php

use App\Enums\UserStatus;
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

    expect($user->status)->toBe(UserStatus::PendingVerification);
    expect($user->phone_verified_at)->toBeNull();

    $verifyResponse = $this->post(route('otp.verify.store'), [
        'code' => config('otp.testing_code'),
    ]);

    $verifyResponse->assertRedirect(route('tutor.dashboard', absolute: false));

    $user->refresh();

    expect($user->status)->toBe(UserStatus::Active);
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

it('resend otp route is available for pending users', function () {
    $user = User::factory()
        ->guardian()
        ->pendingVerification()
        ->create();

    $response = $this
        ->actingAs($user)
        ->post(route('otp.verify.resend'));

    $response
        ->assertRedirect(route('otp.verify', absolute: false))
        ->assertSessionHas('status', 'A new verification code has been sent to your phone.');
});

it('returns a validation error when otp verify throttle is exceeded', function () {
    $user = User::factory()
        ->guardian()
        ->pendingVerification()
        ->create();

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.91'])
            ->actingAs($user)
            ->from(route('otp.verify', absolute: false))
            ->post(route('otp.verify.store'), [
                'code' => '000000',
            ]);

        $response
            ->assertRedirect(route('otp.verify', absolute: false))
            ->assertSessionHasErrors('code');
    }

    $throttledResponse = $this
        ->withServerVariables(['REMOTE_ADDR' => '127.0.0.91'])
        ->actingAs($user)
        ->from(route('otp.verify', absolute: false))
        ->post(route('otp.verify.store'), [
            'code' => '000000',
        ]);

    $throttledResponse
        ->assertRedirect(route('otp.verify', absolute: false))
        ->assertSessionHasErrors('code');

    $errorMessage = session('errors')?->get('code')[0] ?? null;

    expect($errorMessage)->toContain('Too many verification attempts.');
});

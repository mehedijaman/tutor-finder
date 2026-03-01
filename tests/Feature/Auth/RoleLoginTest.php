<?php

use App\Models\User;

it('tutor login fails when guardian role is selected', function () {
    $user = User::factory()->tutor()->create([
        'email' => 'tutor@example.com',
        'password' => 'password',
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'guardian',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

it('guardian login fails when tutor role is selected', function () {
    $user = User::factory()->guardian()->create([
        'email' => 'guardian@example.com',
        'password' => 'password',
    ]);

    $response = $this->from(route('login'))->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'role' => 'tutor',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

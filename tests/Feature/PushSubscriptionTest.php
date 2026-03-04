<?php

use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->tutor()->create();
});

it('stores a push subscription', function (): void {
    $response = $this->actingAs($this->user)->postJson('/push-subscriptions', [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'keys' => [
            'auth' => 'test-auth-key',
            'p256dh' => 'test-p256dh-key',
        ],
    ]);

    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('push_subscriptions', [
        'subscribable_id' => $this->user->id,
        'subscribable_type' => 'App\\Models\\User',
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
    ]);
});

it('updates existing push subscription', function (): void {
    $this->user->updatePushSubscription(
        'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'old-p256dh',
        'old-auth',
    );

    $response = $this->actingAs($this->user)->postJson('/push-subscriptions', [
        'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-endpoint',
        'keys' => [
            'auth' => 'new-auth-key',
            'p256dh' => 'new-p256dh-key',
        ],
    ]);

    $response->assertJson(['success' => true]);

    expect($this->user->pushSubscriptions()->count())->toBe(1);
    expect($this->user->pushSubscriptions()->first()->auth_token)->toBe('new-auth-key');
});

it('deletes a push subscription', function (): void {
    $endpoint = 'https://fcm.googleapis.com/fcm/send/test-endpoint';

    $this->user->updatePushSubscription($endpoint, 'p256dh', 'auth');

    $response = $this->actingAs($this->user)->deleteJson('/push-subscriptions', [
        'endpoint' => $endpoint,
    ]);

    $response->assertJson(['success' => true]);

    $this->assertDatabaseMissing('push_subscriptions', [
        'subscribable_id' => $this->user->id,
        'endpoint' => $endpoint,
    ]);
});

it('validates required fields for store', function (): void {
    $response = $this->actingAs($this->user)->postJson('/push-subscriptions', []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['endpoint', 'keys.auth', 'keys.p256dh']);
});

it('requires authentication', function (): void {
    $response = $this->postJson('/push-subscriptions', [
        'endpoint' => 'https://example.com/push',
        'keys' => ['auth' => 'test', 'p256dh' => 'test'],
    ]);

    $response->assertStatus(401);
});

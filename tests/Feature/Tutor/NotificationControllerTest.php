<?php

use App\Models\Invoice;
use App\Models\User;
use App\Notifications\PaymentNotification;

beforeEach(function (): void {
    $this->tutor = User::factory()->tutor()->create();
});

it('displays notifications index page for tutor', function (): void {
    $this->actingAs($this->tutor)
        ->get(route('tutor.notifications.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('tutor/notifications/Index')
            ->has('items')
            ->has('counts'));
});

it('filters unread notifications only', function (): void {
    $this->tutor->notify(new PaymentNotification(
        Invoice::factory()->create(['payer_user_id' => $this->tutor->id]),
        'invoice.created'
    ));

    $unreadCount = $this->tutor->unreadNotifications()->count();

    expect($unreadCount)->toBe(1);

    $this->actingAs($this->tutor)
        ->get(route('tutor.notifications.index', ['status' => 'unread']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.status', 'unread')
            ->has('items.data', 1));
});

it('marks a single notification as read', function (): void {
    $this->tutor->notify(new PaymentNotification(
        Invoice::factory()->create(['payer_user_id' => $this->tutor->id]),
        'payment.success'
    ));

    $notification = $this->tutor->notifications()->first();

    expect($notification->read_at)->toBeNull();

    $this->actingAs($this->tutor)
        ->patch(route('tutor.notifications.read', $notification->id))
        ->assertRedirect();

    $notification->refresh();

    expect($notification->read_at)->not->toBeNull();
});

it('marks all notifications as read', function (): void {
    $invoice1 = Invoice::factory()->create(['payer_user_id' => $this->tutor->id]);
    $invoice2 = Invoice::factory()->create(['payer_user_id' => $this->tutor->id]);

    $this->tutor->notify(new PaymentNotification($invoice1, 'payment.success'));
    $this->tutor->notify(new PaymentNotification($invoice2, 'invoice.created'));

    expect($this->tutor->unreadNotifications()->count())->toBe(2);

    $this->actingAs($this->tutor)
        ->patch(route('tutor.notifications.read-all'))
        ->assertRedirect();

    expect($this->tutor->unreadNotifications()->count())->toBe(0);
});

it('denies guest access to notifications', function (): void {
    $this->get(route('tutor.notifications.index'))
        ->assertRedirect(route('login'));
});

it('denies guardian access to tutor notifications', function (): void {
    $guardian = User::factory()->guardian()->create();

    $this->actingAs($guardian)
        ->get(route('tutor.notifications.index'))
        ->assertForbidden();
});

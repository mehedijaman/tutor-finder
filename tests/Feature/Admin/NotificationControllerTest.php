<?php

use App\Models\User;
use App\Notifications\TicketNotification;

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
});

it('displays notifications index page for admin', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.notifications.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/notifications/Index')
            ->has('items')
            ->has('counts'));
});

it('filters unread notifications only', function (): void {
    $this->admin->notify(new TicketNotification(
        event: 'ticket.created',
        title: 'New Ticket',
        message: 'A new support ticket was created.',
        url: '/admin/support-tickets/1',
    ));

    expect($this->admin->unreadNotifications()->count())->toBe(1);

    $this->actingAs($this->admin)
        ->get(route('admin.notifications.index', ['status' => 'unread']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.status', 'unread')
            ->has('items.data', 1));
});

it('filters read notifications only', function (): void {
    $this->admin->notify(new TicketNotification(
        event: 'ticket.created',
        title: 'New Ticket',
        message: 'A new support ticket was created.',
        url: '/admin/support-tickets/1',
    ));

    $this->admin->notifications()->first()->markAsRead();

    $this->actingAs($this->admin)
        ->get(route('admin.notifications.index', ['status' => 'read']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('filters.status', 'read')
            ->has('items.data', 1));
});

it('marks a single notification as read', function (): void {
    $this->admin->notify(new TicketNotification(
        event: 'ticket.replied',
        title: 'Ticket Reply',
        message: 'A user replied to a ticket.',
        url: '/admin/support-tickets/1',
    ));

    $notification = $this->admin->notifications()->first();

    expect($notification->read_at)->toBeNull();

    $this->actingAs($this->admin)
        ->patch(route('admin.notifications.read', $notification->id))
        ->assertRedirect();

    $notification->refresh();

    expect($notification->read_at)->not->toBeNull();
});

it('marks all notifications as read', function (): void {
    $this->admin->notify(new TicketNotification(
        event: 'ticket.created',
        title: 'Ticket 1',
        message: 'First ticket.',
        url: '/admin/support-tickets/1',
    ));

    $this->admin->notify(new TicketNotification(
        event: 'ticket.replied',
        title: 'Ticket Reply',
        message: 'Second notification.',
        url: '/admin/support-tickets/2',
    ));

    expect($this->admin->unreadNotifications()->count())->toBe(2);

    $this->actingAs($this->admin)
        ->patch(route('admin.notifications.read-all'))
        ->assertRedirect();

    expect($this->admin->unreadNotifications()->count())->toBe(0);
});

it('denies guest access to admin notifications', function (): void {
    $this->get(route('admin.notifications.index'))
        ->assertRedirect(route('admin.login'));
});

it('denies tutor access to admin notifications', function (): void {
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($tutor)
        ->get(route('admin.notifications.index'))
        ->assertForbidden();
});

it('returns correct unread count in shared props', function (): void {
    $this->admin->notify(new TicketNotification(
        event: 'ticket.created',
        title: 'New Ticket',
        message: 'A ticket was created.',
        url: '/admin/support-tickets/1',
    ));

    $this->actingAs($this->admin)
        ->get(route('admin.notifications.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('notificationCounts.unread', 1)
            ->has('notificationCounts.recent', 1));
});

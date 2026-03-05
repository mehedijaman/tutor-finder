<?php

use App\Enums\TicketStatus;
use App\Events\TicketReplied;
use App\Models\SupportTicket;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
});

it('can view the support tickets index with permission', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-view');

    SupportTicket::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get('/admin/support-tickets')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/support-tickets/Index')
            ->has('items.data', 3)
            ->has('counts')
            ->has('priorityOptions')
            ->has('categoryOptions')
            ->has('statusOptions')
            ->has('adminUsers')
        );
});

it('cannot view tickets without permission', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/support-tickets')
        ->assertForbidden();
});

it('can filter tickets by status', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-view');

    SupportTicket::factory()->open()->count(2)->create();
    SupportTicket::factory()->closed()->create();

    $this->actingAs($admin)
        ->get('/admin/support-tickets?status=open')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 2)
        );
});

it('can filter tickets by priority', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-view');

    SupportTicket::factory()->urgent()->count(2)->create();
    SupportTicket::factory()->create();

    $this->actingAs($admin)
        ->get('/admin/support-tickets?priority=urgent')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 2)
        );
});

it('can search tickets by subject', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-view');

    SupportTicket::factory()->create(['subject' => 'Billing issue urgent']);
    SupportTicket::factory()->create(['subject' => 'Technical problem']);

    $this->actingAs($admin)
        ->get('/admin/support-tickets?q=Billing')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 1)
        );
});

it('can view a ticket detail', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-view');

    $ticket = SupportTicket::factory()->create();

    $this->actingAs($admin)
        ->get("/admin/support-tickets/{$ticket->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/support-tickets/Show')
            ->has('ticket')
            ->has('statusOptions')
            ->has('adminUsers')
        );
});

it('can reply to a ticket', function () {
    Event::fake([TicketReplied::class]);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-update');

    $ticket = SupportTicket::factory()->create();

    $this->actingAs($admin)
        ->post("/admin/support-tickets/{$ticket->id}/reply", [
            'message' => 'Admin reply to the ticket.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('support_ticket_messages', [
        'support_ticket_id' => $ticket->id,
        'user_id' => $admin->id,
        'body' => 'Admin reply to the ticket.',
    ]);

    Event::assertDispatched(TicketReplied::class);
});

it('can close a ticket', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-close');

    $ticket = SupportTicket::factory()->open()->create();

    $this->actingAs($admin)
        ->patch("/admin/support-tickets/{$ticket->id}/status", [
            'status' => TicketStatus::Closed->value,
        ])
        ->assertRedirect();

    expect($ticket->refresh()->status)->toBe(TicketStatus::Closed);
    expect($ticket->closed_at)->not->toBeNull();
    expect($ticket->closed_by)->toBe($admin->id);
});

it('can update ticket status to in progress', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-close');

    $ticket = SupportTicket::factory()->open()->create();

    $this->actingAs($admin)
        ->patch("/admin/support-tickets/{$ticket->id}/status", [
            'status' => TicketStatus::InProgress->value,
        ])
        ->assertRedirect();

    expect($ticket->refresh()->status)->toBe(TicketStatus::InProgress);
});

it('can assign a ticket to an admin', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-assign');

    $assignee = User::factory()->admin()->create();
    $ticket = SupportTicket::factory()->create();

    $this->actingAs($admin)
        ->patch("/admin/support-tickets/{$ticket->id}/assign", [
            'assigned_to' => $assignee->id,
        ])
        ->assertRedirect();

    expect($ticket->refresh()->assigned_to)->toBe($assignee->id);
});

it('cannot assign ticket to non-admin user', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-assign');

    $tutor = User::factory()->tutor()->create();
    $ticket = SupportTicket::factory()->create();

    $this->actingAs($admin)
        ->patch("/admin/support-tickets/{$ticket->id}/assign", [
            'assigned_to' => $tutor->id,
        ])
        ->assertSessionHasErrors('assigned_to');
});

it('cannot close a ticket without permission', function () {
    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('ticket-view');

    $ticket = SupportTicket::factory()->open()->create();

    $this->actingAs($admin)
        ->patch("/admin/support-tickets/{$ticket->id}/status", [
            'status' => TicketStatus::Closed->value,
        ])
        ->assertForbidden();
});

<?php

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Events\TicketCreated;
use App\Events\TicketReplied;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia as Assert;

it('can view the support tickets index', function () {
    $guardian = User::factory()->guardian()->create();
    SupportTicket::factory()->forGuardian()->count(2)->create(['user_id' => $guardian->id]);

    $this->actingAs($guardian)
        ->get('/guardian/support-tickets')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/support-tickets/Index')
            ->has('items.data', 2)
            ->has('counts')
        );
});

it('can view the create ticket page', function () {
    $guardian = User::factory()->guardian()->create();

    $this->actingAs($guardian)
        ->get('/guardian/support-tickets/create')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/support-tickets/Create')
            ->has('categoryOptions')
            ->has('priorityOptions')
        );
});

it('can create a support ticket', function () {
    Event::fake([TicketCreated::class]);

    $guardian = User::factory()->guardian()->create();

    $this->actingAs($guardian)
        ->post('/guardian/support-tickets', [
            'subject' => 'Billing issue',
            'category' => TicketCategory::Billing->value,
            'priority' => TicketPriority::Medium->value,
            'message' => 'I have a billing issue.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('support_tickets', [
        'user_id' => $guardian->id,
        'subject' => 'Billing issue',
        'category' => TicketCategory::Billing->value,
        'status' => TicketStatus::Open->value,
    ]);

    Event::assertDispatched(TicketCreated::class);
});

it('can view own ticket details', function () {
    $guardian = User::factory()->guardian()->create();
    $ticket = SupportTicket::factory()->forGuardian()->create(['user_id' => $guardian->id]);

    $this->actingAs($guardian)
        ->get("/guardian/support-tickets/{$ticket->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('guardian/support-tickets/Show')
            ->has('ticket')
        );
});

it('cannot view another user ticket', function () {
    $guardian = User::factory()->guardian()->create();
    $otherGuardian = User::factory()->guardian()->create();
    $ticket = SupportTicket::factory()->forGuardian()->create(['user_id' => $otherGuardian->id]);

    $this->actingAs($guardian)
        ->get("/guardian/support-tickets/{$ticket->id}")
        ->assertForbidden();
});

it('can reply to own ticket', function () {
    Event::fake([TicketReplied::class]);

    $guardian = User::factory()->guardian()->create();
    $ticket = SupportTicket::factory()->forGuardian()->create(['user_id' => $guardian->id]);

    $this->actingAs($guardian)
        ->post("/guardian/support-tickets/{$ticket->id}/reply", [
            'message' => 'Guardian reply message.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('support_ticket_messages', [
        'support_ticket_id' => $ticket->id,
        'user_id' => $guardian->id,
        'body' => 'Guardian reply message.',
    ]);

    Event::assertDispatched(TicketReplied::class);
});

it('reopens a closed ticket when guardian replies', function () {
    Event::fake([TicketReplied::class]);

    $guardian = User::factory()->guardian()->create();
    $ticket = SupportTicket::factory()->forGuardian()->closed()->create(['user_id' => $guardian->id]);

    expect($ticket->status)->toBe(TicketStatus::Closed);

    $this->actingAs($guardian)
        ->post("/guardian/support-tickets/{$ticket->id}/reply", [
            'message' => 'Reopening this.',
        ])
        ->assertRedirect();

    expect($ticket->refresh()->status)->toBe(TicketStatus::Open);
});

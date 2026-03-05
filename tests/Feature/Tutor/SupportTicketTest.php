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
    $tutor = User::factory()->tutor()->create();
    SupportTicket::factory()->count(3)->create(['user_id' => $tutor->id]);

    $this->actingAs($tutor)
        ->get('/tutor/support-tickets')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/support-tickets/Index')
            ->has('items.data', 3)
            ->has('counts')
        );
});

it('can filter tickets by status', function () {
    $tutor = User::factory()->tutor()->create();
    SupportTicket::factory()->open()->create(['user_id' => $tutor->id]);
    SupportTicket::factory()->closed()->create(['user_id' => $tutor->id]);

    $this->actingAs($tutor)
        ->get('/tutor/support-tickets?status=open')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/support-tickets/Index')
            ->has('items.data', 1)
        );
});

it('can view the create ticket page', function () {
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($tutor)
        ->get('/tutor/support-tickets/create')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/support-tickets/Create')
            ->has('categoryOptions')
            ->has('priorityOptions')
        );
});

it('can create a support ticket', function () {
    Event::fake([TicketCreated::class]);

    $tutor = User::factory()->tutor()->create();

    $this->actingAs($tutor)
        ->post('/tutor/support-tickets', [
            'subject' => 'Test ticket subject',
            'category' => TicketCategory::Technical->value,
            'priority' => TicketPriority::High->value,
            'message' => 'This is a test support ticket message.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('support_tickets', [
        'user_id' => $tutor->id,
        'subject' => 'Test ticket subject',
        'category' => TicketCategory::Technical->value,
        'priority' => TicketPriority::High->value,
        'status' => TicketStatus::Open->value,
    ]);

    $ticket = SupportTicket::query()->latest()->first();
    expect($ticket->messages)->toHaveCount(1);
    expect($ticket->messages->first()->body)->toBe('This is a test support ticket message.');

    Event::assertDispatched(TicketCreated::class);
});

it('validates required fields when creating a ticket', function () {
    $tutor = User::factory()->tutor()->create();

    $this->actingAs($tutor)
        ->post('/tutor/support-tickets', [])
        ->assertSessionHasErrors(['subject', 'category', 'priority', 'message']);
});

it('can view own ticket details', function () {
    $tutor = User::factory()->tutor()->create();
    $ticket = SupportTicket::factory()->create(['user_id' => $tutor->id]);

    $this->actingAs($tutor)
        ->get("/tutor/support-tickets/{$ticket->id}")
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tutor/support-tickets/Show')
            ->has('ticket')
            ->where('ticket.id', $ticket->id)
        );
});

it('cannot view another user ticket', function () {
    $tutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();
    $ticket = SupportTicket::factory()->create(['user_id' => $otherTutor->id]);

    $this->actingAs($tutor)
        ->get("/tutor/support-tickets/{$ticket->id}")
        ->assertForbidden();
});

it('can reply to own ticket', function () {
    Event::fake([TicketReplied::class]);

    $tutor = User::factory()->tutor()->create();
    $ticket = SupportTicket::factory()->create(['user_id' => $tutor->id]);

    $this->actingAs($tutor)
        ->post("/tutor/support-tickets/{$ticket->id}/reply", [
            'message' => 'This is a reply.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('support_ticket_messages', [
        'support_ticket_id' => $ticket->id,
        'user_id' => $tutor->id,
        'body' => 'This is a reply.',
    ]);

    Event::assertDispatched(TicketReplied::class);
});

it('reopens a closed ticket when user replies', function () {
    Event::fake([TicketReplied::class]);

    $tutor = User::factory()->tutor()->create();
    $ticket = SupportTicket::factory()->closed()->create(['user_id' => $tutor->id]);

    expect($ticket->status)->toBe(TicketStatus::Closed);

    $this->actingAs($tutor)
        ->post("/tutor/support-tickets/{$ticket->id}/reply", [
            'message' => 'I need more help.',
        ])
        ->assertRedirect();

    expect($ticket->refresh()->status)->toBe(TicketStatus::Open);
});

it('only shows own tickets in the index', function () {
    $tutor = User::factory()->tutor()->create();
    $otherTutor = User::factory()->tutor()->create();

    SupportTicket::factory()->count(2)->create(['user_id' => $tutor->id]);
    SupportTicket::factory()->count(3)->create(['user_id' => $otherTutor->id]);

    $this->actingAs($tutor)
        ->get('/tutor/support-tickets')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->has('items.data', 2)
        );
});

it('generates unique ticket numbers', function () {
    $tutor = User::factory()->tutor()->create();
    $ticket1 = SupportTicket::factory()->create(['user_id' => $tutor->id]);
    $ticket2 = SupportTicket::factory()->create(['user_id' => $tutor->id]);

    expect($ticket1->ticket_number)->not->toBe($ticket2->ticket_number);
    expect($ticket1->ticket_number)->toStartWith('TKT-');
    expect($ticket2->ticket_number)->toStartWith('TKT-');
});

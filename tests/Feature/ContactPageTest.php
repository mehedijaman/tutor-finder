<?php

use App\Models\ContactMessage;
use Inertia\Testing\AssertableInertia as Assert;

it('can view the contact page', function () {
    $this->get(route('contact'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Contact')
            ->has('contactDetails.phones')
            ->has('contactDetails.emails')
            ->has('contactDetails.addresses')
            ->has('contactDetails.social_details'));
});

it('can submit a valid contact form and store the message', function () {
    $this->post(route('contact.store'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '',
        'subject' => 'General Inquiry',
        'message' => 'I would like to know more about your tutoring plans.',
        'website' => '',
    ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('contact_messages', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => null,
        'status' => ContactMessage::STATUS_OPEN,
    ]);

    $message = ContactMessage::query()->firstOrFail();

    expect($message->ip)->toBe('127.0.0.1');
    expect($message->user_agent)->not()->toBeNull();
});

it('returns validation errors for invalid contact submissions', function () {
    $this->post(route('contact.store'), [
        'name' => '',
        'email' => '',
        'phone' => '',
        'subject' => str_repeat('a', 201),
        'message' => 'short',
        'website' => '',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors([
            'name',
            'email',
            'phone',
            'subject',
            'message',
        ]);
});

it('blocks submission when honeypot field is filled', function () {
    $this->post(route('contact.store'), [
        'name' => 'Spam User',
        'email' => 'spam@example.com',
        'phone' => '',
        'subject' => 'Spam',
        'message' => 'This should fail because honeypot is filled.',
        'website' => 'https://spam.example.com',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['website']);

    expect(ContactMessage::query()->count())->toBe(0);
});

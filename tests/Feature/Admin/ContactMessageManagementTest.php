<?php

use App\Models\ContactMessage;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('authorized admin can list and view contact messages', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('contact-message-view');

    $message = ContactMessage::factory()->create([
        'name' => 'Client User',
        'status' => ContactMessage::STATUS_OPEN,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.contact-messages.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/contact-messages/Index')
            ->has('items.data', 1)
            ->where('items.data.0.id', $message->id));

    $this->actingAs($admin)
        ->get(route('admin.contact-messages.show', $message))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/contact-messages/Show')
            ->where('message.id', $message->id)
            ->where('message.name', 'Client User'));
});

it('unauthorized admin cannot access contact messages module', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $message = ContactMessage::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.contact-messages.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->get(route('admin.contact-messages.show', $message))
        ->assertForbidden();

    $this->actingAs($admin)
        ->patch(route('admin.contact-messages.status', $message), [
            'status' => ContactMessage::STATUS_CLOSED,
        ])
        ->assertForbidden();
});

it('authorized admin can toggle contact message status', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('contact-message-update');

    $message = ContactMessage::factory()->create([
        'status' => ContactMessage::STATUS_OPEN,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.contact-messages.status', $message), [
            'status' => ContactMessage::STATUS_CLOSED,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($message->refresh()->status)->toBe(ContactMessage::STATUS_CLOSED);

    $this->actingAs($admin)
        ->patch(route('admin.contact-messages.status', $message), [
            'status' => ContactMessage::STATUS_OPEN,
        ])
        ->assertRedirect();

    expect($message->refresh()->status)->toBe(ContactMessage::STATUS_OPEN);
});

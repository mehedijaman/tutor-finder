<?php

use App\Models\Faq;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('authorized admin can create faq', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('faq-create');

    $this->actingAs($admin)
        ->post(route('admin.faqs.store'), [
            'question' => 'How do I apply as tutor?',
            'answer' => '<p>Complete your profile and submit required details.</p>',
            'audience' => Faq::AUDIENCE_TUTOR,
            'status' => Faq::STATUS_ACTIVE,
            'sort_order' => 1,
        ])
        ->assertRedirect(route('admin.faqs.index', absolute: false));

    $this->assertDatabaseHas('faqs', [
        'question' => 'How do I apply as tutor?',
        'audience' => Faq::AUDIENCE_TUTOR,
        'status' => Faq::STATUS_ACTIVE,
        'sort_order' => 1,
    ]);
});

it('unauthorized admin cannot access faq routes', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $faq = Faq::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.faqs.index'))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('admin.faqs.store'), [
            'question' => 'Unauthorized create',
            'answer' => '<p>Blocked</p>',
            'audience' => Faq::AUDIENCE_BOTH,
            'status' => Faq::STATUS_ACTIVE,
            'sort_order' => 0,
        ])
        ->assertForbidden();

    $this->actingAs($admin)
        ->patch(route('admin.faqs.status', $faq), ['status' => Faq::STATUS_INACTIVE])
        ->assertForbidden();
});

it('soft delete moves faq to trash list and hides it from active list', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $faq = Faq::factory()->create([
        'status' => Faq::STATUS_ACTIVE,
    ]);

    $this->actingAs($admin)
        ->delete(route('admin.faqs.destroy', $faq))
        ->assertRedirect();

    expect(Faq::withTrashed()->findOrFail($faq->id)->trashed())->toBeTrue();

    $this->actingAs($admin)
        ->get(route('admin.faqs.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/faqs/Index')
            ->where('filters.trash', false)
            ->has('items.data', 0));

    $this->actingAs($admin)
        ->get(route('admin.faqs.index', ['trash' => 1]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/faqs/Index')
            ->where('filters.trash', true)
            ->has('items.data', 1)
            ->where('items.data.0.id', $faq->id));
});

it('restore returns faq from trash', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $faq = Faq::factory()->create();
    $faq->delete();

    $this->actingAs($admin)
        ->patch(route('admin.faqs.restore', $faq->id))
        ->assertRedirect();

    expect(Faq::withTrashed()->findOrFail($faq->id)->trashed())->toBeFalse();
});

it('force delete permanently removes faq', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $faq = Faq::factory()->create();
    $faq->delete();

    $this->actingAs($admin)
        ->delete(route('admin.faqs.force-delete', $faq->id))
        ->assertRedirect();

    expect(Faq::withTrashed()->find($faq->id))->toBeNull();
});

it('authorized admin can toggle faq status', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('faq-update');

    $faq = Faq::factory()->create([
        'status' => Faq::STATUS_ACTIVE,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.faqs.status', $faq), [
            'status' => Faq::STATUS_INACTIVE,
        ])
        ->assertRedirect();

    expect($faq->refresh()->status)->toBe(Faq::STATUS_INACTIVE);
});

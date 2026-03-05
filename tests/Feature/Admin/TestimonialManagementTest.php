<?php

use App\Enums\TaxonomyStatus;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    $this->admin = User::factory()->admin()->create();
    $this->admin->assignRole('super-admin');
});

it('admin can view testimonial index and recycle bin', function () {
    Testimonial::factory()->count(2)->create([
        'status' => TaxonomyStatus::Active,
    ]);

    $trashed = Testimonial::factory()->create([
        'status' => TaxonomyStatus::Active,
    ]);
    $trashed->delete();

    $this->actingAs($this->admin)
        ->get(route('admin.testimonials.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/testimonials/Index')
            ->where('filters.trash', false)
            ->where('counts.active', 2)
            ->where('counts.trash', 1)
            ->has('items.data', 2),
        );

    $this->actingAs($this->admin)
        ->get(route('admin.testimonials.index', ['trash' => 1]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/testimonials/Index')
            ->where('filters.trash', true)
            ->has('items.data', 1)
            ->where('items.data.0.id', $trashed->id),
        );
});

it('admin can create and update a testimonial', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.store'), [
            'name' => 'Guardian One',
            'role' => 'Guardian',
            'avatar_url' => 'https://example.com/avatar.jpg',
            'content' => 'Great tutor matching process.',
            'rating' => 5,
            'status' => TaxonomyStatus::Active->value,
            'sort_order' => 2,
        ])
        ->assertRedirect(route('admin.testimonials.index', absolute: false));

    $testimonial = Testimonial::query()
        ->where('name', 'Guardian One')
        ->first();

    expect($testimonial)->not->toBeNull();
    expect($testimonial?->status)->toBe(TaxonomyStatus::Active);

    $this->actingAs($this->admin)
        ->put(route('admin.testimonials.update', $testimonial), [
            'name' => 'Guardian Updated',
            'role' => 'Parent',
            'avatar_url' => 'https://example.com/avatar-updated.jpg',
            'content' => 'Updated feedback content.',
            'rating' => 4,
            'status' => TaxonomyStatus::Inactive->value,
            'sort_order' => 5,
        ])
        ->assertRedirect(route('admin.testimonials.index', absolute: false));

    $testimonial?->refresh();

    expect($testimonial?->name)->toBe('Guardian Updated');
    expect($testimonial?->status)->toBe(TaxonomyStatus::Inactive);
    expect($testimonial?->rating)->toBe(4);
});

it('admin can restore, force delete, and empty testimonial recycle bin', function () {
    $first = Testimonial::factory()->create();
    $second = Testimonial::factory()->create();
    $third = Testimonial::factory()->create();

    $first->delete();
    $second->delete();
    $third->delete();

    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.restore', $first->id))
        ->assertRedirect();

    expect(Testimonial::withTrashed()->findOrFail($first->id)->trashed())->toBeFalse();

    $this->actingAs($this->admin)
        ->delete(route('admin.testimonials.forceDelete', $second->id))
        ->assertRedirect();

    expect(Testimonial::withTrashed()->find($second->id))->toBeNull();

    $this->actingAs($this->admin)
        ->delete(route('admin.testimonials.empty-recycle-bin'))
        ->assertRedirect();

    expect(Testimonial::withTrashed()->findOrFail($first->id)->trashed())->toBeFalse();
    expect(Testimonial::withTrashed()->find($third->id))->toBeNull();
});

it('admin can upload and remove testimonial avatar with media library', function () {
    Storage::fake('public');
    config(['media-library.disk_name' => 'public']);

    $this->actingAs($this->admin)
        ->post(route('admin.testimonials.store'), [
            'name' => 'Avatar Tester',
            'role' => 'Guardian',
            'content' => 'Avatar upload check',
            'rating' => 5,
            'status' => TaxonomyStatus::Active->value,
            'sort_order' => 1,
            'avatar' => UploadedFile::fake()->image('avatar.jpg', 300, 300),
        ])
        ->assertRedirect(route('admin.testimonials.index', absolute: false));

    $testimonial = Testimonial::query()
        ->where('name', 'Avatar Tester')
        ->firstOrFail();

    expect($testimonial->getFirstMediaUrl('avatar'))->not->toBeEmpty();

    $this->actingAs($this->admin)
        ->put(route('admin.testimonials.update', $testimonial), [
            'name' => $testimonial->name,
            'role' => $testimonial->role,
            'avatar_url' => $testimonial->avatar_url,
            'content' => $testimonial->content,
            'rating' => $testimonial->rating,
            'status' => TaxonomyStatus::Active->value,
            'sort_order' => $testimonial->sort_order,
            'remove_avatar' => true,
        ])
        ->assertRedirect(route('admin.testimonials.index', absolute: false));

    $testimonial->refresh();

    expect($testimonial->getFirstMediaUrl('avatar'))->toBeEmpty();
});

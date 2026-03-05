<?php

use App\Models\Testimonial;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    if (! Role::where('name', 'admin')->exists()) {
        Role::create(['name' => 'admin']);
    }
    // Grant all permissions to admin role
    $permissions = [
        'review-view', 'review-update', 'review-delete', 'review-restore', 'review-force-delete',
        'testimonial-view', 'testimonial-create', 'testimonial-update', 'testimonial-delete', 'testimonial-restore', 'testimonial-force-delete',
    ];
    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm]);
    }
    $role = Role::where('name', 'admin')->first();
    $role->syncPermissions(Permission::all());

    $this->admin = User::factory()->create([
        'role' => 'admin', // force string, not enum
        'status' => 'active', // force string, not enum
    ]);
    $this->admin->assignRole('admin');
    $this->actingAs($this->admin);
});

it('can list testimonials', function () {
    Testimonial::factory()->count(2)->create();

    $this->get('/admin/testimonials')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/testimonials/Index')
            ->has('items.data', 2));
});

it('can create a testimonial', function () {
    $data = Testimonial::factory()->make()->toArray();
    $data['status'] = 'active';
    $response = $this->post('/admin/testimonials', $data);
    $response->assertRedirect('/admin/testimonials');
    $this->assertDatabaseHas('testimonials', ['name' => $data['name']]);
});

it('can update a testimonial', function () {
    $testimonial = Testimonial::factory()->create();
    $response = $this->put("/admin/testimonials/{$testimonial->id}", [
        'name' => 'Updated Name',
        'role' => $testimonial->role,
        'avatar_url' => $testimonial->avatar_url,
        'content' => $testimonial->content,
        'rating' => $testimonial->rating,
        'status' => 'active',
        'sort_order' => $testimonial->sort_order,
        'user_id' => $testimonial->user_id,
    ]);
    $response->assertRedirect('/admin/testimonials');
    $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'name' => 'Updated Name']);
});

it('can soft delete and restore a testimonial', function () {
    $testimonial = Testimonial::factory()->create();
    $this->delete("/admin/testimonials/{$testimonial->id}");
    $this->assertSoftDeleted('testimonials', ['id' => $testimonial->id]);
    $this->post("/admin/testimonials/{$testimonial->id}/restore");
    $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'deleted_at' => null]);
});

it('can force delete a testimonial', function () {
    $testimonial = Testimonial::factory()->create();
    $testimonial->delete();
    $this->delete("/admin/testimonials/{$testimonial->id}/force");
    $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
});

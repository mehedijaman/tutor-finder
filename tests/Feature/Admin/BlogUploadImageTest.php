<?php

use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('uploads a valid blog editor image and returns file url and path', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    Storage::fake('public');

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)
        ->post(route('admin.blog.uploads.images'), [
            'image' => UploadedFile::fake()->image('editor-image.jpg', 640, 360),
        ], [
            'Accept' => 'application/json',
        ]);

    $response
        ->assertSuccessful()
        ->assertJsonStructure([
            'url',
            'path',
        ]);

    $path = $response->json('path');

    expect($path)
        ->toBeString()
        ->toStartWith('blog/uploads/'.now()->format('Y/m').'/');

    Storage::disk('public')->assertExists($path);
    expect((string) $response->json('url'))->toContain('/storage/');
});

it('rejects invalid non-image file upload for editor endpoint', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $response = $this->actingAs($admin)
        ->post(route('admin.blog.uploads.images'), [
            'image' => UploadedFile::fake()->create('malicious.pdf', 32, 'application/pdf'),
        ], [
            'Accept' => 'application/json',
        ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image']);
});

<?php

use App\Models\User;
use Database\Seeders\AdminRolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

it('admin can view backup management page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('admin.backups.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/backups/Index')
            ->has('destinations')
            ->has('backupFiles')
            ->has('stats')
            ->where('permissions.can_run', true)
            ->where('permissions.can_clean', true),
        );
});

it('admin without backup-view permission cannot view backup management page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.backups.index'))
        ->assertForbidden();
});

it('authorized admin can run backup command from backup management page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('backup-run');

    Artisan::shouldReceive('call')
        ->once()
        ->withArgs(function (string $command, array $options): bool {
            return $command === 'backup:run'
                && ($options['--disable-notifications'] ?? false) === true;
        })
        ->andReturn(0);
    Artisan::shouldReceive('output')
        ->once()
        ->andReturn('Backup completed!');

    $response = $this->actingAs($admin)
        ->post(route('admin.backups.run'));

    $response->assertRedirect(route('admin.backups.index', absolute: false));
    $response->assertSessionHas('status', fn (string $message): bool => str_starts_with($message, 'Backup completed!'));
});

it('falls back to files-only backup when sqlite3 binary is missing', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);
    config([
        'database.default' => 'sqlite',
    ]);

    $originalPath = getenv('PATH') ?: '';

    try {
        putenv('PATH=');
        $_SERVER['PATH'] = '';

        $admin = User::factory()->admin()->create();
        $admin->givePermissionTo('backup-run');

        Artisan::shouldReceive('call')
            ->once()
            ->with('backup:run', ['--disable-notifications' => true, '--only-files' => true])
            ->andReturn(0);
        Artisan::shouldReceive('output')
            ->once()
            ->andReturn('Backup completed!');

        $response = $this->actingAs($admin)
            ->post(route('admin.backups.run'));

        $response->assertRedirect(route('admin.backups.index', absolute: false));
        $response->assertSessionHas('status', 'Backup completed! Database dump was skipped because sqlite3 is not installed on this server.');
    } finally {
        putenv("PATH={$originalPath}");
        $_SERVER['PATH'] = $originalPath;
    }
});

it('authorized admin can run backup cleanup command from backup management page', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('backup-clean');

    Artisan::shouldReceive('call')
        ->once()
        ->with('backup:clean', ['--disable-notifications' => true])
        ->andReturn(0);
    Artisan::shouldReceive('output')
        ->once()
        ->andReturn('Cleanup completed!');

    $response = $this->actingAs($admin)
        ->post(route('admin.backups.clean'));

    $response->assertRedirect(route('admin.backups.index', absolute: false));
    $response->assertSessionHas('status', 'Cleanup completed!');
});

it('authorized admin can download backup file', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('backup-download');

    $backupName = (string) config('backup.monitor_backups.0.name');
    $fileName = 'test-backup.zip';
    $path = "{$backupName}/{$fileName}";
    $content = 'dummy backup content';

    Storage::disk('local')->put($path, $content);

    $response = $this->actingAs($admin)
        ->get(route('admin.backups.download', [
            'disk' => 'local',
            'backup_name' => $backupName,
            'path' => $path,
        ]));

    $response->assertSuccessful();
    expect($response->headers->get('content-disposition'))->toContain($fileName);
    expect($response->streamedContent())->toBe($content);

    Storage::disk('local')->delete($path);
});

it('admin without backup-download permission cannot download backup file', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $backupName = (string) config('backup.monitor_backups.0.name');

    $this->actingAs($admin)
        ->get(route('admin.backups.download', [
            'disk' => 'local',
            'backup_name' => $backupName,
            'path' => "{$backupName}/test-backup.zip",
        ]))
        ->assertForbidden();
});

it('authorized admin can delete backup file', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $admin->givePermissionTo('backup-delete');

    $backupName = (string) config('backup.monitor_backups.0.name');
    $fileName = 'delete-backup.zip';
    $path = "{$backupName}/{$fileName}";

    Storage::disk('local')->put($path, 'delete me');

    $response = $this->actingAs($admin)
        ->delete(route('admin.backups.destroy'), [
            'disk' => 'local',
            'backup_name' => $backupName,
            'path' => $path,
        ]);

    $response->assertRedirect(route('admin.backups.index', absolute: false));
    $response->assertSessionHas('status', "Backup file {$fileName} deleted successfully.");
    expect(Storage::disk('local')->exists($path))->toBeFalse();
});

it('admin without backup-delete permission cannot delete backup file', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();
    $backupName = (string) config('backup.monitor_backups.0.name');

    $this->actingAs($admin)
        ->delete(route('admin.backups.destroy'), [
            'disk' => 'local',
            'backup_name' => $backupName,
            'path' => "{$backupName}/delete-backup.zip",
        ])
        ->assertForbidden();
});

it('admin without backup-run permission cannot execute backup command', function () {
    $this->seed(AdminRolesAndPermissionsSeeder::class);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.backups.run'))
        ->assertForbidden();
});

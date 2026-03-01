<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BackupFileActionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ViewErrorBag;
use Inertia\Response;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\Config\Config;
use Spatie\Backup\Helpers\Format;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatus;
use Spatie\Backup\Tasks\Monitor\BackupDestinationStatusFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\ExecutableFinder;

class BackupManagementController extends Controller
{
    /**
     * Display backup destination health and available backup files.
     */
    public function index(Request $request): Response
    {
        $statuses = BackupDestinationStatusFactory::createForMonitorConfig(
            app(Config::class)->monitoredBackups
        );

        $destinations = $statuses->map(function (BackupDestinationStatus $status): array {
            $destination = $status->backupDestination();
            $newestBackup = $destination->newestBackup();
            $isHealthy = $status->isHealthy();

            return [
                'backup_name' => $destination->backupName(),
                'disk' => $destination->diskName(),
                'reachable' => $destination->isReachable(),
                'healthy' => $isHealthy,
                'total_backups' => $destination->backups()->count(),
                'newest_backup_at' => $newestBackup?->date()->toDateTimeString(),
                'newest_backup_age' => $newestBackup ? Format::ageInDays($newestBackup->date()) : 'No backups present',
                'newest_backup_size' => $newestBackup ? Format::humanReadableSize($newestBackup->sizeInBytes()) : '—',
                'used_storage' => Format::humanReadableSize($destination->usedStorage()),
                'failures' => $status->failureMessages()->values()->all(),
            ];
        })->values();

        $backupFiles = $statuses
            ->flatMap(function (BackupDestinationStatus $status) {
                $destination = $status->backupDestination();

                return $destination->backups()->map(function (Backup $backup) use ($destination): array {
                    return [
                        'name' => basename($backup->path()),
                        'path' => $backup->path(),
                        'disk' => $destination->diskName(),
                        'backup_name' => $destination->backupName(),
                        'size' => Format::humanReadableSize($backup->sizeInBytes()),
                        'created_at' => $backup->date()->toDateTimeString(),
                        'age' => Format::ageInDays($backup->date()),
                    ];
                });
            })
            ->sortByDesc('created_at')
            ->values();

        $totalStorageInBytes = $statuses
            ->sum(fn (BackupDestinationStatus $status): float => $status->backupDestination()->usedStorage());

        return inertia('admin/backups/Index', [
            'destinations' => $destinations,
            'backupFiles' => $backupFiles,
            'stats' => [
                'destinations_count' => $destinations->count(),
                'healthy_count' => $destinations->where('healthy', true)->count(),
                'unhealthy_count' => $destinations->where('healthy', false)->count(),
                'backup_files_count' => $backupFiles->count(),
                'total_storage' => Format::humanReadableSize($totalStorageInBytes),
            ],
            'permissions' => [
                'can_run' => $request->user()?->can('backup-run') ?? false,
                'can_clean' => $request->user()?->can('backup-clean') ?? false,
                'can_download' => $request->user()?->can('backup-download') ?? false,
                'can_delete' => $request->user()?->can('backup-delete') ?? false,
            ],
            'resultMessage' => session('status'),
            'errorMessage' => $this->resolveBackupError($request),
        ]);
    }

    /**
     * Run a full backup via the installed Spatie backup command.
     */
    public function run(): RedirectResponse
    {
        $backupCommandOptions = [
            '--disable-notifications' => true,
        ];
        $isFilesOnlyFallback = $this->shouldRunFilesOnlyBackup();

        if ($isFilesOnlyFallback) {
            $backupCommandOptions['--only-files'] = true;
        }

        $exitCode = Artisan::call('backup:run', $backupCommandOptions);

        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return redirect()
                ->route('admin.backups.index')
                ->withErrors([
                    'backup' => $output !== '' ? $output : 'Backup command failed.',
                ]);
        }

        $statusMessage = $output !== '' ? $output : 'Backup completed successfully.';

        if ($isFilesOnlyFallback) {
            $statusMessage .= ' Database dump was skipped because sqlite3 is not installed on this server.';
        }

        return redirect()
            ->route('admin.backups.index')
            ->with('status', $statusMessage);
    }

    /**
     * Clean old backups using the configured retention strategy.
     */
    public function clean(): RedirectResponse
    {
        $exitCode = Artisan::call('backup:clean', [
            '--disable-notifications' => true,
        ]);

        $output = trim(Artisan::output());

        if ($exitCode !== 0) {
            return redirect()
                ->route('admin.backups.index')
                ->withErrors([
                    'backup' => $output !== '' ? $output : 'Backup cleanup command failed.',
                ]);
        }

        return redirect()
            ->route('admin.backups.index')
            ->with('status', $output !== '' ? $output : 'Backup cleanup completed successfully.');
    }

    /**
     * Download a specific backup file.
     */
    public function download(BackupFileActionRequest $request): StreamedResponse
    {
        $validated = $request->validated();

        $backup = $this->findBackup(
            (string) $validated['disk'],
            (string) $validated['backup_name'],
            (string) $validated['path'],
        );

        abort_unless($backup instanceof Backup, 404);

        $stream = $backup->stream();
        $fileName = basename($backup->path());

        return response()->streamDownload(function () use ($stream): void {
            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $fileName, [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Delete a specific backup file.
     */
    public function destroy(BackupFileActionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $backup = $this->findBackup(
            (string) $validated['disk'],
            (string) $validated['backup_name'],
            (string) $validated['path'],
        );

        abort_unless($backup instanceof Backup, 404);

        $fileName = basename($backup->path());

        $backup->delete();

        if ($backup->exists()) {
            return redirect()
                ->route('admin.backups.index')
                ->withErrors([
                    'backup' => 'Backup file could not be deleted.',
                ]);
        }

        return redirect()
            ->route('admin.backups.index')
            ->with('status', "Backup file {$fileName} deleted successfully.");
    }

    /**
     * Resolve backup-related error message from the session error bag.
     */
    private function resolveBackupError(Request $request): ?string
    {
        $errors = $request->session()->get('errors');

        if (! $errors instanceof ViewErrorBag) {
            return null;
        }

        if (! $errors->has('backup')) {
            return null;
        }

        return $errors->first('backup');
    }

    /**
     * Determine whether we should fallback to files-only backup for SQLite.
     */
    private function shouldRunFilesOnlyBackup(): bool
    {
        $defaultConnection = (string) config('database.default');
        $driver = config("database.connections.{$defaultConnection}.driver");

        if ($driver !== 'sqlite') {
            return false;
        }

        return $this->findSqliteBinaryPath() === null;
    }

    /**
     * Locate sqlite3 binary path.
     */
    private function findSqliteBinaryPath(): ?string
    {
        return (new ExecutableFinder)->find('sqlite3');
    }

    /**
     * Find a backup file by destination details.
     */
    private function findBackup(string $disk, string $backupName, string $path): ?Backup
    {
        $statuses = $this->backupStatuses();

        foreach ($statuses as $status) {
            $destination = $status->backupDestination();

            if ($destination->diskName() !== $disk || $destination->backupName() !== $backupName) {
                continue;
            }

            $backup = $destination->backups()->first(
                fn (Backup $candidate): bool => $candidate->path() === $path
            );

            if ($backup instanceof Backup) {
                return $backup;
            }
        }

        return null;
    }

    /**
     * Resolve backup statuses from monitor config.
     *
     * @return Collection<int, BackupDestinationStatus>
     */
    private function backupStatuses(): Collection
    {
        return BackupDestinationStatusFactory::createForMonitorConfig(
            app(Config::class)->monitoredBackups
        );
    }
}

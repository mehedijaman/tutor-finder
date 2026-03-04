<?php

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\OtpRequest;
use Illuminate\Console\Command;

class CleanupExpiredRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:cleanup
                            {--dry-run : Show what would be cleaned without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired OTP requests and mark expired invoices';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in dry-run mode. No changes will be made.');
        }

        $this->cleanupExpiredOtpRequests($dryRun);
        $this->markExpiredInvoices($dryRun);

        $this->newLine();
        $this->info('Cleanup completed.');

        return self::SUCCESS;
    }

    /**
     * Delete expired OTP requests.
     */
    private function cleanupExpiredOtpRequests(bool $dryRun): void
    {
        $expiredCount = OtpRequest::query()
            ->where('expires_at', '<', now())
            ->count();

        if ($expiredCount === 0) {
            $this->line('No expired OTP requests to clean up.');

            return;
        }

        if ($dryRun) {
            $this->info("Would delete {$expiredCount} expired OTP requests.");

            return;
        }

        $deleted = OtpRequest::query()
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Deleted {$deleted} expired OTP requests.");
    }

    /**
     * Mark expired unpaid invoices as expired.
     */
    private function markExpiredInvoices(bool $dryRun): void
    {
        $expiredQuery = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid, InvoiceStatus::Draft])
            ->where('expires_at', '<', now());

        $expiredCount = $expiredQuery->count();

        if ($expiredCount === 0) {
            $this->line('No expired invoices to update.');

            return;
        }

        if ($dryRun) {
            $this->info("Would mark {$expiredCount} invoices as expired.");

            return;
        }

        $updated = Invoice::query()
            ->whereIn('status', [InvoiceStatus::Unpaid, InvoiceStatus::Draft])
            ->where('expires_at', '<', now())
            ->update(['status' => InvoiceStatus::Expired]);

        $this->info("Marked {$updated} invoices as expired.");
    }
}

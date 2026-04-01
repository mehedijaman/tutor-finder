<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addIndexIfNotExists('tuition_jobs', 'guardian_id');
        $this->addIndexIfNotExists('tuition_jobs', 'city_id');
        $this->addIndexIfNotExists('tuition_jobs', 'published_at');

        $this->addIndexIfNotExists('tuition_job_applications', 'job_id');
        $this->addIndexIfNotExists('tuition_job_applications', 'tutor_user_id');

        $this->addIndexIfNotExists('invoices', 'payer_user_id');
        $this->addIndexIfNotExists('invoices', 'payee_user_id');
        $this->addIndexIfNotExists('invoices', 'payment_reference');

        $this->addIndexIfNotExists('payments', 'provider_txn_id');

        $this->addIndexIfNotExists('wallet_ledger_entries', 'owner_user_id');
        $this->addIndexIfNotExists('wallet_ledger_entries', 'journal_uuid');

        $this->addIndexIfNotExists('refund_requests', 'job_assignment_id');
        $this->addIndexIfNotExists('refund_requests', 'requested_by_user_id');

        $this->addIndexIfNotExists('support_tickets', 'assigned_to');

        $this->addIndexIfNotExists('verification_requests', 'user_id');

        $this->addIndexIfNotExists('otp_requests', 'phone');
        $this->addIndexIfNotExists('otp_requests', 'expires_at');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function addIndexIfNotExists(string $table, string $column): void
    {
        $indexName = $table.'_'.$column.'_index';
        $connection = DB::connection()->getDriverName();

        if ($connection === 'sqlite') {
            $exists = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            if (! empty($exists)) {
                return;
            }

            Schema::table($table, function (Blueprint $table) use ($column): void {
                $table->index($column);
            });
        } else {
            Schema::table($table, function (Blueprint $table) use ($column): void {
                $table->index($column);
            });
        }
    }
};

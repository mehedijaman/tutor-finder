<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wallet_ledger_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('journal_uuid');
            $table->foreignId('owner_user_id')->constrained('users')->restrictOnDelete();
            $table->string('type', 20);
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('BDT');
            $table->string('reference_type', 40);
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('counterparty_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->boolean('is_reversal')->default(false);
            $table->uuid('reverses_journal_uuid')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('journal_uuid');
            $table->index(['owner_user_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['journal_uuid', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_ledger_entries');
    }
};

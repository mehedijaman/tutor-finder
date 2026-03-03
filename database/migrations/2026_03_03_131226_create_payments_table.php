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
        $driver = DB::getDriverName();

        Schema::create('payments', function (Blueprint $table) use ($driver): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('gateway', 30);
            $table->string('provider_txn_id', 120)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('pending');
            $table->json('provider_payload')->nullable();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $table->unsignedTinyInteger('pending_guard')
                    ->nullable()
                    ->storedAs("case when `status` = 'pending' then 1 else null end");
            } else {
                $table->unsignedTinyInteger('pending_guard')->nullable();
            }

            $table->timestamps();

            $table->index(['invoice_id', 'status']);
            $table->index(['invoice_id', 'created_at']);
            $table->unique(['gateway', 'provider_txn_id']);
            $table->unique(['invoice_id', 'pending_guard']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

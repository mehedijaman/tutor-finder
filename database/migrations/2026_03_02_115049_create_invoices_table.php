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
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->string('invoice_no', 50)->unique();
            $table->string('invoiceable_type');
            $table->unsignedBigInteger('invoiceable_id');
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('BDT');
            $table->string('status', 30)->default('unpaid')->index();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_gateway', 30)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 100)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->json('gateway_payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoiceable_type', 'invoiceable_id']);
            $table->index('transaction_id');
            $table->index('expires_at');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

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
        Schema::create('tuition_job_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tuition_job_id')->constrained('tuition_jobs')->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained('users')->restrictOnDelete();
            $table->text('cover_letter')->nullable();
            $table->decimal('expected_salary', 12, 2)->nullable();
            $table->enum('status', ['pending', 'shortlisted', 'rejected', 'withdrawn'])->default('pending');
            $table->text('guardian_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['tuition_job_id', 'tutor_id']);
            $table->index('status');
            $table->index(['tutor_id', 'status']);
            $table->index(['tuition_job_id', 'status']);
        });

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'pgsql'], true)) {
            DB::statement("ALTER TABLE tuition_job_applications ADD CONSTRAINT tuition_job_applications_status_check CHECK (status IN ('pending','shortlisted','rejected','withdrawn'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuition_job_applications');
    }
};

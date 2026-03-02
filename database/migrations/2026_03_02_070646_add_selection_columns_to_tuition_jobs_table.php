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
        Schema::table('tuition_jobs', function (Blueprint $table): void {
            $table->foreignId('selected_tutor_id')
                ->nullable()
                ->after('guardian_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('selected_application_id')
                ->nullable()
                ->after('selected_tutor_id')
                ->constrained('tuition_job_applications')
                ->nullOnDelete();

            $table->index('selected_tutor_id');
            $table->index('selected_application_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tuition_jobs', function (Blueprint $table): void {
            $table->dropIndex(['selected_tutor_id']);
            $table->dropIndex(['selected_application_id']);
            $table->dropConstrainedForeignId('selected_application_id');
            $table->dropConstrainedForeignId('selected_tutor_id');
        });
    }
};

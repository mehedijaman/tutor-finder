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
        Schema::create('tutor_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('guardian_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_assignment_id')->constrained('tuition_job_assignments')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('job_assignment_id');
            $table->index(['tutor_user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_reviews');
    }
};

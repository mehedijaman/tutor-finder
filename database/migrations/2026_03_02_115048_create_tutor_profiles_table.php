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
        Schema::create('tutor_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('gender', 30)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('present_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('nid_no')->nullable();
            $table->text('bio')->nullable();
            $table->json('preferred_tuition_types')->nullable();
            $table->json('preferred_categories')->nullable();
            $table->json('preferred_classes')->nullable();
            $table->json('preferred_subjects')->nullable();
            $table->json('preferred_locations')->nullable();
            $table->decimal('expected_salary_min', 12, 2)->nullable();
            $table->decimal('expected_salary_max', 12, 2)->nullable();
            $table->json('available_days')->nullable();
            $table->string('available_time', 100)->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutor_profiles');
    }
};

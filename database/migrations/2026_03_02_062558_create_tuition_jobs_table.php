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
        Schema::create('tuition_jobs', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->longText('description');

            $table->foreignId('tuition_type_id')->constrained('tuition_types')->restrictOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $table->foreignId('city_id')->constrained('cities')->restrictOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->restrictOnDelete();
            $table->foreignId('guardian_id')->constrained('users')->restrictOnDelete();

            $table->string('location', 255)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->enum('student_gender', ['male', 'female', 'any'])->default('any')->index();
            $table->enum('tutor_gender', ['male', 'female', 'any'])->default('any')->index();

            $table->json('tuition_days')->nullable();
            $table->unsignedTinyInteger('days_per_week')->nullable();
            $table->string('tuition_time', 100)->nullable();
            $table->string('tuition_duration', 100)->nullable();

            $table->unsignedSmallInteger('no_of_students')->nullable();

            $table->decimal('salary_amount', 12, 2)->nullable();
            $table->string('salary_currency', 10)->default('BDT');
            $table->boolean('salary_negotiable')->default(false);

            $table->enum('status', ['pending', 'live', 'confirmed', 'cancelled', 'closed'])->default('pending');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();

            $table->unsignedBigInteger('view_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('category_id');
            $table->index('class_id');
            $table->index(['country_id', 'city_id']);
            $table->index('days_per_week');
            $table->index('published_at');
            $table->index('expires_at');
            $table->index(['status', 'published_at']);
            $table->index(['status', 'expires_at']);
            $table->index(['guardian_id', 'status']);
            $table->index('deleted_at');
        });

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'pgsql'], true)) {
            DB::statement("ALTER TABLE tuition_jobs ADD CONSTRAINT tuition_jobs_status_check CHECK (status IN ('pending','live','confirmed','cancelled','closed'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tuition_jobs');
    }
};

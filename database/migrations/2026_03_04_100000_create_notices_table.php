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
        Schema::create('notices', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 180);
            $table->longText('body');
            $table->string('audience', 20)->default('both')->index();
            $table->dateTime('expires_at')->nullable()->index();
            $table->dateTime('published_at')->useCurrent()->index();
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['audience', 'is_active', 'expires_at']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};

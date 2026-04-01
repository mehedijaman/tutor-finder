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
        Schema::table('tuition_jobs', function (Blueprint $table) {
            $table->foreignId('requested_tutor_id')->nullable()->after('guardian_id')->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable()->after('requested_tutor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tuition_jobs', function (Blueprint $table) {
            $table->dropForeign(['requested_tutor_id']);
            $table->dropColumn(['requested_tutor_id', 'requested_at']);
        });
    }
};

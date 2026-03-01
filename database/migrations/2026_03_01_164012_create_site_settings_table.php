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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name');
            $table->string('slogan')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->json('phone_numbers')->nullable();
            $table->json('emails')->nullable();
            $table->json('addresses')->nullable();
            $table->json('social_details')->nullable();
            $table->string('trade_licence_no')->nullable();
            $table->string('tin_no')->nullable();
            $table->string('bin_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

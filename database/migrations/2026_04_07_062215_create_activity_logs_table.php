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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable(); 
            $table->string('description');

            // 1. the subject of the activity (e.g., Company, User, Product)
            $table->nullableMorphs('subject');

            // 2. the actor (the user or admin who performed the action)
            $table->nullableMorphs('causer');

            // Save old and new data or any additional details (JSON)
            $table->json('properties')->nullable();

            // IP address and user agent for auditing purposes
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->enum('type', ['exporter', 'importer', 'both'])->default('exporter'); // 'exporter', 'importer', 'both'
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->json('extra_details')->nullable(); // for any additional information that doesn't fit into the predefined columns
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending'); // to track the verification status of the company
            $table->timestamp('verified_at')->nullable(); // to track when the company was verified
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

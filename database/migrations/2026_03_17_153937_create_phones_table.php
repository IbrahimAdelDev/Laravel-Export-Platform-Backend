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
        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->enum('label', ['main', 'sales', 'whatsapp', 'support'])->default('main'); // optional label to identify the type of phone number
            // Polymorphic relationship fields
            // phoneable_id and phoneable_type will be used to associate the phone with either a User or a Company
            $table->morphs('phoneable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};

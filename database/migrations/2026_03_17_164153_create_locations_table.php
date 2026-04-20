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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('type')->nullable(); 
            $table->foreignId('country_id')->constrained('countries')->onDelete('cascade');
            $table->string('city')->nullable();
            $table->string('address');
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lon', 11, 8)->nullable();
            $table->double('area')->nullable();
            $table->integer('floor')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};

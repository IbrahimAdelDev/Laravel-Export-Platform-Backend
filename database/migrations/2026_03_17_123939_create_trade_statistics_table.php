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
        Schema::create('trade_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('destination_country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade'); // Initially optional
            $table->integer('year');
            $table->enum('unit', ['Ton', 'kg', 'liter', 'piece'])->default('Ton'); // 'Ton', 'kg', 'liter', 'piece', etc.
            $table->decimal('quantity', 15, 4);
            $table->decimal('value_million_usd', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_statistics');
    }
};

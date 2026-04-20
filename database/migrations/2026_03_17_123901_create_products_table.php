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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('hs_code')->unique();
            $table->string('name_ar')->nullable();
            $table->string('name_en')->nullable();
            $table->enum('category', ['agricultural', 'industrial', 'other']); // 'agricultural', 'industrial', 'other'
            $table->enum('unit', [
                'Carton Box', 
                'Ton', 
                'liter', 
                'meter',
                'Crate', 
                'Metric Ton', 
                'Piece', 
                'Pound', 
                '1000 Sticks', 
                'Wooden Barrel',
                'Uncontainerized', 
                'Canes', 
                'Hectometer',
                'Milliliter',
                ])->default('Ton'); // 'Ton', 'kg', 'liter', 'piece', etc.
            $table->decimal('indicative_price', 10, 2)->nullable();
            $table->enum('price_unit', ['USD', 'EGP', 'EUR', 'GBP'])->default('USD'); // 'USD', 'EGP', 'EUR', 'GBP', etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

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
        Schema::create('export_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origin_country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('destination_country_id')->constrained('countries')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('year');
            $table->enum('export_unit', [
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
                ])->default('Ton');// 'Ton', 'kg', 'liter', 'piece', etc.
            $table->decimal('total_export_quantity', 15, 4);
            $table->decimal('total_export_value', 15, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_statistics');
    }
};

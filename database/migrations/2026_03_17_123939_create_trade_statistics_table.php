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
            $table->tinyInteger('month')->required()->default(0); // 0 for annual data, 1-12 for monthly data
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
                ])->default('Ton');// 'Ton', 'kg', 'liter', 'piece', etc.
            $table->decimal('quantity', 15, 4);
            $table->decimal('value_million_usd', 15, 4);
            $table->timestamps();
            $table->unique(
                ['origin_country_id', 'destination_country_id', 'product_id', 'year', 'month'], 
                'trade_flow_unique'
            );
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

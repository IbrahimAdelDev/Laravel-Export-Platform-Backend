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
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('job_batch_id')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // File information
            $table->string('file_name');
            $table->string('file_path'); // File path in storage
            $table->enum('type', ['export_statistics', 'exporters', 'importers', 'trade_statistics', 'products', 'countries'])->default('export_statistics'); // Type of data being imported
            
            // Status and progress tracking
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending'); // pending, processing, completed, failed
            
            // Progress tracking
            $table->integer('total_rows')->default(0);     // total rows in the uploaded file
            $table->integer('processed_rows')->default(0); // rows that have been processed
            
            // Error log (JSON) to keep track of failed rows and reasons
            $table->json('errors')->nullable(); 

            $table->timestamps();
            $table->timestamp('completed_at')->nullable(); // to track when the import was completed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batches');
    }
};

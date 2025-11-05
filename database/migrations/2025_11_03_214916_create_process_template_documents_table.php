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
        Schema::create('process_template_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->string('google_drive_file_id');
            $table->string('file_name');
            $table->string('local_path')->nullable();
            $table->string('google_docs_name');
            $table->timestamps();

            // Unique constraint: same file_name cannot be created twice for the same process
            $table->unique(['process_id', 'file_name'], 'process_file_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_template_documents');
    }
};

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
        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complainant_id')->constrained();
            $table->foreignId('doctor_id')->constrained();
            $table->foreignId('magistrate_instructor_id')->constrained('magistrates');
            $table->foreignId('magistrate_ponente_id')->constrained('magistrates');
            $table->foreignId('template_id')->nullable()->constrained();
            $table->string('name');
            $table->string('process_number')->unique();
            $table->date('start_date');
            $table->string('status'); // enum: pending, in_progress, closed
            $table->text('description');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};

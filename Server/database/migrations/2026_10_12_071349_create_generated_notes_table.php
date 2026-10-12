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
        Schema::create('generated_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_material_id')
                ->constrained('course_materials')
                ->onDelete('cascade');
            $table->foreignId('generated_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->text('content');
            $table->string('ai_provider', 50); // gemini, groq, etc.
            $table->string('ai_model', 100); // model name/version
            $table->unsignedInteger('source_char_count'); // PDF character count
            $table->unsignedInteger('notes_char_count'); // Generated notes character count
            $table->unsignedInteger('generation_time_ms'); // Time taken in milliseconds
            $table->timestamps();

            // Indexes for performance
            $table->index('course_material_id');
            $table->index('generated_by');
            $table->index('created_at');

            // Unique constraint: one note per material per user
            $table->unique(['course_material_id', 'generated_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_notes');
    }
};

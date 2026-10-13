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
        Schema::create('study_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamp('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Optional links to learning resources (nullable, restrict on delete to preserve tasks)
            $table->foreignId('course_material_id')->nullable()->constrained('course_materials')->onDelete('restrict');
            $table->foreignId('generated_note_id')->nullable()->constrained('generated_notes')->onDelete('restrict');
            $table->foreignId('generated_quiz_id')->nullable()->constrained('generated_quizzes')->onDelete('restrict');

            $table->timestamps();

            // Indexes for filtering
            $table->index('user_id');
            $table->index('completed');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_tasks');
    }
};

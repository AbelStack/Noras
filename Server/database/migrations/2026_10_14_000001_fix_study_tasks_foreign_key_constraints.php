<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_tasks', function (Blueprint $table) {
            // Drop existing restrictive foreign keys
            $table->dropForeign(['course_material_id']);
            $table->dropForeign(['generated_note_id']);
            $table->dropForeign(['generated_quiz_id']);

            // Re-add with nullOnDelete — preserves tasks when resources are removed
            $table->foreign('course_material_id')
                ->references('id')->on('course_materials')
                ->nullOnDelete();
            $table->foreign('generated_note_id')
                ->references('id')->on('generated_notes')
                ->nullOnDelete();
            $table->foreign('generated_quiz_id')
                ->references('id')->on('generated_quizzes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('study_tasks', function (Blueprint $table) {
            $table->dropForeign(['course_material_id']);
            $table->dropForeign(['generated_note_id']);
            $table->dropForeign(['generated_quiz_id']);

            $table->foreign('course_material_id')
                ->references('id')->on('course_materials')
                ->onDelete('restrict');
            $table->foreign('generated_note_id')
                ->references('id')->on('generated_notes')
                ->onDelete('restrict');
            $table->foreign('generated_quiz_id')
                ->references('id')->on('generated_quizzes')
                ->onDelete('restrict');
        });
    }
};

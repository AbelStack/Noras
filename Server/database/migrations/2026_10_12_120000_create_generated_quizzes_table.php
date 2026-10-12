<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_material_id')
                ->constrained('course_materials')
                ->onDelete('cascade');
            $table->integer('material_version');
            $table->foreignId('generated_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->string('ai_provider', 50);
            $table->string('ai_model', 100);
            $table->unsignedInteger('question_count');
            $table->unsignedInteger('source_char_count');
            $table->unsignedInteger('generation_time_ms');
            $table->timestamps();

            $table->index('course_material_id');
            $table->index('generated_by');
            $table->index(['course_material_id', 'material_version']);
            $table->index('created_at');
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')
                ->constrained('generated_quizzes')
                ->onDelete('cascade');
            $table->unsignedInteger('question_order');
            $table->text('question_text');
            $table->text('explanation');
            $table->timestamps();

            $table->index('quiz_id');
            $table->unique(['quiz_id', 'question_order']);
        });

        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')
                ->constrained('quiz_questions')
                ->onDelete('cascade');
            $table->unsignedInteger('option_order');
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->index('question_id');
            $table->unique(['question_id', 'option_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('generated_quizzes');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            $table->foreignId('quiz_id')
                ->constrained('generated_quizzes')
                ->onDelete('cascade');
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->json('answers')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('quiz_id');
            $table->index(['user_id', 'quiz_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};

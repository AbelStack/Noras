<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_offerings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->integer('academic_year');
            $table->integer('study_year')->unsigned();
            $table->enum('semester', ['1', '2']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['course_id', 'department_id', 'academic_year', 'study_year', 'semester'], 'course_offering_unique');
            $table->index('is_active');
            $table->index(['department_id', 'study_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_offerings');
    }
};

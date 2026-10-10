<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_offering_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('original_filename');
            $table->string('stored_filename')->unique();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size'); // bytes
            $table->string('mime_type');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->integer('version')->default(1);
            $table->foreignId('replaces_material_id')->nullable()->constrained('course_materials')->nullOnDelete();
            $table->timestamps();

            $table->index('course_offering_id');
            $table->index('status');
            $table->index(['course_offering_id', 'status']);
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_materials');
    }
};

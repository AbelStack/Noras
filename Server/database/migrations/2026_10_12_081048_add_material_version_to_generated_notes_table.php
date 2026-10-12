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
        Schema::table('generated_notes', function (Blueprint $table) {
            // Drop old unique constraint
            $table->dropUnique(['course_material_id', 'generated_by']);
            
            // Add material version tracking
            $table->integer('material_version')->after('course_material_id');
            
            // Add new unique constraint including version (with shorter name)
            // This allows multiple notes per material (one per version per user)
            $table->unique(['course_material_id', 'material_version', 'generated_by'], 'gen_notes_material_version_user_unique');
            
            // Add index for version queries
            $table->index(['course_material_id', 'material_version'], 'gen_notes_material_version_idx');
        });
        
        // Backfill existing records with current material version
        DB::statement('
            UPDATE generated_notes gn
            JOIN course_materials cm ON gn.course_material_id = cm.id
            SET gn.material_version = cm.version
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generated_notes', function (Blueprint $table) {
            // Drop new unique constraint
            $table->dropUnique('gen_notes_material_version_user_unique');
            $table->dropIndex('gen_notes_material_version_idx');
            $table->dropColumn('material_version');
        });
        
        Schema::table('generated_notes', function (Blueprint $table) {
            // Restore original unique constraint
            $table->unique(['course_material_id', 'generated_by']);
        });
    }
};

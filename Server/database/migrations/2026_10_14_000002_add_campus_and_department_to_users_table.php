<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('university_id')->nullable()->constrained('universities')->nullOnDelete()->after('student_id');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('university_id');
            $table->index(['university_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['university_id']);
            $table->dropForeign(['department_id']);
            $table->dropIndex(['university_id', 'department_id']);
            $table->dropColumn(['university_id', 'department_id']);
        });
    }
};

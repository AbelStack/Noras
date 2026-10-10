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
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_id')->unique()->after('email');
            $table->enum('role', ['student', 'admin'])->default('student')->after('student_id');
            $table->enum('account_status', ['pending', 'approved', 'rejected', 'suspended'])
                ->default('pending')
                ->after('role');
            $table->timestamp('approved_at')->nullable()->after('account_status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('approved_at');
            
            $table->index('student_id');
            $table->index('role');
            $table->index('account_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['account_status']);
            $table->dropColumn(['student_id', 'role', 'account_status', 'approved_at', 'approved_by']);
        });
    }
};

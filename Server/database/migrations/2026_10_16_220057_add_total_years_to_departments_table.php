<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedTinyInteger('total_years')->default(4)->after('code');
        });

        // Initialize known department durations
        DB::table('departments')->where('name', 'like', '%Software Engineering%')->orWhere('code', 'SE')->update(['total_years' => 5]);
        DB::table('departments')->where('name', 'like', '%Medicine%')->orWhere('code', 'MED')->update(['total_years' => 7]);
        DB::table('departments')->where('name', 'like', '%Law%')->orWhere('code', 'LAW')->update(['total_years' => 5]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('total_years');
        });
    }
};

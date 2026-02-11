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
        Schema::table('plots', function (Blueprint $table) {
            if (!Schema::hasColumn('plots', 'type')) {
                $table->string('type')->nullable()->after('category');
            }
        });

        // Backfill `type` from existing `category` values where null
        if (Schema::hasColumn('plots', 'type') && Schema::hasColumn('plots', 'category')) {
            DB::table('plots')->whereNull('type')->update(['type' => DB::raw('category')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            if (Schema::hasColumn('plots', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};

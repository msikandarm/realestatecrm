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
        Schema::table('blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('blocks', 'available_plots')) {
                $table->integer('available_plots')->default(0)->after('total_plots');
            }
            if (!Schema::hasColumn('blocks', 'sold_plots')) {
                $table->integer('sold_plots')->default(0)->after('available_plots');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            foreach (['available_plots', 'sold_plots'] as $col) {
                if (Schema::hasColumn('blocks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

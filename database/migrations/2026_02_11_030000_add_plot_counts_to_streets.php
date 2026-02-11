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
        Schema::table('streets', function (Blueprint $table) {
            if (!Schema::hasColumn('streets', 'total_plots')) {
                // place after description (existing column) to avoid relying on non-existent 'length'
                $table->integer('total_plots')->default(0)->after('description');
            }
            if (!Schema::hasColumn('streets', 'available_plots')) {
                $table->integer('available_plots')->default(0)->after('total_plots');
            }
            if (!Schema::hasColumn('streets', 'sold_plots')) {
                $table->integer('sold_plots')->default(0)->after('available_plots');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streets', function (Blueprint $table) {
            foreach (['total_plots', 'available_plots', 'sold_plots'] as $col) {
                if (Schema::hasColumn('streets', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

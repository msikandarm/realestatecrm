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
        Schema::table('plots', function (Blueprint $table) {
            if (!Schema::hasColumn('plots', 'area')) {
                $table->decimal('area', 12, 2)->nullable()->after('plot_number');
            }
            if (!Schema::hasColumn('plots', 'area_unit')) {
                $table->string('area_unit')->nullable()->after('area');
            }
            if (!Schema::hasColumn('plots', 'price_per_marla')) {
                $table->decimal('price_per_marla', 15, 2)->nullable()->after('area_unit');
            }
            if (!Schema::hasColumn('plots', 'total_price')) {
                $table->decimal('total_price', 15, 2)->nullable()->after('price_per_marla');
            }
            if (!Schema::hasColumn('plots', 'corner')) {
                $table->string('corner')->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('plots', 'park_facing')) {
                $table->string('park_facing')->nullable()->after('corner');
            }
            if (!Schema::hasColumn('plots', 'main_road_facing')) {
                $table->string('main_road_facing')->nullable()->after('park_facing');
            }
            if (!Schema::hasColumn('plots', 'facing')) {
                $table->string('facing')->nullable()->after('main_road_facing');
            }
            if (!Schema::hasColumn('plots', 'description')) {
                $table->text('description')->nullable()->after('facing');
            }
            if (!Schema::hasColumn('plots', 'features')) {
                $table->text('features')->nullable()->after('description');
            }
            if (!Schema::hasColumn('plots', 'plot_code')) {
                $table->string('plot_code')->nullable()->after('plot_number');
            }
            if (!Schema::hasColumn('plots', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plots', function (Blueprint $table) {
            $cols = [
                'area','area_unit','price_per_marla','total_price','corner','park_facing','main_road_facing','facing','description','features','plot_code','updated_by'
            ];
            foreach ($cols as $c) {
                if (Schema::hasColumn('plots', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};

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
        Schema::table('societies', function (Blueprint $table) {
            if (!Schema::hasColumn('societies', 'city_id')) {
                $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null')->after('code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('societies', function (Blueprint $table) {
            if (Schema::hasColumn('societies', 'city_id')) {
                $table->dropForeign([$table->getTable()."_city_id_foreign"] ?? ['city_id']);
                $table->dropColumn('city_id');
            }
        });
    }
};

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
            if (!Schema::hasColumn('streets', 'code')) {
                $table->string('code')->nullable()->after('name');
            }
            if (!Schema::hasColumn('streets', 'width')) {
                $table->decimal('width', 8, 2)->nullable()->after('code');
            }
            if (!Schema::hasColumn('streets', 'length')) {
                $table->decimal('length', 8, 2)->nullable()->after('width');
            }
            if (!Schema::hasColumn('streets', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->after('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('streets', function (Blueprint $table) {
            foreach (['length','width','code'] as $col) {
                if (Schema::hasColumn('streets', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('streets', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
};

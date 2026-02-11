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
            if (!Schema::hasColumn('societies', 'developer_name')) {
                $table->string('developer_name')->nullable()->after('description');
            }
            if (!Schema::hasColumn('societies', 'developer_contact')) {
                $table->string('developer_contact')->nullable()->after('developer_name');
            }
            if (!Schema::hasColumn('societies', 'launch_date')) {
                $table->date('launch_date')->nullable()->after('status');
            }
            if (!Schema::hasColumn('societies', 'completion_date')) {
                $table->date('completion_date')->nullable()->after('launch_date');
            }
            if (!Schema::hasColumn('societies', 'amenities')) {
                $table->json('amenities')->nullable()->after('completion_date');
            }
            if (!Schema::hasColumn('societies', 'map_file')) {
                $table->string('map_file')->nullable()->after('amenities');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('societies', function (Blueprint $table) {
            $cols = ['developer_name', 'developer_contact', 'launch_date', 'completion_date', 'amenities', 'map_file'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('societies', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

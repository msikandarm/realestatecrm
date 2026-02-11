<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('province');
            $table->string('reference_path')->nullable()->after('notes');
        });
    }

    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn(['notes', 'reference_path']);
        });
    }
};

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
        Schema::table('clients', function (Blueprint $table) {
            // Lead conversion tracking
            $table->foreignId('converted_from_lead_id')->nullable()->after('created_by')
                ->constrained('leads')->onDelete('set null');
            $table->timestamp('converted_from_lead_at')->nullable()->after('converted_from_lead_id');
            $table->string('lead_source')->nullable()->after('converted_from_lead_at')
                ->comment('Original lead source: website, facebook, referral, etc');

            // Add index for conversion tracking
            $table->index('converted_from_lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['converted_from_lead_id']);
            $table->dropIndex(['converted_from_lead_id']);
            $table->dropColumn(['converted_from_lead_id', 'converted_from_lead_at', 'lead_source']);
        });
    }
};

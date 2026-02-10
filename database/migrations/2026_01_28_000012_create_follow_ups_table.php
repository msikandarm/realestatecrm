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
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->morphs('followable'); // Can be lead or client

            $table->string('type')->default('call'); // call, meeting, email, sms, whatsapp, site_visit
            $table->string('status')->default('pending'); // pending, completed, cancelled, rescheduled

            $table->dateTime('scheduled_at');
            $table->dateTime('completed_at')->nullable();

            $table->text('notes')->nullable();
            $table->text('outcome')->nullable(); // Result of the follow-up

            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'scheduled_at', 'assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};

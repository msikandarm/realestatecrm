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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('phone_secondary')->nullable();

            // Lead source
            $table->string('source')->nullable(); // website, facebook, referral, walk-in, call, etc.
            $table->string('referred_by')->nullable();

            // Interest
            $table->string('interest_type')->nullable(); // plot, house, apartment, commercial
            $table->foreignId('society_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('plot_id')->nullable()->constrained()->onDelete('set null');
            $table->string('budget_range')->nullable();
            $table->string('preferred_location')->nullable();

            // Lead status
            $table->string('status')->default('new'); // new, contacted, qualified, negotiation, converted, lost
            $table->string('priority')->default('medium'); // low, medium, high, urgent

            // Assigned to
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            // Conversion
            $table->foreignId('converted_to_client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->timestamp('converted_at')->nullable();

            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'priority', 'assigned_to']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};

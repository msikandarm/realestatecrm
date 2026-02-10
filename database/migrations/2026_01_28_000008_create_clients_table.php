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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('phone_secondary')->nullable();
            $table->string('cnic')->unique()->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();

            // Client type
            $table->string('client_type')->default('buyer'); // buyer, seller, both
            $table->string('client_status')->default('active'); // active, inactive, blacklisted

            // Business info
            $table->string('occupation')->nullable();
            $table->string('company')->nullable();
            $table->text('remarks')->nullable();

            // Assigned dealer/agent
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            // Documents
            $table->json('documents')->nullable(); // CNIC copy, etc.

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_type', 'client_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};

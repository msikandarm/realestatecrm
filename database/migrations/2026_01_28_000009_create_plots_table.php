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
        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('society_id')->constrained()->onDelete('cascade');
            $table->foreignId('block_id')->constrained()->onDelete('cascade');
            $table->foreignId('street_id')->nullable()->constrained()->onDelete('set null');

            $table->string('plot_number')->unique();
            $table->string('plot_code'); // Internal reference code

            // Plot dimensions
            $table->decimal('size', 10, 2); // Numeric size
            $table->string('size_unit')->default('marla'); // marla, kanal, sqft
            $table->decimal('size_in_sqft', 15, 2)->nullable(); // Converted to sq ft for comparison
            $table->decimal('width', 10, 2)->nullable(); // in feet
            $table->decimal('length', 10, 2)->nullable(); // in feet

            // Plot pricing
            $table->decimal('base_price', 15, 2)->nullable();
            $table->decimal('current_price', 15, 2)->nullable();
            $table->string('price_per_unit')->nullable(); // per marla/sqft/kanal

            // Plot location & details
            $table->string('corner_plot')->default('no'); // yes, no
            $table->string('park_facing')->default('no'); // yes, no
            $table->string('main_road_facing')->default('no'); // yes, no
            $table->text('location_benefits')->nullable();

            // Plot status
            $table->string('status')->default('available'); // available, booked, sold, reserved, hold
            $table->string('category')->default('residential'); // residential, commercial

            // Map & documents
            $table->text('map_image')->nullable();
            $table->json('documents')->nullable(); // Store document paths
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['society_id', 'block_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plots');
    }
};

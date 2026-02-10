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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('reference_code')->unique();

            // Property type
            $table->enum('type', ['house', 'apartment', 'commercial'])->index(); // house, apartment, commercial
            $table->enum('condition', ['new', 'old', 'under_construction'])->default('new')->index(); // new, old, under_construction
            $table->enum('property_for', ['sale', 'rent', 'both'])->default('sale'); // sale, rent, both

            // Location
            $table->foreignId('plot_id')->nullable()->constrained('plots')->nullOnDelete(); // Link to plot if built on owned land
            $table->foreignId('society_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('block_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('street_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('area')->nullable(); // Area/locality name
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Property details
            $table->decimal('size', 10, 2); // Property size
            $table->enum('size_unit', ['sq_ft', 'sq_m', 'marla', 'kanal'])->default('marla');
            $table->decimal('size_in_sqft', 15, 2)->nullable();

            // For houses/apartments
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('floors')->default(1);
            $table->integer('year_built')->nullable();
            $table->boolean('furnished')->default(false);
            $table->boolean('parking')->default(false);
            $table->integer('parking_spaces')->default(0);
            $table->json('amenities')->nullable(); // parking, garden, gym, pool, etc.
            $table->json('features')->nullable(); // Additional features

            // Pricing
            $table->decimal('price', 15, 2);
            $table->decimal('rental_price', 15, 2)->nullable(); // Monthly or yearly rent
            $table->enum('rental_period', ['monthly', 'yearly'])->nullable();
            $table->decimal('price_per_unit', 15, 2)->nullable();
            $table->boolean('negotiable')->default(false);

            // Ownership
            $table->foreignId('owner_id')->nullable()->constrained('clients')->nullOnDelete(); // Property owner (client)
            $table->string('owner_name')->nullable(); // For external owners
            $table->string('owner_contact')->nullable();

            // Status
            $table->enum('status', [
                'available',
                'sold',
                'rented',
                'under_negotiation',
                'reserved',
                'off_market'
            ])->default('available')->index();
            $table->boolean('featured')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->integer('views_count')->default(0);

            // Media & documents
            $table->string('featured_image')->nullable();
            $table->json('images')->nullable(); // Array of image paths
            $table->json('documents')->nullable();
            $table->string('video_url')->nullable();
            $table->string('virtual_tour_url')->nullable();
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status', 'property_for']);
            $table->index(['society_id', 'block_id']);
            $table->index(['city', 'area']);
            $table->index(['price', 'condition']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};

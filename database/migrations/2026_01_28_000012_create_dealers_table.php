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
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('cnic')->unique()->nullable();
            $table->string('license_number')->unique()->nullable();
            $table->decimal('default_commission_rate', 5, 2)->default(0.00)->comment('Default commission percentage');
            $table->enum('specialization', ['plots', 'residential', 'commercial', 'all'])->default('all');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->integer('total_deals')->default(0)->comment('Cached count of total deals');
            $table->decimal('total_commission', 15, 2)->default(0.00)->comment('Cached sum of total commissions earned');
            $table->string('bank_name')->nullable();
            $table->string('account_title')->nullable();
            $table->string('account_number')->nullable();
            $table->string('iban')->nullable();
            $table->date('joined_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'specialization']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealers');
    }
};

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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('deal_number')->unique();

            // Client & Dealer
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('dealer_id')->nullable()->constrained('users')->onDelete('set null'); // Agent/Dealer

            // Deal item (Plot or Property)
            $table->morphs('dealable'); // Can be plot or property

            // Deal details
            $table->string('deal_type')->default('purchase'); // purchase, sale, booking
            $table->decimal('deal_amount', 15, 2);
            $table->decimal('commission_amount', 15, 2)->nullable();
            $table->decimal('commission_percentage', 5, 2)->nullable();

            // Payment terms
            $table->string('payment_type')->default('installment'); // cash, installment
            $table->integer('installment_months')->nullable();
            $table->decimal('down_payment', 15, 2)->nullable();
            $table->decimal('monthly_installment', 15, 2)->nullable();

            // Deal status
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, completed
            $table->date('deal_date');
            $table->date('completion_date')->nullable();

            $table->text('terms_conditions')->nullable();
            $table->text('remarks')->nullable();

            // Documents
            $table->json('documents')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'deal_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};

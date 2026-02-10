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
        Schema::create('file_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_file_id')->constrained('property_files')->onDelete('cascade');
            $table->string('payment_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->date('due_date')->nullable()->comment('For installments');
            $table->enum('payment_type', ['down_payment', 'installment', 'partial', 'full_payment', 'transfer_charges', 'penalty', 'adjustment'])->default('installment');
            $table->enum('payment_method', ['cash', 'cheque', 'bank_transfer', 'online', 'card'])->default('cash');
            $table->string('reference_number')->nullable()->comment('Cheque/Transaction number');
            $table->string('bank_name')->nullable();
            $table->integer('installment_number')->nullable()->comment('Which installment (1, 2, 3...)');
            $table->enum('status', ['pending', 'received', 'cleared', 'bounced', 'cancelled'])->default('received');
            $table->date('clearance_date')->nullable()->comment('When cheque cleared');
            $table->decimal('penalty_amount', 15, 2)->default(0)->comment('Late payment penalty');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('Early payment discount');
            $table->text('remarks')->nullable();
            $table->json('documents')->nullable()->comment('Receipt, cheque image');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['property_file_id', 'payment_date']);
            $table->index(['status', 'payment_type']);
            $table->index('installment_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_payments');
    }
};

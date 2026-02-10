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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('payment_type_id')->constrained('payment_types')->onDelete('restrict');

            // Polymorphic - can be related to Property, Deal, Project, etc.
            $table->nullableMorphs('expensable'); // expensable_type, expensable_id

            $table->decimal('amount', 15, 2);
            $table->date('expense_date');

            // Payment Method
            $table->enum('payment_method', [
                'cash',
                'cheque',
                'bank_transfer',
                'online',
                'card',
                'credit',
                'other'
            ])->default('cash');

            $table->string('reference_number')->nullable(); // Invoice number, cheque number
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();

            // Status
            $table->enum('status', [
                'pending',
                'paid',
                'cleared',
                'cancelled',
                'refunded'
            ])->default('paid');

            $table->date('payment_date')->nullable(); // When payment was made
            $table->date('clearance_date')->nullable();

            // Vendor/Payee Details
            $table->string('paid_to'); // Vendor/supplier name
            $table->string('contact_number')->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id')->nullable(); // NTN, CNIC for tax purposes

            // Expense Details
            $table->text('description')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable(); // monthly, quarterly, yearly
            $table->date('next_due_date')->nullable();

            // Tax & Additional
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2); // amount - discount + tax

            $table->text('remarks')->nullable();
            $table->json('documents')->nullable(); // Invoice, receipt, bill scans

            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['expense_date', 'status']);
            $table->index(['payment_type_id', 'expense_date']);
            $table->index(['is_recurring', 'next_due_date']);
            $table->index('paid_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

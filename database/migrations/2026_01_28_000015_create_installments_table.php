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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_file_id')->constrained()->onDelete('cascade');

            $table->integer('installment_number');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();

            $table->string('status')->default('pending'); // pending, paid, overdue, waived
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->integer('days_overdue')->default(0);

            $table->string('payment_method')->nullable(); // cash, bank_transfer, cheque, online
            $table->string('reference_number')->nullable(); // Transaction/cheque reference

            $table->text('remarks')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['property_file_id', 'status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};

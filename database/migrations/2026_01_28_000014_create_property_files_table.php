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
        Schema::create('property_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_number')->unique();

            // File owner
            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            // Property/Plot
            $table->morphs('fileable'); // Can be plot or property

            // File details
            $table->foreignId('deal_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2);

            // Payment plan
            $table->string('payment_plan')->default('installment'); // cash, installment
            $table->integer('total_installments')->nullable();
            $table->decimal('installment_amount', 15, 2)->nullable();
            $table->string('installment_frequency')->nullable(); // monthly, quarterly, yearly
            $table->date('first_installment_date')->nullable();
            $table->date('last_installment_date')->nullable();

            // File status
            $table->string('status')->default('active'); // active, completed, transferred, cancelled, defaulted
            $table->date('issue_date');
            $table->date('completion_date')->nullable();

            // Transfer history
            $table->boolean('is_transferred')->default(false);
            $table->foreignId('transferred_from_client')->nullable()->constrained('clients')->onDelete('set null');
            $table->date('transfer_date')->nullable();
            $table->decimal('transfer_charges', 15, 2)->nullable();

            $table->text('remarks')->nullable();
            $table->json('documents')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_files');
    }
};

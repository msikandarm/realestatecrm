# File Management System - Real Estate CRM
## Complete Property File, Installments & Payment Tracking

---

## 📋 Table of Contents
1. [Database Structure](#database-structure)
2. [Eloquent Models](#eloquent-models)
3. [Auto Installment Generation](#auto-installment-generation)
4. [Payment Processing](#payment-processing)
5. [Late Payment Tracking](#late-payment-tracking)
6. [File Transfer Logic](#file-transfer-logic)
7. [Payment Receipts](#payment-receipts)
8. [Service Layer](#service-layer)
9. [Controller Logic](#controller-logic)
10. [Complete Workflow](#complete-workflow)

---

## 🗄️ 1. DATABASE STRUCTURE

### ERD Diagram

```
┌─────────────────────────┐
│    property_files       │
│─────────────────────────│
│ id                      │
│ file_number (unique)    │
│ client_id (FK)          │
│ fileable_type           │◄── polymorphic (Plot/Property)
│ fileable_id             │
│ total_price             │
│ down_payment            │
│ remaining_balance       │
│ installment_amount      │
│ installment_frequency   │ (monthly, quarterly)
│ total_installments      │
│ paid_installments       │
│ start_date              │
│ end_date                │
│ status (enum)           │ (active, completed, defaulted)
│ notes                   │
│ timestamps              │
└─────┬───────────────────┘
      │ 1
      │ has many
      │ *
┌─────▼───────────────────┐
│   file_installments     │
│─────────────────────────│
│ id                      │
│ property_file_id (FK)   │
│ installment_number      │
│ due_date                │
│ amount                  │
│ status (enum)           │ (pending, paid, overdue, waived)
│ paid_amount             │
│ paid_date               │
│ late_fee                │
│ is_overdue              │
│ days_overdue            │
│ notes                   │
│ timestamps              │
└─────────────────────────┘

┌─────────────────────────┐
│     file_payments       │
│─────────────────────────│
│ id                      │
│ property_file_id (FK)   │
│ file_installment_id (FK)│
│ client_id (FK)          │
│ amount                  │
│ payment_method (enum)   │
│ payment_date            │
│ receipt_number          │
│ received_by (FK users)  │
│ bank_reference          │
│ cheque_number           │
│ notes                   │
│ timestamps              │
└─────────────────────────┘

┌─────────────────────────┐
│    file_transfers       │
│─────────────────────────│
│ id                      │
│ property_file_id (FK)   │
│ from_client_id (FK)     │
│ to_client_id (FK)       │
│ transfer_date           │
│ transfer_fee            │
│ remaining_balance       │
│ reason                  │
│ approved_by (FK users)  │
│ status (enum)           │
│ notes                   │
│ timestamps              │
└─────────────────────────┘

┌─────────────────────────┐
│   payment_receipts      │
│─────────────────────────│
│ id                      │
│ file_payment_id (FK)    │
│ receipt_number (unique) │
│ receipt_path            │
│ generated_at            │
│ generated_by (FK users) │
│ is_sent                 │
│ sent_at                 │
└─────────────────────────┘
```

### Complete Migrations

```php
// database/migrations/xxxx_create_property_files_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_files', function (Blueprint $table) {
            $table->id();
            $table->string('file_number')->unique()->index();

            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            // Polymorphic relationship (Plot or Property)
            $table->string('fileable_type');
            $table->unsignedBigInteger('fileable_id');
            $table->index(['fileable_type', 'fileable_id']);

            // Financial details
            $table->decimal('total_price', 15, 2);
            $table->decimal('down_payment', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2);
            $table->decimal('installment_amount', 12, 2);

            // Installment configuration
            $table->enum('installment_frequency', ['monthly', 'quarterly', 'semi-annually', 'annually'])
                ->default('monthly');
            $table->integer('total_installments');
            $table->integer('paid_installments')->default(0);

            // Dates
            $table->date('start_date');
            $table->date('end_date');

            // Status
            $table->enum('status', ['active', 'completed', 'defaulted', 'transferred', 'cancelled'])
                ->default('active')
                ->index();

            // Additional info
            $table->foreignId('dealer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('late_fee_percentage', 5, 2)->default(2.00);
            $table->integer('grace_period_days')->default(7);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_files');
    }
};
```

```php
// database/migrations/xxxx_create_file_installments_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_file_id')->constrained()->onDelete('cascade');

            $table->integer('installment_number')->index();
            $table->date('due_date')->index();
            $table->decimal('amount', 12, 2);

            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'waived'])
                ->default('pending')
                ->index();

            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('paid_date')->nullable();

            // Late payment tracking
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->boolean('is_overdue')->default(false)->index();
            $table->integer('days_overdue')->default(0);
            $table->date('overdue_since')->nullable();

            // Reminders
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['property_file_id', 'installment_number']);
            $table->index(['due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_installments');
    }
};
```

```php
// database/migrations/xxxx_create_file_payments_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_file_id')->constrained()->onDelete('cascade');
            $table->foreignId('file_installment_id')->nullable()
                ->constrained()
                ->onDelete('set null');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'cheque', 'bank_transfer', 'online', 'card'])
                ->index();
            $table->date('payment_date')->index();

            $table->string('receipt_number')->unique()->index();
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');

            // Payment details
            $table->string('bank_reference')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('transaction_id')->nullable();

            // Categorization
            $table->enum('payment_type', ['installment', 'down_payment', 'late_fee', 'adjustment'])
                ->default('installment');

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['property_file_id', 'payment_date']);
            $table->index(['client_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_payments');
    }
};
```

```php
// database/migrations/xxxx_create_file_transfers_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('file_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_file_id')->constrained()->onDelete('cascade');

            $table->foreignId('from_client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('to_client_id')->constrained('clients')->onDelete('cascade');

            $table->date('transfer_date')->index();
            $table->decimal('transfer_fee', 10, 2)->default(0);
            $table->decimal('remaining_balance_at_transfer', 15, 2);
            $table->decimal('paid_amount_at_transfer', 15, 2);

            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])
                ->default('pending')
                ->index();

            $table->text('approval_notes')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['property_file_id', 'transfer_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('file_transfers');
    }
};
```

```php
// database/migrations/xxxx_create_payment_receipts_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_payment_id')->constrained()->onDelete('cascade');

            $table->string('receipt_number')->unique()->index();
            $table->string('receipt_path')->nullable();

            $table->timestamp('generated_at')->useCurrent();
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');

            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->string('sent_to_email')->nullable();
            $table->string('sent_to_phone')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_receipts');
    }
};
```

---

## 🎭 2. ELOQUENT MODELS

### PropertyFile Model

```php
// app/Models/PropertyFile.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertyFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'file_number',
        'client_id',
        'fileable_type',
        'fileable_id',
        'total_price',
        'down_payment',
        'remaining_balance',
        'installment_amount',
        'installment_frequency',
        'total_installments',
        'paid_installments',
        'start_date',
        'end_date',
        'status',
        'dealer_id',
        'late_fee_percentage',
        'grace_period_days',
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'late_fee_percentage' => 'decimal:2',
        'total_installments' => 'integer',
        'paid_installments' => 'integer',
        'grace_period_days' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($file) {
            if (empty($file->file_number)) {
                $file->file_number = self::generateFileNumber();
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * File belongs to Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Polymorphic relationship to Plot or Property
     */
    public function fileable()
    {
        return $this->morphTo();
    }

    /**
     * File belongs to Dealer (User)
     */
    public function dealer()
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    /**
     * File has many installments
     */
    public function installments()
    {
        return $this->hasMany(FileInstallment::class, 'property_file_id');
    }

    /**
     * File has many payments
     */
    public function payments()
    {
        return $this->hasMany(FilePayment::class, 'property_file_id');
    }

    /**
     * File has many transfers
     */
    public function transfers()
    {
        return $this->hasMany(FileTransfer::class, 'property_file_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDefaulted($query)
    {
        return $query->where('status', 'defaulted');
    }

    public function scopeOverdue($query)
    {
        return $query->whereHas('installments', function ($q) {
            $q->where('is_overdue', true)
              ->where('status', '!=', 'paid');
        });
    }

    // ==================== ACCESSORS ====================

    /**
     * Get total paid amount
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentageAttribute()
    {
        if ($this->total_installments == 0) {
            return 0;
        }
        return round(($this->paid_installments / $this->total_installments) * 100, 2);
    }

    /**
     * Check if file has overdue installments
     */
    public function getHasOverdueAttribute()
    {
        return $this->installments()
            ->where('is_overdue', true)
            ->where('status', '!=', 'paid')
            ->exists();
    }

    /**
     * Get next due installment
     */
    public function getNextDueInstallmentAttribute()
    {
        return $this->installments()
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->first();
    }

    /**
     * Get total overdue amount
     */
    public function getTotalOverdueAmountAttribute()
    {
        return $this->installments()
            ->where('is_overdue', true)
            ->where('status', '!=', 'paid')
            ->sum('amount');
    }

    /**
     * Get total late fees
     */
    public function getTotalLateFeesAttribute()
    {
        return $this->installments()->sum('late_fee');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Generate unique file number
     */
    public static function generateFileNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastFile = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastFile ? (int) substr($lastFile->file_number, -4) + 1 : 1;

        return "FILE-{$year}{$month}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Update remaining balance
     */
    public function updateRemainingBalance()
    {
        $totalPaid = $this->total_paid;
        $this->remaining_balance = $this->total_price - $this->down_payment - $totalPaid;
        $this->save();
    }

    /**
     * Update paid installments count
     */
    public function updatePaidInstallmentsCount()
    {
        $this->paid_installments = $this->installments()
            ->where('status', 'paid')
            ->count();
        $this->save();
    }

    /**
     * Check if file is complete
     */
    public function checkCompletion()
    {
        if ($this->remaining_balance <= 0 && $this->paid_installments >= $this->total_installments) {
            $this->status = 'completed';
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Calculate monthly installment
     */
    public static function calculateMonthlyInstallment($totalPrice, $downPayment, $totalMonths)
    {
        $remainingAmount = $totalPrice - $downPayment;
        return $remainingAmount / $totalMonths;
    }
}
```

### FileInstallment Model

```php
// app/Models/FileInstallment.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class FileInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_file_id',
        'installment_number',
        'due_date',
        'amount',
        'status',
        'paid_amount',
        'paid_date',
        'late_fee',
        'is_overdue',
        'days_overdue',
        'overdue_since',
        'reminder_sent',
        'reminder_sent_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'installment_number' => 'integer',
        'days_overdue' => 'integer',
        'is_overdue' => 'boolean',
        'reminder_sent' => 'boolean',
        'due_date' => 'date',
        'paid_date' => 'date',
        'overdue_since' => 'date',
        'reminder_sent_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function propertyFile()
    {
        return $this->belongsTo(PropertyFile::class, 'property_file_id');
    }

    public function payments()
    {
        return $this->hasMany(FilePayment::class, 'file_installment_id');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true)
            ->where('status', '!=', 'paid');
    }

    public function scopeDueThisMonth($query)
    {
        return $query->whereMonth('due_date', now()->month)
            ->whereYear('due_date', now()->year);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->whereBetween('due_date', [today(), today()->addDays($days)])
            ->where('status', 'pending');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check and update overdue status
     */
    public function checkOverdue()
    {
        $gracePeriod = $this->propertyFile->grace_period_days ?? 0;
        $dueDateWithGrace = Carbon::parse($this->due_date)->addDays($gracePeriod);

        if (now()->gt($dueDateWithGrace) && $this->status != 'paid') {
            $this->is_overdue = true;
            $this->overdue_since = $dueDateWithGrace;
            $this->days_overdue = now()->diffInDays($dueDateWithGrace);
            $this->status = 'overdue';

            // Calculate late fee
            $this->calculateLateFee();

            $this->save();
        }
    }

    /**
     * Calculate late fee
     */
    public function calculateLateFee()
    {
        $lateFeePercentage = $this->propertyFile->late_fee_percentage ?? 2;
        $this->late_fee = ($this->amount * $lateFeePercentage) / 100;
    }

    /**
     * Mark as paid
     */
    public function markAsPaid($amount, $paymentDate = null)
    {
        $this->paid_amount = $amount;
        $this->paid_date = $paymentDate ?? now();
        $this->status = ($amount >= $this->amount + $this->late_fee) ? 'paid' : 'partial';
        $this->save();

        // Update parent file
        $this->propertyFile->updatePaidInstallmentsCount();
        $this->propertyFile->updateRemainingBalance();
        $this->propertyFile->checkCompletion();
    }

    /**
     * Get total amount due (including late fee)
     */
    public function getTotalAmountDueAttribute()
    {
        return $this->amount + $this->late_fee;
    }

    /**
     * Get remaining amount
     */
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount_due - $this->paid_amount;
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'paid' => 'success',
            'pending' => 'warning',
            'overdue' => 'danger',
            'partial' => 'info',
            'waived' => 'secondary',
            default => 'dark',
        };
    }
}
```

### FilePayment Model

```php
// app/Models/FilePayment.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FilePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_file_id',
        'file_installment_id',
        'client_id',
        'amount',
        'payment_method',
        'payment_date',
        'receipt_number',
        'received_by',
        'bank_reference',
        'cheque_number',
        'transaction_id',
        'payment_type',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->receipt_number)) {
                $payment->receipt_number = self::generateReceiptNumber();
            }
        });

        static::created(function ($payment) {
            // Auto-generate receipt
            $payment->generateReceipt();
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function propertyFile()
    {
        return $this->belongsTo(PropertyFile::class, 'property_file_id');
    }

    public function installment()
    {
        return $this->belongsTo(FileInstallment::class, 'file_installment_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function receipt()
    {
        return $this->hasOne(PaymentReceipt::class, 'file_payment_id');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Generate unique receipt number
     */
    public static function generateReceiptNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastPayment = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastPayment ? (int) substr($lastPayment->receipt_number, -5) + 1 : 1;

        return "RCP-{$year}{$month}-" . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate payment receipt
     */
    public function generateReceipt()
    {
        return PaymentReceipt::create([
            'file_payment_id' => $this->id,
            'receipt_number' => $this->receipt_number,
            'generated_by' => auth()->id(),
            'generated_at' => now(),
        ]);
    }
}
```

### FileTransfer Model

```php
// app/Models/FileTransfer.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FileTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_file_id',
        'from_client_id',
        'to_client_id',
        'transfer_date',
        'transfer_fee',
        'remaining_balance_at_transfer',
        'paid_amount_at_transfer',
        'reason',
        'approved_by',
        'status',
        'approval_notes',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'transfer_fee' => 'decimal:2',
        'remaining_balance_at_transfer' => 'decimal:2',
        'paid_amount_at_transfer' => 'decimal:2',
        'transfer_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function propertyFile()
    {
        return $this->belongsTo(PropertyFile::class, 'property_file_id');
    }

    public function fromClient()
    {
        return $this->belongsTo(Client::class, 'from_client_id');
    }

    public function toClient()
    {
        return $this->belongsTo(Client::class, 'to_client_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
```

### PaymentReceipt Model

```php
// app/Models/PaymentReceipt.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_payment_id',
        'receipt_number',
        'receipt_path',
        'generated_at',
        'generated_by',
        'is_sent',
        'sent_at',
        'sent_to_email',
        'sent_to_phone',
    ];

    protected $casts = [
        'is_sent' => 'boolean',
        'generated_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function payment()
    {
        return $this->belongsTo(FilePayment::class, 'file_payment_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
```

---

## 🤖 3. AUTO INSTALLMENT GENERATION

### Installment Generation Service

```php
// app/Services/InstallmentGenerationService.php

<?php

namespace App\Services;

use App\Models\PropertyFile;
use App\Models\FileInstallment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InstallmentGenerationService
{
    /**
     * Generate installment schedule for a property file
     *
     * @param PropertyFile $file
     * @return array
     */
    public function generateSchedule(PropertyFile $file): array
    {
        DB::beginTransaction();

        try {
            $installments = [];
            $startDate = Carbon::parse($file->start_date);

            for ($i = 1; $i <= $file->total_installments; $i++) {
                $dueDate = $this->calculateDueDate($startDate, $i, $file->installment_frequency);

                $installment = FileInstallment::create([
                    'property_file_id' => $file->id,
                    'installment_number' => $i,
                    'due_date' => $dueDate,
                    'amount' => $file->installment_amount,
                    'status' => 'pending',
                ]);

                $installments[] = $installment;
            }

            DB::commit();

            return $installments;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate due date based on frequency
     *
     * @param Carbon $startDate
     * @param int $installmentNumber
     * @param string $frequency
     * @return Carbon
     */
    protected function calculateDueDate(Carbon $startDate, int $installmentNumber, string $frequency): Carbon
    {
        $date = $startDate->copy();
        $periodsToAdd = $installmentNumber - 1;

        return match($frequency) {
            'monthly' => $date->addMonths($periodsToAdd),
            'quarterly' => $date->addMonths($periodsToAdd * 3),
            'semi-annually' => $date->addMonths($periodsToAdd * 6),
            'annually' => $date->addYears($periodsToAdd),
            default => $date->addMonths($periodsToAdd),
        };
    }

    /**
     * Regenerate schedule (useful after file modification)
     *
     * @param PropertyFile $file
     * @return array
     */
    public function regenerateSchedule(PropertyFile $file): array
    {
        // Delete existing unpaid installments
        $file->installments()->where('status', 'pending')->delete();

        // Get count of already paid installments
        $paidCount = $file->installments()->where('status', 'paid')->count();

        // Recalculate remaining installments
        $remainingInstallments = $file->total_installments - $paidCount;
        $file->installment_amount = $file->remaining_balance / $remainingInstallments;
        $file->save();

        // Generate new schedule for remaining
        return $this->generateRemainingSchedule($file, $paidCount);
    }

    /**
     * Generate schedule for remaining installments
     *
     * @param PropertyFile $file
     * @param int $startFrom
     * @return array
     */
    protected function generateRemainingSchedule(PropertyFile $file, int $startFrom): array
    {
        $installments = [];
        $lastPaidInstallment = $file->installments()
            ->where('status', 'paid')
            ->orderBy('installment_number', 'desc')
            ->first();

        $startDate = $lastPaidInstallment
            ? Carbon::parse($lastPaidInstallment->due_date)
            : Carbon::parse($file->start_date);

        for ($i = $startFrom + 1; $i <= $file->total_installments; $i++) {
            $dueDate = $this->calculateDueDate($startDate, $i - $startFrom, $file->installment_frequency);

            $installment = FileInstallment::create([
                'property_file_id' => $file->id,
                'installment_number' => $i,
                'due_date' => $dueDate,
                'amount' => $file->installment_amount,
                'status' => 'pending',
            ]);

            $installments[] = $installment;
        }

        return $installments;
    }
}
```

---

## 💰 4. PAYMENT PROCESSING

### Payment Processing Service

```php
// app/Services/PaymentProcessingService.php

<?php

namespace App\Services;

use App\Models\PropertyFile;
use App\Models\FileInstallment;
use App\Models\FilePayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PaymentProcessingService
{
    /**
     * Process installment payment
     *
     * @param FileInstallment $installment
     * @param array $paymentData
     * @return FilePayment
     */
    public function processInstallmentPayment(FileInstallment $installment, array $paymentData): FilePayment
    {
        DB::beginTransaction();

        try {
            // Create payment record
            $payment = FilePayment::create([
                'property_file_id' => $installment->property_file_id,
                'file_installment_id' => $installment->id,
                'client_id' => $installment->propertyFile->client_id,
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'received_by' => Auth::id(),
                'bank_reference' => $paymentData['bank_reference'] ?? null,
                'cheque_number' => $paymentData['cheque_number'] ?? null,
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'payment_type' => 'installment',
                'notes' => $paymentData['notes'] ?? null,
            ]);

            // Update installment
            $installment->markAsPaid($paymentData['amount'], $paymentData['payment_date'] ?? now());

            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process down payment
     *
     * @param PropertyFile $file
     * @param array $paymentData
     * @return FilePayment
     */
    public function processDownPayment(PropertyFile $file, array $paymentData): FilePayment
    {
        DB::beginTransaction();

        try {
            $payment = FilePayment::create([
                'property_file_id' => $file->id,
                'file_installment_id' => null,
                'client_id' => $file->client_id,
                'amount' => $paymentData['amount'],
                'payment_method' => $paymentData['payment_method'],
                'payment_date' => $paymentData['payment_date'] ?? now(),
                'received_by' => Auth::id(),
                'payment_type' => 'down_payment',
                'notes' => $paymentData['notes'] ?? 'Down payment received',
            ]);

            // Update file
            $file->down_payment = $paymentData['amount'];
            $file->remaining_balance = $file->total_price - $file->down_payment;
            $file->save();

            DB::commit();

            return $payment;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process partial payment (split across multiple installments)
     *
     * @param PropertyFile $file
     * @param array $paymentData
     * @return array
     */
    public function processPartialPayment(PropertyFile $file, array $paymentData): array
    {
        DB::beginTransaction();

        try {
            $remainingAmount = $paymentData['amount'];
            $payments = [];

            // Get pending/overdue installments in order
            $installments = $file->installments()
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->orderBy('due_date', 'asc')
                ->get();

            foreach ($installments as $installment) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $amountDue = $installment->remaining_amount;
                $paymentAmount = min($remainingAmount, $amountDue);

                // Create payment
                $payment = FilePayment::create([
                    'property_file_id' => $file->id,
                    'file_installment_id' => $installment->id,
                    'client_id' => $file->client_id,
                    'amount' => $paymentAmount,
                    'payment_method' => $paymentData['payment_method'],
                    'payment_date' => $paymentData['payment_date'] ?? now(),
                    'received_by' => Auth::id(),
                    'payment_type' => 'installment',
                    'notes' => $paymentData['notes'] ?? "Partial payment for installment #{$installment->installment_number}",
                ]);

                // Update installment
                $installment->paid_amount += $paymentAmount;
                $installment->status = ($installment->paid_amount >= $installment->total_amount_due) ? 'paid' : 'partial';

                if ($installment->status === 'paid') {
                    $installment->paid_date = $paymentData['payment_date'] ?? now();
                }

                $installment->save();

                $payments[] = $payment;
                $remainingAmount -= $paymentAmount;
            }

            // Update file
            $file->updatePaidInstallmentsCount();
            $file->updateRemainingBalance();
            $file->checkCompletion();

            DB::commit();

            return $payments;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get payment summary for a file
     *
     * @param PropertyFile $file
     * @return array
     */
    public function getPaymentSummary(PropertyFile $file): array
    {
        return [
            'total_price' => $file->total_price,
            'down_payment' => $file->down_payment,
            'total_installments' => $file->total_installments,
            'installment_amount' => $file->installment_amount,
            'paid_installments' => $file->paid_installments,
            'remaining_installments' => $file->total_installments - $file->paid_installments,
            'total_paid' => $file->total_paid,
            'remaining_balance' => $file->remaining_balance,
            'completion_percentage' => $file->completion_percentage,
            'total_late_fees' => $file->total_late_fees,
            'overdue_amount' => $file->total_overdue_amount,
            'has_overdue' => $file->has_overdue,
            'next_due_date' => $file->next_due_installment?->due_date,
            'next_due_amount' => $file->next_due_installment?->amount,
        ];
    }
}
```

---

## ⏰ 5. LATE PAYMENT TRACKING

### Late Payment Tracking Service

```php
// app/Services/LatePaymentTrackingService.php

<?php

namespace App\Services;

use App\Models\FileInstallment;
use App\Models\PropertyFile;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class LatePaymentTrackingService
{
    /**
     * Check and update overdue installments
     *
     * @return array
     */
    public function checkOverdueInstallments(): array
    {
        $overdueInstallments = FileInstallment::where('status', '!=', 'paid')
            ->where('due_date', '<', now())
            ->get();

        $updated = 0;
        foreach ($overdueInstallments as $installment) {
            $installment->checkOverdue();
            $updated++;
        }

        return [
            'total_checked' => $overdueInstallments->count(),
            'updated' => $updated,
        ];
    }

    /**
     * Get all overdue files
     *
     * @return Collection
     */
    public function getOverdueFiles(): Collection
    {
        return PropertyFile::active()
            ->whereHas('installments', function ($query) {
                $query->where('is_overdue', true)
                    ->where('status', '!=', 'paid');
            })
            ->with(['client', 'installments' => function ($query) {
                $query->where('is_overdue', true)
                    ->where('status', '!=', 'paid');
            }])
            ->get();
    }

    /**
     * Get installments due soon
     *
     * @param int $days
     * @return Collection
     */
    public function getInstallmentsDueSoon(int $days = 7): Collection
    {
        return FileInstallment::dueSoon($days)
            ->with(['propertyFile.client'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    /**
     * Get overdue report
     *
     * @return array
     */
    public function getOverdueReport(): array
    {
        $overdueInstallments = FileInstallment::overdue()->get();

        return [
            'total_overdue_installments' => $overdueInstallments->count(),
            'total_overdue_amount' => $overdueInstallments->sum('amount'),
            'total_late_fees' => $overdueInstallments->sum('late_fee'),
            'files_with_overdue' => $overdueInstallments->pluck('property_file_id')->unique()->count(),
            'overdue_by_days' => [
                '1-7_days' => $overdueInstallments->whereBetween('days_overdue', [1, 7])->count(),
                '8-15_days' => $overdueInstallments->whereBetween('days_overdue', [8, 15])->count(),
                '16-30_days' => $overdueInstallments->whereBetween('days_overdue', [16, 30])->count(),
                '30+_days' => $overdueInstallments->where('days_overdue', '>', 30)->count(),
            ],
        ];
    }

    /**
     * Send payment reminders
     *
     * @return array
     */
    public function sendPaymentReminders(): array
    {
        $installmentsDueSoon = $this->getInstallmentsDueSoon(7);
        $sent = 0;

        foreach ($installmentsDueSoon as $installment) {
            if (!$installment->reminder_sent) {
                // Send notification (email/SMS)
                // Notification::send($installment->propertyFile->client, new PaymentReminderNotification($installment));

                $installment->reminder_sent = true;
                $installment->reminder_sent_at = now();
                $installment->save();

                $sent++;
            }
        }

        return [
            'total_due_soon' => $installmentsDueSoon->count(),
            'reminders_sent' => $sent,
        ];
    }

    /**
     * Mark files as defaulted
     *
     * @param int $daysOverdue
     * @return int
     */
    public function markDefaultedFiles(int $daysOverdue = 90): int
    {
        $files = PropertyFile::active()
            ->whereHas('installments', function ($query) use ($daysOverdue) {
                $query->where('is_overdue', true)
                    ->where('days_overdue', '>=', $daysOverdue)
                    ->where('status', '!=', 'paid');
            })
            ->get();

        $defaulted = 0;
        foreach ($files as $file) {
            $file->status = 'defaulted';
            $file->save();
            $defaulted++;
        }

        return $defaulted;
    }
}
```

### Console Command for Daily Check

```php
// app/Console/Commands/CheckOverdueInstallments.php

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LatePaymentTrackingService;

class CheckOverdueInstallments extends Command
{
    protected $signature = 'installments:check-overdue';
    protected $description = 'Check and update overdue installments';

    public function handle(LatePaymentTrackingService $service)
    {
        $this->info('Checking overdue installments...');

        $result = $service->checkOverdueInstallments();

        $this->info("Checked: {$result['total_checked']} installments");
        $this->info("Updated: {$result['updated']} installments");

        // Send reminders
        $this->info('Sending payment reminders...');
        $reminders = $service->sendPaymentReminders();
        $this->info("Reminders sent: {$reminders['reminders_sent']}");

        return 0;
    }
}

// Register in app/Console/Kernel.php or console routes
protected function schedule(Schedule $schedule)
{
    $schedule->command('installments:check-overdue')->daily();
}
```

---

## 🔄 6. FILE TRANSFER LOGIC

### File Transfer Service

```php
// app/Services/FileTransferService.php

<?php

namespace App\Services;

use App\Models\PropertyFile;
use App\Models\FileTransfer;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FileTransferService
{
    /**
     * Initiate file transfer
     *
     * @param PropertyFile $file
     * @param Client $toClient
     * @param array $data
     * @return FileTransfer
     */
    public function initiateTransfer(PropertyFile $file, Client $toClient, array $data): FileTransfer
    {
        if ($file->status !== 'active') {
            throw new \Exception("Cannot transfer file with status: {$file->status}");
        }

        DB::beginTransaction();

        try {
            $transfer = FileTransfer::create([
                'property_file_id' => $file->id,
                'from_client_id' => $file->client_id,
                'to_client_id' => $toClient->id,
                'transfer_date' => $data['transfer_date'] ?? now(),
                'transfer_fee' => $data['transfer_fee'] ?? 0,
                'remaining_balance_at_transfer' => $file->remaining_balance,
                'paid_amount_at_transfer' => $file->total_paid,
                'reason' => $data['reason'] ?? null,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            return $transfer;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve file transfer
     *
     * @param FileTransfer $transfer
     * @param array $data
     * @return FileTransfer
     */
    public function approveTransfer(FileTransfer $transfer, array $data = []): FileTransfer
    {
        if ($transfer->status !== 'pending') {
            throw new \Exception("Transfer is not in pending status");
        }

        DB::beginTransaction();

        try {
            // Update transfer
            $transfer->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'approval_notes' => $data['approval_notes'] ?? null,
            ]);

            // Execute transfer
            $this->executeTransfer($transfer);

            DB::commit();

            return $transfer;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Execute the actual file transfer
     *
     * @param FileTransfer $transfer
     * @return void
     */
    protected function executeTransfer(FileTransfer $transfer): void
    {
        $file = $transfer->propertyFile;

        // Update file client
        $file->client_id = $transfer->to_client_id;
        $file->save();

        // Update plot/property owner if applicable
        if ($file->fileable_type === 'App\\Models\\Plot') {
            $plot = $file->fileable;
            $plot->current_owner_id = $transfer->to_client_id;
            $plot->save();

            // Log in plot history
            \App\Models\PlotHistory::create([
                'plot_id' => $plot->id,
                'action' => 'transferred',
                'old_owner_id' => $transfer->from_client_id,
                'new_owner_id' => $transfer->to_client_id,
                'changed_by' => Auth::id(),
                'notes' => "File transferred via FileTransfer #{$transfer->id}",
                'action_date' => now(),
            ]);
        }

        // Mark transfer as completed
        $transfer->status = 'completed';
        $transfer->save();
    }

    /**
     * Reject file transfer
     *
     * @param FileTransfer $transfer
     * @param string $reason
     * @return FileTransfer
     */
    public function rejectTransfer(FileTransfer $transfer, string $reason): FileTransfer
    {
        $transfer->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $reason,
        ]);

        return $transfer;
    }

    /**
     * Get transfer history for a file
     *
     * @param PropertyFile $file
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTransferHistory(PropertyFile $file)
    {
        return $file->transfers()
            ->with(['fromClient', 'toClient', 'approvedBy'])
            ->orderBy('transfer_date', 'desc')
            ->get();
    }
}
```

---

## 🧾 7. PAYMENT RECEIPTS

### Receipt Generation Service

```php
// app/Services/ReceiptGenerationService.php

<?php

namespace App\Services;

use App\Models\FilePayment;
use App\Models\PaymentReceipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReceiptGenerationService
{
    /**
     * Generate PDF receipt
     *
     * @param FilePayment $payment
     * @return string Path to generated PDF
     */
    public function generatePDF(FilePayment $payment): string
    {
        $data = $this->prepareReceiptData($payment);

        $pdf = Pdf::loadView('receipts.payment', $data);

        $filename = "receipt-{$payment->receipt_number}.pdf";
        $path = "receipts/" . date('Y/m/') . $filename;

        Storage::disk('public')->put($path, $pdf->output());

        // Update receipt record
        $payment->receipt->update([
            'receipt_path' => $path,
        ]);

        return $path;
    }

    /**
     * Prepare receipt data
     *
     * @param FilePayment $payment
     * @return array
     */
    protected function prepareReceiptData(FilePayment $payment): array
    {
        $file = $payment->propertyFile;
        $client = $payment->client;
        $installment = $payment->installment;

        return [
            'receipt_number' => $payment->receipt_number,
            'payment_date' => $payment->payment_date->format('d M Y'),
            'client_name' => $client->name,
            'client_phone' => $client->phone,
            'client_cnic' => $client->cnic,
            'file_number' => $file->file_number,
            'plot_identifier' => $file->fileable?->full_identifier ?? 'N/A',
            'installment_number' => $installment?->installment_number ?? 'N/A',
            'amount_paid' => $payment->amount,
            'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method)),
            'received_by' => $payment->receivedBy->name,
            'bank_reference' => $payment->bank_reference,
            'cheque_number' => $payment->cheque_number,
            'transaction_id' => $payment->transaction_id,
            'remaining_balance' => $file->remaining_balance,
            'total_paid' => $file->total_paid,
            'completion_percentage' => $file->completion_percentage,
            'notes' => $payment->notes,
            'generated_at' => now()->format('d M Y h:i A'),
        ];
    }

    /**
     * Send receipt via email
     *
     * @param FilePayment $payment
     * @param string $email
     * @return bool
     */
    public function sendViaEmail(FilePayment $payment, string $email): bool
    {
        // Generate PDF if not exists
        if (!$payment->receipt->receipt_path) {
            $this->generatePDF($payment);
        }

        // Send email with PDF attachment
        // Mail::to($email)->send(new PaymentReceiptMail($payment));

        // Update receipt
        $payment->receipt->update([
            'is_sent' => true,
            'sent_at' => now(),
            'sent_to_email' => $email,
        ]);

        return true;
    }

    /**
     * Send receipt via SMS
     *
     * @param FilePayment $payment
     * @param string $phone
     * @return bool
     */
    public function sendViaSMS(FilePayment $payment, string $phone): bool
    {
        $message = "Payment Receipt\n";
        $message .= "Receipt: {$payment->receipt_number}\n";
        $message .= "Amount: Rs. " . number_format($payment->amount, 2) . "\n";
        $message .= "File: {$payment->propertyFile->file_number}\n";
        $message .= "Date: {$payment->payment_date->format('d M Y')}";

        // Send SMS
        // SMS::send($phone, $message);

        $payment->receipt->update([
            'is_sent' => true,
            'sent_at' => now(),
            'sent_to_phone' => $phone,
        ]);

        return true;
    }
}
```

### Receipt Blade Template

```blade
{{-- resources/views/receipts/payment.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <title>Payment Receipt - {{ $receipt_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; }
        .receipt-title { font-size: 20px; margin-top: 10px; }
        .section { margin-bottom: 20px; }
        .label { font-weight: bold; display: inline-block; width: 180px; }
        .value { display: inline-block; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .amount { font-size: 18px; font-weight: bold; color: #28a745; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Your Real Estate Company</div>
        <div class="receipt-title">PAYMENT RECEIPT</div>
        <div>Receipt No: {{ $receipt_number }}</div>
    </div>

    <div class="section">
        <h3>Client Information</h3>
        <div><span class="label">Name:</span> <span class="value">{{ $client_name }}</span></div>
        <div><span class="label">Phone:</span> <span class="value">{{ $client_phone }}</span></div>
        <div><span class="label">CNIC:</span> <span class="value">{{ $client_cnic }}</span></div>
    </div>

    <div class="section">
        <h3>File Information</h3>
        <div><span class="label">File Number:</span> <span class="value">{{ $file_number }}</span></div>
        <div><span class="label">Plot/Property:</span> <span class="value">{{ $plot_identifier }}</span></div>
        <div><span class="label">Installment Number:</span> <span class="value">{{ $installment_number }}</span></div>
    </div>

    <div class="section">
        <h3>Payment Details</h3>
        <div><span class="label">Payment Date:</span> <span class="value">{{ $payment_date }}</span></div>
        <div><span class="label">Amount Paid:</span> <span class="value amount">Rs. {{ number_format($amount_paid, 2) }}</span></div>
        <div><span class="label">Payment Method:</span> <span class="value">{{ $payment_method }}</span></div>
        @if($bank_reference)
            <div><span class="label">Bank Reference:</span> <span class="value">{{ $bank_reference }}</span></div>
        @endif
        @if($cheque_number)
            <div><span class="label">Cheque Number:</span> <span class="value">{{ $cheque_number }}</span></div>
        @endif
        <div><span class="label">Received By:</span> <span class="value">{{ $received_by }}</span></div>
    </div>

    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Total Paid (Including This Payment)</td>
            <td>Rs. {{ number_format($total_paid, 2) }}</td>
        </tr>
        <tr>
            <td>Remaining Balance</td>
            <td>Rs. {{ number_format($remaining_balance, 2) }}</td>
        </tr>
        <tr>
            <td>Completion</td>
            <td>{{ $completion_percentage }}%</td>
        </tr>
    </table>

    @if($notes)
    <div class="section">
        <h3>Notes</h3>
        <p>{{ $notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Generated on: {{ $generated_at }}</p>
        <p>This is a computer-generated receipt and does not require a signature.</p>
    </div>
</body>
</html>
```

---

## 🎯 8. SERVICE LAYER

### Main File Management Service

```php
// app/Services/FileManagementService.php

<?php

namespace App\Services;

use App\Models\PropertyFile;
use App\Models\Client;
use App\Models\Plot;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class FileManagementService
{
    public function __construct(
        protected InstallmentGenerationService $installmentService,
        protected PaymentProcessingService $paymentService,
    ) {}

    /**
     * Create new property file
     *
     * @param Client $client
     * @param Plot|Property $fileable
     * @param array $data
     * @return PropertyFile
     */
    public function createFile(Client $client, $fileable, array $data): PropertyFile
    {
        DB::beginTransaction();

        try {
            // Calculate remaining balance
            $remainingBalance = $data['total_price'] - $data['down_payment'];

            // Calculate installment amount
            $installmentAmount = PropertyFile::calculateMonthlyInstallment(
                $data['total_price'],
                $data['down_payment'],
                $data['total_installments']
            );

            // Calculate end date
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $endDate = $this->calculateEndDate(
                $startDate,
                $data['total_installments'],
                $data['installment_frequency']
            );

            // Create file
            $file = PropertyFile::create([
                'client_id' => $client->id,
                'fileable_type' => get_class($fileable),
                'fileable_id' => $fileable->id,
                'total_price' => $data['total_price'],
                'down_payment' => $data['down_payment'],
                'remaining_balance' => $remainingBalance,
                'installment_amount' => $installmentAmount,
                'installment_frequency' => $data['installment_frequency'],
                'total_installments' => $data['total_installments'],
                'start_date' => $data['start_date'],
                'end_date' => $endDate,
                'dealer_id' => $data['dealer_id'] ?? auth()->id(),
                'late_fee_percentage' => $data['late_fee_percentage'] ?? 2.00,
                'grace_period_days' => $data['grace_period_days'] ?? 7,
                'notes' => $data['notes'] ?? null,
            ]);

            // Generate installment schedule
            $this->installmentService->generateSchedule($file);

            // Process down payment if provided
            if ($data['down_payment'] > 0 && isset($data['down_payment_details'])) {
                $this->paymentService->processDownPayment($file, $data['down_payment_details']);
            }

            DB::commit();

            return $file->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate end date based on frequency
     */
    protected function calculateEndDate($startDate, $totalInstallments, $frequency)
    {
        $date = \Carbon\Carbon::parse($startDate);

        return match($frequency) {
            'monthly' => $date->addMonths($totalInstallments),
            'quarterly' => $date->addMonths($totalInstallments * 3),
            'semi-annually' => $date->addMonths($totalInstallments * 6),
            'annually' => $date->addYears($totalInstallments),
            default => $date->addMonths($totalInstallments),
        };
    }

    /**
     * Get file dashboard data
     */
    public function getFileDashboard(PropertyFile $file): array
    {
        $paymentSummary = $this->paymentService->getPaymentSummary($file);

        $overdueInstallments = $file->installments()
            ->where('is_overdue', true)
            ->where('status', '!=', 'paid')
            ->count();

        $upcomingInstallments = $file->installments()
            ->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->count();

        return array_merge($paymentSummary, [
            'overdue_installments_count' => $overdueInstallments,
            'upcoming_installments_count' => $upcomingInstallments,
            'recent_payments' => $file->payments()
                ->with('receivedBy')
                ->latest()
                ->take(5)
                ->get(),
            'installment_schedule' => $file->installments()
                ->orderBy('due_date', 'asc')
                ->get(),
        ]);
    }
}
```

---

## 🎮 9. CONTROLLER LOGIC

### PropertyFile Controller

```php
// app/Http/Controllers/PropertyFileController.php

<?php

namespace App\Http\Controllers;

use App\Models\PropertyFile;
use App\Models\Client;
use App\Models\Plot;
use App\Models\FileInstallment;
use App\Services\FileManagementService;
use App\Services\PaymentProcessingService;
use App\Services\FileTransferService;
use App\Services\ReceiptGenerationService;
use Illuminate\Http\Request;

class PropertyFileController extends Controller
{
    public function __construct(
        protected FileManagementService $fileService,
        protected PaymentProcessingService $paymentService,
        protected FileTransferService $transferService,
        protected ReceiptGenerationService $receiptService,
    ) {
        $this->middleware('auth');
        $this->middleware('permission:files.view_all')->only('index');
        $this->middleware('permission:files.create')->only(['create', 'store']);
        $this->middleware('permission:files.update')->only(['edit', 'update']);
        $this->middleware('permission:files.transfer')->only(['transfer', 'processTransfer']);
    }

    /**
     * Display listing of files
     */
    public function index(Request $request)
    {
        $query = PropertyFile::with(['client', 'fileable', 'dealer']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Check user role
        if (auth()->user()->hasRole('dealer')) {
            $query->where('dealer_id', auth()->id());
        }

        $files = $query->latest()->paginate(20);

        return view('files.index', compact('files'));
    }

    /**
     * Show file details with dashboard
     */
    public function show(PropertyFile $file)
    {
        $dashboard = $this->fileService->getFileDashboard($file);

        return view('files.show', compact('file', 'dashboard'));
    }

    /**
     * Create new file
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'fileable_type' => 'required|in:App\Models\Plot,App\Models\Property',
            'fileable_id' => 'required',
            'total_price' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'installment_frequency' => 'required|in:monthly,quarterly,semi-annually,annually',
            'total_installments' => 'required|integer|min:1',
            'start_date' => 'required|date',
        ]);

        try {
            $client = Client::findOrFail($validated['client_id']);
            $fileable = $validated['fileable_type']::findOrFail($validated['fileable_id']);

            $file = $this->fileService->createFile($client, $fileable, $validated);

            return redirect()->route('files.show', $file)
                ->with('success', 'Property file created successfully!');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating file: ' . $e->getMessage());
        }
    }

    /**
     * Record payment
     */
    public function recordPayment(Request $request, FileInstallment $installment)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,online,card',
            'payment_date' => 'required|date',
            'bank_reference' => 'nullable|string',
            'cheque_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = $this->paymentService->processInstallmentPayment($installment, $validated);

            return redirect()->route('files.show', $installment->property_file_id)
                ->with('success', 'Payment recorded successfully! Receipt: ' . $payment->receipt_number);

        } catch (\Exception $e) {
            return back()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt(FilePayment $payment)
    {
        try {
            $path = $this->receiptService->generatePDF($payment);

            return Storage::disk('public')->download($path);

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating receipt: ' . $e->getMessage());
        }
    }

    /**
     * Initiate file transfer
     */
    public function processTransfer(Request $request, PropertyFile $file)
    {
        $validated = $request->validate([
            'to_client_id' => 'required|exists:clients,id',
            'transfer_fee' => 'required|numeric|min:0',
            'reason' => 'required|string',
        ]);

        try {
            $toClient = Client::findOrFail($validated['to_client_id']);
            $transfer = $this->transferService->initiateTransfer($file, $toClient, $validated);

            return redirect()->route('files.show', $file)
                ->with('success', 'Transfer initiated. Waiting for approval.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error initiating transfer: ' . $e->getMessage());
        }
    }
}
```

---

## 🔄 10. COMPLETE WORKFLOW

### Workflow Diagram

```
┌──────────────────────────────────────────────────────────────┐
│              FILE MANAGEMENT WORKFLOW                        │
└──────────────────────────────────────────────────────────────┘

1. CREATE FILE
   │
   ├─► Client selects Plot/Property
   ├─► Define payment terms (down payment, installments)
   ├─► FileManagementService::createFile()
   ├─► Auto-generate installment schedule
   ├─► Record down payment (if provided)
   └─► File Status: "active"

2. INSTALLMENT SCHEDULE
   │
   ├─► Monthly/Quarterly/etc. installments generated
   ├─► Each with due_date, amount, status: "pending"
   └─► Grace period configured (default 7 days)

3. PAYMENT PROCESSING
   │
   ├─► Client makes payment
   ├─► PaymentProcessingService::processInstallmentPayment()
   ├─► Create FilePayment record
   ├─► Update FileInstallment (mark as paid)
   ├─► Auto-generate receipt (PDF)
   ├─► Update PropertyFile (paid_installments, remaining_balance)
   └─► Check if complete (mark as "completed")

4. LATE PAYMENT TRACKING (Daily Cron)
   │
   ├─► Check all pending installments
   ├─► If due_date + grace_period passed:
   │   ├─► Mark as "overdue"
   │   ├─► Calculate late_fee
   │   └─► Update days_overdue
   ├─► Send payment reminders (7 days before due)
   └─► Mark as "defaulted" (90+ days overdue)

5. FILE TRANSFER (Optional)
   │
   ├─► FileTransferService::initiateTransfer()
   ├─► Status: "pending"
   ├─► Manager reviews and approves
   ├─► FileTransferService::approveTransfer()
   ├─► Update PropertyFile client_id
   ├─► Update Plot/Property owner
   └─► Status: "transferred" → "completed"

6. COMPLETION
   │
   ├─► All installments paid
   ├─► Remaining balance = 0
   ├─► PropertyFile status: "completed"
   └─► Plot/Property status: "sold"
```

### API Routes Example

```php
// routes/api.php

Route::middleware(['auth:sanctum'])->prefix('v1/files')->group(function () {
    // Files
    Route::get('/', [PropertyFileController::class, 'index']);
    Route::post('/', [PropertyFileController::class, 'store']);
    Route::get('/{file}', [PropertyFileController::class, 'show']);

    // Payments
    Route::post('/installments/{installment}/pay', [PropertyFileController::class, 'recordPayment']);
    Route::get('/payments/{payment}/receipt', [PropertyFileController::class, 'downloadReceipt']);

    // Transfers
    Route::post('/{file}/transfer', [PropertyFileController::class, 'processTransfer']);
    Route::post('/transfers/{transfer}/approve', [TransferController::class, 'approve']);

    // Reports
    Route::get('/{file}/dashboard', [PropertyFileController::class, 'dashboard']);
    Route::get('/overdue', [FileReportController::class, 'overdue']);
});
```

---

## ✅ IMPLEMENTATION CHECKLIST

### Database
- [ ] Create all 5 migrations
- [ ] Run migrations
- [ ] Create seeders (optional)

### Models
- [ ] PropertyFile model with relationships
- [ ] FileInstallment model
- [ ] FilePayment model
- [ ] FileTransfer model
- [ ] PaymentReceipt model

### Services
- [ ] InstallmentGenerationService
- [ ] PaymentProcessingService
- [ ] LatePaymentTrackingService
- [ ] FileTransferService
- [ ] ReceiptGenerationService
- [ ] FileManagementService

### Controllers
- [ ] PropertyFileController
- [ ] PaymentController
- [ ] TransferController

### Commands & Jobs
- [ ] CheckOverdueInstallments command
- [ ] Schedule daily cron job
- [ ] SendPaymentReminders job (optional)

### Views & PDF
- [ ] File listing view
- [ ] File details/dashboard view
- [ ] Payment form
- [ ] Receipt PDF template
- [ ] Transfer form

### Additional
- [ ] Install dompdf package: `composer require barryvdh/laravel-dompdf`
- [ ] Configure storage disk for receipts
- [ ] Set up email/SMS for notifications
- [ ] Add permission checks
- [ ] Write tests

---

**Created**: January 28, 2026
**Laravel Version**: 11.x
**Features**: Auto Installments, Late Tracking, Transfers, PDF Receipts

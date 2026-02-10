# Property File & Payment System

## Overview

The Property File System manages property/plot sale files with comprehensive payment tracking. Each file represents a sale agreement with a client, including payment plans, installment schedules, and complete payment history with remaining balance calculations.

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Database Schema](#database-schema)
3. [Models & Relationships](#models--relationships)
4. [Payment Logic](#payment-logic)
5. [Controller Methods](#controller-methods)
6. [Routes](#routes)
7. [Usage Examples](#usage-examples)
8. [Integration Guide](#integration-guide)

---

## System Architecture

### Core Components

```
┌─────────────────────────────────────────────────────────────┐
│              PROPERTY FILE & PAYMENT SYSTEM                 │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐     ┌──────────────┐     ┌────────────┐ │
│  │ PROPERTY FILE│────→│FILE PAYMENT  │────→│  BALANCE   │ │
│  │   (File)     │     │  (Tracking)  │     │(Remaining) │ │
│  └──────────────┘     └──────────────┘     └────────────┘ │
│        │                     │                            │
│        ├──→ CLIENT           ├──→ PAYMENT TYPE           │
│        ├──→ PROPERTY/PLOT    ├──→ PAYMENT METHOD         │
│        ├──→ DEAL             └──→ STATUS (Cleared/Bounced)│
│        └──→ INSTALLMENT PLAN                             │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Payment Flow

```
FILE CREATED → DOWN PAYMENT → INSTALLMENTS → BALANCE TRACKING → FILE COMPLETED
     ↓              ↓              ↓                ↓                 ↓
  Agreement    First Payment   Monthly/Quarterly  Auto-Calculate  Ownership
```

---

## Database Schema

### `property_files` Table

```php
Schema::create('property_files', function (Blueprint $table) {
    $table->id();
    $table->string('file_number')->unique();

    // Parties
    $table->foreignId('client_id')->constrained()->onDelete('cascade');
    $table->morphs('fileable'); // Plot or Property
    $table->foreignId('deal_id')->nullable()->constrained()->onDelete('set null');

    // Payment Details
    $table->decimal('total_amount', 15, 2);
    $table->decimal('paid_amount', 15, 2)->default(0);
    $table->decimal('remaining_amount', 15, 2);

    // Payment Plan
    $table->string('payment_plan')->default('installment'); // cash, installment
    $table->integer('total_installments')->nullable();
    $table->decimal('installment_amount', 15, 2)->nullable();
    $table->string('installment_frequency')->nullable(); // monthly, quarterly, yearly
    $table->date('first_installment_date')->nullable();
    $table->date('last_installment_date')->nullable();

    // File Status
    $table->string('status')->default('active'); // active, completed, transferred, cancelled, defaulted
    $table->date('issue_date');
    $table->date('completion_date')->nullable();

    // Transfer
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
```

### `file_payments` Table

```php
Schema::create('file_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('property_file_id')->constrained('property_files')->onDelete('cascade');
    $table->string('payment_number')->unique();
    $table->decimal('amount', 15, 2);
    $table->date('payment_date');
    $table->date('due_date')->nullable();

    // Payment Classification
    $table->enum('payment_type', [
        'down_payment',
        'installment',
        'partial',
        'full_payment',
        'transfer_charges',
        'penalty',
        'adjustment'
    ])->default('installment');

    $table->enum('payment_method', [
        'cash',
        'cheque',
        'bank_transfer',
        'online',
        'card'
    ])->default('cash');

    $table->string('reference_number')->nullable(); // Cheque/Transaction number
    $table->string('bank_name')->nullable();
    $table->integer('installment_number')->nullable();

    // Payment Status
    $table->enum('status', [
        'pending',
        'received',
        'cleared',
        'bounced',
        'cancelled'
    ])->default('received');

    $table->date('clearance_date')->nullable();
    $table->decimal('penalty_amount', 15, 2)->default(0);
    $table->decimal('discount_amount', 15, 2)->default(0);

    $table->text('remarks')->nullable();
    $table->json('documents')->nullable(); // Receipt, cheque image
    $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
    $table->softDeletes();

    $table->index(['property_file_id', 'payment_date']);
    $table->index(['status', 'payment_type']);
    $table->index('installment_number');
});
```

---

## Models & Relationships

### PropertyFile Model

**Location:** `app/Models/PropertyFile.php`

#### Constants

```php
// Status
const STATUS_ACTIVE = 'active';
const STATUS_COMPLETED = 'completed';
const STATUS_TRANSFERRED = 'transferred';
const STATUS_CANCELLED = 'cancelled';
const STATUS_DEFAULTED = 'defaulted';

// Payment Plan
const PLAN_CASH = 'cash';
const PLAN_INSTALLMENT = 'installment';
```

#### Relationships

```php
fileable(): MorphTo              // Property or Plot
client(): BelongsTo              // Current owner
deal(): BelongsTo                // Associated deal
transferredFromClient(): BelongsTo // Previous owner
filePayments(): HasMany          // All payments
creator(): BelongsTo             // User who created file
```

#### Key Methods

**Balance Calculations:**
```php
getRemainingBalance(): float              // Total - Paid
getPaymentProgress(): float               // Percentage paid (0-100)
incrementPaidAmount(float): void          // Add to paid amount
decrementPaidAmount(float): void          // Reduce paid (for bounced payments)
calculateTotalPaid(): float               // Sum from payments table
syncPaidAmount(): void                    // Recalculate from payments
```

**Payment Tracking:**
```php
getNextInstallmentDue()                   // Next pending installment
getOverduePayments()                      // All overdue payments
getPaidInstallmentsCount(): int           // Count of paid installments
getPendingInstallmentsCount(): int        // Count of pending installments
hasOverduePayments(): bool                // Check if has overdue
```

**Status Checks:**
```php
isFullyPaid(): bool                       // Check if completed
isActive(): bool                          // Check if active
isCompleted(): bool                       // Check if completed
```

**Installment Schedule:**
```php
getInstallmentSchedule(): array           // Full payment schedule with dates
```

**File Management:**
```php
transferTo(Client, charges, remarks): bool // Transfer to new client
markAsDefaulted(reason): bool              // Mark as defaulted
cancel(reason): bool                       // Cancel file
updatePaymentStatus(): void                // Update based on payments
```

### FilePayment Model

**Location:** `app/Models/FilePayment.php`

#### Constants

```php
// Payment Types
const TYPE_DOWN_PAYMENT = 'down_payment';
const TYPE_INSTALLMENT = 'installment';
const TYPE_PARTIAL = 'partial';
const TYPE_FULL_PAYMENT = 'full_payment';
const TYPE_TRANSFER_CHARGES = 'transfer_charges';
const TYPE_PENALTY = 'penalty';
const TYPE_ADJUSTMENT = 'adjustment';

// Payment Methods
const METHOD_CASH = 'cash';
const METHOD_CHEQUE = 'cheque';
const METHOD_BANK_TRANSFER = 'bank_transfer';
const METHOD_ONLINE = 'online';
const METHOD_CARD = 'card';

// Status
const STATUS_PENDING = 'pending';
const STATUS_RECEIVED = 'received';
const STATUS_CLEARED = 'cleared';
const STATUS_BOUNCED = 'bounced';
const STATUS_CANCELLED = 'cancelled';
```

#### Relationships

```php
propertyFile(): BelongsTo         // Parent file
receiver(): BelongsTo             // User who received payment
```

#### Scopes

```php
scopeReceived($query)             // Received payments
scopeCleared($query)              // Cleared payments
scopePending($query)              // Pending payments
scopeBounced($query)              // Bounced payments
scopeByType($query, $type)        // Filter by payment type
scopeByMethod($query, $method)    // Filter by payment method
scopeOverdue($query)              // Overdue payments
```

#### Key Methods

```php
getNetAmount(): float             // Amount after penalties/discounts
isOverdue(): bool                 // Check if overdue
isReceived(): bool                // Check if received
isCleared(): bool                 // Check if cleared
isBounced(): bool                 // Check if bounced
markAsReceived(): bool            // Mark as received
markAsCleared(): bool             // Mark as cleared + update file
markAsBounced(reason): bool       // Mark as bounced + reverse payment
cancel(reason): bool              // Cancel payment
calculatePenalty(rate): float     // Calculate late payment penalty
generatePaymentNumber(): string   // Auto-generate payment number
```

---

## Payment Logic

### Remaining Balance Calculation

```php
// Formula
remaining_amount = total_amount - paid_amount

// Automatic updates
- When payment is cleared: paid_amount += payment_amount
- When payment is bounced: paid_amount -= payment_amount
- When file is completed: remaining_amount = 0
```

### Payment Status Flow

```
PENDING → RECEIVED → CLEARED → File Balance Updated
   ↓
BOUNCED → Reverse Balance Update
```

### Installment Schedule Generation

```php
// Monthly installments
First installment: 2024-01-01
Month 2: 2024-02-01
Month 3: 2024-03-01
...

// Quarterly installments
Quarter 1: 2024-01-01
Quarter 2: 2024-04-01
Quarter 3: 2024-07-01
...

// Yearly installments
Year 1: 2024-01-01
Year 2: 2025-01-01
Year 3: 2026-01-01
```

### Payment Type Logic

| Type | Description | Affects Balance |
|------|-------------|-----------------|
| `down_payment` | Initial payment | Yes |
| `installment` | Monthly/periodic payment | Yes |
| `partial` | Partial installment | Yes |
| `full_payment` | Full payment at once | Yes |
| `transfer_charges` | Transfer fees | Separate |
| `penalty` | Late payment penalty | Added to balance |
| `adjustment` | Balance adjustment | Yes |

---

## Controller Methods

### PropertyFileController

#### File Management

```php
index(Request)                    // List files with filters
create()                          // Show create form
store(Request)                    // Create new file
show(PropertyFile)                // Show file details with stats
edit(PropertyFile)                // Show edit form
update(Request, PropertyFile)     // Update file
```

#### Payment Operations

```php
addPayment(Request, PropertyFile) // Add payment to file
clearPayment(FilePayment)         // Mark payment as cleared
bouncePayment(Request, FilePayment) // Mark payment as bounced
paymentReceipt(FilePayment)       // Generate payment receipt
```

#### File Operations

```php
transfer(Request, PropertyFile)   // Transfer file to new client
statement(PropertyFile)           // Generate file statement
syncPaidAmount(PropertyFile)      // Sync paid amount from payments
markAsDefaulted(Request, PropertyFile) // Mark as defaulted
cancelFile(Request, PropertyFile) // Cancel file
```

---

## Routes

```php
// File Management
GET    /files                           files.index
GET    /files/{file}                    files.show
GET    /files/create                    files.create
POST   /files                           files.store
GET    /files/{file}/edit               files.edit
PUT    /files/{file}                    files.update
GET    /files/{file}/statement          files.statement

// File Actions
POST   /files/{file}/transfer           files.transfer
POST   /files/{file}/sync-paid-amount   files.sync-paid-amount
POST   /files/{file}/mark-defaulted     files.mark-defaulted
POST   /files/{file}/cancel             files.cancel

// Payment Management
POST   /files/{file}/payments           files.add-payment
POST   /file-payments/{payment}/clear   file-payments.clear
POST   /file-payments/{payment}/bounce  file-payments.bounce
GET    /file-payments/{payment}/receipt file-payments.receipt
```

---

## Usage Examples

### 1. Create Property File with Installments

```php
use App\Models\PropertyFile;
use App\Models\Client;
use App\Models\Property;

$file = PropertyFile::create([
    'file_number' => PropertyFile::generateFileNumber(),
    'client_id' => 1,
    'fileable_type' => Property::class,
    'fileable_id' => 5,
    'deal_id' => 10,
    'total_amount' => 5000000.00,
    'paid_amount' => 0,
    'remaining_amount' => 5000000.00,
    'payment_plan' => PropertyFile::PLAN_INSTALLMENT,
    'total_installments' => 24,
    'installment_amount' => 208333.33, // ~5M / 24
    'installment_frequency' => 'monthly',
    'first_installment_date' => now()->addMonth(),
    'issue_date' => now(),
    'status' => PropertyFile::STATUS_ACTIVE,
    'created_by' => auth()->id(),
]);
```

### 2. Add Down Payment

```php
use App\Models\FilePayment;

$payment = FilePayment::create([
    'property_file_id' => $file->id,
    'amount' => 500000.00, // 10% down payment
    'payment_date' => now(),
    'payment_type' => FilePayment::TYPE_DOWN_PAYMENT,
    'payment_method' => FilePayment::METHOD_CASH,
    'status' => FilePayment::STATUS_RECEIVED,
    'received_by' => auth()->id(),
]);

// Auto-clear cash payment
$payment->markAsCleared();
// File balance automatically updated: paid_amount = 500,000, remaining = 4,500,000
```

### 3. Add Installment Payment

```php
$installment = FilePayment::create([
    'property_file_id' => $file->id,
    'amount' => 208333.33,
    'payment_date' => now(),
    'due_date' => now(),
    'payment_type' => FilePayment::TYPE_INSTALLMENT,
    'payment_method' => FilePayment::METHOD_CHEQUE,
    'reference_number' => 'CHQ-12345',
    'bank_name' => 'HBL',
    'installment_number' => 1,
    'status' => FilePayment::STATUS_RECEIVED,
    'received_by' => auth()->id(),
]);

// Mark as cleared after cheque clears
$installment->markAsCleared();
```

### 4. Handle Bounced Payment

```php
$payment = FilePayment::find(1);

// Mark as bounced
$payment->markAsBounced('Insufficient funds');

// This automatically:
// - Sets status to 'bounced'
// - Reverses the paid_amount in PropertyFile
// - Adds remark with reason
```

### 5. Get File Balance & Progress

```php
$file = PropertyFile::find(1);

$remainingBalance = $file->getRemainingBalance(); // e.g., 3,500,000
$progress = $file->getPaymentProgress(); // e.g., 30% (1,500,000 / 5,000,000)
$isFullyPaid = $file->isFullyPaid(); // false
$paidInstallments = $file->getPaidInstallmentsCount(); // e.g., 5
$pendingInstallments = $file->getPendingInstallmentsCount(); // e.g., 19
```

### 6. Get Installment Schedule

```php
$schedule = $file->getInstallmentSchedule();

// Returns:
[
    [
        'installment_number' => 1,
        'due_date' => '2024-02-01',
        'amount' => 208333.33,
        'status' => 'cleared',
        'paid_date' => '2024-02-01',
        'payment_id' => 123,
    ],
    [
        'installment_number' => 2,
        'due_date' => '2024-03-01',
        'amount' => 208333.33,
        'status' => 'pending',
        'paid_date' => null,
        'payment_id' => null,
    ],
    // ... 24 installments
]
```

### 7. Get Overdue Payments

```php
$overduePayments = $file->getOverduePayments();

foreach ($overduePayments as $payment) {
    echo "Installment {$payment->installment_number} ";
    echo "was due on {$payment->due_date->format('Y-m-d')} ";
    echo "for PKR {$payment->amount}\n";

    // Calculate penalty (1% per month)
    $penalty = $payment->calculatePenalty(1.0);
}
```

### 8. Transfer File to New Client

```php
$newClient = Client::find(5);
$transferCharges = 50000.00;

$file->transferTo($newClient, $transferCharges, 'Sold to new buyer');

// This automatically:
// - Updates transferred_from_client
// - Changes client_id to new client
// - Sets transfer_date and is_transferred
// - Sets status to 'transferred'
// - Creates transfer charges payment (if applicable)
```

### 9. Sync Paid Amount from Payments

```php
// If paid_amount is out of sync with actual payments
$file->syncPaidAmount();

// This recalculates:
// - paid_amount = SUM(cleared payments)
// - remaining_amount = total_amount - paid_amount
// - Updates status to 'completed' if fully paid
```

### 10. Generate File Statement

```php
$file = PropertyFile::with(['client', 'fileable', 'filePayments'])->find(1);

$statement = [
    'file_number' => $file->file_number,
    'client' => $file->client->name,
    'property' => $file->fileable->title,
    'total_amount' => $file->total_amount,
    'paid_amount' => $file->paid_amount,
    'remaining_balance' => $file->getRemainingBalance(),
    'progress' => $file->getPaymentProgress() . '%',
    'payments' => $file->filePayments->map(function($payment) {
        return [
            'date' => $payment->payment_date->format('Y-m-d'),
            'type' => $payment->payment_type,
            'amount' => $payment->amount,
            'method' => $payment->payment_method,
            'status' => $payment->status,
        ];
    }),
];
```

---

## Integration Guide

### With Client Module

```php
// Get all files for a client
$client = Client::find(1);
$files = $client->propertyFiles;

// Active files
$activeFiles = $client->propertyFiles()->active()->get();

// Total outstanding balance
$totalOutstanding = $client->propertyFiles()
    ->active()
    ->sum('remaining_amount');
```

### With Property Module

```php
// Get all files for a property
$property = Property::find(1);
$files = $property->propertyFiles;

// Current file (if any)
$currentFile = $property->propertyFiles()
    ->whereIn('status', ['active', 'completed'])
    ->latest()
    ->first();
```

### With Plot Module

```php
// Get all files for a plot
$plot = Plot::find(1);
$files = $plot->propertyFiles;

// Check if plot is under file
$hasActiveFile = $plot->propertyFiles()
    ->active()
    ->exists();
```

### With Deal Module

```php
// Create file from deal
$deal = Deal::find(1);

$file = PropertyFile::create([
    'client_id' => $deal->client_id,
    'deal_id' => $deal->id,
    'fileable_type' => $deal->dealable_type,
    'fileable_id' => $deal->dealable_id,
    'total_amount' => $deal->deal_amount,
    'payment_plan' => $deal->payment_type,
    // ... other fields from deal
]);

// Get file from deal
$file = $deal->propertyFile;
```

---

## Balance Logic Flow

### Payment Clearing Process

```
1. Payment Created
   └─> status = 'received'
   └─> paid_amount NOT updated yet

2. Payment Cleared (manually or auto for cash)
   └─> status = 'cleared'
   └─> clearance_date = now()
   └─> file->paid_amount += payment->amount
   └─> file->remaining_amount = total - paid
   └─> IF remaining <= 0: status = 'completed'

3. Payment Bounced
   └─> status = 'bounced'
   └─> file->paid_amount -= payment->amount (reverse)
   └─> file->status = 'active' (if was completed)
```

### Automatic Balance Updates

```php
// Triggered when:
1. FilePayment created (if status = 'cleared')
2. FilePayment->markAsCleared()
3. FilePayment->markAsBounced() (reverses)
4. PropertyFile->syncPaidAmount() (manual sync)
```

---

## Business Rules

1. **Payment Clearing:** Only cleared payments count toward paid_amount
2. **Cash Payments:** Auto-cleared immediately
3. **Cheque Payments:** Must be manually cleared after bank clearance
4. **Bounced Payments:** Automatically reverse the paid_amount
5. **File Completion:** Automatic when remaining_amount <= 0
6. **Overdue Detection:** Based on due_date vs current date
7. **Transfer Charges:** Separate from main balance
8. **Installment Numbering:** Sequential starting from 1

---

## Formulas

### Payment Calculations

```php
// Monthly Installment
monthly_installment = (total_amount - down_payment) / total_installments

// Remaining Balance
remaining_balance = total_amount - paid_amount

// Payment Progress
progress = (paid_amount / total_amount) * 100

// Net Payment Amount
net_amount = amount + penalty_amount - discount_amount

// Late Payment Penalty (daily)
penalty = amount * (penalty_rate / 100) * days_late
```

---

## Permissions Required

```
files.view          View files
files.create        Create files
files.edit          Edit files
files.transfer      Transfer files
files.manage        Manage file status (default, cancel)
payments.view       View payments
payments.create     Add payments
payments.edit       Clear/bounce payments
```

---

## Best Practices

1. **Always use clearPayment()** instead of manually updating status
2. **Sync paid amounts regularly** using syncPaidAmount()
3. **Track payment methods** accurately for reconciliation
4. **Document transfer reasons** in remarks
5. **Handle bounced payments immediately** to maintain accurate balance
6. **Generate receipts** for every payment
7. **Monitor overdue payments** and send reminders
8. **Use installment schedules** for payment planning

---

## Troubleshooting

**Q: Balance not updating after payment?**
```php
// Check payment status
$payment->status; // Must be 'cleared'

// Manually sync
$file->syncPaidAmount();
```

**Q: File not marking as completed?**
```php
// Check remaining balance
$file->getRemainingBalance(); // Should be <= 0

// Force update
$file->updatePaymentStatus();
```

**Q: Overdue payments not showing?**
```php
// Check due dates
$payments = $file->filePayments()
    ->where('status', 'pending')
    ->where('due_date', '<', now())
    ->get();
```

---

## Summary

The Property File & Payment System provides:
- ✅ Complete file management (create, transfer, cancel)
- ✅ Comprehensive payment tracking (received, cleared, bounced)
- ✅ Automatic balance calculations
- ✅ Installment schedule generation
- ✅ Overdue payment detection
- ✅ Transfer management
- ✅ Integration with all modules (Client, Property, Plot, Deal)

---

**Version:** 1.0
**Last Updated:** 2024
**Maintained By:** RealEstate CRM Team

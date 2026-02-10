# Payment & Expense Management System

## Overview

The Payment & Expense Management System is a comprehensive accounting module for tracking all financial transactions in your Real Estate CRM. It handles:

1. **Incoming Payments** - Revenue from various sources (commissions, rents, deposits, etc.)
2. **Expenses** - Business costs and expenditures (utilities, salaries, maintenance, etc.)
3. **Payment Types** - Categorized payment/expense types for organized tracking

This is separate from the **FilePayments** module (which tracks property file installments) and provides general accounting functionality.

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Database Schema](#database-schema)
3. [Models & Relationships](#models--relationships)
4. [Controller Methods](#controller-methods)
5. [Routes](#routes)
6. [Usage Examples](#usage-examples)
7. [Integration Guide](#integration-guide)
8. [Reports & Analytics](#reports--analytics)

---

## System Architecture

### Core Components

```
┌─────────────────────────────────────────────────────────────┐
│         PAYMENT & EXPENSE MANAGEMENT SYSTEM                 │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────┐     ┌──────────────┐     ┌────────────┐ │
│  │PAYMENT TYPES │────→│   PAYMENTS   │────→│  REPORTS   │ │
│  │(Categories)  │     │  (Income)    │     │(Analytics) │ │
│  └──────────────┘     └──────────────┘     └────────────┘ │
│        │                     │                            │
│        │              ┌──────┴──────┐                     │
│        │              │             │                     │
│        ↓              ↓             ↓                     │
│  ┌──────────────┐  Client      Deal                      │
│  │   EXPENSES   │  Property    Dealer                    │
│  │   (Costs)    │  PropertyFile (Polymorphic)            │
│  └──────────────┘                                         │
│        │                                                  │
│        └──→ Recurring Expenses (Auto-generate)           │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Flow Diagram

```
INCOME FLOW:
Client Payment → Payment Type → AccountPayment → Linked to (Client/Deal/PropertyFile)

EXPENSE FLOW:
Expense Need → Payment Type → Expense → Linked to (Property/Deal) → Recurring Check → Next Expense
```

---

## Database Schema

### 1. `payment_types` Table

Categorizes both incoming payments and expenses.

```php
Schema::create('payment_types', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->enum('category', ['income', 'expense']); // Income or Expense
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->integer('display_order')->default(0);
    $table->timestamps();
    $table->softDeletes();

    $table->index(['category', 'is_active']);
});
```

**Example Payment Types:**
- **Income:** Commission, Rent, Deposit, Advance, Registration Fee, Transfer Fee
- **Expense:** Office Rent, Utilities, Salaries, Marketing, Maintenance, Fuel, Taxes

### 2. `payments` Table (Income)

Tracks all incoming payments/revenue.

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->string('payment_number')->unique();
    $table->foreignId('payment_type_id')->constrained('payment_types')->onDelete('restrict');

    // Polymorphic - Link to Client, Deal, PropertyFile, Dealer, etc.
    $table->nullableMorphs('payable'); // payable_type, payable_id

    $table->decimal('amount', 15, 2);
    $table->date('payment_date');

    // Payment Method
    $table->enum('payment_method', [
        'cash', 'cheque', 'bank_transfer', 'online', 'card', 'other'
    ])->default('cash');

    $table->string('reference_number')->nullable(); // Cheque/Transaction ID
    $table->string('bank_name')->nullable();
    $table->string('account_number')->nullable();

    // Status
    $table->enum('status', [
        'pending', 'received', 'cleared', 'bounced', 'cancelled'
    ])->default('received');

    $table->date('clearance_date')->nullable();

    // Additional Details
    $table->string('received_from')->nullable(); // Name if not linked
    $table->string('contact_number')->nullable();
    $table->text('purpose')->nullable(); // Purpose of payment
    $table->text('remarks')->nullable();
    $table->json('documents')->nullable(); // Receipt, invoice scans

    $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
    $table->softDeletes();

    $table->index(['payment_date', 'status']);
    $table->index(['payment_type_id', 'payment_date']);
    $table->index('clearance_date');
});
```

### 3. `expenses` Table

Tracks all business expenses and costs.

```php
Schema::create('expenses', function (Blueprint $table) {
    $table->id();
    $table->string('expense_number')->unique();
    $table->foreignId('payment_type_id')->constrained('payment_types')->onDelete('restrict');

    // Polymorphic - Link to Property, Deal, Project, etc.
    $table->nullableMorphs('expensable'); // expensable_type, expensable_id

    $table->decimal('amount', 15, 2);
    $table->date('expense_date');

    // Payment Method
    $table->enum('payment_method', [
        'cash', 'cheque', 'bank_transfer', 'online', 'card', 'credit', 'other'
    ])->default('cash');

    $table->string('reference_number')->nullable(); // Invoice/Cheque number
    $table->string('bank_name')->nullable();
    $table->string('account_number')->nullable();

    // Status
    $table->enum('status', [
        'pending', 'paid', 'cleared', 'cancelled', 'refunded'
    ])->default('paid');

    $table->date('payment_date')->nullable(); // When paid
    $table->date('clearance_date')->nullable();

    // Vendor/Payee Details
    $table->string('paid_to'); // Vendor/supplier name
    $table->string('contact_number')->nullable();
    $table->text('address')->nullable();
    $table->string('tax_id')->nullable(); // NTN, CNIC

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
    $table->json('documents')->nullable(); // Invoice, receipt scans

    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
    $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
    $table->softDeletes();

    $table->index(['expense_date', 'status']);
    $table->index(['payment_type_id', 'expense_date']);
    $table->index(['is_recurring', 'next_due_date']);
    $table->index('paid_to');
});
```

---

## Models & Relationships

### 1. PaymentType Model

**Location:** `app/Models/PaymentType.php`

#### Constants

```php
const CATEGORY_INCOME = 'income';
const CATEGORY_EXPENSE = 'expense';
```

#### Relationships

```php
accountPayments(): HasMany      // Income payments using this type
expenses(): HasMany             // Expenses using this type
```

#### Key Methods

```php
scopeActive($query)             // Active types only
scopeIncome($query)             // Income types
scopeExpense($query)            // Expense types
scopeOrdered($query)            // Ordered by display_order
isIncome(): bool                // Check if income type
isExpense(): bool               // Check if expense type
```

### 2. AccountPayment Model (Income)

**Location:** `app/Models/AccountPayment.php`

#### Constants

```php
// Payment Methods
METHOD_CASH, METHOD_CHEQUE, METHOD_BANK_TRANSFER, METHOD_ONLINE, METHOD_CARD, METHOD_OTHER

// Status
STATUS_PENDING, STATUS_RECEIVED, STATUS_CLEARED, STATUS_BOUNCED, STATUS_CANCELLED
```

#### Relationships

```php
paymentType(): BelongsTo        // Payment category
payable(): MorphTo              // Linked entity (Client/Deal/etc.)
receiver(): BelongsTo           // User who received payment
```

#### Scopes

```php
scopeReceived($query)           // Received payments
scopeCleared($query)            // Cleared payments
scopePending($query)            // Pending payments
scopeBounced($query)            // Bounced payments
scopeByType($query, $typeId)    // Filter by payment type
scopeByMethod($query, $method)  // Filter by payment method
scopeDateRange($query, $start, $end) // Date range filter
scopeCurrentMonth($query)       // Current month
scopeCurrentYear($query)        // Current year
```

#### Key Methods

```php
isReceived(): bool              // Check status
isCleared(): bool               // Check status
isPending(): bool               // Check status
isBounced(): bool               // Check status
isCash(): bool                  // Check method
markAsReceived(): bool          // Update status
markAsCleared(): bool           // Mark cleared
markAsBounced(?string): bool    // Mark bounced
cancel(?string): bool           // Cancel payment
generatePaymentNumber(): string // PMT-2026-000001
```

#### Auto Features

- **Auto-Number Generation:** `PMT-YEAR-NNNNNN`
- **Auto-Clear Cash:** Cash payments auto-cleared on creation

### 3. Expense Model

**Location:** `app/Models/Expense.php`

#### Constants

```php
// Payment Methods
METHOD_CASH, METHOD_CHEQUE, METHOD_BANK_TRANSFER, METHOD_ONLINE, METHOD_CARD, METHOD_CREDIT, METHOD_OTHER

// Status
STATUS_PENDING, STATUS_PAID, STATUS_CLEARED, STATUS_CANCELLED, STATUS_REFUNDED

// Recurring Frequency
FREQUENCY_MONTHLY, FREQUENCY_QUARTERLY, FREQUENCY_YEARLY
```

#### Relationships

```php
paymentType(): BelongsTo        // Expense category
expensable(): MorphTo           // Linked entity (Property/Deal/etc.)
approver(): BelongsTo           // User who approved
payer(): BelongsTo              // User who paid
```

#### Scopes

```php
scopePending($query)            // Pending expenses
scopePaid($query)               // Paid expenses
scopeCleared($query)            // Cleared expenses
scopeRecurring($query)          // Recurring only
scopeUpcomingDue($query, $days) // Due soon (default 7 days)
scopeOverdue($query)            // Overdue recurring
scopeByType($query, $typeId)    // Filter by type
scopeByMethod($query, $method)  // Filter by method
scopeDateRange($query, $start, $end) // Date range
scopeCurrentMonth($query)       // Current month
scopeCurrentYear($query)        // Current year
scopeByVendor($query, $name)    // Filter by vendor
```

#### Key Methods

```php
isPending(): bool               // Check status
isPaid(): bool                  // Check status
isCleared(): bool               // Check status
isCancelled(): bool             // Check status
isRefunded(): bool              // Check status
isRecurring(): bool             // Check if recurring
isOverdue(): bool               // Check if overdue
markAsPaid(?int): bool          // Mark as paid
markAsCleared(): bool           // Mark cleared
cancel(?string): bool           // Cancel expense
markAsRefunded(?string): bool   // Mark refunded
createNextRecurrence(): ?self   // Create next recurring expense
calculateNextDueDate(?Carbon): Carbon // Calculate next due
generateExpenseNumber(): string // EXP-2026-000001
```

#### Auto Features

- **Auto-Number Generation:** `EXP-YEAR-NNNNNN`
- **Auto-Net Calculation:** `net_amount = amount + tax - discount`
- **Auto-Clear Cash:** Cash expenses auto-cleared
- **Recurring Management:** Auto-calculates next due date

---

## Controller Methods

### AccountPaymentController

**Location:** `app/Http/Controllers/AccountPaymentController.php`

#### CRUD Operations

```php
index(Request)                  // List payments with filters
create()                        // Show create form
store(Request)                  // Create payment
show(AccountPayment)            // Show payment details
edit(AccountPayment)            // Show edit form
update(Request, AccountPayment) // Update payment
destroy(AccountPayment)         // Delete payment
```

#### Payment Operations

```php
clearPayment(AccountPayment)    // Mark as cleared
bouncePayment(Request, AccountPayment) // Mark as bounced
cancelPayment(Request, AccountPayment) // Cancel payment
receipt(AccountPayment)         // Generate receipt
```

#### Reports & Linking

```php
report(Request)                 // Generate payment report
linkToEntity(Request, AccountPayment) // Link to entity (Client/Deal/etc.)
```

### ExpenseController

**Location:** `app/Http/Controllers/ExpenseController.php`

#### CRUD Operations

```php
index(Request)                  // List expenses with filters
create()                        // Show create form
store(Request)                  // Create expense
show(Expense)                   // Show expense details
edit(Expense)                   // Show edit form
update(Request, Expense)        // Update expense
destroy(Expense)                // Delete expense
```

#### Expense Operations

```php
markAsPaid(Request, Expense)    // Mark as paid
clearExpense(Expense)           // Mark as cleared
cancelExpense(Request, Expense) // Cancel expense
refundExpense(Request, Expense) // Mark as refunded
approveExpense(Expense)         // Approve expense
```

#### Recurring & Reports

```php
createRecurrence(Expense)       // Create next recurring expense
upcomingRecurring(Request)      // Show upcoming recurring expenses
report(Request)                 // Generate expense report
linkToEntity(Request, Expense)  // Link to entity
```

---

## Routes

### Account Payment Routes

```php
// View Payments
GET    /account-payments                      // List all payments
GET    /account-payments/{accountPayment}     // Show payment
GET    /account-payments/{accountPayment}/receipt // Payment receipt
GET    /payments/report                       // Payment report

// Create Payment
GET    /account-payments/create               // Create form
POST   /account-payments                      // Store payment

// Edit Payment
GET    /account-payments/{accountPayment}/edit // Edit form
PUT    /account-payments/{accountPayment}     // Update payment
POST   /account-payments/{accountPayment}/clear // Clear payment
POST   /account-payments/{accountPayment}/bounce // Bounce payment
POST   /account-payments/{accountPayment}/cancel // Cancel payment
POST   /account-payments/{accountPayment}/link // Link to entity

// Delete Payment
DELETE /account-payments/{accountPayment}     // Delete payment
```

### Expense Routes

```php
// View Expenses
GET    /expenses                              // List all expenses
GET    /expenses/{expense}                    // Show expense
GET    /expenses/report                       // Expense report
GET    /expenses/recurring/upcoming           // Upcoming recurring

// Create Expense
GET    /expenses/create                       // Create form
POST   /expenses                              // Store expense
POST   /expenses/{expense}/recurrence         // Create next recurrence

// Edit Expense
GET    /expenses/{expense}/edit               // Edit form
PUT    /expenses/{expense}                    // Update expense
POST   /expenses/{expense}/mark-paid          // Mark paid
POST   /expenses/{expense}/clear              // Clear expense
POST   /expenses/{expense}/cancel             // Cancel expense
POST   /expenses/{expense}/refund             // Refund expense
POST   /expenses/{expense}/approve            // Approve expense
POST   /expenses/{expense}/link               // Link to entity

// Delete Expense
DELETE /expenses/{expense}                    // Delete expense
```

---

## Usage Examples

### 1. Create Payment Type (Income)

```php
use App\Models\PaymentType;

$paymentType = PaymentType::create([
    'name' => 'Commission Payment',
    'slug' => 'commission-payment',
    'category' => PaymentType::CATEGORY_INCOME,
    'description' => 'Commission received from property sales',
    'is_active' => true,
    'display_order' => 1,
]);
```

### 2. Record Incoming Payment (Standalone)

```php
use App\Models\AccountPayment;

$payment = AccountPayment::create([
    'payment_type_id' => 1, // Commission
    'amount' => 50000.00,
    'payment_date' => now(),
    'payment_method' => AccountPayment::METHOD_CASH,
    'received_from' => 'Ali Khan',
    'contact_number' => '0300-1234567',
    'purpose' => 'Commission for Plot Sale Deal #123',
    'status' => AccountPayment::STATUS_RECEIVED,
    'received_by' => auth()->id(),
]);

// Auto-generated: payment_number = PMT-2026-000001
// Auto-cleared: Cash payments are cleared automatically
```

### 3. Link Payment to Deal

```php
use App\Models\Deal;

$deal = Deal::find(1);

$payment = AccountPayment::create([
    'payment_type_id' => 1,
    'payable_type' => Deal::class,
    'payable_id' => $deal->id,
    'amount' => 100000.00,
    'payment_date' => now(),
    'payment_method' => AccountPayment::METHOD_BANK_TRANSFER,
    'reference_number' => 'TXN-12345',
    'bank_name' => 'HBL',
    'purpose' => 'Deal commission payment',
    'received_by' => auth()->id(),
]);

// Access payment from deal
$deal->accountPayments; // Collection of payments
```

### 4. Record Cheque Payment (Requires Clearing)

```php
$payment = AccountPayment::create([
    'payment_type_id' => 2, // Deposit
    'amount' => 25000.00,
    'payment_date' => now(),
    'payment_method' => AccountPayment::METHOD_CHEQUE,
    'reference_number' => 'CHQ-12345',
    'bank_name' => 'UBL',
    'received_from' => 'Sara Ahmed',
    'status' => AccountPayment::STATUS_RECEIVED, // Not cleared yet
    'received_by' => auth()->id(),
]);

// Later, when cheque clears
$payment->markAsCleared();
// Updates: status = 'cleared', clearance_date = now()
```

### 5. Handle Bounced Payment

```php
$payment = AccountPayment::find(1);

if ($payment->payment_method === AccountPayment::METHOD_CHEQUE) {
    $payment->markAsBounced('Insufficient funds');

    // Payment status changed to 'bounced'
    // Remarks updated with reason
}
```

### 6. Create Expense (One-Time)

```php
use App\Models\Expense;

$expense = Expense::create([
    'payment_type_id' => 5, // Office Utilities
    'amount' => 15000.00,
    'expense_date' => now(),
    'payment_method' => Expense::METHOD_CASH,
    'paid_to' => 'WAPDA',
    'contact_number' => '042-111-123-123',
    'description' => 'Office electricity bill for January 2026',
    'tax_amount' => 2400.00, // 16% GST
    'discount_amount' => 0,
    'status' => Expense::STATUS_PAID,
    'paid_by' => auth()->id(),
]);

// Auto-calculated: net_amount = 17,400 (15,000 + 2,400 - 0)
// Auto-generated: expense_number = EXP-2026-000001
```

### 7. Create Recurring Expense (Monthly)

```php
$expense = Expense::create([
    'payment_type_id' => 6, // Office Rent
    'amount' => 50000.00,
    'expense_date' => now()->startOfMonth(),
    'payment_method' => Expense::METHOD_BANK_TRANSFER,
    'reference_number' => 'RENT-JAN-2026',
    'paid_to' => 'Property Owner',
    'contact_number' => '0300-9876543',
    'description' => 'Monthly office rent',
    'is_recurring' => true,
    'recurring_frequency' => Expense::FREQUENCY_MONTHLY,
    'status' => Expense::STATUS_PAID,
    'paid_by' => auth()->id(),
]);

// Auto-calculated: next_due_date = Feb 1, 2026

// Next month, create recurrence
$nextExpense = $expense->createNextRecurrence();
// Creates new expense for next month
// Updates current expense's next_due_date
```

### 8. Link Expense to Property

```php
use App\Models\Property;

$property = Property::find(1);

$expense = Expense::create([
    'payment_type_id' => 7, // Maintenance
    'expensable_type' => Property::class,
    'expensable_id' => $property->id,
    'amount' => 10000.00,
    'expense_date' => now(),
    'payment_method' => Expense::METHOD_CASH,
    'paid_to' => 'Maintenance Company',
    'description' => 'Property repairs and maintenance',
    'status' => Expense::STATUS_PAID,
    'paid_by' => auth()->id(),
]);

// Access expenses from property
$property->expenses; // Collection of expenses
```

### 9. Get Payment Statistics

```php
// Current month payments
$currentMonthPayments = AccountPayment::currentMonth()->sum('amount');

// Current year payments by type
$commissionPayments = AccountPayment::currentYear()
    ->byType(1) // Commission type
    ->sum('amount');

// Cleared vs pending
$clearedAmount = AccountPayment::cleared()->sum('amount');
$pendingAmount = AccountPayment::pending()->sum('amount');

// By payment method
$cashPayments = AccountPayment::byMethod('cash')->sum('amount');
$chequePayments = AccountPayment::byMethod('cheque')->sum('amount');
```

### 10. Get Expense Statistics

```php
// Current month expenses
$currentMonthExpenses = Expense::currentMonth()->sum('net_amount');

// Expenses by category
$utilitiesExpenses = Expense::byType(5)->currentYear()->sum('net_amount');

// Recurring expenses due this week
$upcomingDue = Expense::upcomingDue(7)->get();

foreach ($upcomingDue as $expense) {
    echo "{$expense->paid_to} - {$expense->net_amount} - Due: {$expense->next_due_date}";
}

// Overdue recurring expenses
$overdueCount = Expense::overdue()->count();

// Top vendors by expense
$topVendors = Expense::currentYear()
    ->selectRaw('paid_to, SUM(net_amount) as total')
    ->groupBy('paid_to')
    ->orderByDesc('total')
    ->take(10)
    ->get();
```

### 11. Generate Payment Report

```php
$startDate = '2026-01-01';
$endDate = '2026-01-31';

$payments = AccountPayment::with(['paymentType', 'receiver'])
    ->dateRange($startDate, $endDate)
    ->orderBy('payment_date')
    ->get();

// Group by payment type
$byType = $payments->groupBy('payment_type.name')->map(function($items) {
    return [
        'count' => $items->count(),
        'total' => $items->sum('amount'),
        'cleared' => $items->where('status', 'cleared')->sum('amount'),
    ];
});

// Group by payment method
$byMethod = $payments->groupBy('payment_method')->map(function($items) {
    return [
        'count' => $items->count(),
        'total' => $items->sum('amount'),
    ];
});
```

### 12. Generate Expense Report

```php
$startDate = '2026-01-01';
$endDate = '2026-01-31';

$expenses = Expense::with(['paymentType', 'payer'])
    ->dateRange($startDate, $endDate)
    ->orderBy('expense_date')
    ->get();

// Total expenses
$totalExpenses = $expenses->sum('net_amount');
$totalTax = $expenses->sum('tax_amount');
$totalDiscounts = $expenses->sum('discount_amount');

// By vendor
$byVendor = $expenses->groupBy('paid_to')->map(function($items) {
    return [
        'count' => $items->count(),
        'total' => $items->sum('net_amount'),
    ];
})->sortByDesc('total');

// By expense type
$byType = $expenses->groupBy('paymentType.name')->map(function($items) {
    return [
        'count' => $items->count(),
        'total' => $items->sum('net_amount'),
        'paid' => $items->whereIn('status', ['paid', 'cleared'])->sum('net_amount'),
    ];
});
```

---

## Integration Guide

### With Client Module

```php
use App\Models\Client;

$client = Client::find(1);

// Get all payments from this client
$clientPayments = $client->accountPayments;

// Total payments received from client
$totalReceived = $client->accountPayments()->sum('amount');

// Record payment from client
$payment = AccountPayment::create([
    'payable_type' => Client::class,
    'payable_id' => $client->id,
    'payment_type_id' => 1,
    'amount' => 50000,
    'payment_date' => now(),
    'received_by' => auth()->id(),
]);
```

### With Deal Module

```php
use App\Models\Deal;

$deal = Deal::find(1);

// Get all payments for this deal
$dealPayments = $deal->accountPayments;

// Record commission payment for deal
$payment = AccountPayment::create([
    'payable_type' => Deal::class,
    'payable_id' => $deal->id,
    'payment_type_id' => 1, // Commission
    'amount' => $deal->commission_amount,
    'payment_date' => now(),
    'purpose' => "Commission for Deal #{$deal->deal_number}",
    'received_by' => auth()->id(),
]);

// Get deal expenses
$dealExpenses = $deal->expenses;
```

### With Property Module

```php
use App\Models\Property;

$property = Property::find(1);

// Get all expenses for this property
$propertyExpenses = $property->expenses;

// Total maintenance costs
$maintenanceCosts = $property->expenses()
    ->byType(7) // Maintenance type
    ->sum('net_amount');

// Record property expense
$expense = Expense::create([
    'expensable_type' => Property::class,
    'expensable_id' => $property->id,
    'payment_type_id' => 7, // Maintenance
    'amount' => 15000,
    'expense_date' => now(),
    'paid_to' => 'Contractor',
    'description' => 'Property repairs',
    'paid_by' => auth()->id(),
]);
```

### With PropertyFile Module

```php
use App\Models\PropertyFile;

$file = PropertyFile::find(1);

// Get all payments for this file
$filePayments = $file->accountPayments;

// Record down payment
$payment = AccountPayment::create([
    'payable_type' => PropertyFile::class,
    'payable_id' => $file->id,
    'payment_type_id' => 2, // Down Payment
    'amount' => 500000,
    'payment_date' => now(),
    'purpose' => "Down payment for File #{$file->file_number}",
    'received_by' => auth()->id(),
]);
```

### With Dealer Module

```php
use App\Models\Dealer;

$dealer = Dealer::find(1);

// Get all commission payments to dealer
$commissionPayments = $dealer->accountPayments;

// Total commission paid to dealer
$totalCommission = $dealer->accountPayments()->sum('amount');

// Record commission payment to dealer
$payment = AccountPayment::create([
    'payable_type' => Dealer::class,
    'payable_id' => $dealer->id,
    'payment_type_id' => 1, // Commission
    'amount' => 75000,
    'payment_date' => now(),
    'purpose' => 'Commission payment for January 2026',
    'received_by' => auth()->id(),
]);
```

---

## Reports & Analytics

### Financial Summary (Current Month)

```php
// Income
$totalIncome = AccountPayment::currentMonth()->sum('amount');
$clearedIncome = AccountPayment::currentMonth()->cleared()->sum('amount');
$pendingIncome = AccountPayment::currentMonth()->pending()->sum('amount');

// Expenses
$totalExpenses = Expense::currentMonth()->sum('net_amount');
$paidExpenses = Expense::currentMonth()->whereIn('status', ['paid', 'cleared'])->sum('net_amount');

// Net Profit
$netProfit = $totalIncome - $totalExpenses;

// Summary
$summary = [
    'total_income' => $totalIncome,
    'cleared_income' => $clearedIncome,
    'pending_income' => $pendingIncome,
    'total_expenses' => $totalExpenses,
    'paid_expenses' => $paidExpenses,
    'net_profit' => $netProfit,
    'profit_margin' => $totalIncome > 0 ? ($netProfit / $totalIncome) * 100 : 0,
];
```

### Cash Flow Analysis

```php
$startDate = now()->startOfYear();
$endDate = now()->endOfYear();

// Monthly breakdown
$months = [];
for ($month = 1; $month <= 12; $month++) {
    $monthStart = now()->setYear(2026)->setMonth($month)->startOfMonth();
    $monthEnd = $monthStart->copy()->endOfMonth();

    $income = AccountPayment::dateRange($monthStart, $monthEnd)->sum('amount');
    $expenses = Expense::dateRange($monthStart, $monthEnd)->sum('net_amount');

    $months[] = [
        'month' => $monthStart->format('F'),
        'income' => $income,
        'expenses' => $expenses,
        'net' => $income - $expenses,
    ];
}
```

### Payment Method Analysis

```php
$methods = [
    'cash' => AccountPayment::byMethod('cash')->currentMonth()->sum('amount'),
    'cheque' => AccountPayment::byMethod('cheque')->currentMonth()->sum('amount'),
    'bank_transfer' => AccountPayment::byMethod('bank_transfer')->currentMonth()->sum('amount'),
    'online' => AccountPayment::byMethod('online')->currentMonth()->sum('amount'),
    'card' => AccountPayment::byMethod('card')->currentMonth()->sum('amount'),
];
```

### Top Expense Categories

```php
$topExpenseTypes = Expense::with('paymentType')
    ->currentYear()
    ->selectRaw('payment_type_id, SUM(net_amount) as total')
    ->groupBy('payment_type_id')
    ->orderByDesc('total')
    ->take(10)
    ->get()
    ->map(function($item) {
        return [
            'category' => $item->paymentType->name,
            'total' => $item->total,
        ];
    });
```

### Recurring Expense Management

```php
// Upcoming recurring expenses (next 30 days)
$upcoming = Expense::recurring()
    ->where('next_due_date', '<=', now()->addDays(30))
    ->where('next_due_date', '>=', now())
    ->orderBy('next_due_date')
    ->get();

// Overdue recurring expenses
$overdue = Expense::overdue()->get();

// Auto-create recurring expenses
foreach ($overdue as $expense) {
    if ($expense->isOverdue()) {
        $expense->createNextRecurrence();
    }
}
```

---

## Business Logic

### Payment Status Flow

```
INCOMING PAYMENT:
pending → received → cleared
    ↓
bounced (if cheque bounces)
    ↓
cancelled (if payment cancelled)
```

### Expense Status Flow

```
EXPENSE:
pending → paid → cleared
    ↓         ↓
cancelled  refunded
```

### Auto-Clearing Logic

- **Cash Payments:** Auto-cleared immediately upon creation
- **Cheque/Transfer:** Must be manually cleared after bank clearance
- **Status Updates:** Automatic status transitions based on payment method

### Recurring Expense Logic

```php
// Example: Monthly office rent
1. Create first expense (Jan 2026)
   - is_recurring = true
   - recurring_frequency = 'monthly'
   - next_due_date = Feb 1, 2026

2. When due date arrives:
   - Call createNextRecurrence()
   - New expense created for Feb 2026
   - Original expense's next_due_date → Mar 1, 2026

3. Repeat monthly
```

---

## Formulas

### Net Amount Calculation (Expenses)

```php
net_amount = amount + tax_amount - discount_amount
```

### Profit Calculation

```php
net_profit = total_income - total_expenses
profit_margin = (net_profit / total_income) * 100
```

### Next Due Date (Recurring)

```php
// Monthly: Add 1 month
next_due = current_due->addMonth()

// Quarterly: Add 3 months
next_due = current_due->addMonths(3)

// Yearly: Add 1 year
next_due = current_due->addYear()
```

---

## Best Practices

1. **Use Payment Types:** Always categorize payments/expenses with appropriate types
2. **Link to Entities:** Link payments to Client/Deal/Property for better tracking
3. **Clear Cheques:** Always manually clear cheque payments after bank clearance
4. **Document Everything:** Attach receipts/invoices in documents field (JSON)
5. **Recurring Expenses:** Set up recurring for regular expenses (rent, utilities)
6. **Regular Reconciliation:** Run syncPaidAmount() regularly to ensure accuracy
7. **Monitor Overdue:** Check overdue recurring expenses weekly
8. **Generate Reports:** Monthly financial reports for analysis
9. **Approve Expenses:** Large expenses should require approval
10. **Tax Tracking:** Always record tax amounts for compliance

---

## Permissions Required

```
payments.view          View incoming payments
payments.create        Record payments
payments.edit          Edit/clear/bounce payments
payments.delete        Delete payments

expenses.view          View expenses
expenses.create        Record expenses
expenses.edit          Edit/clear/approve expenses
expenses.delete        Delete expenses
```

---

## Troubleshooting

**Q: Payment not auto-clearing?**
```php
// Check payment method
$payment->payment_method; // Must be 'cash' for auto-clear

// Manually clear
$payment->markAsCleared();
```

**Q: Net amount incorrect?**
```php
// Recalculate
$expense->net_amount = $expense->amount + $expense->tax_amount - $expense->discount_amount;
$expense->save();
```

**Q: Recurring expense not generating?**
```php
// Check settings
$expense->is_recurring; // Must be true
$expense->recurring_frequency; // Must be set
$expense->next_due_date; // Must be in past

// Create manually
$expense->createNextRecurrence();
```

---

## Summary

The Payment & Expense Management System provides:

✅ Complete income tracking (payments from various sources)
✅ Comprehensive expense management (all business costs)
✅ Payment type categorization (organized tracking)
✅ Polymorphic linking (connect to any entity)
✅ Recurring expense automation
✅ Multiple payment methods (cash, cheque, transfer, etc.)
✅ Status tracking (pending, cleared, bounced, etc.)
✅ Financial reports & analytics
✅ Integration with all CRM modules
✅ Audit trail (who received/paid, when, how much)

**Difference from FilePayments:**
- **FilePayments:** Specific to property file installment tracking (part of PropertyFile module)
- **AccountPayments/Expenses:** General accounting for all business income/expenses

---

**Version:** 1.0
**Last Updated:** January 29, 2026
**Maintained By:** RealEstate CRM Team

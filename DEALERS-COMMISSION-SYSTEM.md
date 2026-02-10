# Dealers & Commission Tracking System

## Overview

The Dealers & Commission Tracking System is a comprehensive module for managing real estate dealers (agents), tracking their deals, and calculating commissions. This system integrates seamlessly with the existing CRM modules (Clients, Properties, Plots, Leads) to provide complete transaction management.

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Database Schema](#database-schema)
3. [Models & Relationships](#models--relationships)
4. [Controller Methods](#controller-methods)
5. [Routes](#routes)
6. [Usage Examples](#usage-examples)
7. [Commission Calculation](#commission-calculation)
8. [Deal Lifecycle](#deal-lifecycle)
9. [Integration Guide](#integration-guide)
10. [API Reference](#api-reference)

---

## System Architecture

### Core Components

```
┌─────────────────────────────────────────────────────────────┐
│                    DEALERS & COMMISSION SYSTEM              │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────┐      ┌──────────┐      ┌──────────────┐    │
│  │  DEALER  │──────│   DEAL   │──────│ COMMISSION   │    │
│  │ (Profile)│      │(Central) │      │  (Tracking)  │    │
│  └──────────┘      └──────────┘      └──────────────┘    │
│       │                  │                                │
│       │                  ├──→ CLIENT                      │
│       │                  ├──→ PROPERTY (Polymorphic)      │
│       │                  ├──→ PLOT (Polymorphic)          │
│       │                  └──→ USER (Creator)              │
│       │                                                    │
│       └──→ USER (One-to-One)                              │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Deal Flow

```
LEAD → CLIENT → DEAL (Property/Plot) → COMMISSION
  ↓       ↓        ↓                      ↓
Source  Convert  Transaction           Earnings
```

---

## Database Schema

### `dealers` Table

Stores dealer/agent profiles and their performance statistics.

```php
Schema::create('dealers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->string('cnic')->unique()->nullable();
    $table->string('license_number')->unique()->nullable();
    $table->decimal('default_commission_rate', 5, 2)->default(0.00);
    $table->enum('specialization', ['plots', 'residential', 'commercial', 'all'])->default('all');
    $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
    $table->integer('total_deals')->default(0);
    $table->decimal('total_commission', 15, 2)->default(0.00);
    $table->string('bank_name')->nullable();
    $table->string('account_title')->nullable();
    $table->string('account_number')->nullable();
    $table->string('iban')->nullable();
    $table->date('joined_date')->nullable();
    $table->text('remarks')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['status', 'specialization']);
    $table->index('user_id');
});
```

### `deals` Table

Stores all property/plot transactions with commission tracking.

```php
Schema::create('deals', function (Blueprint $table) {
    $table->id();
    $table->string('deal_number')->unique();
    $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
    $table->foreignId('dealer_id')->nullable()->constrained('users')->onDelete('set null');
    $table->string('dealable_type'); // App\Models\Property or App\Models\Plot
    $table->unsignedBigInteger('dealable_id');
    $table->enum('deal_type', ['purchase', 'sale', 'booking']);
    $table->decimal('deal_amount', 15, 2);
    $table->decimal('commission_amount', 15, 2)->nullable();
    $table->decimal('commission_percentage', 5, 2)->nullable();
    $table->enum('payment_type', ['cash', 'installment']);
    $table->integer('installment_months')->nullable();
    $table->decimal('down_payment', 15, 2)->nullable();
    $table->decimal('monthly_installment', 15, 2)->nullable();
    $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
    $table->date('deal_date');
    $table->date('completion_date')->nullable();
    $table->text('terms_conditions')->nullable();
    $table->text('remarks')->nullable();
    $table->json('documents')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
    $table->softDeletes();

    $table->index(['dealable_type', 'dealable_id']);
    $table->index(['status', 'deal_date']);
});
```

---

## Models & Relationships

### Dealer Model

**Location:** `app/Models/Dealer.php`

#### Constants

```php
const STATUS_ACTIVE = 'active';
const STATUS_INACTIVE = 'inactive';
const STATUS_SUSPENDED = 'suspended';

const SPEC_PLOTS = 'plots';
const SPEC_RESIDENTIAL = 'residential';
const SPEC_COMMERCIAL = 'commercial';
const SPEC_ALL = 'all';
```

#### Relationships

```php
// User relationship (one-to-one)
public function user(): BelongsTo

// All deals by this dealer
public function deals(): HasMany
```

#### Scopes

```php
scopeActive($query)                           // Active dealers only
scopeInactive($query)                         // Inactive dealers
scopeSuspended($query)                        // Suspended dealers
scopeBySpecialization($query, $specialization) // Filter by specialization
scopeTopPerformers($query, $limit = 10)       // Top earners
```

#### Key Methods

```php
getActiveDealsCount(): int                    // Count active deals
getCompletedDealsCount(): int                 // Count completed deals
calculateEarnings($startDate, $endDate): float // Calculate earnings for period
getPendingCommission(): float                 // Commission from confirmed deals
getEarnedCommission(): float                  // Commission from completed deals
updateStatistics(): void                      // Update cached statistics
isActive(): bool                              // Check if active
activate(): bool                              // Activate dealer
deactivate(): bool                            // Deactivate dealer
suspend(): bool                               // Suspend dealer
getPerformanceRating(): string                // Get rating (Platinum/Gold/Silver/Bronze/Starter)
```

### Deal Model

**Location:** `app/Models/Deal.php`

#### Constants

```php
// Status
const STATUS_PENDING = 'pending';
const STATUS_CONFIRMED = 'confirmed';
const STATUS_CANCELLED = 'cancelled';
const STATUS_COMPLETED = 'completed';

// Types
const TYPE_PURCHASE = 'purchase';
const TYPE_SALE = 'sale';
const TYPE_BOOKING = 'booking';

// Payment
const PAYMENT_CASH = 'cash';
const PAYMENT_INSTALLMENT = 'installment';
```

#### Relationships

```php
dealable(): MorphTo                           // Property or Plot (polymorphic)
client(): BelongsTo                           // Client
dealer(): BelongsTo                           // Dealer (User)
creator(): BelongsTo                          // Creator (User)
propertyFile(): HasOne                        // Associated property file
```

#### Scopes

```php
scopeConfirmed($query)                        // Confirmed deals
scopeCompleted($query)                        // Completed deals
scopePending($query)                          // Pending deals
scopeCancelled($query)                        // Cancelled deals
scopeByDealer($query, $dealerId)              // Deals by specific dealer
scopeByClient($query, $clientId)              // Deals by specific client
scopeThisMonth($query)                        // This month's deals
scopeThisYear($query)                         // This year's deals
scopeCashDeals($query)                        // Cash payment deals
scopeInstallmentDeals($query)                 // Installment payment deals
```

#### Key Methods

**Commission Management:**
```php
calculateCommission(): float                  // Calculate commission from percentage
setCommission(): void                         // Auto-set commission amount
```

**Payment Calculations:**
```php
calculateMonthlyInstallment(): float          // Calculate monthly payment
getRemainingAmount(): float                   // Amount after down payment
getPaymentSchedule(): array                   // Full payment schedule
```

**Status Checks:**
```php
isPending(): bool                             // Check if pending
isConfirmed(): bool                           // Check if confirmed
isCancelled(): bool                           // Check if cancelled
isCompleted(): bool                           // Check if completed
```

**Status Transitions:**
```php
confirm(): bool                               // Confirm pending deal
cancel(string $reason = null): bool           // Cancel deal with reason
complete(): bool                              // Complete confirmed deal (earns commission)
```

**Analytics:**
```php
getDealAge(): int                             // Age in days
getCompletionStatus(): array                  // Status with progress percentage
isCommissionEarned(): bool                    // Check if commission earned
```

#### Auto-Generated Fields

```php
generateDealNumber(): string                  // Format: DEAL-2024-0001
```

---

## Controller Methods

### DealController

**Location:** `app/Http/Controllers/DealController.php`

#### CRUD Operations

```php
index(Request $request)                       // List deals with filters
create()                                      // Show create form
store(Request $request)                       // Create new deal
show(Deal $deal)                              // Show deal details
update(Request $request, Deal $deal)          // Update deal (pending only)
destroy(Deal $deal)                           // Delete deal (pending only)
```

#### Deal Lifecycle

```php
approve(Deal $deal)                           // Confirm deal
complete(Deal $deal)                          // Complete & earn commission
cancel(Request $request, Deal $deal)          // Cancel deal
```

#### Commission & Reports

```php
commissionReport(Request $request)            // Commission report (all/filtered)
dealerCommissions($dealerId)                  // Dealer-specific commissions
statistics()                                  // Dashboard statistics
```

---

## Routes

### Deal Management Routes

```php
// View
GET    /deals                             deals.index
GET    /deals/{deal}                      deals.show

// Create
GET    /deals/create                      deals.create
POST   /deals                             deals.store

// Update
GET    /deals/{deal}/edit                 deals.edit
PUT    /deals/{deal}                      deals.update

// Delete
DELETE /deals/{deal}                      deals.destroy

// Status Changes
POST   /deals/{deal}/approve              deals.approve
POST   /deals/{deal}/complete             deals.complete
POST   /deals/{deal}/cancel               deals.cancel

// Reports
GET    /deals/reports/commission          deals.commission-report
GET    /deals/reports/statistics          deals.statistics
GET    /dealers/{dealer}/commissions      dealers.commissions
```

---

## Usage Examples

### 1. Creating a Dealer Profile

```php
use App\Models\User;
use App\Models\Dealer;

$user = User::find(1);

$dealer = Dealer::create([
    'user_id' => $user->id,
    'cnic' => '12345-1234567-1',
    'license_number' => 'LIC-2024-001',
    'default_commission_rate' => 2.5, // 2.5%
    'specialization' => Dealer::SPEC_RESIDENTIAL,
    'status' => Dealer::STATUS_ACTIVE,
    'bank_name' => 'HBL',
    'account_title' => 'John Doe',
    'account_number' => '1234567890',
    'joined_date' => now(),
]);
```

### 2. Creating a Deal (Property Sale)

```php
use App\Models\Deal;
use App\Models\Client;
use App\Models\Property;

$deal = Deal::create([
    'client_id' => 1,
    'dealer_id' => 2,
    'dealable_type' => Property::class,
    'dealable_id' => 5,
    'deal_type' => Deal::TYPE_SALE,
    'deal_amount' => 5000000.00,
    'commission_percentage' => 2.5,
    'payment_type' => Deal::PAYMENT_INSTALLMENT,
    'installment_months' => 12,
    'down_payment' => 1000000.00,
    'deal_date' => now(),
    'status' => Deal::STATUS_PENDING,
    'created_by' => auth()->id(),
]);

// Auto-calculate commission and installments
$deal->setCommission(); // commission_amount = 125,000
$deal->monthly_installment = $deal->calculateMonthlyInstallment(); // 333,333.33
$deal->save();
```

### 3. Creating a Deal (Plot Booking)

```php
use App\Models\Plot;

$deal = Deal::create([
    'client_id' => 3,
    'dealer_id' => 2,
    'dealable_type' => Plot::class,
    'dealable_id' => 10,
    'deal_type' => Deal::TYPE_BOOKING,
    'deal_amount' => 2000000.00,
    'commission_percentage' => 3.0,
    'payment_type' => Deal::PAYMENT_CASH,
    'deal_date' => now(),
    'status' => Deal::STATUS_PENDING,
    'created_by' => auth()->id(),
]);
```

### 4. Confirming a Deal

```php
$deal = Deal::find(1);

if ($deal->confirm()) {
    // Deal confirmed successfully
    // Property/Plot status updated to 'sold'
}
```

### 5. Completing a Deal & Earning Commission

```php
$deal = Deal::find(1);

if ($deal->complete()) {
    // Deal completed
    // Commission earned
    // Dealer statistics updated

    $commission = $deal->commission_amount; // e.g., 125,000.00
}
```

### 6. Cancelling a Deal

```php
$deal = Deal::find(1);

$deal->cancel('Client changed mind');

// Deal cancelled
// Property/Plot status restored to 'available'
```

### 7. Getting Dealer Performance

```php
$dealer = Dealer::find(1);

// Active deals
$activeDeals = $dealer->getActiveDealsCount();

// Completed deals
$completedDeals = $dealer->getCompletedDealsCount();

// Total earned (completed deals only)
$totalEarned = $dealer->getEarnedCommission();

// Pending commission (confirmed but not completed)
$pendingCommission = $dealer->getPendingCommission();

// Period earnings
$monthlyEarnings = $dealer->calculateEarnings(
    now()->startOfMonth(),
    now()->endOfMonth()
);

// Performance rating
$rating = $dealer->getPerformanceRating(); // 'Platinum', 'Gold', etc.
```

### 8. Querying Deals

```php
// Active deals by dealer
$deals = Deal::byDealer(2)
    ->whereIn('status', [Deal::STATUS_PENDING, Deal::STATUS_CONFIRMED])
    ->with(['client', 'dealable'])
    ->get();

// This month's completed deals
$monthlyDeals = Deal::thisMonth()
    ->completed()
    ->with('dealer')
    ->get();

// Total commission this year
$yearlyCommission = Deal::thisYear()
    ->completed()
    ->sum('commission_amount');

// Installment deals
$installments = Deal::installmentDeals()
    ->confirmed()
    ->get();
```

### 9. Commission Report

```php
// Get commission report for specific dealer
$report = Deal::with(['client', 'dealable'])
    ->where('dealer_id', 2)
    ->whereBetween('deal_date', [$startDate, $endDate])
    ->get();

$stats = [
    'total_deals' => $report->count(),
    'completed_deals' => $report->where('status', Deal::STATUS_COMPLETED)->count(),
    'total_earned' => $report->where('status', Deal::STATUS_COMPLETED)->sum('commission_amount'),
    'pending_commission' => $report->where('status', Deal::STATUS_CONFIRMED)->sum('commission_amount'),
];
```

### 10. Top Performing Dealers

```php
$topDealers = Dealer::topPerformers(10)
    ->with('user')
    ->get();

foreach ($topDealers as $dealer) {
    echo "{$dealer->user->name}: {$dealer->total_commission}\n";
}
```

---

## Commission Calculation

### Percentage-Based Commission

```php
Deal Amount: PKR 5,000,000
Commission Rate: 2.5%

Commission = 5,000,000 × (2.5 / 100)
           = PKR 125,000
```

### Fixed Commission

```php
Deal Amount: PKR 3,000,000
Commission Amount: PKR 75,000 (fixed)

Commission = PKR 75,000
```

### Implementation

```php
// Option 1: Percentage-based (auto-calculated)
$deal->commission_percentage = 2.5;
$deal->setCommission(); // Calculates and sets commission_amount

// Option 2: Fixed amount
$deal->commission_amount = 75000.00;
$deal->commission_percentage = null;
```

### Commission Earning

Commissions are **only earned** when a deal is completed:

```php
Deal Status → Commission Status
────────────────────────────────
pending     → Not earned (0%)
confirmed   → Pending (0% - but expected)
completed   → Earned (100%)
cancelled   → Lost (0%)
```

---

## Deal Lifecycle

### Status Flow

```
┌──────────┐
│ PENDING  │ ← Initial state
└─────┬────┘
      │
      ├──→ confirm() ──→ ┌───────────┐
      │                  │ CONFIRMED │
      │                  └─────┬─────┘
      │                        │
      │                        ├──→ complete() ──→ ┌───────────┐
      │                        │                   │ COMPLETED │
      │                        │                   └───────────┘
      │                        │
      │                        └──→ cancel() ──→ ┌───────────┐
      │                                          │ CANCELLED │
      └──→ cancel() ──────────────────────────→ └───────────┘
```

### State Transitions

| From       | To         | Method      | Permission           | Effect                          |
|------------|------------|-------------|----------------------|---------------------------------|
| pending    | confirmed  | confirm()   | deals.approve        | Property/Plot → 'sold'          |
| confirmed  | completed  | complete()  | deals.complete       | Commission earned, stats updated|
| pending    | cancelled  | cancel()    | deals.cancel         | Property/Plot → 'available'     |
| confirmed  | cancelled  | cancel()    | deals.cancel         | Property/Plot → 'available'     |

### Business Rules

1. **Only pending deals** can be fully edited
2. **Only pending deals** can be deleted
3. **Only pending deals** can be confirmed
4. **Only confirmed deals** can be completed
5. **Completed deals cannot be modified**
6. **Cancelled deals cannot be modified**
7. **Commission earned only on completion**
8. **Dealer statistics update on completion**

---

## Integration Guide

### With Existing Modules

#### 1. Client Module

```php
// Get client's deals
$client = Client::find(1);
$deals = $client->deals()->with('dealable')->get();

// Create deal from client
$client->deals()->create([...]);
```

#### 2. Property Module

```php
// Get property deals
$property = Property::find(1);
$deals = $property->deals;

// Check if property has active deal
$hasActiveDeal = $property->deals()
    ->whereIn('status', ['pending', 'confirmed'])
    ->exists();
```

#### 3. Plot Module

```php
// Get plot deals
$plot = Plot::find(1);
$deals = $plot->deals;

// Latest deal
$latestDeal = $plot->deals()->latest()->first();
```

#### 4. Lead Module

```php
// When converting lead to client, track source
$client = Client::create([
    'converted_from_lead_id' => $lead->id,
    'lead_source' => $lead->source,
    // ... other fields
]);

// Later, create deal with converted client
$deal = Deal::create([
    'client_id' => $client->id,
    // ... other fields
]);
```

#### 5. User Module

```php
// Get user's dealer profile
$user = User::find(1);
$dealer = $user->dealerProfile;

// Get deals created by user
$createdDeals = $user->createdDeals;

// Get deals where user is dealer
$dealerDeals = $user->dealerDeals;
```

### Event Hooks

You can add event listeners for deal lifecycle:

```php
// In Deal model or EventServiceProvider

protected static function boot()
{
    parent::boot();

    static::confirmed(function ($deal) {
        // Send notification to client
        // Update property/plot status
    });

    static::completed(function ($deal) {
        // Send commission notification to dealer
        // Update dealer statistics
        // Generate invoice/receipt
    });

    static::cancelled(function ($deal) {
        // Notify stakeholders
        // Restore property/plot availability
    });
}
```

---

## API Reference

### Commission Calculation Formulas

```php
// 1. Commission Amount
commission_amount = deal_amount × (commission_percentage / 100)

// 2. Monthly Installment
remaining_amount = deal_amount - down_payment
monthly_installment = remaining_amount / installment_months

// 3. Total Paid (Installments)
total_paid = down_payment + (monthly_installment × installment_months)

// 4. Dealer Earnings (Period)
period_earnings = SUM(commission_amount WHERE status = 'completed' AND deal_date BETWEEN start AND end)

// 5. Pending Commission
pending = SUM(commission_amount WHERE status = 'confirmed')
```

### Performance Ratings

| Rating    | Commission Range    |
|-----------|---------------------|
| Platinum  | ≥ PKR 1,000,000     |
| Gold      | ≥ PKR 500,000       |
| Silver    | ≥ PKR 200,000       |
| Bronze    | ≥ PKR 50,000        |
| Starter   | < PKR 50,000        |

---

## Best Practices

### 1. Commission Setup

- Set default commission rates per dealer profile
- Allow deal-specific commission overrides
- Document commission agreements in `terms_conditions`

### 2. Deal Workflow

- Always generate deal number automatically
- Require confirmation before marking as sold
- Only complete deals after full verification
- Document cancellation reasons

### 3. Payment Tracking

- For installments, integrate with Payment module
- Track each installment separately
- Generate payment schedules
- Send payment reminders

### 4. Performance Monitoring

- Run `updateStatistics()` regularly for dealers
- Generate monthly commission reports
- Track deal conversion rates
- Monitor cancelled deal reasons

### 5. Data Integrity

- Use transactions for status changes
- Update dealable status atomically
- Validate business rules in model methods
- Use soft deletes for audit trail

---

## Troubleshooting

### Common Issues

**Issue:** Commission not calculating
```php
// Solution: Ensure percentage is set or manually set amount
$deal->commission_percentage = 2.5;
$deal->setCommission();
$deal->save();
```

**Issue:** Cannot complete deal
```php
// Solution: Check deal status
if (!$deal->isConfirmed()) {
    // Deal must be confirmed first
    $deal->confirm();
}
$deal->complete();
```

**Issue:** Dealer statistics not updating
```php
// Solution: Manually trigger update
$dealer->updateStatistics();
```

**Issue:** Property/Plot still showing as sold after cancellation
```php
// Solution: Manually restore status
$deal->cancel('reason');
$deal->dealable->update(['status' => 'available']);
```

---

## Security Considerations

1. **Permission Checks:** All controller methods check user permissions
2. **Status Validation:** Business rules enforced in model methods
3. **Soft Deletes:** Data preserved for audit trails
4. **Creator Tracking:** All deals track who created them
5. **Commission Protection:** Commission only earned on completion

---

## Performance Optimization

### Caching Strategies

```php
// Cache dealer statistics
Cache::remember("dealer_{$dealerId}_stats", 3600, function () use ($dealerId) {
    $dealer = Dealer::find($dealerId);
    return [
        'total_deals' => $dealer->deals()->count(),
        'total_commission' => $dealer->getEarnedCommission(),
    ];
});

// Eager load relationships
$deals = Deal::with(['client', 'dealer', 'dealable'])->paginate(20);
```

### Database Indexes

Key indexes already in place:
- `dealers (status, specialization)`
- `deals (dealable_type, dealable_id)`
- `deals (status, deal_date)`

---

## Future Enhancements

1. **Commission Split:** Multiple dealers per deal
2. **Bonus Structure:** Performance-based bonuses
3. **Payment Integration:** Track installment payments
4. **Analytics Dashboard:** Visual reports and charts
5. **Notification System:** Deal status change alerts
6. **Document Management:** Upload and attach contracts
7. **SMS/Email:** Automated client communication
8. **Mobile App:** Dealer mobile interface

---

## Support & Maintenance

### Migration Commands

```bash
# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback --step=1

# Fresh migration
php artisan migrate:fresh
```

### Seeding Sample Data

```bash
php artisan db:seed --class=DealerSeeder
php artisan db:seed --class=DealSeeder
```

---

## Conclusion

The Dealers & Commission Tracking System provides a complete solution for managing real estate transactions, tracking dealer performance, and calculating commissions. It integrates seamlessly with existing CRM modules and provides comprehensive APIs for customization and extension.

For detailed code examples and advanced usage, refer to the inline documentation in the model and controller files.

---

**Version:** 1.0
**Last Updated:** 2024
**Maintained By:** RealEstate CRM Team

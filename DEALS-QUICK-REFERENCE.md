# Deal Management - Quick Reference

## Quick Start

### Create a Deal

```php
use App\Models\Deal;
use App\Models\Property;

$deal = Deal::create([
    'deal_number' => Deal::generateDealNumber(),
    'client_id' => 1,
    'dealer_id' => 2,
    'dealable_type' => Property::class,
    'dealable_id' => 5,
    'deal_type' => 'sale',
    'deal_amount' => 5000000,
    'commission_percentage' => 2.5,
    'payment_type' => 'installment',
    'installment_months' => 12,
    'down_payment' => 1000000,
    'deal_date' => now(),
    'status' => 'pending',
    'created_by' => auth()->id(),
]);

// Auto-calculate commission
$deal->setCommission();
$deal->monthly_installment = $deal->calculateMonthlyInstallment();
$deal->save();
```

## Deal Lifecycle Commands

```php
// Confirm pending deal
$deal->confirm();

// Complete confirmed deal (earns commission)
$deal->complete();

// Cancel deal
$deal->cancel('Client withdrew');
```

## Common Queries

```php
// Active deals
$deals = Deal::pending()->orWhere->confirmed()->get();

// Completed deals this month
$deals = Deal::thisMonth()->completed()->get();

// Dealer's deals
$deals = Deal::byDealer($dealerId)->get();

// Total commission earned
$commission = Deal::completed()->sum('commission_amount');
```

## Dealer Statistics

```php
$dealer = Dealer::find(1);

$dealer->getActiveDealsCount();           // Active deals count
$dealer->getCompletedDealsCount();        // Completed deals count
$dealer->getEarnedCommission();           // Total earned
$dealer->getPendingCommission();          // Pending commission
$dealer->calculateEarnings($start, $end); // Period earnings
$dealer->getPerformanceRating();          // Rating (Platinum/Gold/etc)
$dealer->updateStatistics();              // Update cached stats
```

## Scopes Reference

```php
// Status
Deal::pending()
Deal::confirmed()
Deal::completed()
Deal::cancelled()

// Filters
Deal::byDealer($id)
Deal::byClient($id)
Deal::thisMonth()
Deal::thisYear()

// Payment
Deal::cashDeals()
Deal::installmentDeals()
```

## Status Checks

```php
$deal->isPending()
$deal->isConfirmed()
$deal->isCompleted()
$deal->isCancelled()
$deal->isCommissionEarned()
```

## Calculations

```php
$deal->calculateCommission()          // Calculate from percentage
$deal->calculateMonthlyInstallment()  // Monthly installment amount
$deal->getRemainingAmount()           // After down payment
$deal->getPaymentSchedule()           // Full schedule array
$deal->getDealAge()                   // Age in days
$deal->getCompletionStatus()          // Status with progress %
```

## Commission Formulas

```php
// Percentage-based
commission = deal_amount × (commission_percentage / 100)

// Monthly installment
remaining = deal_amount - down_payment
monthly = remaining / installment_months

// Period earnings
earnings = SUM(commission WHERE status='completed' AND date BETWEEN start AND end)
```

## Routes

```http
GET    /deals                              # List all deals
POST   /deals                              # Create deal
GET    /deals/{id}                         # Show deal details
PUT    /deals/{id}                         # Update deal
DELETE /deals/{id}                         # Delete deal
POST   /deals/{id}/approve                 # Confirm deal
POST   /deals/{id}/complete                # Complete deal
POST   /deals/{id}/cancel                  # Cancel deal
GET    /deals/reports/commission           # Commission report
GET    /deals/reports/statistics           # Statistics
GET    /dealers/{id}/commissions           # Dealer commissions
```

## Constants

```php
// Status
Deal::STATUS_PENDING
Deal::STATUS_CONFIRMED
Deal::STATUS_CANCELLED
Deal::STATUS_COMPLETED

// Type
Deal::TYPE_PURCHASE
Deal::TYPE_SALE
Deal::TYPE_BOOKING

// Payment
Deal::PAYMENT_CASH
Deal::PAYMENT_INSTALLMENT

// Dealer Status
Dealer::STATUS_ACTIVE
Dealer::STATUS_INACTIVE
Dealer::STATUS_SUSPENDED

// Specialization
Dealer::SPEC_PLOTS
Dealer::SPEC_RESIDENTIAL
Dealer::SPEC_COMMERCIAL
Dealer::SPEC_ALL
```

## Relationships

```php
// Deal relationships
$deal->client          // Client who made deal
$deal->dealer          // Dealer earning commission
$deal->dealable        // Property or Plot (polymorphic)
$deal->creator         // User who created deal
$deal->propertyFile    // Associated file

// Dealer relationships
$dealer->user          // User account
$dealer->deals         // All dealer's deals

// Property/Plot relationships
$property->deals       // All deals for this property
$plot->deals           // All deals for this plot

// Client relationships
$client->deals         // All client's deals

// User relationships
$user->dealerProfile   // Dealer profile
$user->dealerDeals     // Deals as dealer
$user->createdDeals    // Deals created by user
```

## Performance Ratings

| Rating    | Total Commission    |
|-----------|---------------------|
| Platinum  | ≥ PKR 1,000,000     |
| Gold      | ≥ PKR 500,000       |
| Silver    | ≥ PKR 200,000       |
| Bronze    | ≥ PKR 50,000        |
| Starter   | < PKR 50,000        |

## Business Rules

1. Only **pending** deals can be edited/deleted
2. Only **pending** deals can be confirmed
3. Only **confirmed** deals can be completed
4. **Completed** deals cannot be modified
5. Commission **only earned** on completion
6. Cancellation restores property/plot availability
7. Dealer statistics update on completion

## Common Patterns

### Create Deal with Validation

```php
DB::beginTransaction();
try {
    $deal = Deal::create($validated);
    $deal->setCommission();
    $deal->save();

    // Update property status
    $deal->dealable->update(['status' => 'sold']);

    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}
```

### Generate Commission Report

```php
$report = Deal::with(['client', 'dealer', 'dealable'])
    ->whereNotNull('dealer_id')
    ->whereBetween('deal_date', [$start, $end])
    ->get();

$stats = [
    'total' => $report->count(),
    'completed' => $report->where('status', 'completed')->count(),
    'earned' => $report->where('status', 'completed')->sum('commission_amount'),
    'pending' => $report->where('status', 'confirmed')->sum('commission_amount'),
];
```

### Top Performers

```php
$topDealers = Dealer::topPerformers(10)
    ->with('user')
    ->get()
    ->map(function ($dealer) {
        return [
            'name' => $dealer->user->name,
            'deals' => $dealer->total_deals,
            'commission' => $dealer->total_commission,
            'rating' => $dealer->getPerformanceRating(),
        ];
    });
```

### Deal Payment Schedule

```php
$schedule = $deal->getPaymentSchedule();

// Returns:
[
    ['installment_number' => 1, 'due_date' => '2024-02-01', 'amount' => 333333.33, 'status' => 'pending'],
    ['installment_number' => 2, 'due_date' => '2024-03-01', 'amount' => 333333.33, 'status' => 'pending'],
    // ... 12 months
]
```

## Troubleshooting

**Q: Commission not calculating?**
```php
$deal->commission_percentage = 2.5;
$deal->setCommission(); // Force recalculation
$deal->save();
```

**Q: Cannot complete deal?**
```php
// Must be confirmed first
if ($deal->isPending()) {
    $deal->confirm();
}
$deal->complete();
```

**Q: Dealer stats not updating?**
```php
$dealer->updateStatistics(); // Manual update
```

**Q: Property still showing as sold after cancel?**
```php
$deal->cancel('reason');
$deal->dealable->update(['status' => 'available']);
```

## Events & Hooks

```php
// In Deal model boot method
static::confirmed(function ($deal) {
    // Send confirmation email
    // Update property status
});

static::completed(function ($deal) {
    // Send commission notification
    // Update dealer statistics
    // Generate invoice
});

static::cancelled(function ($deal) {
    // Notify stakeholders
    // Restore availability
});
```

## Testing Examples

```php
// Test deal creation
$deal = Deal::factory()->create();

// Test commission calculation
$deal = Deal::factory()->make([
    'deal_amount' => 5000000,
    'commission_percentage' => 2.5,
]);
$commission = $deal->calculateCommission();
$this->assertEquals(125000, $commission);

// Test lifecycle
$deal = Deal::factory()->pending()->create();
$this->assertTrue($deal->confirm());
$this->assertTrue($deal->isConfirmed());
$this->assertTrue($deal->complete());
$this->assertTrue($deal->isCommissionEarned());
```

---

**Tip:** Always use scopes and model methods instead of raw queries for consistency and maintainability.

# Reports Module Documentation

## Overview

The Reports Module provides comprehensive business intelligence and analytics for the Real Estate CRM system. It offers deep insights into property sales, financial performance, dealer commissions, and payment tracking with real-time data aggregation.

## Table of Contents

1. [Features](#features)
2. [Report Types](#report-types)
3. [Controller Methods](#controller-methods)
4. [Database Integration](#database-integration)
5. [Routes](#routes)
6. [Usage Examples](#usage-examples)
7. [Export Functionality](#export-functionality)
8. [Permissions](#permissions)

---

## Features

- **5 Core Report Types**: Plots, Payments, Commissions, Overdue Installments, Society Sales
- **Enhanced Income Tracking**: Combines FilePayment + AccountPayment + Expense data
- **Real-time Calculations**: Dynamic aggregations with filtering options
- **Visual Analytics**: Chart-ready data (labels + values arrays)
- **CSV Export**: Export any report to CSV format
- **Date Range Filtering**: Flexible date-based queries
- **Multi-dimensional Analysis**: Society, dealer, payment type breakdowns
- **Penalty Calculations**: Automated late fee computation
- **Profit Analysis**: Net profit with income vs expense comparison

---

## Report Types

### 1. Plots Report

**Purpose**: Analyze plot inventory across societies with availability status.

**Data Points**:
- Total plots
- Available plots
- Booked plots
- Sold plots
- Plot values by status
- Society-wise breakdown

**Filtering**:
- By society
- By block
- By status

**Use Cases**:
- Inventory management
- Sales performance tracking
- Society comparison
- Investment planning

---

### 2. Comprehensive Monthly Income Report

**Purpose**: Track all revenue sources including property file payments, account payments, and expenses.

**Data Sources**:
1. **FilePayment** (Property File Installments)
   - Down payments
   - Installments
   - Processing fees
   - Transfer fees

2. **AccountPayment** (General Income)
   - Rental income
   - Service charges
   - Miscellaneous income
   - Dealer commissions paid

3. **Expense** (Business Costs)
   - Operating expenses
   - Salaries
   - Marketing costs
   - Administrative expenses

**Metrics Calculated**:
- Total income (file payments + account payments)
- Total cleared income
- Income by payment type
- Total expenses
- Net profit (income - expenses)
- Profit margin percentage
- Payment method breakdown
- Pending clearance amounts

**Filtering**:
- By year/month
- By payment status
- By payment type
- By payment method

**Formula**:
```
Net Profit = (FilePayment.sum + AccountPayment.sum) - Expense.sum
Profit Margin = (Net Profit / Total Income) × 100
```

---

### 3. Payments Report (Legacy)

**Purpose**: Track payment collections with daily/monthly trends.

**Data Points**:
- Total received
- Total transactions
- Daily average
- Monthly average
- Payment method breakdown
- Monthly trend data

**Charts Available**:
- Monthly payment trend (line chart)
- Payment method distribution (pie chart)
- Daily payment summary (bar chart)

**Filtering**:
- Date range
- Payment method
- Society

---

### 4. Comprehensive Dealer Commission Report

**Purpose**: Track dealer commissions earned, paid, and pending using the new polymorphic payment system.

**Data Sources**:
1. **Deal** table (commission_amount field)
2. **AccountPayment** (where payable_type = 'App\\Models\\Deal')

**Metrics Calculated**:
- Total deals per dealer
- Confirmed deals count
- Total deal amount
- Total commission earned
- Paid commission (from AccountPayment)
- Pending commission (earned - paid)

**Dealer Summary**:
- Dealer name
- Total deals
- Confirmed deals
- Total deal amount
- Total commission
- Paid amount
- Pending amount

**Filtering**:
- By dealer
- By date range
- By deal status

**Use Cases**:
- Commission tracking
- Dealer performance analysis
- Payment reconciliation
- Outstanding commission reporting

**Example Query Logic**:
```php
// Get all deals with commissions
$deals = Deal::whereNotNull('dealer_id')->get();

// Get commission payments
$payments = AccountPayment::where('payable_type', Deal::class)
    ->whereIn('payable_id', $dealIds)
    ->get();

// Calculate pending
$pending = $totalCommission - $paidCommission;
```

---

### 5. Dealer Commissions Report (Legacy)

**Purpose**: Track dealer performance and commission payments.

**Data Points**:
- Total earned
- Total paid
- Pending commissions
- Dealer-wise breakdown
- Top dealer rankings

**Charts Available**:
- Top dealers by commission (bar chart)
- Commission status breakdown
- Dealer comparison

**Filtering**:
- Date range
- Dealer selection
- Commission status

---

### 6. Comprehensive Overdue Installments Report

**Purpose**: Monitor overdue payments with automated penalty calculations using the new FilePayment system.

**Data Source**:
- **FilePayment** table
  - Filters: `payment_type = 'installment'`
  - Filters: `status = 'pending'`
  - Filters: `due_date < NOW()`

**Metrics Calculated**:
- Total overdue count
- Total overdue amount
- Total penalties (using `calculatePenalty()` method)
- Total clients affected
- Average overdue days

**Grouping**:
- By property file
- By client
- By days overdue

**Penalty Calculation**:
```php
$payment->calculatePenalty($penaltyRatePercentage)
// Uses: (amount × penalty_rate × days_overdue) / 365
```

**Aging Buckets**:
- 1-30 days overdue
- 31-60 days overdue
- 61-90 days overdue
- 90+ days overdue

**Filtering**:
- By days overdue (e.g., >30 days)
- By property file
- By client
- By amount range

**Use Cases**:
- Collection management
- Client follow-up prioritization
- Penalty enforcement
- Cash flow forecasting

**Example Query**:
```php
FilePayment::where('payment_type', 'installment')
    ->where('status', 'pending')
    ->where('due_date', '<', now())
    ->with(['propertyFile.client', 'propertyFile.fileable'])
    ->get();
```

---

### 7. Overdue Installments Report (Legacy)

**Purpose**: Track late payments and calculate penalties.

**Data Points**:
- Total overdue count
- Total overdue amount
- Total late fees
- Aging analysis
- Client-wise breakdown

**Aging Buckets**:
- 1-30 days
- 31-60 days
- 61-90 days
- 90+ days

**Filtering**:
- By client
- By days overdue
- By society

---

### 8. Society-wise Sales Report

**Purpose**: Analyze sales performance across different societies.

**Data Points**:
- Sales by society
- Top performing society
- Deal counts
- Average deal value
- Society comparison

**Charts Available**:
- Society sales comparison (bar chart)
- Society contribution (pie chart)
- Monthly trend by society

**Filtering**:
- Date range
- Society selection

---

## Controller Methods

### Main Dashboard

#### `index(Request $request)`

**Purpose**: Main reports dashboard with all report types.

**Parameters**:
- `date_from` (optional): Start date for filtering
- `date_to` (optional): End date for filtering
- `society_id` (optional): Filter by society
- `dealer_id` (optional): Filter by dealer

**Returns**: View with all report data arrays.

**Data Provided**:
- Plot statistics
- Payment statistics
- Commission statistics
- Overdue statistics
- Society sales statistics
- Chart data for visualizations

**Example**:
```php
// GET /reports
// GET /reports?date_from=2024-01-01&date_to=2024-12-31
// GET /reports?society_id=5&dealer_id=3
```

---

### Enhanced Report Methods

#### `comprehensiveMonthlyIncome(Request $request)`

**Purpose**: Generate monthly income report with all payment sources and expenses.

**Parameters**:
- `year` (optional): Year to report (default: current year)
- `month` (optional): Month to report (default: current month)

**Returns**: View with:
- `$filePayments` - Collection of FilePayment records
- `$accountPayments` - Collection of AccountPayment records
- `$filePaymentStats` - Aggregated file payment statistics
- `$accountPaymentStats` - Aggregated account payment statistics
- `$summary` - Combined summary with net profit

**Statistics Included**:
```php
[
    'total_income' => 500000,
    'total_cleared' => 480000,
    'file_payments' => 350000,
    'account_payments' => 150000,
    'total_expenses' => 100000,
    'net_profit' => 400000,
    'profit_margin' => 80.00, // percentage
]
```

**Example**:
```php
// GET /reports/monthly-income
// GET /reports/monthly-income?year=2024&month=6
```

---

#### `comprehensiveDealerCommission(Request $request)`

**Purpose**: Generate dealer commission report with payment tracking.

**Parameters**:
- `start_date` (optional): Start date for filtering
- `end_date` (optional): End date for filtering
- `dealer_id` (optional): Filter by specific dealer

**Returns**: View with:
- `$deals` - Collection of Deal records with dealer info
- `$dealerSummary` - Dealer-wise aggregated statistics
- `$commissionPayments` - AccountPayment records grouped by deal
- `$stats` - Overall statistics

**Dealer Summary Structure**:
```php
[
    'dealer' => Dealer model,
    'total_deals' => 15,
    'confirmed_deals' => 12,
    'total_deal_amount' => 5000000,
    'total_commission' => 250000,
    'paid_commission' => 180000,
    'pending_commission' => 70000,
]
```

**Example**:
```php
// GET /reports/dealer-commission
// GET /reports/dealer-commission?dealer_id=5
// GET /reports/dealer-commission?start_date=2024-01-01&end_date=2024-12-31
```

---

#### `comprehensiveOverdueInstallments(Request $request)`

**Purpose**: Generate overdue installments report with penalty calculations.

**Parameters**:
- `days_overdue` (optional): Filter by minimum days overdue

**Returns**: View with:
- `$overduePayments` - Collection of overdue FilePayment records
- `$byPropertyFile` - Payments grouped by property file
- `$stats` - Aggregated statistics

**Each Payment Enhanced With**:
- `days_overdue`: Calculated days overdue
- `calculated_penalty`: Penalty amount using calculatePenalty()

**Statistics Included**:
```php
[
    'total_overdue_count' => 45,
    'total_overdue_amount' => 450000,
    'total_penalties' => 15000,
    'total_clients_affected' => 30,
    'average_overdue_days' => 23.5,
]
```

**Example**:
```php
// GET /reports/overdue-installments
// GET /reports/overdue-installments?days_overdue=30
```

---

### Legacy Report Helper Methods

#### Plot Reports

- `getPlotsReport($societyId)` - Get plot statistics for society
- `getSocietyPlotsBreakdown()` - Society-wise plot breakdown

#### Payment Reports

- `getPaymentsReport($dateFrom, $dateTo)` - Payment statistics
- `getDailyPayments($dateFrom, $dateTo)` - Daily payment summary
- `getPaymentTotals($dateFrom, $dateTo)` - Totals by method
- `getPaymentMonthLabels($dateFrom, $dateTo)` - Month labels for charts
- `getMonthlyPaymentAmounts($dateFrom, $dateTo)` - Monthly trend data
- `getPaymentMethodData($dateFrom, $dateTo)` - Method distribution

#### Commission Reports

- `getCommissionsReport($dateFrom, $dateTo, $dealerId)` - Commission stats
- `getDealerCommissionsBreakdown($dateFrom, $dateTo, $dealerId)` - Per-dealer breakdown
- `getTopDealerNames($dateFrom, $dateTo)` - Top dealer names
- `getTopDealerCommissions($dateFrom, $dateTo)` - Top dealer amounts

#### Overdue Reports

- `getOverdueReport()` - Overdue statistics
- `getOverdueInstallments()` - Detailed overdue list
- `getOverdueAging()` - Age bucket analysis

#### Society Reports

- `getSocietyReport($dateFrom, $dateTo)` - Society sales stats
- `getSocietySalesBreakdown($dateFrom, $dateTo, $societyId)` - Per-society breakdown
- `getSocietyNames()` - Society names for charts
- `getSocietySalesData($dateFrom, $dateTo)` - Sales data for charts

---

## Database Integration

### Models Used

1. **Plot**: Property plot information
2. **Deal**: Sales deals with commission tracking
3. **FilePayment**: Property file installment payments
4. **AccountPayment**: General income (polymorphic)
5. **Expense**: Business expenses
6. **PropertyFile**: Property file information
7. **Client**: Client information
8. **Property**: Property listings
9. **Dealer**: Dealer information
10. **Society**: Housing society information

### Relationships Leveraged

#### FilePayment Relationships
```php
FilePayment->propertyFile() // BelongsTo
FilePayment->propertyFile->client() // Through PropertyFile
FilePayment->propertyFile->fileable() // Through PropertyFile (polymorphic)
```

#### AccountPayment Relationships
```php
AccountPayment->payable() // MorphTo (Deal, Client, PropertyFile, Dealer, etc.)
AccountPayment->paymentType() // BelongsTo PaymentType
```

#### Deal Relationships
```php
Deal->dealer() // BelongsTo
Deal->client() // BelongsTo
Deal->dealable() // MorphTo (Plot, Property, etc.)
```

### Query Patterns

#### Complex Aggregation Example
```php
DB::table('plots')
    ->join('societies', 'plots.society_id', '=', 'societies.id')
    ->select(
        'societies.name',
        DB::raw('COUNT(*) as total_plots'),
        DB::raw('SUM(CASE WHEN plots.status = "available" THEN 1 ELSE 0 END) as available'),
        DB::raw('SUM(CASE WHEN plots.status = "sold" THEN 1 ELSE 0 END) as sold'),
        DB::raw('SUM(plots.price) as total_value')
    )
    ->groupBy('societies.id')
    ->get();
```

#### Date Range Filtering
```php
FilePayment::whereBetween('payment_date', [$startDate, $endDate])
    ->whereIn('status', ['received', 'cleared'])
    ->sum('amount');
```

#### Polymorphic Queries
```php
// Get commission payments for deals
AccountPayment::where('payable_type', Deal::class)
    ->whereIn('payable_id', $dealIds)
    ->where('payment_type_id', $commissionTypeId)
    ->sum('amount');
```

#### Overdue Calculation
```php
FilePayment::where('payment_type', 'installment')
    ->where('status', 'pending')
    ->where('due_date', '<', now())
    ->whereNotNull('due_date')
    ->with(['propertyFile.client'])
    ->get()
    ->map(function($payment) {
        $payment->days_overdue = $payment->due_date->diffInDays(now());
        $payment->penalty = $payment->calculatePenalty(1.0);
        return $payment;
    });
```

---

## Routes

### Report Routes

```php
Route::middleware(['permission:reports.view'])->group(function () {
    // Main dashboard
    Route::get('reports', [ReportController::class, 'index'])
        ->name('reports.index');

    // Enhanced reports
    Route::get('reports/monthly-income', [ReportController::class, 'comprehensiveMonthlyIncome'])
        ->name('reports.monthly-income');

    Route::get('reports/dealer-commission', [ReportController::class, 'comprehensiveDealerCommission'])
        ->name('reports.dealer-commission');

    Route::get('reports/overdue-installments', [ReportController::class, 'comprehensiveOverdueInstallments'])
        ->name('reports.overdue-installments');

    // Export
    Route::get('reports/export', [ReportController::class, 'exportReport'])
        ->name('reports.export');
});
```

### Route Parameters

**Main Dashboard** (`reports.index`):
- `date_from`: Start date (Y-m-d)
- `date_to`: End date (Y-m-d)
- `society_id`: Society filter
- `dealer_id`: Dealer filter

**Monthly Income** (`reports.monthly-income`):
- `year`: Year (default: current)
- `month`: Month (1-12, default: current)

**Dealer Commission** (`reports.dealer-commission`):
- `start_date`: Start date (Y-m-d)
- `end_date`: End date (Y-m-d)
- `dealer_id`: Dealer ID

**Overdue Installments** (`reports.overdue-installments`):
- `days_overdue`: Minimum days overdue

**Export** (`reports.export`):
- `type`: Report type (sold-plots, available-plots, overdue-installments, dealer-commission)

---

## Usage Examples

### Basic Report Access

```php
// Main dashboard
GET /reports

// Monthly income for current month
GET /reports/monthly-income

// Specific month/year
GET /reports/monthly-income?year=2024&month=6

// Dealer commission for specific dealer
GET /reports/dealer-commission?dealer_id=5

// Overdue payments over 30 days
GET /reports/overdue-installments?days_overdue=30
```

### Filtered Reports

```php
// Dashboard with date range
GET /reports?date_from=2024-01-01&date_to=2024-12-31

// Dashboard for specific society
GET /reports?society_id=3

// Dashboard for specific dealer
GET /reports?dealer_id=7

// Combined filters
GET /reports?date_from=2024-01-01&date_to=2024-06-30&society_id=3&dealer_id=7
```

### Export Examples

```php
// Export sold plots
GET /reports/export?type=sold-plots

// Export available plots
GET /reports/export?type=available-plots

// Export overdue installments
GET /reports/export?type=overdue-installments

// Export dealer commissions
GET /reports/export?type=dealer-commission
```

### Controller Usage in Views

```blade
{{-- Display monthly income --}}
@foreach($filePayments as $payment)
    <tr>
        <td>{{ $payment->propertyFile->file_number }}</td>
        <td>{{ $payment->propertyFile->client->name }}</td>
        <td>{{ number_format($payment->amount, 2) }}</td>
        <td>{{ $payment->payment_type }}</td>
        <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
    </tr>
@endforeach

{{-- Display summary --}}
<div class="summary">
    <p>Total Income: {{ number_format($summary['total_income'], 2) }}</p>
    <p>Total Expenses: {{ number_format($summary['total_expenses'], 2) }}</p>
    <p>Net Profit: {{ number_format($summary['net_profit'], 2) }}</p>
    <p>Profit Margin: {{ number_format($summary['profit_margin'], 2) }}%</p>
</div>

{{-- Display dealer commissions --}}
@foreach($dealerSummary as $summary)
    <tr>
        <td>{{ $summary['dealer']->name }}</td>
        <td>{{ $summary['total_deals'] }}</td>
        <td>{{ number_format($summary['total_commission'], 2) }}</td>
        <td>{{ number_format($summary['paid_commission'], 2) }}</td>
        <td>{{ number_format($summary['pending_commission'], 2) }}</td>
    </tr>
@endforeach

{{-- Display overdue installments --}}
@foreach($overduePayments as $payment)
    <tr>
        <td>{{ $payment->propertyFile->file_number }}</td>
        <td>{{ $payment->propertyFile->client->name }}</td>
        <td>{{ number_format($payment->amount, 2) }}</td>
        <td>{{ $payment->due_date->format('Y-m-d') }}</td>
        <td>{{ $payment->days_overdue }} days</td>
        <td>{{ number_format($payment->calculated_penalty, 2) }}</td>
    </tr>
@endforeach
```

---

## Export Functionality

### Export Methods

#### `exportReport(Request $request)`

Main export dispatcher that routes to specific export methods.

**Supported Types**:
- `sold-plots`: Export sold plots
- `available-plots`: Export available plots
- `overdue-installments`: Export overdue payments
- `dealer-commission`: Export dealer commissions

**Response**:
- Content-Type: `text/csv`
- Filename: `{type}-{timestamp}.csv`

### Export Format

#### Sold Plots CSV
```csv
Plot Number,Society,Block,Street,Size (Marla),Price,Owner,Status
PLT-001,DHA Phase 5,A,Street 1,10,5000000,John Doe,sold
PLT-002,Bahria Town,B,Street 2,15,7500000,Jane Smith,sold
```

#### Available Plots CSV
```csv
Plot Number,Society,Block,Street,Size (Marla),Price,Status
PLT-003,DHA Phase 5,A,Street 3,10,5000000,available
PLT-004,Bahria Town,B,Street 4,12,6000000,reserved
```

#### Overdue Installments CSV
```csv
File Number,Client,Property,Installment #,Amount,Due Date,Days Overdue,Penalty
FILE-001,John Doe,PLT-001,3,50000,2024-05-01,45,1500
FILE-002,Jane Smith,PLT-002,2,75000,2024-06-01,15,450
```

#### Dealer Commission CSV
```csv
Deal Number,Dealer,Client,Property,Deal Amount,Commission %,Commission Amount,Status
DEAL-001,Dealer A,Client X,PLT-001,5000000,5,250000,confirmed
DEAL-002,Dealer B,Client Y,PLT-002,7500000,4,300000,pending
```

### Export Usage

```php
// In controller
return response()->stream($callback, 200, $headers);

// Callback function
$callback = function() use ($reportType, $request) {
    $file = fopen('php://output', 'w');

    // Write headers
    fputcsv($file, ['Column1', 'Column2', 'Column3']);

    // Write data rows
    foreach ($data as $row) {
        fputcsv($file, [
            $row->column1,
            $row->column2,
            $row->column3,
        ]);
    }

    fclose($file);
};
```

---

## Permissions

### Required Permissions

All report routes require the `reports.view` permission:

```php
Route::middleware(['permission:reports.view'])->group(function () {
    // All report routes
});
```

### Permission Setup

Create the permission in your database:

```php
// Database seeder
Permission::create(['name' => 'reports.view', 'guard_name' => 'web']);
```

### Assign to Roles

```php
$role = Role::findByName('Manager');
$role->givePermissionTo('reports.view');
```

### Check Permission in Code

```php
// In controller
$this->authorize('reports.view');

// In blade
@can('reports.view')
    <a href="{{ route('reports.index') }}">View Reports</a>
@endcan
```

---

## Performance Considerations

### Query Optimization

1. **Eager Loading**: Always use `with()` for relationships
```php
FilePayment::with(['propertyFile.client', 'propertyFile.fileable'])
```

2. **Index Usage**: Ensure indexes on:
   - `payment_date` (FilePayment, AccountPayment, Expense)
   - `due_date` (FilePayment)
   - `status` (FilePayment, AccountPayment, Expense)
   - `payment_type` (FilePayment)
   - `dealer_id` (Deal)

3. **Selective Columns**: Use `select()` when full models not needed
```php
FilePayment::select('id', 'amount', 'payment_date', 'status')
```

4. **Chunking**: For large datasets
```php
FilePayment::chunk(1000, function($payments) {
    // Process chunk
});
```

### Caching Strategy

```php
// Cache report data for 1 hour
$plotReport = Cache::remember('plots-report-' . $societyId, 3600, function() use ($societyId) {
    return $this->getPlotsReport($societyId);
});
```

### Database Indexes

```sql
-- FilePayment indexes
CREATE INDEX idx_filepayment_payment_date ON file_payments(payment_date);
CREATE INDEX idx_filepayment_due_date ON file_payments(due_date);
CREATE INDEX idx_filepayment_status ON file_payments(status);
CREATE INDEX idx_filepayment_type_status ON file_payments(payment_type, status);

-- AccountPayment indexes
CREATE INDEX idx_accountpayment_payment_date ON account_payments(payment_date);
CREATE INDEX idx_accountpayment_payable ON account_payments(payable_type, payable_id);

-- Deal indexes
CREATE INDEX idx_deal_dealer ON deals(dealer_id);
CREATE INDEX idx_deal_date ON deals(deal_date);
```

---

## Migration Guide

### From Old to New Payment System

**Old System**:
- `Payment` model → Property file payments only
- `DealCommission` model → Commission tracking
- `Installment` model → Scheduled payments

**New System**:
- `FilePayment` model → Property file installments
- `AccountPayment` model → General income (polymorphic)
- `Expense` model → Business expenses

### Code Migration Examples

#### Payment Reports

**Old**:
```php
Payment::whereBetween('payment_date', [$start, $end])->sum('amount');
```

**New**:
```php
$filePayments = FilePayment::whereBetween('payment_date', [$start, $end])
    ->whereIn('status', ['received', 'cleared'])
    ->sum('amount');

$accountPayments = AccountPayment::whereBetween('payment_date', [$start, $end])
    ->whereIn('status', ['received', 'cleared'])
    ->sum('amount');

$totalIncome = $filePayments + $accountPayments;
```

#### Commission Reports

**Old**:
```php
DealCommission::where('dealer_id', $dealerId)->sum('commission_amount');
```

**New**:
```php
// Get earned commissions
$earned = Deal::where('dealer_id', $dealerId)->sum('commission_amount');

// Get paid commissions
$paid = AccountPayment::where('payable_type', Deal::class)
    ->whereIn('payable_id', $dealIds)
    ->sum('amount');

$pending = $earned - $paid;
```

#### Overdue Reports

**Old**:
```php
Installment::where('status', 'overdue')->count();
```

**New**:
```php
FilePayment::where('payment_type', 'installment')
    ->where('status', 'pending')
    ->where('due_date', '<', now())
    ->count();
```

---

## Testing

### Unit Tests

```php
public function test_comprehensive_monthly_income_report()
{
    // Create test data
    $file = PropertyFile::factory()->create();
    FilePayment::factory()->create([
        'property_file_id' => $file->id,
        'amount' => 50000,
        'payment_date' => now(),
        'status' => 'received',
    ]);

    // Generate report
    $response = $this->get(route('reports.monthly-income'));

    $response->assertStatus(200);
    $response->assertViewHas('summary');
}

public function test_overdue_installments_calculation()
{
    $file = PropertyFile::factory()->create();
    $payment = FilePayment::factory()->create([
        'property_file_id' => $file->id,
        'payment_type' => 'installment',
        'status' => 'pending',
        'due_date' => now()->subDays(30),
        'amount' => 50000,
    ]);

    $response = $this->get(route('reports.overdue-installments'));

    $response->assertStatus(200);
    $response->assertSee($file->file_number);
}
```

---

## API Integration (Future)

### Potential API Endpoints

```php
// JSON API for reports
Route::prefix('api/v1')->group(function() {
    Route::get('reports/monthly-income', [ApiReportController::class, 'monthlyIncome']);
    Route::get('reports/dealer-commission', [ApiReportController::class, 'dealerCommission']);
    Route::get('reports/overdue', [ApiReportController::class, 'overdueInstallments']);
});
```

### JSON Response Example

```json
{
    "success": true,
    "data": {
        "summary": {
            "total_income": 500000,
            "total_cleared": 480000,
            "file_payments": 350000,
            "account_payments": 150000,
            "total_expenses": 100000,
            "net_profit": 400000,
            "profit_margin": 80.00
        },
        "file_payments": [...],
        "account_payments": [...]
    },
    "meta": {
        "year": 2024,
        "month": 6,
        "generated_at": "2024-06-15T10:30:00Z"
    }
}
```

---

## Troubleshooting

### Common Issues

1. **"Payment model not found"**
   - Solution: Ensure using `FilePayment` and `AccountPayment`, not `Payment`

2. **"Zero income reported"**
   - Check payment status filters (`received`, `cleared`)
   - Verify date range includes payment dates
   - Check payment_date vs due_date

3. **"Commission not matching"**
   - Verify AccountPayment links to Deal (payable_type)
   - Check Deal.commission_amount is calculated
   - Ensure payment status is correct

4. **"Overdue count incorrect"**
   - Verify FilePayment.payment_type = 'installment'
   - Check FilePayment.status = 'pending'
   - Ensure due_date is not null

### Debug Mode

```php
// Enable query logging
DB::enableQueryLog();

// Run report
$report = $this->comprehensiveMonthlyIncome($request);

// View queries
dd(DB::getQueryLog());
```

---

## Future Enhancements

1. **Automated Email Reports**: Schedule daily/weekly/monthly email reports
2. **Dashboard Widgets**: Real-time metrics on main dashboard
3. **Comparative Analysis**: Year-over-year, month-over-month comparisons
4. **Forecasting**: Predictive analytics based on historical data
5. **Custom Report Builder**: User-defined report parameters
6. **PDF Export**: Generate PDF versions of reports
7. **Excel Export**: Advanced Excel exports with formatting
8. **Chart Visualizations**: Interactive charts with Chart.js/ApexCharts
9. **Drill-down Reports**: Click to view detailed breakdowns
10. **Mobile App Integration**: API for mobile app reports

---

## Conclusion

The Reports Module provides comprehensive business intelligence for your Real Estate CRM. It integrates seamlessly with the new payment system (FilePayment, AccountPayment, Expense) while maintaining backward compatibility with legacy reports.

### Key Benefits

- **Unified Income Tracking**: Combines all payment sources
- **Accurate Commission Tracking**: Polymorphic payment relationships
- **Automated Penalty Calculation**: Built-in late fee computation
- **Flexible Filtering**: Date, dealer, society, payment type filters
- **Export Capabilities**: CSV exports for all major reports
- **Performance Optimized**: Efficient queries with proper indexing
- **Permission Protected**: Role-based access control

### Getting Started

1. Ensure all migrations are run
2. Assign `reports.view` permission to appropriate roles
3. Access reports at `/reports`
4. Use filters to customize data views
5. Export reports as needed

For additional support or feature requests, contact the development team.

---

**Document Version**: 1.0
**Last Updated**: {{ now()->format('Y-m-d') }}
**Module**: Reports
**Laravel Version**: 11.x

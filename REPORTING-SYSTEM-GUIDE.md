# REAL ESTATE CRM - REPORTING SYSTEM GUIDE

## Table of Contents
1. [Overview](#overview)
2. [Report Types](#report-types)
3. [Database Queries Explained](#database-queries-explained)
4. [Chart Implementations](#chart-implementations)
5. [Filter System](#filter-system)
6. [Export & Print Features](#export-print-features)
7. [Performance Optimization](#performance-optimization)

---

## Overview

The reporting system provides 5 comprehensive reports for real estate business analytics:

- **Plots Report**: Available vs Sold plots with status distribution
- **Payments Report**: Monthly payment collections and trends
- **Commissions Report**: Dealer performance and commission tracking
- **Overdue Installments**: Late payment tracking with aging analysis
- **Society-wise Sales**: Performance comparison across societies

**Key Features:**
- Real-time data aggregation
- Interactive charts (Chart.js)
- Date range filters
- Society/Dealer filters
- Export to Excel/PDF
- Print-friendly layout
- Responsive design

---

## Report Types

### 1. PLOTS REPORT (Available vs Sold)

**Purpose:** Track inventory status and sales performance

**Metrics Displayed:**
- Total plots count
- Available plots (%)
- Booked plots (%)
- Sold plots (%)
- Total inventory value
- Value by status

**Charts:**
- Doughnut chart: Plot status distribution
- Bar chart: Plot value by status

**Data Table:**
- Society-wise breakdown
- Counts by status
- Total and sold values

---

### 2. PAYMENTS REPORT (Monthly Collections)

**Purpose:** Monitor cash flow and payment trends

**Metrics Displayed:**
- Total amount received
- Total transactions
- Average payment amount
- Daily collection average

**Charts:**
- Line chart: Monthly payment trend
- Pie chart: Payment method distribution

**Data Table:**
- Daily payment summary
- Breakdown by payment method (Cash, Bank, Cheque, Online)
- Daily totals with grand total footer

---

### 3. DEALER COMMISSIONS REPORT

**Purpose:** Track dealer performance and commission payouts

**Metrics Displayed:**
- Total commission earned
- Total commission paid
- Pending payments
- Active dealers count

**Charts:**
- Horizontal bar chart: Top 10 dealers by commission

**Data Table:**
- Dealer-wise commission breakdown
- Total deals per dealer
- Earned vs Paid vs Pending
- Average commission per deal
- Active/Inactive status

---

### 4. OVERDUE INSTALLMENTS REPORT

**Purpose:** Identify late payments and collections needed

**Metrics Displayed:**
- Total overdue count
- Total overdue amount
- Average days overdue
- Total late fees accumulated

**Aging Buckets:**
- 1-30 days overdue (Warning)
- 31-60 days overdue (Danger)
- 61-90 days overdue (Critical)
- 90+ days overdue (Severe)

**Data Table:**
- Complete overdue installments list
- Client information
- Days overdue with badges
- Late fees calculation
- Pay Now action button

---

### 5. SOCIETY-WISE SALES REPORT

**Purpose:** Compare performance across different societies

**Metrics Displayed:**
- Total societies count
- Total sales value
- Total deals completed
- Top performing society

**Charts:**
- Bar chart: Sales by society (in millions PKR)

**Data Table:**
- Society performance breakdown
- Total plots vs sold plots
- Sales rate percentage with visual progress bar
- Total and average plot prices
- Performance badges (Excellent, Good, Average, Poor)

---

## Database Queries Explained

### Query 1: Plots Report - Status Distribution

```php
// Get plot counts by status
$total = Plot::count();
$available = Plot::where('status', 'available')->count();
$booked = Plot::where('status', 'booked')->count();
$sold = Plot::where('status', 'sold')->count();

// Get values by status
$availableValue = Plot::where('status', 'available')->sum('price');
$bookedValue = Plot::where('status', 'booked')->sum('price');
$soldValue = Plot::where('status', 'sold')->sum('price');

// Calculate percentages
$available_percentage = ($available / $total) * 100;
```

**Purpose:** Simple aggregation to count plots and sum prices by status.

**Performance:** Uses indexed `status` column, very fast.

---

### Query 2: Society-wise Plot Breakdown

```php
DB::table('societies')
    ->leftJoin('blocks', 'societies.id', '=', 'blocks.society_id')
    ->leftJoin('streets', 'blocks.id', '=', 'streets.block_id')
    ->leftJoin('plots', 'streets.id', '=', 'plots.street_id')
    ->select(
        'societies.id',
        'societies.name',
        DB::raw('COUNT(plots.id) as total_plots'),
        DB::raw('SUM(CASE WHEN plots.status = "available" THEN 1 ELSE 0 END) as available_plots'),
        DB::raw('SUM(CASE WHEN plots.status = "booked" THEN 1 ELSE 0 END) as booked_plots'),
        DB::raw('SUM(CASE WHEN plots.status = "sold" THEN 1 ELSE 0 END) as sold_plots'),
        DB::raw('SUM(plots.price) as total_value'),
        DB::raw('SUM(CASE WHEN plots.status = "sold" THEN plots.price ELSE 0 END) as sold_value')
    )
    ->groupBy('societies.id', 'societies.name')
    ->having('total_plots', '>', 0)
    ->get();
```

**Explanation:**
- Joins 4 tables: societies → blocks → streets → plots
- Uses `CASE WHEN` to conditionally count by status
- Groups by society
- Filters societies with at least 1 plot

**Performance Tip:**
- Add indexes: `blocks.society_id`, `streets.block_id`, `plots.street_id`
- Use LEFT JOIN to include societies with no plots
- HAVING clause filters after grouping

---

### Query 3: Daily Payment Summary

```php
DB::table('payments')
    ->whereBetween('payment_date', [$dateFrom, $dateTo])
    ->select(
        DB::raw('DATE(payment_date) as date'),
        DB::raw('COUNT(*) as transaction_count'),
        DB::raw('SUM(CASE WHEN payment_method = "cash" THEN amount ELSE 0 END) as cash_amount'),
        DB::raw('SUM(CASE WHEN payment_method = "bank_transfer" THEN amount ELSE 0 END) as bank_amount'),
        DB::raw('SUM(CASE WHEN payment_method = "cheque" THEN amount ELSE 0 END) as cheque_amount'),
        DB::raw('SUM(CASE WHEN payment_method = "online" THEN amount ELSE 0 END) as online_amount'),
        DB::raw('SUM(amount) as total_amount')
    )
    ->groupBy('date')
    ->orderBy('date', 'desc')
    ->get();
```

**Explanation:**
- Groups payments by day
- Sums amounts by payment method using conditional aggregation
- Filters by date range

**Performance Tip:**
- Add composite index: `(payment_date, payment_method, amount)`
- Use DATE function to strip time component

---

### Query 4: Monthly Payment Trend

```php
DB::table('payments')
    ->whereBetween('payment_date', [$dateFrom, $dateTo])
    ->select(
        DB::raw('DATE_FORMAT(payment_date, "%Y-%m") as month'),
        DB::raw('SUM(amount) as total')
    )
    ->groupBy('month')
    ->orderBy('month')
    ->pluck('total', 'month');
```

**Explanation:**
- Uses `DATE_FORMAT` to extract year-month
- Groups by month
- Returns key-value pairs for chart data

**Chart Implementation:**
```javascript
// Fill missing months with zero
$amounts = [];
$start = Carbon::parse($dateFrom)->startOfMonth();
$end = Carbon::parse($dateTo)->endOfMonth();

while ($start <= $end) {
    $key = $start->format('Y-m');
    $amounts[] = $payments[$key] ?? 0;  // Fill gaps
    $start->addMonth();
}
```

---

### Query 5: Dealer Commission Breakdown

```php
DB::table('dealers')
    ->join('users', 'dealers.user_id', '=', 'users.id')
    ->leftJoin('deal_commissions', 'dealers.id', '=', 'deal_commissions.dealer_id')
    ->leftJoin('deals', 'deal_commissions.deal_id', '=', 'deals.id')
    ->whereBetween('deal_commissions.created_at', [$dateFrom, $dateTo])
    ->select(
        'dealers.id',
        'users.name',
        'dealers.phone',
        'dealers.is_active',
        DB::raw('COUNT(DISTINCT deals.id) as total_deals'),
        DB::raw('SUM(CASE WHEN deal_commissions.payment_status IN ("approved", "paid") THEN deal_commissions.commission_amount ELSE 0 END) as commission_earned'),
        DB::raw('SUM(CASE WHEN deal_commissions.payment_status = "paid" THEN deal_commissions.commission_amount ELSE 0 END) as commission_paid'),
        DB::raw('SUM(CASE WHEN deal_commissions.payment_status = "approved" THEN deal_commissions.commission_amount ELSE 0 END) as commission_pending'),
        DB::raw('AVG(deal_commissions.commission_amount) as avg_commission')
    )
    ->groupBy('dealers.id', 'users.name', 'dealers.phone', 'dealers.is_active')
    ->get();
```

**Explanation:**
- Joins dealers with users for name
- Joins commissions and deals
- Counts distinct deals (avoid duplicates from multiple commissions)
- Calculates earned (approved + paid), paid, and pending separately
- Computes average commission per deal

**Performance Tip:**
- Index `deal_commissions.payment_status`
- Use `COUNT(DISTINCT)` carefully (can be slow on large datasets)

---

### Query 6: Overdue Installments with Aging

```php
DB::table('installments')
    ->join('property_files', 'installments.property_file_id', '=', 'property_files.id')
    ->join('clients', 'property_files.client_id', '=', 'clients.id')
    ->where('installments.status', 'overdue')
    ->select(
        'installments.id',
        'property_files.file_number',
        'clients.name as client_name',
        'clients.phone as client_phone',
        'installments.installment_number',
        'installments.due_date',
        'installments.days_overdue',
        'installments.amount',
        'installments.late_fee',
        DB::raw('installments.amount + installments.late_fee as total_due')
    )
    ->orderBy('installments.days_overdue', 'desc')
    ->get();
```

**Explanation:**
- Joins installments with files and clients
- Filters only overdue status
- Calculates total due (amount + late fee)
- Orders by days overdue (most critical first)

**Aging Analysis Query:**
```php
DB::table('installments')
    ->where('status', 'overdue')
    ->select(
        DB::raw('SUM(CASE WHEN days_overdue BETWEEN 1 AND 30 THEN 1 ELSE 0 END) as `1_30_days`'),
        DB::raw('SUM(CASE WHEN days_overdue BETWEEN 1 AND 30 THEN amount ELSE 0 END) as `1_30_amount`'),
        DB::raw('SUM(CASE WHEN days_overdue BETWEEN 31 AND 60 THEN 1 ELSE 0 END) as `31_60_days`'),
        DB::raw('SUM(CASE WHEN days_overdue BETWEEN 31 AND 60 THEN amount ELSE 0 END) as `31_60_amount`'),
        // ... more ranges
    )
    ->first();
```

**Purpose:** Groups overdue installments into aging buckets for risk assessment.

---

### Query 7: Society-wise Sales with Performance

```php
DB::table('societies')
    ->leftJoin('blocks', 'societies.id', '=', 'blocks.society_id')
    ->leftJoin('streets', 'blocks.id', '=', 'streets.block_id')
    ->leftJoin('plots', 'streets.id', '=', 'plots.street_id')
    ->leftJoin('deals', function($join) use ($dateFrom, $dateTo) {
        $join->on('deals.dealable_id', '=', 'plots.id')
             ->where('deals.dealable_type', '=', 'App\Models\Plot')
             ->where('deals.status', '=', 'completed')
             ->whereBetween('deals.completed_at', [$dateFrom, $dateTo]);
    })
    ->select(
        'societies.id',
        'societies.name',
        DB::raw('COUNT(DISTINCT plots.id) as total_plots'),
        DB::raw('SUM(CASE WHEN plots.status = "sold" THEN 1 ELSE 0 END) as sold_plots'),
        DB::raw('SUM(deals.deal_amount) as total_sales'),
        DB::raw('AVG(plots.price) as avg_price'),
        DB::raw('COUNT(deals.id) as total_deals'),
        DB::raw('ROUND((SUM(CASE WHEN plots.status = "sold" THEN 1 ELSE 0 END) / COUNT(DISTINCT plots.id)) * 100, 2) as sales_rate')
    )
    ->groupBy('societies.id', 'societies.name')
    ->get();
```

**Explanation:**
- Complex join through society hierarchy to deals
- Uses polymorphic relationship condition (`dealable_type`, `dealable_id`)
- Filters completed deals in date range
- Calculates sales rate percentage in SQL
- Groups by society

**Performance Tip:**
- Add composite index: `(deals.dealable_type, deals.dealable_id, deals.status, deals.completed_at)`

---

## Chart Implementations

### Chart.js Integration

**CDN:**
```html
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
```

### Chart 1: Plot Status Doughnut Chart

```javascript
new Chart(document.getElementById('plotStatusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Available', 'Booked', 'Sold'],
        datasets: [{
            data: [
                {{ $plotsReport['available'] }},
                {{ $plotsReport['booked'] }},
                {{ $plotsReport['sold'] }}
            ],
            backgroundColor: ['#10b981', '#f59e0b', '#3b82f6']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
```

**Purpose:** Visual representation of plot inventory status distribution.

---

### Chart 2: Monthly Payment Trend Line Chart

```javascript
new Chart(document.getElementById('paymentTrendChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($paymentMonths) !!},  // ['Jan 2026', 'Feb 2026', ...]
        datasets: [{
            label: 'Payments (PKR)',
            data: {!! json_encode($paymentAmounts) !!},  // [500000, 650000, ...]
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,  // Smooth curve
            fill: true     // Fill area under line
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rs. ' + value.toLocaleString();
                    }
                }
            }
        }
    }
});
```

**Purpose:** Show payment collection trends over time.

---

### Chart 3: Payment Method Pie Chart

```javascript
new Chart(document.getElementById('paymentMethodChart'), {
    type: 'pie',
    data: {
        labels: ['Cash', 'Bank Transfer', 'Cheque', 'Online'],
        datasets: [{
            data: {!! json_encode($paymentMethodData) !!},  // [2000000, 1500000, 800000, 300000]
            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = context.parsed;
                        let total = context.dataset.data.reduce((a, b) => a + b, 0);
                        let percentage = ((value / total) * 100).toFixed(1);
                        return label + ': Rs. ' + value.toLocaleString() + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
```

**Purpose:** Show distribution of payment methods with percentages.

---

### Chart 4: Top Dealers Horizontal Bar Chart

```javascript
new Chart(document.getElementById('topDealersChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($topDealerNames) !!},  // ['Ahmed Ali', 'Bilal Khan', ...]
        datasets: [{
            label: 'Commission Earned (PKR)',
            data: {!! json_encode($topDealerCommissions) !!},  // [250000, 180000, ...]
            backgroundColor: '#3b82f6'
        }]
    },
    options: {
        responsive: true,
        indexAxis: 'y',  // Horizontal bars
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rs. ' + (value / 1000) + 'K';
                    }
                }
            }
        }
    }
});
```

**Purpose:** Compare dealer performance by commission earned.

---

### Chart 5: Society Sales Bar Chart

```javascript
new Chart(document.getElementById('societySalesChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($societyNames) !!},  // ['DHA Phase 8', 'Bahria Town', ...]
        datasets: [{
            label: 'Total Sales (Million PKR)',
            data: {!! json_encode($societySalesData) !!},  // [45.5, 32.8, ...]
            backgroundColor: '#3b82f6'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rs. ' + value + 'M';
                    }
                }
            }
        }
    }
});
```

**Purpose:** Compare total sales across different societies.

---

## Filter System

### Filter Form Structure

```html
<form method="GET" action="{{ route('reports.index') }}">
    <!-- Date Range -->
    <input type="date" name="date_from" value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}">
    <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}">

    <!-- Society Filter -->
    <select name="society_id">
        <option value="">All Societies</option>
        @foreach($societies as $society)
            <option value="{{ $society->id }}" {{ request('society_id') == $society->id ? 'selected' : '' }}>
                {{ $society->name }}
            </option>
        @endforeach
    </select>

    <!-- Dealer Filter -->
    <select name="dealer_id">
        <option value="">All Dealers</option>
        @foreach($dealers as $dealer)
            <option value="{{ $dealer->id }}" {{ request('dealer_id') == $dealer->id ? 'selected' : '' }}>
                {{ $dealer->user->name }}
            </option>
        @endforeach
    </select>

    <button type="submit">Apply Filters</button>
</form>
```

### Filter Application in Queries

**Example: Filter Plots by Society**
```php
$query = Plot::query();

if ($societyId) {
    $query->whereHas('street.block.society', function ($q) use ($societyId) {
        $q->where('id', $societyId);
    });
}

$plots = $query->get();
```

**Example: Filter Payments by Date Range**
```php
$payments = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])->get();
```

**Example: Filter Commissions by Dealer**
```php
$query = DealCommission::whereBetween('created_at', [$dateFrom, $dateTo]);

if ($dealerId) {
    $query->where('dealer_id', $dealerId);
}

$commissions = $query->get();
```

### Preserving Filter State

**URL Parameters:**
```
/reports?date_from=2026-01-01&date_to=2026-01-31&society_id=5&dealer_id=3
```

**Blade Helper:**
```php
{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}
```

**Benefits:**
- Shareable URLs
- Browser back/forward works
- No session storage needed

---

## Export & Print Features

### Export to Excel

**Implementation (using Laravel Excel):**

```php
public function export(Request $request)
{
    $reportType = $request->input('report_type', 'plots');

    return Excel::download(
        new ReportsExport($reportType, $request->all()),
        'report-' . $reportType . '-' . date('Y-m-d') . '.xlsx'
    );
}
```

**Export Class:**
```php
class ReportsExport implements FromCollection, WithHeadings
{
    protected $reportType;
    protected $filters;

    public function __construct($reportType, $filters)
    {
        $this->reportType = $reportType;
        $this->filters = $filters;
    }

    public function collection()
    {
        if ($this->reportType === 'plots') {
            return $this->exportPlotsReport();
        }
        // ... other report types
    }

    public function headings(): array
    {
        if ($this->reportType === 'plots') {
            return ['Society', 'Total Plots', 'Available', 'Booked', 'Sold', 'Total Value', 'Sold Value'];
        }
        // ... other headings
    }
}
```

### Export to PDF

**Implementation (using DomPDF):**

```php
public function exportPdf(Request $request)
{
    $reportType = $request->input('report_type', 'plots');
    $data = $this->getReportData($reportType, $request->all());

    $pdf = PDF::loadView('reports.pdf.' . $reportType, $data);

    return $pdf->download('report-' . $reportType . '-' . date('Y-m-d') . '.pdf');
}
```

**PDF View:**
```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $reportTitle }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #3b82f6; color: white; }
    </style>
</head>
<body>
    <h1>{{ $reportTitle }}</h1>
    <p>Date: {{ date('Y-m-d') }}</p>
    <table>
        <!-- Report data -->
    </table>
</body>
</html>
```

### Print Functionality

**CSS for Print:**
```css
@media print {
    .no-print {
        display: none !important;
    }

    .page-break {
        page-break-after: always;
    }

    .report-section {
        page-break-inside: avoid;
    }

    body {
        font-size: 12pt;
    }
}
```

**JavaScript Print:**
```javascript
function printReport() {
    window.print();
}
```

---

## Performance Optimization

### 1. Database Indexing

**Required Indexes:**
```sql
-- Plots table
CREATE INDEX idx_plots_status ON plots(status);
CREATE INDEX idx_plots_street_id ON plots(street_id);

-- Payments table
CREATE INDEX idx_payments_date_method ON payments(payment_date, payment_method);

-- Installments table
CREATE INDEX idx_installments_status_overdue ON installments(status, days_overdue);

-- Deal Commissions table
CREATE INDEX idx_commissions_dealer_status ON deal_commissions(dealer_id, payment_status, created_at);

-- Deals table (polymorphic)
CREATE INDEX idx_deals_polymorphic ON deals(dealable_type, dealable_id, status, completed_at);
```

### 2. Query Caching

**Cache Report Results:**
```php
public function getPlotsReport($societyId = null)
{
    $cacheKey = 'plots_report_' . ($societyId ?? 'all');

    return Cache::remember($cacheKey, 3600, function () use ($societyId) {
        // Expensive query here
    });
}
```

**Clear Cache on Data Changes:**
```php
// In PlotObserver
public function updated(Plot $plot)
{
    Cache::forget('plots_report_all');
    Cache::forget('plots_report_' . $plot->street->block->society_id);
}
```

### 3. Eager Loading

**Load Relationships:**
```php
$installments = Installment::with(['propertyFile.client'])
    ->where('status', 'overdue')
    ->get();
```

**Avoids N+1 Query Problem:**
```blade
@foreach($installments as $installment)
    {{ $installment->propertyFile->client->name }}  <!-- No extra query -->
@endforeach
```

### 4. Chunk Large Datasets

**For Export:**
```php
Installment::where('status', 'overdue')
    ->chunk(1000, function ($installments) {
        foreach ($installments as $installment) {
            // Process each installment
        }
    });
```

### 5. Database View for Complex Reports

**Create View:**
```sql
CREATE VIEW vw_society_sales AS
SELECT
    s.id AS society_id,
    s.name AS society_name,
    COUNT(DISTINCT p.id) AS total_plots,
    SUM(CASE WHEN p.status = 'sold' THEN 1 ELSE 0 END) AS sold_plots,
    SUM(d.deal_amount) AS total_sales
FROM societies s
LEFT JOIN blocks b ON s.id = b.society_id
LEFT JOIN streets st ON b.id = st.block_id
LEFT JOIN plots p ON st.id = p.street_id
LEFT JOIN deals d ON d.dealable_id = p.id AND d.dealable_type = 'App\\Models\\Plot'
GROUP BY s.id, s.name;
```

**Use in Laravel:**
```php
$societySales = DB::table('vw_society_sales')
    ->where('total_sales', '>', 0)
    ->get();
```

### 6. Pagination for Large Reports

**Paginate Results:**
```php
$overdueInstallments = Installment::where('status', 'overdue')
    ->paginate(50);
```

**Blade Pagination:**
```blade
{{ $overdueInstallments->links() }}
```

### 7. Background Processing

**Queue Heavy Reports:**
```php
dispatch(new GenerateReportJob($reportType, $filters, auth()->id()));
```

**Email Results:**
```php
Mail::to(auth()->user())->send(new ReportReadyMail($reportUrl));
```

---

## Route Configuration

**web.php:**
```php
Route::middleware(['auth', 'role:super_admin|admin|manager'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
});
```

---

## Usage Examples

### Example 1: View Current Month Payment Report

**URL:**
```
/reports?date_from=2026-01-01&date_to=2026-01-31
```

**Result:**
- Shows all payments from January 2026
- Daily breakdown table
- Monthly trend chart
- Payment method distribution

---

### Example 2: Filter by Specific Society

**URL:**
```
/reports?society_id=5
```

**Result:**
- Plots report filtered to Society #5
- Shows only plots in that society
- Society-wise table shows single row

---

### Example 3: Dealer Performance for Quarter

**URL:**
```
/reports?date_from=2026-01-01&date_to=2026-03-31&dealer_id=3
```

**Result:**
- Commission report for Dealer #3
- Q1 2026 data only
- Shows all deals and commissions for that dealer

---

## Security Considerations

### 1. Authorization

**Restrict Report Access:**
```php
public function index()
{
    $this->authorize('view-reports');

    // ... report logic
}
```

**Policy:**
```php
public function viewReports(User $user)
{
    return $user->hasAnyRole(['super_admin', 'admin', 'manager', 'accountant']);
}
```

### 2. Input Validation

**Validate Filters:**
```php
$request->validate([
    'date_from' => 'nullable|date',
    'date_to' => 'nullable|date|after_or_equal:date_from',
    'society_id' => 'nullable|exists:societies,id',
    'dealer_id' => 'nullable|exists:dealers,id',
]);
```

### 3. Prevent SQL Injection

**Use Parameter Binding:**
```php
// GOOD
DB::table('payments')->where('id', $id)->get();

// BAD
DB::select("SELECT * FROM payments WHERE id = $id");
```

---

## Testing Recommendations

### Unit Tests

**Test Report Calculations:**
```php
public function test_plots_report_calculates_percentages_correctly()
{
    Plot::factory()->count(10)->create(['status' => 'available']);
    Plot::factory()->count(5)->create(['status' => 'sold']);

    $report = (new ReportController)->getPlotsReport();

    $this->assertEquals(66.7, $report['available_percentage']);
    $this->assertEquals(33.3, $report['sold_percentage']);
}
```

### Feature Tests

**Test Report Page Access:**
```php
public function test_authorized_user_can_view_reports()
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->get('/reports');

    $response->assertStatus(200);
    $response->assertViewIs('reports.index');
}
```

---

## Conclusion

This reporting system provides:
✅ 5 comprehensive reports covering all business metrics
✅ Interactive charts for data visualization
✅ Flexible filtering (date range, society, dealer)
✅ Export capabilities (Excel, PDF)
✅ Print-friendly layouts
✅ Optimized database queries
✅ Role-based access control
✅ Responsive design for mobile access

**Next Steps:**
1. Add route to `web.php`
2. Create navigation link in sidebar
3. Test with sample data
4. Implement export functionality
5. Add scheduled email reports
6. Create automated weekly/monthly summaries

**Performance Tips:**
- Add database indexes as documented
- Cache frequently accessed reports
- Use pagination for large datasets
- Consider database views for complex joins
- Queue heavy report generation

---

## Additional Features (Future Enhancements)

1. **Custom Date Ranges:** Last 7 days, Last 30 days, This Quarter, This Year presets
2. **Report Scheduling:** Email reports automatically (daily/weekly/monthly)
3. **Dashboard Widgets:** Mini report cards on main dashboard
4. **Comparison Mode:** Compare this month vs last month
5. **Forecasting:** Predict next month's sales based on trends
6. **Export Templates:** Customize Excel export columns
7. **Saved Filters:** Save frequently used filter combinations
8. **Report Sharing:** Generate shareable links for specific reports
9. **Mobile App:** Dedicated mobile report views
10. **Real-time Updates:** WebSocket for live report data

---

**Documentation Version:** 1.0
**Last Updated:** January 28, 2026
**Author:** Real Estate CRM Development Team

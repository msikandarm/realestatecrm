# Real Estate CRM - Module Relationships & Integration Guide

## 🔗 Complete Module Relationship Map

This document defines all relationships between modules in the Real Estate CRM system.

---

## 📊 Entity Relationship Diagram

```
                                    ┌─────────────┐
                                    │    Users    │
                                    └──────┬──────┘
                                           │ 1:1
                                           │
┌─────────────┐                    ┌──────▼──────┐
│  Societies  │                    │   Dealers   │
└──────┬──────┘                    └──────┬──────┘
       │ 1:N                               │ 1:N
       │                                   │
┌──────▼──────┐                    ┌──────▼──────┐
│   Blocks    │                    │    Deals    │◄──┐
└──────┬──────┘                    └──────┬──────┘   │
       │ 1:N                               │          │
       │                                   │ N:1      │
┌──────▼──────┐                    ┌──────▼──────┐   │
│   Streets   │                    │   Clients   │   │
└──────┬──────┘                    └─────────────┘   │
       │ 1:N                                          │
       │                                              │
┌──────▼──────┐       ┌──────────────┐              │
│    Plots    │──────►│ PropertyFiles│              │
└──────┬──────┘  1:N  └──────┬───────┘              │
       │                      │ 1:N                  │ Polymorphic
       │                      │                      │
       │ 1:N          ┌───────▼────────┐            │
       │              │  Installments  │            │
       │              └────────────────┘             │
       │                                             │
       └─────────────────────────────────────────────┘

┌──────────────┐              ┌──────────────┐
│  Properties  │─────────────►│    Deals     │ (Polymorphic)
└──────┬───────┘      1:N     └──────────────┘
       │
       │ 1:N
       │
       └─────────────────────►│PropertyFiles │ (Polymorphic)
                               └──────────────┘

┌──────────────┐              ┌──────────────┐
│   Expenses   │              │   Account    │
│  (Standalone)│              │   Payments   │ (Polymorphic to Dealer/Client)
└──────────────┘              └──────────────┘
```

---

## 🏗️ Module Definitions

### 1. Location Hierarchy (Core Structure)

#### Society (Parent)
```php
// Model: App\Models\Society

class Society extends Model
{
    protected $fillable = ['name', 'location', 'description', 'status'];

    // Relationships
    public function blocks() {
        return $this->hasMany(Block::class);
    }

    // Accessors
    public function getTotalBlocksAttribute() {
        return $this->blocks()->count();
    }

    public function getTotalPlotsAttribute() {
        return Plot::whereHas('street.block', function($q) {
            $q->where('society_id', $this->id);
        })->count();
    }
}
```

**Database Schema:**
```sql
CREATE TABLE societies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    location VARCHAR(500),
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_status (status)
);
```

---

#### Block (Child of Society)
```php
// Model: App\Models\Block

class Block extends Model
{
    protected $fillable = ['society_id', 'name', 'description', 'status'];

    // Relationships
    public function society() {
        return $this->belongsTo(Society::class);
    }

    public function streets() {
        return $this->hasMany(Street::class);
    }

    // Accessors
    public function getTotalStreetsAttribute() {
        return $this->streets()->count();
    }
}
```

**Database Schema:**
```sql
CREATE TABLE blocks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    society_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (society_id) REFERENCES societies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_block_per_society (society_id, name),
    INDEX idx_society (society_id),
    INDEX idx_status (status)
);
```

---

#### Street (Child of Block)
```php
// Model: App\Models\Street

class Street extends Model
{
    protected $fillable = ['block_id', 'name', 'description', 'status'];

    // Relationships
    public function block() {
        return $this->belongsTo(Block::class);
    }

    public function plots() {
        return $this->hasMany(Plot::class);
    }

    // Accessors
    public function getTotalPlotsAttribute() {
        return $this->plots()->count();
    }

    public function getAvailablePlotsAttribute() {
        return $this->plots()->where('status', 'available')->count();
    }
}
```

**Database Schema:**
```sql
CREATE TABLE streets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    block_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (block_id) REFERENCES blocks(id) ON DELETE CASCADE,
    UNIQUE KEY unique_street_per_block (block_id, name),
    INDEX idx_block (block_id),
    INDEX idx_status (status)
);
```

---

#### Plot (Child of Street)
```php
// Model: App\Models\Plot

class Plot extends Model
{
    protected $fillable = [
        'street_id', 'plot_number', 'size', 'unit',
        'price', 'type', 'status', 'description'
    ];

    // Relationships
    public function street() {
        return $this->belongsTo(Street::class);
    }

    public function deals() {
        return $this->morphMany(Deal::class, 'dealable');
    }

    public function files() {
        return $this->morphMany(PropertyFile::class, 'fileable');
    }

    // Accessors
    public function getLocationHierarchyAttribute() {
        return [
            'society' => $this->street->block->society->name,
            'block' => $this->street->block->name,
            'street' => $this->street->name,
            'plot' => $this->plot_number,
        ];
    }
}
```

**Database Schema:**
```sql
CREATE TABLE plots (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    street_id BIGINT UNSIGNED NOT NULL,
    plot_number VARCHAR(100) NOT NULL,
    size DECIMAL(10, 2) NOT NULL,
    unit ENUM('marla', 'kanal', 'sqft', 'sqyd') NOT NULL,
    price DECIMAL(15, 2) NOT NULL,
    type ENUM('residential', 'commercial', 'agricultural') NOT NULL,
    status ENUM('available', 'reserved', 'sold', 'blocked') DEFAULT 'available',
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (street_id) REFERENCES streets(id) ON DELETE CASCADE,
    UNIQUE KEY unique_plot_per_street (street_id, plot_number),
    INDEX idx_street (street_id),
    INDEX idx_status (status),
    INDEX idx_price (price)
);
```

---

### 2. Property Management

#### Property
```php
// Model: App\Models\Property

class Property extends Model
{
    protected $fillable = [
        'title', 'type', 'status', 'price', 'location',
        'bedrooms', 'bathrooms', 'area', 'area_unit', 'description'
    ];

    // Relationships
    public function deals() {
        return $this->morphMany(Deal::class, 'dealable');
    }

    public function files() {
        return $this->morphMany(PropertyFile::class, 'fileable');
    }

    public function images() {
        return $this->hasMany(PropertyImage::class);
    }

    // Accessors
    public function getFormattedPriceAttribute() {
        return 'PKR ' . number_format($this->price);
    }
}
```

**Database Schema:**
```sql
CREATE TABLE properties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    type ENUM('house', 'apartment', 'commercial', 'plot') NOT NULL,
    status ENUM('for_sale', 'rented', 'sold', 'pending') DEFAULT 'for_sale',
    price DECIMAL(15, 2) NOT NULL,
    location VARCHAR(500),
    bedrooms INT,
    bathrooms INT,
    area DECIMAL(10, 2),
    area_unit VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_price (price)
);

CREATE TABLE property_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    is_primary BOOLEAN DEFAULT 0,
    created_at TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_property (property_id)
);
```

---

### 3. People Management

#### Dealer
```php
// Model: App\Models\Dealer

class Dealer extends Model
{
    protected $fillable = [
        'user_id', 'name', 'phone', 'cnic', 'email',
        'commission_rate', 'status', 'address',
        'bank_name', 'account_number', 'account_title'
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
    ];

    // Relationships
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function deals() {
        return $this->hasMany(Deal::class);
    }

    public function payments() {
        return $this->morphMany(AccountPayment::class, 'payable');
    }

    // Accessors
    public function getTotalDealsAttribute() {
        return $this->deals()->count();
    }

    public function getTotalCommissionAttribute() {
        return $this->deals()->sum('commission_amount');
    }

    public function getCompletedDealsAttribute() {
        return $this->deals()->where('status', 'completed')->count();
    }

    public function getSuccessRateAttribute() {
        $total = $this->total_deals;
        return $total > 0 ? round(($this->completed_deals / $total) * 100, 2) : 0;
    }
}
```

**Database Schema:**
```sql
CREATE TABLE dealers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    cnic VARCHAR(20),
    email VARCHAR(255),
    commission_rate DECIMAL(5, 2) DEFAULT 2.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    address TEXT,
    bank_name VARCHAR(255),
    account_number VARCHAR(100),
    account_title VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_phone (phone)
);
```

---

#### Client
```php
// Model: App\Models\Client

class Client extends Model
{
    protected $fillable = [
        'name', 'phone', 'cnic', 'email', 'address', 'status'
    ];

    // Relationships
    public function deals() {
        return $this->hasMany(Deal::class);
    }

    public function files() {
        return $this->hasMany(PropertyFile::class);
    }

    public function payments() {
        return $this->morphMany(AccountPayment::class, 'payable');
    }
}
```

**Database Schema:**
```sql
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    cnic VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_status (status)
);
```

---

### 4. Deal Management (Polymorphic)

#### Deal
```php
// Model: App\Models\Deal

class Deal extends Model
{
    protected $fillable = [
        'dealable_type', 'dealable_id', 'dealer_id', 'client_id',
        'amount', 'commission_amount', 'status', 'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
    ];

    // Polymorphic Relationships
    public function dealable() {
        return $this->morphTo(); // Property or Plot
    }

    // Standard Relationships
    public function dealer() {
        return $this->belongsTo(Dealer::class);
    }

    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function file() {
        return $this->hasOne(PropertyFile::class);
    }

    // Scopes
    public function scopeCompleted($query) {
        return $query->where('status', 'completed');
    }

    public function scopePending($query) {
        return $query->where('status', 'pending');
    }
}
```

**Database Schema:**
```sql
CREATE TABLE deals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dealable_type VARCHAR(255) NOT NULL, -- Property or Plot
    dealable_id BIGINT UNSIGNED NOT NULL,
    dealer_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    commission_amount DECIMAL(15, 2) NOT NULL,
    status ENUM('pending', 'approved', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (dealer_id) REFERENCES dealers(id) ON DELETE RESTRICT,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    INDEX idx_dealable (dealable_type, dealable_id),
    INDEX idx_dealer (dealer_id),
    INDEX idx_client (client_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

---

### 5. File & Installment System (Polymorphic)

#### Property File
```php
// Model: App\Models\PropertyFile

class PropertyFile extends Model
{
    protected $fillable = [
        'fileable_type', 'fileable_id', 'client_id',
        'total_amount', 'down_payment', 'total_installments',
        'installment_amount', 'frequency', 'start_date',
        'status', 'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'start_date' => 'date',
    ];

    // Polymorphic Relationships
    public function fileable() {
        return $this->morphTo(); // Property or Plot
    }

    // Standard Relationships
    public function client() {
        return $this->belongsTo(Client::class);
    }

    public function installments() {
        return $this->hasMany(Installment::class);
    }

    // Accessors
    public function getPaidAmountAttribute() {
        return $this->installments()->where('status', 'paid')->sum('amount') + $this->down_payment;
    }

    public function getRemainingAmountAttribute() {
        return $this->total_amount - $this->paid_amount;
    }

    public function getCompletionPercentageAttribute() {
        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }
}
```

**Database Schema:**
```sql
CREATE TABLE property_files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fileable_type VARCHAR(255) NOT NULL, -- Property or Plot
    fileable_id BIGINT UNSIGNED NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    total_amount DECIMAL(15, 2) NOT NULL,
    down_payment DECIMAL(15, 2) NOT NULL,
    total_installments INT NOT NULL,
    installment_amount DECIMAL(15, 2) NOT NULL,
    frequency ENUM('monthly', 'quarterly', 'yearly') NOT NULL,
    start_date DATE NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    INDEX idx_fileable (fileable_type, fileable_id),
    INDEX idx_client (client_id),
    INDEX idx_status (status)
);
```

---

#### Installment
```php
// Model: App\Models\Installment

class Installment extends Model
{
    protected $fillable = [
        'property_file_id', 'installment_number', 'amount',
        'due_date', 'paid_date', 'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    // Relationships
    public function propertyFile() {
        return $this->belongsTo(PropertyFile::class);
    }

    // Scopes
    public function scopeOverdue($query) {
        return $query->where('status', 'pending')
                    ->where('due_date', '<', now());
    }

    public function scopePaid($query) {
        return $query->where('status', 'paid');
    }
}
```

**Database Schema:**
```sql
CREATE TABLE installments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_file_id BIGINT UNSIGNED NOT NULL,
    installment_number INT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    due_date DATE NOT NULL,
    paid_date DATE NULL,
    status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (property_file_id) REFERENCES property_files(id) ON DELETE CASCADE,
    INDEX idx_property_file (property_file_id),
    INDEX idx_due_date (due_date),
    INDEX idx_status (status)
);
```

---

### 6. Financial Tracking

#### Expense
```php
// Model: App\Models\Expense

class Expense extends Model
{
    protected $fillable = [
        'category', 'description', 'amount', 'date',
        'payment_method', 'reference', 'attachment',
        'is_recurring', 'frequency', 'end_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'end_date' => 'date',
        'is_recurring' => 'boolean',
    ];

    // Scopes
    public function scopeByCategory($query, $category) {
        return $query->where('category', $category);
    }

    public function scopeThisMonth($query) {
        return $query->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month);
    }
}
```

**Database Schema:**
```sql
CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category ENUM('utilities', 'maintenance', 'salaries', 'marketing', 'other') NOT NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    date DATE NOT NULL,
    payment_method ENUM('cash', 'bank', 'cheque', 'online') NOT NULL,
    reference VARCHAR(255),
    attachment VARCHAR(500),
    is_recurring BOOLEAN DEFAULT 0,
    frequency ENUM('monthly', 'quarterly', 'yearly'),
    end_date DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_date (date)
);
```

---

#### Account Payment (Polymorphic)
```php
// Model: App\Models\AccountPayment

class AccountPayment extends Model
{
    protected $fillable = [
        'type', 'payable_type', 'payable_id', 'amount',
        'payment_method', 'reference', 'date', 'status', 'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    // Polymorphic Relationships
    public function payable() {
        return $this->morphTo(); // Dealer or Client
    }

    // Scopes
    public function scopeCompleted($query) {
        return $query->where('status', 'completed');
    }

    public function scopeByType($query, $type) {
        return $query->where('type', $type);
    }
}
```

**Database Schema:**
```sql
CREATE TABLE account_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('commission', 'refund', 'salary', 'other') NOT NULL,
    payable_type VARCHAR(255) NOT NULL, -- Dealer or Client
    payable_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    payment_method ENUM('cash', 'bank', 'cheque', 'online') NOT NULL,
    reference VARCHAR(255),
    date DATE NOT NULL,
    status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_payable (payable_type, payable_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_date (date)
);
```

---

## 🔄 Common Query Patterns

### 1. Get All Plots in a Society
```php
$society = Society::find(1);
$plots = Plot::whereHas('street.block', function($query) use ($society) {
    $query->where('society_id', $society->id);
})->get();
```

### 2. Get Dealer Performance
```php
$dealer = Dealer::with(['deals' => function($query) {
    $query->where('status', 'completed');
}])->find(1);

$performance = [
    'total_deals' => $dealer->deals()->count(),
    'completed_deals' => $dealer->completed_deals,
    'total_commission' => $dealer->total_commission,
    'success_rate' => $dealer->success_rate,
];
```

### 3. Get Property/Plot Deals
```php
// For Property
$property = Property::with('deals.dealer', 'deals.client')->find(1);

// For Plot
$plot = Plot::with('deals.dealer', 'deals.client')->find(1);
```

### 4. Get Overdue Installments
```php
$overdueInstallments = Installment::with('propertyFile.client')
    ->overdue()
    ->orderBy('due_date')
    ->get();
```

### 5. Monthly Revenue Report
```php
$monthlyRevenue = Deal::completed()
    ->whereYear('created_at', now()->year)
    ->whereMonth('created_at', now()->month)
    ->sum('amount');

$monthlyExpenses = Expense::thisMonth()->sum('amount');

$netProfit = $monthlyRevenue - $monthlyExpenses;
```

---

## ✅ Implementation Checklist

### Models
- [ ] Society, Block, Street, Plot (Location hierarchy)
- [ ] Property, PropertyImage
- [ ] Dealer, Client
- [ ] Deal
- [ ] PropertyFile, Installment
- [ ] Expense
- [ ] AccountPayment

### Migrations
- [ ] All table structures as defined above
- [ ] Foreign key constraints
- [ ] Indexes for performance
- [ ] Unique constraints where needed

### Relationships
- [ ] Define all Eloquent relationships
- [ ] Test polymorphic relationships (Deal, PropertyFile, AccountPayment)
- [ ] Verify cascading deletes work correctly
- [ ] Test eager loading to avoid N+1 queries

### Seeders
- [ ] Create sample societies, blocks, streets, plots
- [ ] Create sample properties
- [ ] Create sample dealers and clients
- [ ] Create sample deals and files
- [ ] Create sample expenses and payments

### API Endpoints
- [ ] Cascading dropdown endpoints (get blocks by society, etc.)
- [ ] AJAX delete endpoints
- [ ] Mark installment as paid endpoint
- [ ] Filter and search endpoints

### Authorization
- [ ] Create policies for each model
- [ ] Implement permission checks in controllers
- [ ] Test @can directives in views

---

**This comprehensive guide provides all relationships needed to connect the frontend views with the backend Laravel models.**

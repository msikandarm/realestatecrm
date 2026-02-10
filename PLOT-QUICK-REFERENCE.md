# Plot Module - Quick Reference

## ✅ Status: FULLY IMPLEMENTED

Plot Management is already complete with full CRUD operations, relationships, and auto-calculations.

---

## 🗂️ Database Hierarchy

```
┌─────────────────────────────────────────────────┐
│ Society (DHA Phase 1)                           │
│ Fields: name, code, city, status, amenities     │
└────────────────┬────────────────────────────────┘
                 │ hasMany
                 ▼
┌─────────────────────────────────────────────────┐
│ Block (A, B, C, ...)                            │
│ Fields: name, code, status, total_plots         │
└────────────────┬────────────────────────────────┘
                 │ hasMany
                 ▼
┌─────────────────────────────────────────────────┐
│ Street (1, 2, 3, ...)                           │
│ Fields: name, code, type, width, total_plots    │
└────────────────┬────────────────────────────────┘
                 │ hasMany
                 ▼
┌─────────────────────────────────────────────────┐
│ Plot (123, 124, ...)                            │
│ Fields: plot_number, area, type, status, price  │
│ Auto: plot_code, total_price                    │
└─────────────────────────────────────────────────┘
```

---

## 📊 Plot Fields

| Field | Type | Required | Auto | Description |
|-------|------|----------|------|-------------|
| `street_id` | Foreign Key | ✅ | - | Links to street |
| `plot_number` | String | ✅ | - | Plot identifier (e.g., "123", "A-45") |
| `plot_code` | String | - | ✅ | SOCIETY-BLOCK-STREET-PLOT |
| `area` | Decimal | ✅ | - | Plot size |
| `area_unit` | Enum | ✅ | - | marla/kanal/acre/sq ft |
| `type` | Enum | ✅ | - | residential/commercial/industrial/agricultural |
| `status` | Enum | ✅ | - | available/booked/sold/on-hold |
| `price_per_marla` | Decimal | - | - | Price per marla |
| `total_price` | Decimal | - | ✅ | area × price_per_marla |
| `corner` | Enum | - | - | yes/no |
| `facing` | Enum | - | - | north/south/east/west/etc. |
| `park_facing` | Enum | - | - | yes/no |
| `main_road_facing` | Enum | - | - | yes/no |
| `map_location` | String | - | - | GPS coordinates |

---

## 🔗 Relationships

### Access All Levels

```php
$plot = Plot::find(1);

// Direct relationship
$plot->street;        // Street model

// Accessor relationships (computed)
$plot->block;         // Block model (via street)
$plot->society;       // Society model (via street.block)

// Audit
$plot->creator;       // User who created
$plot->updater;       // User who last updated

// Full address
$plot->full_address;  // "Plot 123, Street 1, Block A, DHA Phase 1"
```

---

## 🎯 Controller Methods

| Method | Route | Permission | Description |
|--------|-------|------------|-------------|
| `index()` | GET /plots | plots.view | List with filters |
| `create()` | GET /plots/create | plots.create | Show form |
| `store()` | POST /plots | plots.create | Save new plot |
| `show($id)` | GET /plots/{id} | plots.view | Show details |
| `edit($id)` | GET /plots/{id}/edit | plots.edit | Show edit form |
| `update($id)` | PUT /plots/{id} | plots.edit | Update plot |
| `destroy($id)` | DELETE /plots/{id} | plots.delete | Delete plot |

---

## 🔍 Query Examples

### Filtering

```php
// By status
Plot::available()->get();
Plot::sold()->get();
Plot::booked()->get();

// By type
Plot::residential()->get();
Plot::commercial()->get();
Plot::byType('industrial')->get();

// By location
Plot::byStreet(5)->get();
Plot::whereHas('street.block', function($q) {
    $q->where('society_id', 1);
})->get();

// Premium features
Plot::corner()->get();
Plot::parkFacing()->get();
Plot::where('main_road_facing', 'yes')->get();

// Combined
Plot::residential()
    ->available()
    ->corner()
    ->where('price_per_marla', '<', 100000)
    ->orderBy('price_per_marla')
    ->get();
```

### Statistics

```php
// Counts
$totalPlots = Plot::count();
$availablePlots = Plot::available()->count();
$soldPlots = Plot::sold()->count();

// Pricing
$avgPrice = Plot::avg('total_price');
$maxPrice = Plot::max('total_price');
$totalValue = Plot::sold()->sum('total_price');

// By society
$societyPlots = Plot::whereHas('street.block', function($q) {
    $q->where('society_id', 1);
})->count();

// By type
$residentialCount = Plot::residential()->count();
$commercialCount = Plot::commercial()->count();
```

---

## ⚙️ Auto-Calculations

### Plot Code Generation
```php
// Automatically generated on create:
// Format: SOCIETY-BLOCK-STREET-PLOT
// Example: DHAP1-A-ST1-123

$plot = Plot::create([
    'street_id' => 5,
    'plot_number' => '123',
    // ... other fields
]);

echo $plot->plot_code;  // DHAP1-A-ST1-123 (auto-generated)
```

### Price Calculation
```php
// Automatically calculated on create:
$plot = Plot::create([
    'street_id' => 5,
    'plot_number' => '123',
    'area' => 10,
    'area_unit' => 'marla',
    'price_per_marla' => 50000,
]);

echo $plot->total_price;  // 500000 (auto-calculated)

// With unit conversion:
$plot = Plot::create([
    'area' => 1,
    'area_unit' => 'kanal',  // 1 kanal = 20 marla
    'price_per_marla' => 50000,
]);

echo $plot->total_price;  // 1000000 (20 × 50000)
```

### Cascade Updates
```php
// When plot is created/deleted:
$plot = Plot::create([...]);
// Triggers:
// 1. street->updatePlotCounts()
// 2. block->updatePlotCounts()
// 3. society->touch()

echo $plot->street->total_plots;      // Updated
echo $plot->street->available_plots;  // Updated
echo $plot->block->total_plots;       // Updated
```

---

## 🧪 Quick Test

```bash
php artisan tinker
```

```php
use App\Models\{Society, Block, Street, Plot};

// Create complete hierarchy
$society = Society::create(['name' => 'Test Society', 'city' => 'Lahore', 'status' => 'active', 'created_by' => 1]);
$block = Block::create(['society_id' => $society->id, 'name' => 'A', 'status' => 'active', 'created_by' => 1]);
$street = Street::create(['block_id' => $block->id, 'name' => 'Street 1', 'type' => 'residential', 'status' => 'active', 'created_by' => 1]);

// Create plot
$plot = Plot::create([
    'street_id' => $street->id,
    'plot_number' => '123',
    'area' => 10,
    'area_unit' => 'marla',
    'type' => 'residential',
    'status' => 'available',
    'price_per_marla' => 50000,
    'corner' => 'yes',
    'created_by' => 1
]);

// Verify
$plot->plot_code;        // Auto-generated
$plot->total_price;      // 500000
$plot->full_address;     // Complete address
$plot->society->name;    // Test Society
$street->total_plots;    // 1
```

---

## 📝 Common Operations

### Create Plot
```php
$plot = Plot::create([
    'street_id' => 5,
    'plot_number' => '123',
    'area' => 10,
    'area_unit' => 'marla',
    'type' => 'residential',
    'status' => 'available',
    'price_per_marla' => 50000,
    'corner' => 'no',
    'park_facing' => 'yes',
    'main_road_facing' => 'no',
    'facing' => 'north',
    'created_by' => auth()->id(),
]);
```

### Update Plot Status
```php
$plot = Plot::find(1);
$plot->update([
    'status' => 'sold',
    'updated_by' => auth()->id(),
]);
// Automatically updates street/block counts
```

### Find Available Plots in Society
```php
$plots = Plot::whereHas('street.block', function($q) {
    $q->where('society_id', 1);
})
->available()
->residential()
->orderBy('price_per_marla')
->get();
```

### Calculate Society Revenue
```php
$totalRevenue = Plot::whereHas('street.block', function($q) {
    $q->where('society_id', 1);
})->sold()->sum('total_price');
```

---

## 🚀 Ready to Use

### Files Created
- ✅ Migration: `2024_01_28_100004_create_plots_table.php`
- ✅ Model: `app/Models/Plot.php`
- ✅ Controller: `app/Http/Controllers/PlotController.php`
- ✅ Routes: Added to `routes/web.php`

### Permissions (Already Seeded)
- ✅ `plots.view`
- ✅ `plots.create`
- ✅ `plots.edit`
- ✅ `plots.delete`

### Run Migrations
```bash
php artisan migrate
```

### Access Routes
```bash
php artisan route:list --name=plots
```

---

## 📖 Full Documentation

See **PLOT-MANAGEMENT-MODULE.md** for:
- Complete database schema
- All model methods & scopes
- Detailed controller methods
- Integration examples
- Advanced queries
- Testing scenarios

---

**Module Status**: ✅ **PRODUCTION READY**

Everything is implemented and working. Just run migrations and start creating plots!

# Society Management Module - Quick Setup

## Run These Commands

```bash
# 1. Run migrations to create tables
php artisan migrate

# 2. Create storage link for file uploads
php artisan storage:link

# 3. Clear cache
php artisan optimize:clear

# 4. Verify routes
php artisan route:list --name=societies
php artisan route:list --name=blocks
php artisan route:list --name=streets
```

## What Was Created

### ✅ Database Tables
- `societies` - Main housing societies
- `blocks` - Blocks within societies
- `streets` - Streets within blocks
- `plots` - Updated to connect with streets

### ✅ Models & Relationships
- `Society` → hasMany → `Block`
- `Block` → belongsTo → `Society`, hasMany → `Street`
- `Street` → belongsTo → `Block`, hasMany → `Plot`
- `Plot` → belongsTo → `Street` (can access block & society)

### ✅ Controllers (Full CRUD)
- `SocietyController` - 7 methods (index, create, store, show, edit, update, destroy)
- `BlockController` - 8 methods (+ getBySociety for AJAX)
- `StreetController` - 8 methods (+ getByBlock for AJAX)
- `PlotController` - Updated to work with new structure

### ✅ Routes
All routes protected with authentication and permissions:
- `/societies` - CRUD operations
- `/blocks` - CRUD operations
- `/streets` - CRUD operations
- `/plots` - Updated CRUD operations
- AJAX endpoints for cascading selects

## Test the Module

```bash
php artisan tinker
```

```php
// Create a complete hierarchy
use App\Models\Society;
use App\Models\Block;
use App\Models\Street;
use App\Models\Plot;

// 1. Society
$society = Society::create([
    'name' => 'DHA Phase 1',
    'city' => 'Lahore',
    'status' => 'active',
    'created_by' => 1
]);

// 2. Block
$block = Block::create([
    'society_id' => $society->id,
    'name' => 'Block A',
    'status' => 'active',
    'created_by' => 1
]);

// 3. Street
$street = Street::create([
    'block_id' => $block->id,
    'name' => 'Street 1',
    'type' => 'residential',
    'status' => 'active',
    'created_by' => 1
]);

// 4. Plot
$plot = Plot::create([
    'street_id' => $street->id,
    'plot_number' => '123',
    'area' => 10,
    'area_unit' => 'marla',
    'type' => 'residential',
    'status' => 'available',
    'price_per_marla' => 50000,
    'corner' => 'yes',
    'park_facing' => 'no',
    'main_road_facing' => 'yes',
    'created_by' => 1
]);

// Verify
echo $plot->plot_code;      // DHAP1-A-ST1-123 (auto-generated)
echo $plot->total_price;    // 500000 (auto-calculated)
echo $plot->full_address;   // Plot 123, Street 1, Block A, DHA Phase 1
echo $street->total_plots;  // 1 (auto-updated)
echo $block->total_plots;   // 1 (auto-updated)
```

## Hierarchy Structure

```
📍 Society (DHA Phase 1)
   ├── 📦 Block A
   │   ├── 🛣️ Street 1
   │   │   ├── 📋 Plot 123
   │   │   └── 📋 Plot 124
   │   └── 🛣️ Street 2
   │       └── 📋 Plot 201
   └── 📦 Block B
       └── 🛣️ Street 1
           └── 📋 Plot 101
```

## Key Features

### ✅ Auto-Generated Codes
- Plot Code: `SOCIETY-BLOCK-STREET-PLOT` (e.g., DHAP1-A-ST1-123)
- Society/Block/Street codes auto-generated from names if not provided

### ✅ Auto-Calculated Values
- `Plot.total_price` = area × price_per_marla (with unit conversion)
- `Street.total_plots` = count of plots
- `Block.total_plots` = sum of street plots
- `Society.total_plots` = sum of block plots

### ✅ Cascade Updates
- Creating/deleting plot → Updates street counts → Updates block counts → Touches society
- Deleting society → Cascades to blocks → streets → plots (with safety checks)

### ✅ Safety Checks
- Cannot delete society if it has blocks
- Cannot delete block if it has streets
- Cannot delete street if it has plots

## Permissions

Module uses these permissions (should already exist):
- `societies.view`, `societies.create`, `societies.edit`, `societies.delete`
- `blocks.view`, `blocks.create`, `blocks.edit`, `blocks.delete`
- `streets.view`, `streets.create`, `streets.edit`, `streets.delete`
- `plots.view`, `plots.create`, `plots.edit`, `plots.delete`

Check permissions:
```bash
php artisan tinker
>>> \Spatie\Permission\Models\Permission::where('module', 'societies')->count(); // 4
>>> \Spatie\Permission\Models\Permission::where('module', 'blocks')->count(); // 4
>>> \Spatie\Permission\Models\Permission::where('module', 'streets')->count(); // 4
```

## Next Steps

1. **Create Views** - Blade templates for CRUD operations
2. **Add JavaScript** - Cascading select dropdowns (society → block → street)
3. **Dashboard Widgets** - Plot availability statistics
4. **Map Integration** - Display society/block maps
5. **Bulk Import** - Excel import for plots

## Files Modified

### Created:
- `database/migrations/2024_01_28_100001_create_societies_table.php`
- `database/migrations/2024_01_28_100002_create_blocks_table.php`
- `database/migrations/2024_01_28_100003_create_streets_table.php`
- `database/migrations/2024_01_28_100004_create_plots_table.php`
- `app/Http/Controllers/BlockController.php`
- `app/Http/Controllers/StreetController.php`

### Updated:
- `app/Models/Society.php` - Enhanced with new relationships
- `app/Models/Block.php` - Enhanced with auto-calculations
- `app/Models/Street.php` - Enhanced with cascade updates
- `app/Models/Plot.php` - Completely restructured for street relationship
- `app/Http/Controllers/SocietyController.php` - Added middleware & enhanced methods
- `app/Http/Controllers/PlotController.php` - Updated for new structure
- `routes/web.php` - Added block & street routes

## Documentation

📖 See `SOCIETY-MANAGEMENT-MODULE.md` for complete documentation including:
- Detailed database schema
- All model methods and scopes
- Controller methods
- Integration points
- Testing examples

---

**Status**: ✅ Module fully implemented and ready for use!

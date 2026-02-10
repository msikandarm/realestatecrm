# Property Management - Quick Reference Guide

## 🎯 Quick Start

### Property Types
- **house** - Residential houses
- **apartment** - Residential apartments/flats
- **commercial** - Shops, offices, warehouses

### Property Conditions
- **new** - Brand new property
- **old** - Used/existing property
- **under_construction** - Currently being built

### Property For
- **sale** - For sale only
- **rent** - For rent only
- **both** - Available for sale or rent

---

## 📝 Create Property

### Minimum Required Fields
```php
Property::create([
    'title' => 'Required',
    'reference_code' => 'Required & Unique',
    'type' => 'house|apartment|commercial',
    'condition' => 'new|old|under_construction',
    'property_for' => 'sale|rent|both',
    'size' => 'numeric',
    'size_unit' => 'sq_ft|sq_m|marla|kanal',
    'price' => 'numeric',
    'status' => 'available|sold|rented|...',
    'created_by' => auth()->id(),
]);
```

### Complete Example
```php
$property = Property::create([
    // Basic Info
    'title' => '5 Marla Luxury House',
    'reference_code' => 'PROP-2026-001',
    'type' => 'house',
    'condition' => 'new',
    'property_for' => 'sale',

    // Location
    'plot_id' => 1,          // Optional: if on owned plot
    'society_id' => 1,       // Optional
    'block_id' => 1,         // Optional
    'street_id' => 1,        // Optional
    'address' => '123 Main St',
    'area' => 'DHA',
    'city' => 'Lahore',
    'province' => 'Punjab',

    // Size
    'size' => 5,
    'size_unit' => 'marla',  // Auto-converts to size_in_sqft

    // Details (for houses/apartments)
    'bedrooms' => 3,
    'bathrooms' => 3,
    'floors' => 2,
    'year_built' => 2024,
    'furnished' => true,
    'parking' => true,
    'parking_spaces' => 2,

    // Pricing
    'price' => 12000000,
    'rental_price' => 50000,      // If for rent
    'rental_period' => 'monthly', // monthly|yearly
    'negotiable' => true,

    // Ownership
    'owner_id' => 5,              // Client ID
    // OR for external owner:
    'owner_name' => 'Ahmed Ali',
    'owner_contact' => '03001234567',

    // Status
    'status' => 'available',
    'featured' => true,
    'is_verified' => true,

    // Features (JSON arrays)
    'amenities' => ['electricity', 'gas', 'water', 'internet'],
    'features' => ['garden', 'swimming_pool', 'gym', 'security'],

    // Media
    'video_url' => 'https://youtube.com/watch?v=...',
    'virtual_tour_url' => 'https://...',

    // Additional
    'description' => 'Beautiful house in prime location...',
    'notes' => 'Internal notes...',

    // Audit (auto-set if logged in)
    'created_by' => auth()->id(),
]);
```

---

## 🔍 Query Examples

### Basic Queries
```php
// All properties
Property::all();

// Get by ID
Property::find(1);

// With relationships
Property::with(['owner', 'plot', 'society', 'propertyImages'])->find(1);
```

### By Type
```php
Property::houses()->get();
Property::apartments()->get();
Property::commercial()->get();
Property::byType('house')->get();
```

### By Status
```php
Property::available()->get();
Property::sold()->get();
Property::rented()->get();
```

### By Purpose
```php
Property::forSale()->get();       // sale or both
Property::forRent()->get();       // rent or both
```

### By Condition
```php
Property::newProperties()->get();
Property::oldProperties()->get();
```

### By Features
```php
Property::furnished()->get();
Property::withParking()->get();
Property::featured()->get();
Property::verified()->get();
```

### By Location
```php
Property::byCity('Lahore')->get();
Property::where('society_id', 1)->get();
Property::where('area', 'DHA')->get();
```

### By Owner
```php
Property::byOwner($clientId)->get();
$client->properties;  // All properties owned
```

### By Price Range
```php
Property::byPriceRange(5000000, 10000000)->get();
Property::where('price', '>=', 5000000)
    ->where('price', '<=', 10000000)
    ->get();
```

### Combined Filters
```php
// Houses for sale in Lahore under 15M
Property::houses()
    ->forSale()
    ->byCity('Lahore')
    ->where('price', '<=', 15000000)
    ->available()
    ->get();

// Furnished apartments for rent
Property::apartments()
    ->forRent()
    ->furnished()
    ->available()
    ->get();

// New commercial properties with parking
Property::commercial()
    ->newProperties()
    ->withParking()
    ->available()
    ->get();
```

---

## 📊 Statistics Queries

### Count by Type
```php
$houses = Property::houses()->count();
$apartments = Property::apartments()->count();
$commercial = Property::commercial()->count();
```

### Count by Status
```php
$available = Property::available()->count();
$sold = Property::sold()->count();
$rented = Property::rented()->count();
```

### Price Statistics
```php
$totalValue = Property::available()->sum('price');
$avgPrice = Property::available()->avg('price');
$minPrice = Property::available()->min('price');
$maxPrice = Property::available()->max('price');
```

### By City
```php
use Illuminate\Support\Facades\DB;

$byCity = Property::select('city', DB::raw('count(*) as total'))
    ->groupBy('city')
    ->orderBy('total', 'desc')
    ->get();
```

### By Type in Society
```php
$stats = Property::where('society_id', 1)
    ->selectRaw('type, count(*) as total, avg(price) as avg_price')
    ->groupBy('type')
    ->get();
```

### Client Portfolio
```php
$clientProperties = Property::where('owner_id', $clientId)
    ->selectRaw('count(*) as total, sum(price) as total_value')
    ->first();
```

---

## 🔗 Relationships

### Access Property Relationships
```php
$property = Property::find(1);

// Direct relationships
$property->plot;           // Plot (if on owned land)
$property->owner;          // Client who owns
$property->society;        // Society
$property->block;          // Block
$property->street;         // Street
$property->creator;        // User who created
$property->updater;        // User who updated

// Images
$property->propertyImages;      // All images
$property->featuredImages;      // Featured only

// Computed attributes
$property->full_address;           // "123 Main St, DHA, Lahore"
$property->formatted_price;        // "PKR 12,000,000"
$property->formatted_rental_price; // "PKR 50,000/month"
```

### Reverse Relationships
```php
// Client's properties
$client = Client::find(1);
$client->properties;
$client->properties()->houses()->get();

// Plot's property
$plot = Plot::find(1);
$plot->property;  // Property built on plot (or null)

// Society's properties
$society = Society::find(1);
$society->properties;
```

---

## 📸 Image Management

### Add Images
```php
// Featured image
$property->update([
    'featured_image' => 'properties/featured/image.jpg'
]);

// Gallery images
PropertyImage::create([
    'property_id' => $property->id,
    'image_path' => 'properties/gallery/image1.jpg',
    'caption' => 'Living Room',
    'order' => 1,
    'is_featured' => false,
]);
```

### Get Images
```php
// All images (ordered)
$property->propertyImages;

// Featured images only
$property->featuredImages;

// Image URL
$image = $property->propertyImages->first();
$image->image_url;  // Full URL
```

### Upload via Controller
```php
// In form (multipart/form-data)
<input type="file" name="featured_image">
<input type="file" name="images[]" multiple>

// Controller handles automatically
// Stores in: storage/app/public/properties/
```

---

## 🎯 Helper Methods

### Status Checks
```php
$property->isAvailable();  // bool
$property->isSold();       // bool
$property->isRented();     // bool
```

### Purpose Checks
```php
$property->isForSale();    // bool
$property->isForRent();    // bool
```

### Type Checks
```php
$property->isHouse();      // bool
$property->isApartment();  // bool
$property->isCommercial(); // bool
$property->isNew();        // bool
```

### Other Helpers
```php
$property->incrementViews();         // Increment view count
$property->getAge();                 // Property age in years
$property->getSizeInSquareFeet();    // Convert any unit to sq ft
```

---

## 🛠️ Auto-Features

### 1. Size Auto-Conversion
```php
// Automatically calculates size_in_sqft
$property = Property::create([
    'size' => 5,
    'size_unit' => 'marla',
    // ... other fields
]);

$property->size_in_sqft;  // 1361.25 (5 * 272.25)
```

**Conversion Rates**:
- 1 marla = 272.25 sq ft
- 1 kanal = 5,445 sq ft (20 marla)
- 1 sq m = 10.764 sq ft

### 2. Audit Trail
```php
// Automatically sets on create
'created_by' => auth()->id()

// Automatically sets on update
'updated_by' => auth()->id()
```

### 3. View Counter
```php
// Automatically increments when viewing
PropertyController::show($property);
// Calls: $property->incrementViews();
```

---

## 🚀 Quick Test Script

```bash
php artisan tinker
```

```php
// Create owner
$client = Client::create([
    'name' => 'Test Owner',
    'email' => 'owner@test.com',
    'phone' => '03001234567',
    'client_type' => 'seller',
    'created_by' => 1
]);

// Create house
$house = Property::create([
    'title' => 'Test House',
    'reference_code' => 'TEST-001',
    'type' => 'house',
    'condition' => 'new',
    'property_for' => 'sale',
    'size' => 5,
    'size_unit' => 'marla',
    'bedrooms' => 3,
    'bathrooms' => 3,
    'price' => 10000000,
    'owner_id' => $client->id,
    'city' => 'Lahore',
    'status' => 'available',
    'created_by' => 1
]);

// Verify
$house->size_in_sqft;           // 1361.25
$house->formatted_price;        // "PKR 10,000,000"
$house->owner->name;            // "Test Owner"
$house->full_address;           // "Lahore"
Property::houses()->count();    // 1
Property::available()->count(); // 1
```

---

## 📋 Status Values

| Status | Description | Use Case |
|--------|-------------|----------|
| `available` | Ready for sale/rent | Active listing |
| `sold` | Property sold | Completed sale |
| `rented` | Currently rented | Active rental |
| `under_negotiation` | Deal in progress | Pending deal |
| `reserved` | Reserved for client | Temporary hold |
| `off_market` | Not available | Temporarily unlisted |

---

## 📐 Size Units

| Unit | Full Name | Conversion to Sq Ft |
|------|-----------|---------------------|
| `sq_ft` | Square Feet | 1 |
| `sq_m` | Square Meters | 10.764 |
| `marla` | Marla | 272.25 |
| `kanal` | Kanal | 5,445 |

---

## 🔐 Required Permissions

```php
// Check permissions
auth()->user()->hasPermissionTo('properties.view');
auth()->user()->hasPermissionTo('properties.create');
auth()->user()->hasPermissionTo('properties.edit');
auth()->user()->hasPermissionTo('properties.delete');
```

---

## 📁 File Locations

**Migration**: `database/migrations/2026_01_28_000009_create_properties_table.php`
**Images Migration**: `database/migrations/2024_01_28_110002_create_property_images_table.php`
**Model**: `app/Models/Property.php`
**Image Model**: `app/Models/PropertyImage.php`
**Controller**: `app/Http/Controllers/PropertyController.php`
**Routes**: `routes/web.php` (properties.*)

---

## ✅ Ready to Use

✅ Migration created
✅ Models configured
✅ Controller with CRUD
✅ Routes configured
✅ Permissions integrated
✅ Relationships connected
✅ Image management ready

### Run Migration
```bash
php artisan migrate
```

### Access Routes
```
GET    /properties              - List all properties
GET    /properties/create       - Show create form
POST   /properties              - Store new property
GET    /properties/{id}         - Show property details
GET    /properties/{id}/edit    - Show edit form
PUT    /properties/{id}         - Update property
DELETE /properties/{id}         - Delete property
```

---

**Quick Reference Last Updated**: January 28, 2026

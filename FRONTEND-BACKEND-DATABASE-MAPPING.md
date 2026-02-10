# 🎯 COMPLETE FRONTEND ↔ BACKEND ↔ DATABASE MAPPING

**Real Estate CRM - Integration Reference**
**Created:** January 29, 2026

---

## 📋 TABLE OF CONTENTS

1. [Mapping Legend](#mapping-legend)
2. [Authentication Module](#authentication-module)
3. [Society Management Module](#society-management-module)
4. [Plot Management Module](#plot-management-module)
5. [Property Management Module](#property-management-module)
6. [Lead Management Module](#lead-management-module)
7. [Client Management Module](#client-management-module)
8. [Dealer Management Module](#dealer-management-module)
9. [Deal Management Module](#deal-management-module)
10. [Property File Module](#property-file-module)
11. [Payment Module](#payment-module)
12. [Expense Management Module](#expense-management-module)
13. [Report Module](#report-module)
14. [Dashboard Module](#dashboard-module)
15. [Follow-Up Module](#follow-up-module)

---

## 🔍 MAPPING LEGEND

```
✅ = Fully Implemented & Working
⚠️ = Partially Implemented (Needs Enhancement)
❌ = Missing / Not Implemented
🔄 = Needs Testing/Verification
```

---

## 1️⃣ AUTHENTICATION MODULE

### LOGIN SCREEN

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Login Form** (`resources/views/auth/login.blade.php`) | `POST /login` → `AuthenticatedSessionController@store` | `users` | ✅ |
| - Email Input | Validates email format | `users.email` (unique) | ✅ |
| - Password Input | Hashes & verifies with bcrypt | `users.password` | ✅ |
| - Remember Me | Sets remember_token | `users.remember_token` | ✅ |
| Success Redirect | Redirects to `/dashboard` | Session created | ✅ |
| Error Messages | Returns validation errors | - | ✅ |

**Data Flow:**
```
User enters email/password
  ↓
POST /login
  ↓
AuthenticatedSessionController@store
  ↓
Attempt authentication: Auth::attempt($credentials, $remember)
  ↓
Query: SELECT * FROM users WHERE email = ? AND deleted_at IS NULL
  ↓
Verify password: Hash::check($password, $user->password)
  ↓
Success: Create session, set remember_token (if checked)
  ↓
Redirect to /dashboard
```

### REGISTER SCREEN

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Register Form** (`auth/register.blade.php`) | `POST /register` → `RegisteredUserController@store` | `users`, `model_has_roles` | ✅ |
| - Name, Email, Phone | Validates & stores | `users.*` | ✅ |
| - Password & Confirm | Validates match, hashes | `users.password` | ✅ |
| Default Role Assignment | Assigns 'Staff' role | `model_has_roles` | ✅ |

### FORGOT PASSWORD

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Forgot Password Form** | `POST /forgot-password` → `PasswordResetLinkController` | `password_reset_tokens` | ✅ |
| - Email Input | Sends reset link | Creates token | ✅ |
| **Reset Password Form** | `POST /reset-password` → `NewPasswordController` | Updates `users.password` | ✅ |

### LOGOUT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Logout Button** | `POST /logout` → `AuthenticatedSessionController@destroy` | Session destroyed | ✅ |

---

## 2️⃣ SOCIETY MANAGEMENT MODULE

### SOCIETIES INDEX

| Frontend Component | Backend API | Database Tables | Logic |
|-------------------|-------------|-----------------|-------|
| **Societies List** (`societies/index.blade.php`) | `GET /societies` → `SocietyController@index` | `societies` | ✅ |
| Search Bar | Query param: `?search=DHA` | FULLTEXT search on `name`, `location`, `city` | ✅ |
| Filter by Status | `?status=active` | WHERE `status` = ? | ✅ |
| Filter by City | `?city=Lahore` | WHERE `city` = ? | ✅ |
| Sort Options | `?sort_by=name&sort_dir=asc` | ORDER BY name ASC | ✅ |
| Pagination | `?page=2` | LIMIT 20 OFFSET 20 | ✅ |
| **Data Displayed:** | | | |
| - Society Name | `$society->name` | `societies.name` | ✅ |
| - Code | `$society->code` | `societies.code` (auto-generated) | ✅ |
| - City | `$society->city` | `societies.city` | ✅ |
| - Status Badge | Color based on status | `societies.status` | ✅ |
| - Total Blocks | `$society->blocks->count()` | COUNT from `blocks` | ✅ |
| - Total Plots | `$society->total_plots` | Accessor via relationships | ✅ |
| **Actions:** | | | |
| - View Button | `GET /societies/{id}` | - | ✅ |
| - Edit Button | `GET /societies/{id}/edit` | - | ✅ |
| - Delete Button | `DELETE /societies/{id}` | Soft delete | ✅ |

**Query Example:**
```php
// Backend: SocietyController@index
$societies = Society::query()
    ->when($search, fn($q) => $q->where('name', 'LIKE', "%$search%"))
    ->when($status, fn($q) => $q->where('status', $status))
    ->when($city, fn($q) => $q->where('city', $city))
    ->withCount('blocks')
    ->orderBy($sortBy, $sortDir)
    ->paginate(20);
```

### SOCIETY CREATE/EDIT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Create Form** (`societies/create.blade.php`) | `GET /societies/create` | - | ✅ |
| - Name Input | Required | `societies.name` | ✅ |
| - Code Input | Auto-filled from name (JS) | `societies.code` | ✅ |
| - City Dropdown | Required | `societies.city` | ✅ |
| - Province Dropdown | Required | `societies.province` | ✅ |
| - Status Dropdown | Default: active | `societies.status` | ✅ |
| - Amenities Checkboxes | JSON array | `societies.amenities` (JSON) | ✅ |
| - Map File Upload | File upload | `societies.map_file` | ✅ |
| **Submit** | `POST /societies` → `store()` | INSERT INTO societies | ✅ |
| **Edit Form** | `GET /societies/{id}/edit` | SELECT * FROM societies WHERE id = ? | ✅ |
| **Update** | `PUT /societies/{id}` → `update()` | UPDATE societies WHERE id = ? | ✅ |

**Validation Rules:**
```php
'name' => 'required|string|max:255',
'code' => 'required|string|unique:societies,code,' . $society->id,
'city' => 'required|string|max:100',
'status' => 'required|in:planning,under_development,developed,completed',
'amenities' => 'nullable|array',
'map_file' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
```

### SOCIETY SHOW

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Society Details** (`societies/show.blade.php`) | `GET /societies/{id}` → `show()` | `societies` | ✅ |
| - Basic Info Card | `$society->*` | All society fields | ✅ |
| - Blocks List | `$society->blocks` | JOIN blocks ON society_id | ✅ |
| - Statistics | Counts & sums | Aggregations from blocks/plots | ✅ |
| - Map Display | Display map file | `societies.map_file` | ✅ |
| - Amenities List | Parse JSON | `societies.amenities` | ✅ |

**Eager Loading:**
```php
$society = Society::with(['blocks.streets.plots'])->findOrFail($id);
```

---

## 3️⃣ PLOT MANAGEMENT MODULE

### PLOTS INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Plots List** (`plots/index.blade.php`) | `GET /plots` → `PlotController@index` | `plots` JOIN `streets`, `blocks`, `societies` | ✅ |
| **Filters:** | | | |
| - Society Dropdown | `?society_id=1` | WHERE street.block.society_id = ? | ✅ |
| - Block Dropdown | `?block_id=2` (AJAX load) | WHERE street.block_id = ? | ✅ |
| - Street Dropdown | `?street_id=5` (AJAX load) | WHERE street_id = ? | ✅ |
| - Status Filter | `?status=available` | WHERE status = ? | ✅ |
| - Type Filter | `?type=residential` | WHERE type = ? | ✅ |
| Search | `?search=123` | WHERE plot_number OR plot_code LIKE ? | ✅ |
| **Displayed Data:** | | | |
| - Plot Code | `$plot->plot_code` | `plots.plot_code` (auto) | ✅ |
| - Plot Number | `$plot->plot_number` | `plots.plot_number` | ✅ |
| - Full Address | `$plot->full_address` | Accessor via relationships | ✅ |
| - Size | `$plot->area . ' ' . $plot->area_unit` | `plots.area`, `plots.area_unit` | ✅ |
| - Price | Number format | `plots.price_per_marla`, `plots.total_price` | ✅ |
| - Status Badge | Color-coded | `plots.status` | ✅ |
| - Premium Icons | Corner, Park, Road | `plots.corner`, `plots.park_facing`, etc. | ✅ |

**Cascading Dropdown Logic (AJAX):**
```javascript
// On society change
$('#society_id').on('change', function() {
    $.get('/api/blocks/by-society?society_id=' + $(this).val(), function(blocks) {
        // Populate block dropdown
        $('#block_id').html('<option value="">Select Block</option>');
        blocks.forEach(block => {
            $('#block_id').append(`<option value="${block.id}">${block.name}</option>`);
        });
    });
});

// On block change
$('#block_id').on('change', function() {
    $.get('/api/streets/by-block?block_id=' + $(this).val(), function(streets) {
        // Populate street dropdown
    });
});
```

**Backend API:**
```php
// BlockController@getBySociety
Route::get('api/blocks/by-society', [BlockController::class, 'getBySociety']);

public function getBySociety(Request $request) {
    return Block::where('society_id', $request->society_id)
                ->where('is_active', true)
                ->select('id', 'name', 'code')
                ->get();
}

// StreetController@getByBlock
Route::get('api/streets/by-block', [StreetController::class, 'getByBlock']);
```

### PLOT CREATE/EDIT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Create Form** (`plots/create.blade.php`) | `GET /plots/create` | - | ✅ |
| - Cascading Dropdowns | Society → Block → Street (AJAX) | - | ✅ |
| - Plot Number | Required, unique per street | `plots.plot_number` | ✅ |
| - Area Input | Numeric | `plots.area` | ✅ |
| - Unit Dropdown | marla/kanal/acre/sq_ft | `plots.area_unit` | ✅ |
| - Type Radio | residential/commercial/etc. | `plots.type` | ✅ |
| - Status Radio | available/booked/sold | `plots.status` | ✅ |
| - Price per Marla | Numeric | `plots.price_per_marla` | ✅ |
| - Total Price | Auto-calculated (JS) | `plots.total_price` | ✅ |
| - Corner Checkbox | yes/no | `plots.corner` | ✅ |
| - Facing Dropdown | north/south/etc. | `plots.facing` | ✅ |
| **Submit** | `POST /plots` → `store()` | INSERT INTO plots | ✅ |

**Auto-Calculations (Backend):**
```php
// PlotController@store
$validated = $request->validate([...]);

// Auto-generate plot_code
$street = Street::with('block.society')->findOrFail($validated['street_id']);
$validated['plot_code'] = $street->block->society->code . '-' .
                          $street->block->code . '-' .
                          $street->code . '-' .
                          $validated['plot_number'];

// Auto-calculate total_price
if ($validated['price_per_marla']) {
    $validated['total_price'] = $validated['area'] * $validated['price_per_marla'];
}

// Set created_by
$validated['created_by'] = auth()->id();

$plot = Plot::create($validated);

// Update parent counts
$plot->street->updatePlotCounts(); // Cascades to block & society
```

### PLOT SHOW

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Plot Details** (`plots/show.blade.php`) | `GET /plots/{id}` → `show()` | `plots` with relationships | ✅ |
| - Breadcrumb | Society > Block > Street > Plot | Relationships | ✅ |
| - Basic Info Card | All plot details | `plots.*` | ✅ |
| - Location Hierarchy | Links to parents | Society, Block, Street | ✅ |
| - Premium Features | Visual badges | corner, park_facing, etc. | ✅ |
| - Status History | If implemented | `plot_history` table | ⚠️ (Optional) |
| - Related Deals | `$plot->deals` | `deals` polymorphic | ✅ |
| - Property File | `$plot->propertyFile` | `property_files` polymorphic | ✅ |

**Query:**
```php
$plot = Plot::with([
    'street.block.society',
    'deals.client',
    'propertyFile.installments',
    'creator',
    'updater'
])->findOrFail($id);
```

---

## 4️⃣ PROPERTY MANAGEMENT MODULE

### PROPERTIES INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Properties List** (`properties/index.blade.php`) | `GET /properties` → `PropertyController@index` | `properties` JOIN tables | ✅ |
| **Filters:** | | | |
| - Type Filter | `?type=house` | WHERE type = ? | ✅ |
| - Condition Filter | `?condition=new` | WHERE condition = ? | ✅ |
| - Property For | `?property_for=sale` | WHERE property_for IN (?, 'both') | ✅ |
| - Status Filter | `?status=available` | WHERE status = ? | ✅ |
| - Society Filter | `?society_id=1` | WHERE society_id = ? | ✅ |
| - City Filter | `?city=Lahore` | WHERE city = ? | ✅ |
| - Price Range | `?min_price=5000000&max_price=10000000` | WHERE price BETWEEN ? AND ? | ✅ |
| - Featured Only | `?featured=1` | WHERE featured = TRUE | ✅ |
| Search | `?search=DHA` | FULLTEXT(title, address, description) | ✅ |
| **Grid/List View Toggle** | JS toggle | - | ✅ |
| **Displayed Data:** | | | |
| - Featured Image | `$property->featured_image` | `properties.featured_image` | ✅ |
| - Title | `$property->title` | `properties.title` | ✅ |
| - Reference Code | `$property->reference_code` | Auto-generated | ✅ |
| - Type Badge | house/apartment/commercial | `properties.type` | ✅ |
| - Price | Formatted | `properties.price` or `properties.rental_price` | ✅ |
| - Specs | Beds, Baths, Size | Multiple columns | ✅ |
| - Status Badge | available/sold/rented | `properties.status` | ✅ |
| - View Counter | `$property->views_count` | `properties.views_count` | ✅ |

**Query:**
```php
$properties = Property::query()
    ->with(['society', 'block', 'street', 'owner', 'propertyImages'])
    ->when($type, fn($q) => $q->where('type', $type))
    ->when($propertyFor, fn($q) => $q->forSale()) // Custom scope
    ->when($status, fn($q) => $q->where('status', $status))
    ->when($search, fn($q) => $q->whereRaw("MATCH(title, address, description) AGAINST(? IN BOOLEAN MODE)", [$search]))
    ->when($minPrice, fn($q) => $q->where('price', '>=', $minPrice))
    ->when($maxPrice, fn($q) => $q->where('price', '<=', $maxPrice))
    ->paginate(20);
```

### PROPERTY CREATE/EDIT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Create Form** (`properties/create.blade.php`) | `GET /properties/create` | - | ✅ |
| - Basic Info Tab | Title, Type, Condition | `properties.title`, `type`, `condition` | ✅ |
| - Location Tab | Society, Block, Street, Address | Multiple columns | ✅ |
| - Details Tab | Size, Beds, Baths, Floors | Multiple columns | ✅ |
| - Pricing Tab | Sale Price, Rental Price | `properties.price`, `rental_price` | ✅ |
| - Features Tab | Amenities & Features (checkboxes) | `properties.amenities`, `features` (JSON) | ✅ |
| - Media Tab | Images Upload (multiple) | `property_images` table | ✅ |
| **Image Upload:** | | | |
| - Drag & Drop | Multiple file upload | - | ✅ |
| - Image Preview | Client-side preview | - | ✅ |
| - Featured Image | Radio select | `property_images.is_featured` | ✅ |
| - Image Order | Sortable (drag) | `property_images.order` | ✅ |
| **Submit** | `POST /properties` → `store()` | INSERT INTO properties + images | ✅ |

**Image Upload Logic:**
```php
// PropertyController@store
$property = Property::create($validated);

// Handle image uploads
if ($request->hasFile('images')) {
    foreach ($request->file('images') as $index => $file) {
        $path = $file->store('properties', 'public');

        PropertyImage::create([
            'property_id' => $property->id,
            'image_path' => $path,
            'order' => $index,
            'is_featured' => $index === 0, // First image is featured
        ]);
    }
}
```

### PROPERTY SHOW

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Property Details** (`properties/show.blade.php`) | `GET /properties/{id}` → `show()` | `properties` with relationships | ✅ |
| **Image Gallery** | Carousel/lightbox | `property_images` | ✅ |
| - Main Image Display | Large display | `featured_image` or first image | ✅ |
| - Thumbnail Strip | Click to change main | All images ordered | ✅ |
| **Info Panels:** | | | |
| - Basic Info | Type, condition, year, etc. | Multiple columns | ✅ |
| - Specifications | Beds, baths, size, floors | Multiple columns | ✅ |
| - Pricing | Sale/Rental prices | With formatting | ✅ |
| - Location | Full address + map | With GPS coordinates | ✅ |
| - Amenities List | Parsed from JSON | `properties.amenities` | ✅ |
| - Features List | Parsed from JSON | `properties.features` | ✅ |
| - Owner Info | If exists | `clients` relationship | ✅ |
| **Related Items:** | | | |
| - Deal History | `$property->deals` | `deals` polymorphic | ✅ |
| - Property File | `$property->propertyFile` | `property_files` polymorphic | ✅ |
| **Actions:** | | | |
| - Edit Button | Auth check | - | ✅ |
| - Create Deal | Link to deal creation | - | ✅ |
| - View Counter | Auto-increment on page load | `properties.views_count++` | ✅ |

---

## 5️⃣ LEAD MANAGEMENT MODULE

### LEADS INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Leads List** (`leads/index.blade.php`) | `GET /leads` → `LeadController@index` | `leads` JOIN `users`, `societies` | ✅ |
| **Statistics Cards** | Dashboard metrics | Aggregations | ✅ |
| - Total Leads | COUNT(*) | WHERE status IN active statuses | ✅ |
| - New Leads | COUNT WHERE status = 'new' | - | ✅ |
| - Converted | COUNT WHERE status = 'converted' | - | ✅ |
| - Conversion Rate | (converted / total) * 100 | - | ✅ |
| **Filters:** | | | |
| - Status Tabs | new/contacted/qualified/etc. | WHERE status = ? | ✅ |
| - Priority Filter | `?priority=urgent` | WHERE priority = ? | ✅ |
| - Source Filter | `?source=website` | WHERE source = ? | ✅ |
| - Interest Filter | `?interest_type=plot` | WHERE interest_type = ? | ✅ |
| - Assigned To | `?assigned_to=5` or `unassigned` | WHERE assigned_to = ? OR IS NULL | ✅ |
| - Date Range | `?date_from=&date_to=` | WHERE created_at BETWEEN ? AND ? | ✅ |
| Search | `?search=john` | WHERE name OR phone OR email LIKE ? | ✅ |
| **Data Access Control:** | | | |
| - Dealers | Only see own leads | WHERE assigned_to = auth()->id() | ✅ |
| - Managers/Admins | See all leads | No filter | ✅ |
| **Displayed Data:** | | | |
| - Lead Name | `$lead->name` | `leads.name` | ✅ |
| - Contact | `$lead->phone`, `$lead->email` | `leads.phone`, `email` | ✅ |
| - Status Badge | Color-coded | `leads.status` | ✅ |
| - Priority Badge | Color-coded | `leads.priority` | ✅ |
| - Source Icon | Different icon per source | `leads.source` | ✅ |
| - Interest | plot/house/apartment | `leads.interest_type` | ✅ |
| - Assigned To | Dealer name | `users.name` via relationship | ✅ |
| - Days Since Created | Calculated | DATEDIFF(NOW(), created_at) | ✅ |
| - Next Follow-Up | If scheduled | `follow_ups` relationship | ✅ |
| **Actions:** | | | |
| - View Details | `GET /leads/{id}` | - | ✅ |
| - Add Follow-Up | Modal or page | - | ✅ |
| - Convert to Client | Button (if qualified) | - | ✅ |
| - Mark as Lost | Button with reason | - | ✅ |
| - Edit | `GET /leads/{id}/edit` | - | ✅ |

**Permission-Based Query:**
```php
// LeadController@index
$query = Lead::with(['assignedTo', 'society', 'convertedToClient']);

// Dealers see only own leads
if (auth()->user()->hasRole('dealer')) {
    $query->where('assigned_to', auth()->id());
}

// Apply filters
$leads = $query->when($status, fn($q) => $q->where('status', $status))
               ->when($priority, fn($q) => $q->where('priority', $priority))
               ->latest()
               ->paginate(20);
```

### LEAD CREATE/EDIT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Create Form** (`leads/create.blade.php`) | `GET /leads/create` | - | ✅ |
| **Contact Info:** | | | |
| - Name | Required | `leads.name` | ✅ |
| - Email | Optional, validated | `leads.email` | ✅ |
| - Phone | Required | `leads.phone` | ✅ |
| - Secondary Phone | Optional | `leads.phone_secondary` | ✅ |
| **Source Tracking:** | | | |
| - Source Dropdown | Required | `leads.source` | ✅ |
| - Referred By | If source = referral | `leads.referred_by` | ✅ |
| **Interest Details:** | | | |
| - Interest Type | Radio: plot/house/apartment | `leads.interest_type` | ✅ |
| - Society | Dropdown (optional) | `leads.society_id` | ✅ |
| - Property | Dropdown (optional) | `leads.property_id` | ✅ |
| - Plot | Dropdown (optional) | `leads.plot_id` | ✅ |
| - Budget Range | Text | `leads.budget_range` | ✅ |
| - Preferred Location | Text | `leads.preferred_location` | ✅ |
| **Classification:** | | | |
| - Status | Dropdown, default: new | `leads.status` | ✅ |
| - Priority | Dropdown, default: medium | `leads.priority` | ✅ |
| - Assigned To | Dealer dropdown | `leads.assigned_to` | ✅ |
| - Remarks | Textarea | `leads.remarks` | ✅ |
| **Submit** | `POST /leads` → `store()` | INSERT INTO leads | ✅ |

**Auto-Assignment Logic:**
```php
// LeadController@store
$validated['created_by'] = auth()->id();

// Auto-assign to creator if dealer
if (!$request->has('assigned_to') && auth()->user()->hasRole('dealer')) {
    $validated['assigned_to'] = auth()->id();
}

// Or round-robin assignment
if (!$request->has('assigned_to')) {
    $validated['assigned_to'] = $this->getNextAvailableDealer();
}

Lead::create($validated);
```

### LEAD SHOW

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Lead Details** (`leads/show.blade.php`) | `GET /leads/{id}` → `show()` | `leads` with relationships | ✅ |
| **Info Cards:** | | | |
| - Contact Info | Name, phone, email | `leads.*` | ✅ |
| - Source & Assignment | Source, assigned dealer | With relationships | ✅ |
| - Interest Details | Type, society, budget | Multiple columns | ✅ |
| - Status Timeline | Visual timeline | Status changes (optional table) | ⚠️ |
| **Follow-Ups Section:** | | | |
| - List All Follow-Ups | `$lead->followUps` | `follow_ups` polymorphic | ✅ |
| - Add New Follow-Up | Form/Modal | - | ✅ |
| - Mark Completed | Update follow-up status | - | ✅ |
| **Actions:** | | | |
| - Update Status | Dropdown with save | UPDATE `leads.status` | ✅ |
| - Update Priority | Dropdown with save | UPDATE `leads.priority` | ✅ |
| - Reassign | Dealer dropdown | UPDATE `leads.assigned_to` | ✅ |
| - **Convert to Client** | Button → Form | See below | ✅ |
| - Mark as Lost | Button with reason modal | UPDATE status='lost', append reason | ✅ |

### LEAD CONVERSION

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Convert Button** | Opens conversion modal | - | ✅ |
| **Conversion Form:** | | | |
| - Pre-filled Data | From lead | - | ✅ |
| - Additional Fields | CNIC, full address | `clients.cnic`, `address` | ✅ |
| - Client Type | buyer/seller/investor | `clients.client_type` | ✅ |
| **Submit** | `POST /leads/{id}/convert` → `convert()` | Multiple tables | ✅ |

**Conversion Logic:**
```php
// LeadController@convert
DB::transaction(function() use ($lead, $request) {
    // 1. Create client
    $client = Client::create([
        'name' => $lead->name,
        'email' => $lead->email,
        'phone' => $lead->phone,
        'cnic' => $request->cnic,
        'address' => $request->address,
        'client_type' => $request->client_type,
        'assigned_to' => $lead->assigned_to,
        'converted_from_lead_id' => $lead->id,
        'converted_at' => now(),
        'lead_source' => $lead->source,
        'created_by' => auth()->id(),
    ]);

    // 2. Update lead
    $lead->update([
        'status' => 'converted',
        'converted_to_client_id' => $client->id,
        'converted_at' => now(),
    ]);

    // 3. Transfer follow-ups (optional)
    $lead->followUps()->update([
        'followupable_type' => Client::class,
        'followupable_id' => $client->id,
    ]);
});

return redirect()->route('clients.show', $client);
```

---

## 6️⃣ CLIENT MANAGEMENT MODULE

### CLIENTS INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Clients List** (`clients/index.blade.php`) | `GET /clients` → `ClientController@index` | `clients` JOIN `users`, `leads` | ✅ |
| **Statistics Cards** | Dashboard metrics | Aggregations | ✅ |
| - Total Clients | COUNT(*) | WHERE is_active = TRUE | ✅ |
| - Active Deals | COUNT from deals | WHERE status IN active | ✅ |
| - Total Revenue | SUM from deals | WHERE status = 'completed' | ✅ |
| **Filters:** | | | |
| - Client Type | `?client_type=buyer` | WHERE client_type = ? | ✅ |
| - Assigned To | `?assigned_to=5` | WHERE assigned_to = ? | ✅ |
| - Lead Source | `?lead_source=website` | WHERE lead_source = ? | ✅ |
| - Converted From Lead | `?converted=1` | WHERE converted_from_lead_id IS NOT NULL | ✅ |
| Search | `?search=john` | WHERE name OR cnic OR phone LIKE ? | ✅ |
| **Data Access Control:** | | | |
| - Dealers | Only see own clients | WHERE assigned_to = auth()->id() | ✅ |
| - Others | See all clients | No filter | ✅ |
| **Displayed Data:** | | | |
| - Client Name | `$client->name` | `clients.name` | ✅ |
| - CNIC | `$client->cnic` | `clients.cnic` | ✅ |
| - Contact | Phone, Email | Multiple columns | ✅ |
| - Type Badge | buyer/seller/etc. | `clients.client_type` | ✅ |
| - Assigned Dealer | `$client->dealer->name` | `users.name` via relationship | ✅ |
| - Total Deals | `$client->deals->count()` | COUNT from deals | ✅ |
| - Active File | `$client->propertyFiles->active()->count()` | From property_files | ✅ |
| - Lead Source Icon | If converted | `clients.lead_source` | ✅ |
| - Registration Date | Formatted | `clients.created_at` | ✅ |

### CLIENT SHOW

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Client Profile** (`clients/show.blade.php`) | `GET /clients/{id}` → `show()` | `clients` with relationships | ✅ |
| **Info Cards:** | | | |
| - Personal Info | Name, CNIC, contact | `clients.*` | ✅ |
| - Address | Full address | `clients.address` | ✅ |
| - Client Type | Badge | `clients.client_type` | ✅ |
| - Registration Info | Date, source, dealer | Multiple columns | ✅ |
| - Original Lead | Link to lead (if converted) | `clients.originalLead` relationship | ✅ |
| **Deals Section:** | | | |
| - List All Deals | `$client->deals` | `deals` with plot/property | ✅ |
| - Deal Status | Status badges | `deals.status` | ✅ |
| - Deal Amount | Formatted | `deals.deal_amount` | ✅ |
| - Create New Deal | Button → form | - | ✅ |
| **Property Files:** | | | |
| - List All Files | `$client->propertyFiles` | `property_files` | ✅ |
| - File Status | active/completed | `property_files.status` | ✅ |
| - Payment Progress | Visual progress bar | paid_installments / total_installments | ✅ |
| - View Statement | Link | - | ✅ |
| **Follow-Ups:** | | | |
| - List All Follow-Ups | `$client->followUps` | `follow_ups` polymorphic | ✅ |
| - Add Follow-Up | Form | - | ✅ |
| **Payments History:** | | | |
| - All Payments | `$client->payments` | From property_files.payments | ✅ |

---

## 7️⃣ DEALER MANAGEMENT MODULE

### DEALERS INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Dealers List** (`dealers/index.blade.php`) | `GET /dealers` → `DealerController@index` | `dealers` JOIN `users` | ✅ |
| **Statistics Cards** | Dashboard metrics | Aggregations | ✅ |
| - Total Dealers | COUNT(*) | WHERE status = 'active' | ✅ |
| - Total Deals | SUM from deals | - | ✅ |
| - Total Commission | SUM commission | - | ✅ |
| - Pending Commission | SUM where status = 'pending' | - | ✅ |
| **Filters:** | | | |
| - Status Filter | `?status=active` | WHERE status = ? | ✅ |
| - Specialization | `?specialization=plots` | WHERE specialization = ? | ✅ |
| Search | `?search=john` | WHERE user.name LIKE ? | ✅ |
| **Displayed Data:** | | | |
| - Dealer Name | `$dealer->user->name` | `users.name` | ✅ |
| - CNIC | `$dealer->cnic` | `dealers.cnic` | ✅ |
| - License Number | `$dealer->license_number` | `dealers.license_number` | ✅ |
| - Commission Rate | `$dealer->default_commission_rate . '%'` | `dealers.default_commission_rate` | ✅ |
| - Specialization Badge | plots/residential/etc. | `dealers.specialization` | ✅ |
| - Status Badge | active/inactive/suspended | `dealers.status` | ✅ |
| - Total Deals | `$dealer->total_deals` | Auto-calculated | ✅ |
| - Total Commission | Formatted | `dealers.total_commission` | ✅ |
| **Actions:** | | | |
| - View Profile | `GET /dealers/{id}` | - | ✅ |
| - View Performance | `GET /dealers/{id}/performance` | - | ✅ |
| - Edit | `GET /dealers/{id}/edit` | - | ✅ |
| - Activate/Deactivate | POST action | UPDATE status | ✅ |

### DEALER PERFORMANCE

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Performance Dashboard** (`dealers/performance.blade.php`) | `GET /dealers/{id}/performance` | Multiple aggregations | ✅ |
| **KPI Cards:** | | | |
| - Total Deals | COUNT from deals | WHERE dealer_id = ? | ✅ |
| - Completed Deals | COUNT WHERE status = 'completed' | - | ✅ |
| - Total Commission | SUM commission_amount | - | ✅ |
| - Success Rate | (completed / total) * 100 | - | ✅ |
| **Charts (Chart.js):** | | | |
| - Monthly Deals | Line chart | GROUP BY MONTH(deal_date) | ✅ |
| - Deal Status | Doughnut chart | GROUP BY status | ✅ |
| - Commission Trend | Bar chart | Monthly commission | ✅ |
| **Recent Deals Table** | Last 10 deals | ORDER BY deal_date DESC LIMIT 10 | ✅ |
| **Performance Rating** | Badge (Platinum/Gold/etc.) | Calculated from total_commission | ✅ |

---

## 8️⃣ DEAL MANAGEMENT MODULE

### DEALS INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Deals List** (`deals/index.blade.php`) | `GET /deals` → `DealController@index` | `deals` JOIN `clients`, `users`, polymorphic | ✅ |
| **Statistics Cards** | Dashboard metrics | Aggregations | ✅ |
| - Total Deals | COUNT(*) | - | ✅ |
| - Pending | COUNT WHERE status = 'pending' | - | ✅ |
| - Confirmed | COUNT WHERE status = 'confirmed' | - | ✅ |
| - Total Value | SUM(deal_amount) | WHERE status != 'cancelled' | ✅ |
| **Filters:** | | | |
| - Status Tabs | pending/confirmed/completed/cancelled | WHERE status = ? | ✅ |
| - Deal Type | `?deal_type=purchase` | WHERE deal_type = ? | ✅ |
| - Dealer Filter | `?dealer_id=5` | WHERE dealer_id = ? | ✅ |
| - Client Filter | `?client_id=10` | WHERE client_id = ? | ✅ |
| - Dealable Type | `?dealable_type=Plot` | WHERE dealable_type = ? | ✅ |
| - Date Range | `?date_from=&date_to=` | WHERE deal_date BETWEEN ? AND ? | ✅ |
| Search | `?search=DEAL-2026` | WHERE deal_number LIKE ? | ✅ |
| **Data Access Control:** | | | |
| - Dealers | Only see own deals | WHERE dealer_id = auth()->id() | ✅ |
| - Others | See all deals | No filter | ✅ |
| **Displayed Data:** | | | |
| - Deal Number | `$deal->deal_number` | Auto-generated | ✅ |
| - Client Name | `$deal->client->name` | `clients.name` via relationship | ✅ |
| - Dealer Name | `$deal->dealer->name` | `users.name` via relationship | ✅ |
| - Property/Plot | `$deal->dealable->title` or `plot_code` | Polymorphic relationship | ✅ |
| - Deal Type Badge | purchase/sale/booking | `deals.deal_type` | ✅ |
| - Deal Amount | Formatted | `deals.deal_amount` | ✅ |
| - Commission | Formatted | `deals.commission_amount` | ✅ |
| - Payment Type | cash/installment | `deals.payment_type` | ✅ |
| - Status Badge | Color-coded | `deals.status` | ✅ |
| - Deal Date | Formatted | `deals.deal_date` | ✅ |
| **Actions:** | | | |
| - View Details | `GET /deals/{id}` | - | ✅ |
| - Confirm (if pending) | POST action | - | ✅ |
| - Complete (if confirmed) | POST action | - | ✅ |
| - Cancel | POST action with reason | - | ✅ |
| - Edit (if pending) | `GET /deals/{id}/edit` | - | ✅ |

### DEAL CREATE

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Create Form** (`deals/create.blade.php`) | `GET /deals/create` | - | ✅ |
| **Client Selection:** | | | |
| - Client Dropdown | Search/select | `deals.client_id` | ✅ |
| - Create New Client | Link to client creation | - | ✅ |
| **Dealer Selection:** | | | |
| - Dealer Dropdown | Active dealers | `deals.dealer_id` | ✅ |
| - Auto-fill Current User | If dealer role | - | ✅ |
| **Property/Plot Selection:** | | | |
| - Type Radio | Plot or Property | `deals.dealable_type` | ✅ |
| - Plot Dropdown | If type = Plot, show available plots | `deals.dealable_id` | ✅ |
| - Property Dropdown | If type = Property, show available properties | `deals.dealable_id` | ✅ |
| **Deal Details:** | | | |
| - Deal Type | purchase/sale/booking | `deals.deal_type` | ✅ |
| - Deal Amount | Numeric | `deals.deal_amount` | ✅ |
| - Deal Date | Date picker | `deals.deal_date` | ✅ |
| **Payment Details:** | | | |
| - Payment Type | Radio: cash/installment | `deals.payment_type` | ✅ |
| - Down Payment | If installment | `deals.down_payment` | ✅ |
| - Installment Months | If installment | `deals.installment_months` | ✅ |
| - Monthly Installment | Auto-calculated (JS) | `deals.monthly_installment` | ✅ |
| **Commission:** | | | |
| - Commission % | From dealer default, editable | `deals.commission_percentage` | ✅ |
| - Commission Amount | Auto-calculated (JS) | `deals.commission_amount` | ✅ |
| **Additional:** | | | |
| - Terms & Conditions | Textarea | `deals.terms_conditions` | ✅ |
| - Remarks | Textarea | `deals.remarks` | ✅ |
| - Documents Upload | Multiple files | `deals.documents` (JSON) | ✅ |
| **Submit** | `POST /deals` → `store()` | INSERT INTO deals | ✅ |

**Auto-Calculations (JS):**
```javascript
// Calculate monthly installment
$('#down_payment, #installment_months').on('change', function() {
    let dealAmount = parseFloat($('#deal_amount').val()) || 0;
    let downPayment = parseFloat($('#down_payment').val()) || 0;
    let months = parseInt($('#installment_months').val()) || 1;

    let remaining = dealAmount - downPayment;
    let monthly = remaining / months;

    $('#monthly_installment').val(monthly.toFixed(2));
});

// Calculate commission
$('#deal_amount, #commission_percentage').on('change', function() {
    let amount = parseFloat($('#deal_amount').val()) || 0;
    let percentage = parseFloat($('#commission_percentage').val()) || 0;

    let commission = (amount * percentage) / 100;

    $('#commission_amount').val(commission.toFixed(2));
});
```

**Backend Logic:**
```php
// DealController@store
DB::transaction(function() use ($validated) {
    // 1. Auto-generate deal_number
    $validated['deal_number'] = $this->generateDealNumber();

    // 2. Set created_by
    $validated['created_by'] = auth()->id();

    // 3. Auto-calculate commission if not provided
    if (!isset($validated['commission_amount'])) {
        $validated['commission_amount'] = ($validated['deal_amount'] * $validated['commission_percentage']) / 100;
    }

    // 4. Create deal
    $deal = Deal::create($validated);

    // 5. Update plot/property status
    $dealable = $deal->dealable; // Plot or Property
    $dealable->status = 'booked';
    $dealable->save();

    // 6. Update dealer stats
    $dealer = Dealer::where('user_id', $deal->dealer_id)->first();
    $dealer->increment('total_deals');

    return $deal;
});
```

### DEAL SHOW

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Deal Details** (`deals/show.blade.php`) | `GET /deals/{id}` → `show()` | `deals` with all relationships | ✅ |
| **Info Cards:** | | | |
| - Deal Header | Deal number, status badge | - | ✅ |
| - Client Info | Name, CNIC, contact | `clients.*` | ✅ |
| - Dealer Info | Name, commission rate | `users.*`, `dealers.*` | ✅ |
| - Property/Plot Info | Full details | Polymorphic `dealable` | ✅ |
| - Financial Summary | Amount, commission, payment plan | Multiple columns | ✅ |
| **Status Timeline** | Visual timeline | Status changes | ✅ |
| **Payment Schedule** | If installment type | Calculated breakdown | ✅ |
| **Documents** | Download links | `deals.documents` (JSON) | ✅ |
| **Commission Tracking** | If completed | `deal_commissions` table | ✅ |
| **Property File** | Link if exists | `property_files` relationship | ✅ |
| **Actions:** | | | |
| - Confirm Deal | Button (if pending) | POST `/deals/{id}/confirm` | ✅ |
| - Complete Deal | Button (if confirmed) | POST `/deals/{id}/complete` | ✅ |
| - Cancel Deal | Button with reason | POST `/deals/{id}/cancel` | ✅ |
| - Edit (if pending) | Link | GET `/deals/{id}/edit` | ✅ |
| - Create Property File | Button (if confirmed + installment) | Link to file creation | ✅ |
| - Print | Print stylesheet | - | ✅ |

**Status Actions:**
```php
// DealController@confirm
public function confirm(Deal $deal) {
    if ($deal->status !== 'pending') {
        return back()->withErrors(['msg' => 'Only pending deals can be confirmed.']);
    }

    DB::transaction(function() use ($deal) {
        $deal->status = 'confirmed';
        $deal->save();

        // Update inventory status
        $dealable = $deal->dealable;
        $dealable->status = 'booked';
        $dealable->save();
    });

    return back()->with('success', 'Deal confirmed successfully!');
}

// DealController@complete
public function complete(Deal $deal) {
    if ($deal->status !== 'confirmed') {
        return back()->withErrors(['msg' => 'Only confirmed deals can be completed.']);
    }

    DB::transaction(function() use ($deal) {
        $deal->status = 'completed';
        $deal->completion_date = now();
        $deal->save();

        // Update inventory status
        $dealable = $deal->dealable;
        $dealable->status = ($deal->deal_type === 'purchase') ? 'sold' : 'available';
        $dealable->save();

        // Create commission record
        DealCommission::create([
            'deal_id' => $deal->id,
            'dealer_id' => $deal->dealer_id,
            'commission_type' => 'primary',
            'commission_percentage' => $deal->commission_percentage,
            'commission_amount' => $deal->commission_amount,
            'payment_status' => 'pending',
        ]);

        // Update dealer total commission
        $dealer = Dealer::where('user_id', $deal->dealer_id)->first();
        $dealer->increment('total_commission', $deal->commission_amount);
    });

    return back()->with('success', 'Deal completed! Commission recorded.');
}
```

---

## 9️⃣ PROPERTY FILE MODULE

### FILES INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Files List** (`property-files/index.blade.php`) | `GET /property-files` → `PropertyFileController@index` | `property_files` JOIN tables | ✅ |
| **Statistics Cards** | Dashboard metrics | Aggregations | ✅ |
| - Total Files | COUNT(*) | WHERE status = 'active' | ✅ |
| - Active Files | COUNT WHERE status = 'active' | - | ✅ |
| - Completed Files | COUNT WHERE status = 'completed' | - | ✅ |
| - Total Outstanding | SUM(remaining_balance) | WHERE status = 'active' | ✅ |
| **Filters:** | | | |
| - Status Filter | `?status=active` | WHERE status = ? | ✅ |
| - Client Filter | `?client_id=10` | WHERE client_id = ? | ✅ |
| - Fileable Type | `?fileable_type=Plot` | WHERE fileable_type = ? | ✅ |
| - Dealer Filter | `?dealer_id=5` | WHERE dealer_id = ? | ✅ |
| Search | `?search=FILE-2026` | WHERE file_number LIKE ? | ✅ |
| **Displayed Data:** | | | |
| - File Number | `$file->file_number` | Auto-generated | ✅ |
| - Client Name | `$file->client->name` | `clients.name` | ✅ |
| - Property/Plot | `$file->fileable->title` or `plot_code` | Polymorphic | ✅ |
| - Total Price | Formatted | `property_files.total_price` | ✅ |
| - Remaining Balance | Formatted | `property_files.remaining_balance` | ✅ |
| - Payment Progress | Progress bar | (paid_installments / total_installments) * 100 | ✅ |
| - Status Badge | active/completed/defaulted | `property_files.status` | ✅ |
| - Next Installment | Next pending installment date | MIN(due_date) WHERE status = 'pending' | ✅ |
| **Actions:** | | | |
| - View Details | GET `/property-files/{id}` | - | ✅ |
| - Record Payment | Link | - | ✅ |
| - Statement | PDF download | - | ✅ |
| - Transfer | Link (if authorized) | - | ✅ |

### FILE CREATE

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Create Form** (`property-files/create.blade.php`) | `GET /property-files/create` | - | ✅ |
| **Pre-fill from Deal:** | | | |
| - Deal Selection | Dropdown (confirmed deals) | - | ✅ |
| - Auto-fill Client | From deal | `property_files.client_id` | ✅ |
| - Auto-fill Dealable | Plot or Property from deal | `fileable_type`, `fileable_id` | ✅ |
| - Auto-fill Total Price | From deal | `property_files.total_price` | ✅ |
| - Auto-fill Down Payment | From deal | `property_files.down_payment` | ✅ |
| **File Configuration:** | | | |
| - Installment Frequency | monthly/quarterly/semi-annually/annually | `property_files.installment_frequency` | ✅ |
| - Total Installments | Calculated or manual | `property_files.total_installments` | ✅ |
| - Start Date | Date picker | `property_files.start_date` | ✅ |
| - End Date | Auto-calculated (JS) | `property_files.end_date` | ✅ |
| **Late Payment Config:** | | | |
| - Late Fee % | Default 2%, editable | `property_files.late_fee_percentage` | ✅ |
| - Grace Period (days) | Default 7, editable | `property_files.grace_period_days` | ✅ |
| **Preview:** | | | |
| - Installment Schedule | Table preview | Generated before saving | ✅ |
| - Installment Amount | Calculated | (total - down_payment) / installments | ✅ |
| - Due Dates | Listed | Based on frequency | ✅ |
| **Submit** | `POST /property-files` → `store()` | INSERT INTO property_files + installments | ✅ |

**Auto-Generation Logic:**
```php
// PropertyFileController@store
DB::transaction(function() use ($validated) {
    // 1. Auto-generate file_number
    $validated['file_number'] = $this->generateFileNumber();

    // 2. Calculate remaining balance
    $validated['remaining_balance'] = $validated['total_price'] - $validated['down_payment'];

    // 3. Calculate installment amount
    $validated['installment_amount'] = $validated['remaining_balance'] / $validated['total_installments'];

    // 4. Calculate end_date based on frequency
    $startDate = Carbon::parse($validated['start_date']);
    $endDate = $this->calculateEndDate($startDate, $validated['installment_frequency'], $validated['total_installments']);
    $validated['end_date'] = $endDate;

    // 5. Set dealer_id and paid_installments
    $validated['dealer_id'] = auth()->id();
    $validated['paid_installments'] = 0;

    // 6. Create property file
    $file = PropertyFile::create($validated);

    // 7. Auto-generate installments
    $this->generateInstallments($file);

    return $file;
});

protected function generateInstallments(PropertyFile $file) {
    $startDate = Carbon::parse($file->start_date);
    $amount = $file->installment_amount;

    for ($i = 1; $i <= $file->total_installments; $i++) {
        // Calculate due date based on frequency
        $dueDate = match($file->installment_frequency) {
            'monthly' => $startDate->copy()->addMonths($i),
            'quarterly' => $startDate->copy()->addMonths($i * 3),
            'semi-annually' => $startDate->copy()->addMonths($i * 6),
            'annually' => $startDate->copy()->addYears($i),
        };

        Installment::create([
            'property_file_id' => $file->id,
            'installment_number' => $i,
            'due_date' => $dueDate,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }
}
```

### FILE SHOW

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **File Details** (`property-files/show.blade.php`) | `GET /property-files/{id}` → `show()` | `property_files` with relationships | ✅ |
| **File Header:** | | | |
| - File Number | Large display | `property_files.file_number` | ✅ |
| - Status Badge | Color-coded | `property_files.status` | ✅ |
| **Client & Property Info:** | | | |
| - Client Details | Name, CNIC, contact | `clients.*` | ✅ |
| - Property/Plot Details | Full details | Polymorphic `fileable` | ✅ |
| **Financial Summary:** | | | |
| - Total Price | Formatted | `property_files.total_price` | ✅ |
| - Down Payment | Formatted | `property_files.down_payment` | ✅ |
| - Remaining Balance | Formatted | `property_files.remaining_balance` | ✅ |
| - Total Paid | SUM of payments | SUM(file_payments.amount) | ✅ |
| - Remaining | Calculated | remaining_balance - total_paid | ✅ |
| **Payment Progress:** | | | |
| - Progress Bar | Visual | (paid_installments / total_installments) * 100 | ✅ |
| - Paid/Total Installments | Text | "12 / 24" | ✅ |
| - Completion Percentage | "50%" | - | ✅ |
| **Installment Schedule Table:** | | | |
| - All Installments | `$file->installments` | ORDER BY installment_number | ✅ |
| - Installment # | Column | `installments.installment_number` | ✅ |
| - Due Date | Column | `installments.due_date` | ✅ |
| - Amount | Column | `installments.amount` | ✅ |
| - Status Badge | pending/paid/overdue | `installments.status` | ✅ |
| - Paid Amount | Column (if paid) | `installments.paid_amount` | ✅ |
| - Paid Date | Column (if paid) | `installments.paid_date` | ✅ |
| - Late Fee | Column (if overdue) | `installments.late_fee` | ✅ |
| - Days Overdue | Badge (if overdue) | `installments.days_overdue` | ✅ |
| - Action Button | Record Payment | Link to payment form | ✅ |
| **Payment History:** | | | |
| - All Payments | `$file->filePayments` | ORDER BY payment_date DESC | ✅ |
| - Receipt Number | Link to download | `file_payments.receipt_number` | ✅ |
| - Amount | Formatted | `file_payments.amount` | ✅ |
| - Method | cash/cheque/etc. | `file_payments.payment_method` | ✅ |
| - Date | Formatted | `file_payments.payment_date` | ✅ |
| - Received By | User name | `users.name` | ✅ |
| **Actions:** | | | |
| - Record Payment | Button → modal/page | Link | ✅ |
| - Download Statement | PDF button | GET `/property-files/{id}/statement` | ✅ |
| - Transfer File | Button (if authorized) | Link | ✅ |
| - Edit (if active) | Link | - | ✅ |

---

## 🔟 PAYMENT MODULE

### PAYMENTS INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Payments List** (`payments/index.blade.php`) | `GET /payments` → `PaymentController@index` | `file_payments` JOIN tables | ✅ |
| **Statistics Cards** | Dashboard metrics | Aggregations | ✅ |
| - Total Payments | COUNT(*) | - | ✅ |
| - Today's Collection | SUM WHERE DATE(payment_date) = TODAY | - | ✅ |
| - This Month | SUM WHERE MONTH(payment_date) = CURRENT_MONTH | - | ✅ |
| - Total Collection | SUM(amount) | - | ✅ |
| **Filters:** | | | |
| - Date Range | `?date_from=&date_to=` | WHERE payment_date BETWEEN ? AND ? | ✅ |
| - Payment Method | `?payment_method=cash` | WHERE payment_method = ? | ✅ |
| - File Filter | `?property_file_id=5` | WHERE property_file_id = ? | ✅ |
| - Client Filter | `?client_id=10` | WHERE client_id = ? | ✅ |
| - Received By | `?received_by=5` | WHERE received_by = ? | ✅ |
| Search | `?search=RCT-2026` | WHERE receipt_number LIKE ? | ✅ |
| **Displayed Data:** | | | |
| - Receipt Number | `$payment->receipt_number` | Auto-generated | ✅ |
| - Client Name | `$payment->client->name` | `clients.name` | ✅ |
| - File Number | `$payment->propertyFile->file_number` | `property_files.file_number` | ✅ |
| - Amount | Formatted | `file_payments.amount` | ✅ |
| - Method Badge | cash/cheque/bank | `file_payments.payment_method` | ✅ |
| - Date | Formatted | `file_payments.payment_date` | ✅ |
| - Received By | User name | `users.name` | ✅ |
| **Actions:** | | | |
| - View Details | GET `/payments/{id}` | - | ✅ |
| - Download Receipt | PDF download | - | ✅ |
| - Delete (if authorized) | POST action | - | ✅ |

### PAYMENT CREATE

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Payment Form** (`payments/create.blade.php`) | `GET /payments/create` | - | ✅ |
| **File Selection:** | | | |
| - Property File Dropdown | Search/select (active files only) | `file_payments.property_file_id` | ✅ |
| - Client Auto-Display | From selected file | Read-only | ✅ |
| **Installment Selection:** | | | |
| - Installment Dropdown | Pending installments for file | `file_payments.file_installment_id` | ✅ |
| - Due Date Display | From installment | Read-only | ✅ |
| - Amount Due Display | From installment | Read-only | ✅ |
| - Late Fee Display | If overdue | Read-only | ✅ |
| **Payment Details:** | | | |
| - Amount | Numeric, min = amount due | `file_payments.amount` | ✅ |
| - Payment Method | Dropdown | `file_payments.payment_method` | ✅ |
| - Payment Date | Date picker, default today | `file_payments.payment_date` | ✅ |
| **Method-Specific Fields:** | | | |
| - Bank Reference | If bank_transfer | `file_payments.bank_reference` | ✅ |
| - Cheque Number | If cheque | `file_payments.cheque_number` | ✅ |
| - Transaction ID | If online | `file_payments.transaction_id` | ✅ |
| **Additional:** | | | |
| - Notes | Textarea | `file_payments.notes` | ✅ |
| **Submit** | `POST /payments` → `store()` | INSERT INTO file_payments + update installment | ✅ |

**Backend Logic:**
```php
// PaymentController@store
DB::transaction(function() use ($validated) {
    // 1. Auto-generate receipt_number
    $validated['receipt_number'] = $this->generateReceiptNumber();

    // 2. Set received_by
    $validated['received_by'] = auth()->id();

    // 3. Create payment
    $payment = FilePayment::create($validated);

    // 4. Update installment
    $installment = Installment::findOrFail($validated['file_installment_id']);
    $installment->paid_amount += $validated['amount'];

    if ($installment->paid_amount >= $installment->amount) {
        $installment->status = 'paid';
        $installment->paid_date = $validated['payment_date'];
    } else {
        $installment->status = 'partial';
    }
    $installment->save();

    // 5. Update property file
    $file = PropertyFile::findOrFail($validated['property_file_id']);
    if ($installment->status === 'paid') {
        $file->increment('paid_installments');
    }

    // Check file completion
    if ($file->paid_installments >= $file->total_installments) {
        $file->status = 'completed';
        $file->save();

        // Update inventory status to sold
        $file->fileable->status = 'sold';
        $file->fileable->save();
    }

    // 6. Generate PDF receipt
    $this->generateReceipt($payment);

    return $payment;
});
```

### PAYMENT RECEIPT (PDF)

| Component | Data Source | Status |
|-----------|-------------|--------|
| **Receipt Header** | Company logo, info | Config | ✅ |
| **Receipt Number** | `$payment->receipt_number` | Auto-generated | ✅ |
| **Date** | `$payment->payment_date` | Formatted | ✅ |
| **Client Details** | Name, CNIC, contact | `clients.*` | ✅ |
| **File Details** | File number, property | `property_files.*` | ✅ |
| **Payment Details** | Amount, method | `file_payments.*` | ✅ |
| **Installment Info** | Installment #, due date | `installments.*` | ✅ |
| **Breakdown** | Amount, late fee (if any) | Calculated | ✅ |
| **Signature Section** | Received by, client signature | - | ✅ |
| **QR Code** | Receipt verification (optional) | Generated | ⚠️ |

---

## 1️⃣1️⃣ EXPENSE MANAGEMENT MODULE

### EXPENSES INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Expenses List** (`expenses/index.blade.php`) | `GET /expenses` → `ExpenseController@index` | `expenses` JOIN `payment_types`, `users` | ✅ |
| **Statistics Cards** | Dashboard metrics | Aggregations | ✅ |
| - Total Expenses | SUM(net_amount) | WHERE status = 'paid' | ✅ |
| - This Month | SUM WHERE MONTH = CURRENT | - | ✅ |
| - Pending Approval | COUNT WHERE status = 'pending' | - | ✅ |
| - Recurring Count | COUNT WHERE is_recurring = TRUE | - | ✅ |
| **Filters:** | | | |
| - Date Range | `?date_from=&date_to=` | WHERE expense_date BETWEEN ? AND ? | ✅ |
| - Payment Type | `?payment_type_id=5` | WHERE payment_type_id = ? | ✅ |
| - Status | `?status=paid` | WHERE status = ? | ✅ |
| - Recurring | `?is_recurring=1` | WHERE is_recurring = TRUE | ✅ |
| - Paid To (Vendor) | `?paid_to=xyz` | WHERE paid_to LIKE ? | ✅ |
| Search | `?search=EXP-2026` | WHERE expense_number OR paid_to LIKE ? | ✅ |
| **Displayed Data:** | | | |
| - Expense Number | `$expense->expense_number` | Auto-generated | ✅ |
| - Payment Type | `$expense->paymentType->name` | `payment_types.name` | ✅ |
| - Paid To (Vendor) | `$expense->paid_to` | `expenses.paid_to` | ✅ |
| - Amount | Formatted | `expenses.amount` | ✅ |
| - Net Amount | Amount + tax - discount | `expenses.net_amount` | ✅ |
| - Expense Date | Formatted | `expenses.expense_date` | ✅ |
| - Status Badge | pending/paid/cleared | `expenses.status` | ✅ |
| - Recurring Badge | If recurring | `expenses.is_recurring` | ✅ |
| - Next Due | If recurring | `expenses.next_due_date` | ✅ |
| **Actions:** | | | |
| - View Details | GET `/expenses/{id}` | - | ✅ |
| - Approve (if pending) | POST action | - | ✅ |
| - Mark as Paid | POST action | - | ✅ |
| - Edit | Link | - | ✅ |
| - Delete | POST action | - | ✅ |

### EXPENSE CREATE/EDIT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Create Form** (`expenses/create.blade.php`) | `GET /expenses/create` | - | ✅ |
| **Expense Details:** | | | |
| - Payment Type | Dropdown (expense category) | `expenses.payment_type_id` | ✅ |
| - Amount | Numeric | `expenses.amount` | ✅ |
| - Expense Date | Date picker | `expenses.expense_date` | ✅ |
| - Payment Method | Dropdown | `expenses.payment_method` | ✅ |
| **Vendor Details:** | | | |
| - Paid To (Vendor Name) | Required | `expenses.paid_to` | ✅ |
| - Contact Number | Optional | `expenses.contact_number` | ✅ |
| - Address | Optional | `expenses.address` | ✅ |
| - Tax ID (NTN/CNIC) | Optional | `expenses.tax_id` | ✅ |
| **Financial Breakdown:** | | | |
| - Tax Amount | Numeric | `expenses.tax_amount` | ✅ |
| - Discount Amount | Numeric | `expenses.discount_amount` | ✅ |
| - Net Amount | Auto-calculated (JS) | `expenses.net_amount` | ✅ |
| **Recurring Config:** | | | |
| - Is Recurring | Checkbox | `expenses.is_recurring` | ✅ |
| - Recurring Frequency | If recurring: monthly/quarterly/yearly | `expenses.recurring_frequency` | ✅ |
| - Next Due Date | If recurring | `expenses.next_due_date` | ✅ |
| **Additional:** | | | |
| - Description | Textarea | `expenses.description` | ✅ |
| - Remarks | Textarea | `expenses.remarks` | ✅ |
| - Attach Documents | Multiple files | `expenses.documents` (JSON) | ✅ |
| **Submit** | `POST /expenses` → `store()` | INSERT INTO expenses | ✅ |

**Net Amount Calculation (JS):**
```javascript
$('#amount, #tax_amount, #discount_amount').on('change', function() {
    let amount = parseFloat($('#amount').val()) || 0;
    let tax = parseFloat($('#tax_amount').val()) || 0;
    let discount = parseFloat($('#discount_amount').val()) || 0;

    let net = amount + tax - discount;

    $('#net_amount').val(net.toFixed(2));
});
```

---

## 1️⃣2️⃣ REPORT MODULE

### REPORTS DASHBOARD

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Reports Hub** (`reports/index.blade.php`) | `GET /reports` | - | ✅ |
| **Report Cards** | Links to each report | - | ✅ |
| - Plots Report | Link | - | ✅ |
| - Payments Report | Link | - | ✅ |
| - Commissions Report | Link | - | ✅ |
| - Overdue Installments | Link | - | ✅ |
| - Society Sales Report | Link | - | ✅ |

### PLOTS REPORT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Plots Report** (`reports/plots.blade.php`) | `GET /reports/plots` | `plots` with aggregations | ✅ |
| **KPI Cards** | | | |
| - Total Plots | COUNT(*) | - | ✅ |
| - Available | COUNT WHERE status = 'available' | - | ✅ |
| - Booked | COUNT WHERE status = 'booked' | - | ✅ |
| - Sold | COUNT WHERE status = 'sold' | - | ✅ |
| - Total Value | SUM(total_price) | - | ✅ |
| - Sold Value | SUM(total_price) WHERE status = 'sold' | - | ✅ |
| **Charts (Chart.js):** | | | |
| - Doughnut Chart | Plot status distribution | GROUP BY status | ✅ |
| - Bar Chart | Plot value by status | SUM(total_price) GROUP BY status | ✅ |
| **Society-wise Table** | | | |
| - Society breakdown | Society → Blocks → Streets → Plots (JOIN) | Multiple aggregations | ✅ |
| - Total plots per society | COUNT | - | ✅ |
| - Available/Booked/Sold count | Conditional COUNT | - | ✅ |
| - Total value | SUM | - | ✅ |
| **Filters:** | | | |
| - Society Filter | `?society_id=1` | WHERE society_id = ? | ✅ |
| - Block Filter | `?block_id=2` | WHERE block_id = ? | ✅ |
| - Street Filter | `?street_id=5` | WHERE street_id = ? | ✅ |
| **Export:** | | | |
| - Excel Export | Button → download | Using Laravel Excel | ⚠️ |
| - PDF Export | Button → download | Using DomPDF | ⚠️ |

### PAYMENTS REPORT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Payments Report** (`reports/payments.blade.php`) | `GET /reports/payments` | `file_payments` with aggregations | ✅ |
| **KPI Cards** | | | |
| - Total Amount | SUM(amount) | - | ✅ |
| - Total Transactions | COUNT(*) | - | ✅ |
| - Average Payment | AVG(amount) | - | ✅ |
| - Daily Average | SUM / DATEDIFF | - | ✅ |
| **Charts (Chart.js):** | | | |
| - Line Chart | Monthly payment trend | GROUP BY MONTH(payment_date), SUM(amount) | ✅ |
| - Pie Chart | Payment method distribution | GROUP BY payment_method, COUNT(*) | ✅ |
| **Daily Breakdown Table** | | | |
| - Date | GROUP BY DATE(payment_date) | - | ✅ |
| - Cash Count & Amount | SUM WHERE method = 'cash' | - | ✅ |
| - Cheque Count & Amount | SUM WHERE method = 'cheque' | - | ✅ |
| - Bank Count & Amount | SUM WHERE method = 'bank_transfer' | - | ✅ |
| - Online Count & Amount | SUM WHERE method = 'online' | - | ✅ |
| - Daily Total | SUM per date | - | ✅ |
| **Filters:** | | | |
| - Date Range | `?date_from=&date_to=` | WHERE payment_date BETWEEN ? AND ? | ✅ |
| - Payment Method | `?payment_method=cash` | WHERE payment_method = ? | ✅ |

### COMMISSIONS REPORT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Commissions Report** (`reports/commissions.blade.php`) | `GET /reports/commissions` | `dealers`, `deals` with aggregations | ✅ |
| **KPI Cards** | | | |
| - Total Earned | SUM(commission_amount) WHERE status = 'completed' | - | ✅ |
| - Total Paid | SUM FROM commission_payments | - | ✅ |
| - Pending | Earned - Paid | - | ✅ |
| - Active Dealers | COUNT WHERE status = 'active' | - | ✅ |
| **Charts (Chart.js):** | | | |
| - Horizontal Bar | Top 10 dealers by commission | GROUP BY dealer_id ORDER BY SUM DESC LIMIT 10 | ✅ |
| **Dealer-wise Table** | | | |
| - Dealer Name | `dealers.user.name` | - | ✅ |
| - Total Deals | COUNT from deals | - | ✅ |
| - Total Earned | SUM(commission_amount) | - | ✅ |
| - Total Paid | SUM from payments | - | ✅ |
| - Pending | Earned - Paid | - | ✅ |
| - Average per Deal | Earned / Total Deals | - | ✅ |
| - Status | active/inactive | - | ✅ |
| **Filters:** | | | |
| - Dealer Filter | `?dealer_id=5` | WHERE dealer_id = ? | ✅ |
| - Date Range | `?date_from=&date_to=` | WHERE deal_date BETWEEN ? AND ? | ✅ |

### OVERDUE REPORT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Overdue Report** (`reports/overdue.blade.php`) | `GET /reports/overdue` | `file_installments` WHERE status = 'overdue' | ✅ |
| **KPI Cards** | | | |
| - Total Overdue | COUNT(*) | - | ✅ |
| - Total Amount | SUM(amount - paid_amount) | - | ✅ |
| - Average Days Overdue | AVG(days_overdue) | - | ✅ |
| - Total Late Fees | SUM(late_fee) | - | ✅ |
| **Aging Buckets** | | | |
| - 1-30 Days | COUNT WHERE days_overdue <= 30 | Warning | ✅ |
| - 31-60 Days | COUNT WHERE days_overdue BETWEEN 31 AND 60 | Danger | ✅ |
| - 61-90 Days | COUNT WHERE days_overdue BETWEEN 61 AND 90 | Critical | ✅ |
| - 90+ Days | COUNT WHERE days_overdue > 90 | Severe | ✅ |
| **Overdue List Table** | | | |
| - File Number | `property_files.file_number` | - | ✅ |
| - Client Name | `clients.name` | - | ✅ |
| - Installment # | `installments.installment_number` | - | ✅ |
| - Due Date | `installments.due_date` | - | ✅ |
| - Amount Due | `installments.amount` | - | ✅ |
| - Days Overdue | Badge with color | `installments.days_overdue` | ✅ |
| - Late Fee | Formatted | `installments.late_fee` | ✅ |
| - Action | Pay Now button | Link to payment form | ✅ |
| **Filters:** | | | |
| - Client Filter | `?client_id=10` | WHERE client_id = ? | ✅ |
| - Days Overdue Range | `?min_days=30&max_days=60` | WHERE days_overdue BETWEEN ? AND ? | ✅ |

### SOCIETY SALES REPORT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Society Sales Report** (`reports/society-sales.blade.php`) | `GET /reports/society-sales` | Complex JOIN with aggregations | ✅ |
| **KPI Cards** | | | |
| - Total Societies | COUNT FROM societies | - | ✅ |
| - Total Sales Value | SUM(plots.total_price) WHERE status = 'sold' | - | ✅ |
| - Total Deals | COUNT FROM deals WHERE status = 'completed' | - | ✅ |
| - Top Society | Society with highest sales | MAX | ✅ |
| **Charts (Chart.js):** | | | |
| - Bar Chart | Sales by society (in millions) | GROUP BY society, SUM | ✅ |
| **Society Performance Table** | | | |
| - Society Name | `societies.name` | - | ✅ |
| - Total Plots | COUNT from plots | - | ✅ |
| - Sold Plots | COUNT WHERE status = 'sold' | - | ✅ |
| - Sales Rate % | (sold / total) * 100 with progress bar | Visual indicator | ✅ |
| - Total Value | SUM(total_price) | - | ✅ |
| - Average Price | AVG(total_price) | - | ✅ |
| - Performance Badge | Excellent/Good/Average/Poor | Based on sales rate | ✅ |

---

## 1️⃣3️⃣ DASHBOARD MODULE

### MAIN DASHBOARD

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Dashboard** (`dashboard/index.blade.php`) | `GET /dashboard` → `DashboardController@index` | Multiple aggregations | ✅ |
| **Welcome Card** | Personalized greeting | auth()->user()->name | ✅ |
| **Quick Stats (Top Row)** | | | |
| - Total Plots | COUNT FROM plots | - | ✅ |
| - Available Plots | COUNT WHERE status = 'available' | - | ✅ |
| - Total Clients | COUNT FROM clients WHERE is_active = TRUE | - | ✅ |
| - Active Deals | COUNT FROM deals WHERE status IN ('pending', 'confirmed') | - | ✅ |
| - Total Revenue | SUM FROM deals WHERE status = 'completed' | - | ✅ |
| - This Month Revenue | SUM WHERE MONTH(completion_date) = CURRENT | - | ✅ |
| **Charts Row:** | | | |
| - Monthly Sales Chart | Line chart | GROUP BY MONTH, SUM(deal_amount) | ✅ |
| - Plot Status Chart | Pie chart | GROUP BY plots.status | ✅ |
| - Payment Methods Chart | Doughnut | GROUP BY payment_method | ✅ |
| **Recent Activities (Tables):** | | | |
| - Recent Leads | Last 5 leads | ORDER BY created_at DESC LIMIT 5 | ✅ |
| - Recent Deals | Last 5 deals | ORDER BY deal_date DESC LIMIT 5 | ✅ |
| - Recent Payments | Last 5 payments | ORDER BY payment_date DESC LIMIT 5 | ✅ |
| **Quick Actions (Buttons):** | | | |
| - Add Lead | Link to create | - | ✅ |
| - Add Deal | Link to create | - | ✅ |
| - Record Payment | Link to create | - | ✅ |
| - View Reports | Link to reports | - | ✅ |
| **Overdue Alerts** | | | |
| - Overdue Count | COUNT FROM installments WHERE is_overdue = TRUE | Badge | ✅ |
| - Total Overdue Amount | SUM(amount) WHERE is_overdue = TRUE | Formatted | ✅ |
| - View Details | Link to overdue report | - | ✅ |
| **Role-Based Dashboard:** | | | |
| - Dealers | See only own leads, clients, deals | WHERE assigned_to = auth()->id() | ✅ |
| - Managers/Admins | See all data | No filter | ✅ |
| - Accountants | Focus on financial data | Payments, expenses | ✅ |

**Role-Based Content:**
```php
// DashboardController@index
$user = auth()->user();

if ($user->hasRole('dealer')) {
    // Dealer-specific dashboard
    $data['my_leads'] = Lead::where('assigned_to', $user->id)->count();
    $data['my_clients'] = Client::where('assigned_to', $user->id)->count();
    $data['my_deals'] = Deal::where('dealer_id', $user->id)->count();
    $data['my_commission'] = Deal::where('dealer_id', $user->id)->completed()->sum('commission_amount');
} else {
    // Admin/Manager dashboard (all data)
    $data['total_leads'] = Lead::count();
    $data['total_clients'] = Client::count();
    $data['total_deals'] = Deal::count();
    $data['total_revenue'] = Deal::completed()->sum('deal_amount');
}

return view('dashboard.index', $data);
```

---

## 1️⃣4️⃣ FOLLOW-UP MODULE

### FOLLOW-UPS INDEX

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Follow-Ups List** (`follow-ups/index.blade.php`) | `GET /follow-ups` → `FollowUpController@index` | `follow_ups` polymorphic | ✅ |
| **Filter Tabs** | | | |
| - My Follow-Ups | WHERE dealer_id = auth()->id() | - | ✅ |
| - All Follow-Ups | No filter (managers/admins) | - | ✅ |
| - Today | WHERE follow_up_date = TODAY | - | ✅ |
| - Upcoming | WHERE follow_up_date > TODAY AND status = 'scheduled' | - | ✅ |
| - Overdue | WHERE follow_up_date < TODAY AND status = 'scheduled' | - | ✅ |
| **Displayed Data:** | | | |
| - Entity | Lead or Client name | Polymorphic `followupable` | ✅ |
| - Type | call/meeting/email/visit | `follow_ups.follow_up_type` | ✅ |
| - Date & Time | Formatted | `follow_ups.follow_up_date` | ✅ |
| - Status Badge | scheduled/completed/cancelled | `follow_ups.status` | ✅ |
| - Assigned To | Dealer name | `users.name` | ✅ |
| - Notes Preview | Truncated | `follow_ups.notes` | ✅ |
| **Actions:** | | | |
| - View Details | Modal or page | - | ✅ |
| - Mark Completed | Button | Update status | ✅ |
| - Cancel | Button | Update status | ✅ |
| - Edit | Link | - | ✅ |

### FOLLOW-UP CREATE/EDIT

| Frontend Component | Backend API | Database Tables | Status |
|-------------------|-------------|-----------------|--------|
| **Create Form** (Modal or Page) | `POST /follow-ups` → `store()` | INSERT INTO follow_ups | ✅ |
| - Entity Type | Radio: Lead or Client | `follow_ups.followupable_type` | ✅ |
| - Entity Selection | Dropdown based on type | `follow_ups.followupable_id` | ✅ |
| - Follow-Up Type | Dropdown: call/meeting/email/visit | `follow_ups.follow_up_type` | ✅ |
| - Date & Time | Date/time picker | `follow_ups.follow_up_date`, `follow_up_time` | ✅ |
| - Assigned To | Dealer dropdown, default current user | `follow_ups.dealer_id` | ✅ |
| - Status | Dropdown, default scheduled | `follow_ups.status` | ✅ |
| - Notes | Textarea | `follow_ups.notes` | ✅ |
| - Outcome | Textarea (for completed) | `follow_ups.outcome` | ✅ |
| - Next Follow-Up Date | Date picker (optional) | `follow_ups.next_follow_up_date` | ✅ |

**Calendar View (Optional Enhancement):**
```html
<!-- FullCalendar.js integration -->
<div id="calendar"></div>

<script>
// Load follow-ups as calendar events
$.get('/api/follow-ups/calendar', function(events) {
    $('#calendar').fullCalendar({
        events: events,
        eventClick: function(event) {
            // Show follow-up details modal
        }
    });
});
</script>
```

---

## 🚨 SYSTEM GAPS & MISSING INTEGRATIONS

Based on the comprehensive mapping above, here are identified gaps:

### ❌ MISSING FEATURES

1. **SMS/Email Notifications**
   - Lead assignment notifications
   - Follow-up reminders
   - Payment reminders
   - Overdue alerts
   - Deal confirmation emails
   - Receipt emails

2. **Advanced Search**
   - Global search across all modules
   - Advanced filter builder
   - Saved searches

3. **Bulk Operations**
   - Bulk lead import (CSV)
   - Bulk status updates
   - Bulk assignments

4. **Document Management**
   - Document versioning
   - Digital signatures
   - Document templates

5. **Audit Trail**
   - Complete activity log
   - Change history for critical data
   - User action tracking

6. **API for Mobile App**
   - RESTful API endpoints
   - API authentication (Sanctum)
   - Mobile-optimized responses

### ⚠️ PARTIAL IMPLEMENTATIONS

1. **Property File Transfer**
   - **Exists:** Transfer functionality
   - **Missing:** Approval workflow UI
   - **Missing:** Transfer history tracking

2. **Commission Payments**
   - **Exists:** Commission calculation
   - **Missing:** Payment disbursement tracking
   - **Missing:** Commission payment receipts

3. **Recurring Expenses**
   - **Exists:** Recurring flag and config
   - **Missing:** Automated generation cron job
   - **Missing:** Reminder system

4. **Late Payment Processing**
   - **Exists:** Late fee calculation logic
   - **Missing:** Automated daily cron job implementation
   - **Missing:** Escalation workflow

5. **Plot History**
   - **Mentioned:** Plot history tracking
   - **Missing:** Actual `plot_history` table and logic

### 🔄 NEEDS TESTING/VERIFICATION

1. **Cascading Dropdowns**
   - AJAX endpoints exist
   - Need to verify JS implementation across all forms

2. **Auto-Calculations**
   - Backend logic exists
   - Need to verify all frontend JS calculations match backend

3. **Permission Enforcement**
   - Middleware applied on routes
   - Need to verify Blade directive usage in all views

4. **Soft Delete Handling**
   - Soft deletes used in models
   - Need to verify restore functionality

5. **File Upload Security**
   - Validation rules exist
   - Need to verify file storage security

---

**END OF FRONTEND-BACKEND-DATABASE MAPPING**

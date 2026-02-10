# Backend Implementation Checklist

## ✅ Quick Start Guide for Laravel Backend

This checklist helps you implement the backend for all 45 frontend views that are already created.

---

## 📦 Step 1: Create Models

```bash
# Location Hierarchy
php artisan make:model Society -mcr
php artisan make:model Block -mcr
php artisan make:model Street -mcr
php artisan make:model Plot -mcr

# Property Management
php artisan make:model Property -mcr
php artisan make:model PropertyImage -m

# People
php artisan make:model Dealer -mcr
php artisan make:model Client -mcr

# Deals & Files
php artisan make:model Deal -mcr
php artisan make:model PropertyFile -mcr
php artisan make:model Installment -mcr

# Financial
php artisan make:model Expense -mcr
php artisan make:model AccountPayment -mcr
```

---

## 🗄️ Step 2: Define Database Migrations

### Example: Create Societies Table
```php
// database/migrations/xxxx_create_societies_table.php

public function up()
{
    Schema::create('societies', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('location', 500)->nullable();
        $table->text('description')->nullable();
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();

        $table->index('status');
    });
}
```

### Example: Create Blocks Table (with Foreign Key)
```php
// database/migrations/xxxx_create_blocks_table.php

public function up()
{
    Schema::create('blocks', function (Blueprint $table) {
        $table->id();
        $table->foreignId('society_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->text('description')->nullable();
        $table->enum('status', ['active', 'inactive'])->default('active');
        $table->timestamps();

        $table->unique(['society_id', 'name'], 'unique_block_per_society');
        $table->index('society_id');
        $table->index('status');
    });
}
```

### Example: Polymorphic Table (Deals)
```php
// database/migrations/xxxx_create_deals_table.php

public function up()
{
    Schema::create('deals', function (Blueprint $table) {
        $table->id();
        $table->morphs('dealable'); // Creates dealable_id and dealable_type
        $table->foreignId('dealer_id')->constrained()->onDelete('restrict');
        $table->foreignId('client_id')->constrained()->onDelete('restrict');
        $table->decimal('amount', 15, 2);
        $table->decimal('commission_amount', 15, 2);
        $table->enum('status', ['pending', 'approved', 'completed', 'cancelled'])->default('pending');
        $table->text('notes')->nullable();
        $table->timestamps();

        $table->index('status');
        $table->index('created_at');
    });
}
```

**Run migrations:**
```bash
php artisan migrate
```

---

## 🔗 Step 3: Define Model Relationships

### Example: Society Model
```php
// app/Models/Society.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Society extends Model
{
    protected $fillable = ['name', 'location', 'description', 'status'];

    // Relationships
    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    // Accessors
    public function getTotalBlocksAttribute()
    {
        return $this->blocks()->count();
    }

    public function getTotalPlotsAttribute()
    {
        return Plot::whereHas('street.block', function($q) {
            $q->where('society_id', $this->id);
        })->count();
    }
}
```

### Example: Deal Model (Polymorphic)
```php
// app/Models/Deal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Polymorphic Relationship
    public function dealable()
    {
        return $this->morphTo(); // Property or Plot
    }

    // Standard Relationships
    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
```

---

## 🎮 Step 4: Create Controllers

### Example: SocietyController
```php
// app/Http/Controllers/SocietyController.php

namespace App\Http\Controllers;

use App\Models\Society;
use Illuminate\Http\Request;

class SocietyController extends Controller
{
    public function index()
    {
        $societies = Society::withCount('blocks')->paginate(15);

        $stats = [
            'total' => Society::count(),
            'active' => Society::where('status', 'active')->count(),
            'inactive' => Society::where('status', 'inactive')->count(),
            'total_blocks' => Block::count(),
        ];

        return view('societies.index', compact('societies', 'stats'));
    }

    public function create()
    {
        return view('societies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:societies',
            'location' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Society::create($validated);

        return redirect()->route('societies.index')
            ->with('success', 'Society created successfully!');
    }

    public function show(Society $society)
    {
        $society->load('blocks.streets');

        return view('societies.show', compact('society'));
    }

    public function edit(Society $society)
    {
        return view('societies.edit', compact('society'));
    }

    public function update(Request $request, Society $society)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:societies,name,' . $society->id,
            'location' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $society->update($validated);

        return redirect()->route('societies.show', $society)
            ->with('success', 'Society updated successfully!');
    }

    public function destroy(Society $society)
    {
        $society->delete();

        return response()->json([
            'success' => true,
            'message' => 'Society deleted successfully!'
        ]);
    }
}
```

---

## 🛣️ Step 5: Define Routes

```php
// routes/web.php

use App\Http\Controllers\{
    SocietyController,
    BlockController,
    StreetController,
    PlotController,
    PropertyController,
    DealerController,
    DealController,
    PropertyFileController,
    ExpenseController,
    AccountPaymentController,
    InstallmentController
};

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Location Hierarchy
    Route::resource('societies', SocietyController::class);
    Route::resource('blocks', BlockController::class);
    Route::resource('streets', StreetController::class);
    Route::resource('plots', PlotController::class);

    // Property Management
    Route::resource('properties', PropertyController::class);

    // People Management
    Route::resource('dealers', DealerController::class);
    Route::get('dealers/{dealer}/performance', [DealerController::class, 'performance'])
        ->name('dealers.performance');

    Route::resource('clients', ClientController::class);

    // Deals
    Route::resource('deals', DealController::class);

    // Property Files
    Route::resource('files', PropertyFileController::class);
    Route::get('files/{file}/statement', [PropertyFileController::class, 'statement'])
        ->name('files.statement');

    // Installments
    Route::post('installments/{installment}/mark-paid', [InstallmentController::class, 'markPaid'])
        ->name('installments.mark-paid');

    // Financial
    Route::resource('expenses', ExpenseController::class);
    Route::resource('account-payments', AccountPaymentController::class);

    // AJAX Endpoints for Cascading Dropdowns
    Route::get('api/blocks-by-society/{society}', [BlockController::class, 'getBySociety']);
    Route::get('api/streets-by-block/{block}', [StreetController::class, 'getByBlock']);
    Route::get('api/plots-by-street/{street}', [PlotController::class, 'getByStreet']);
});
```

---

## ✅ Step 6: Form Request Validation

### Example: StoreSocietyRequest
```php
// app/Http/Requests/StoreSocietyRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocietyRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Or check user permissions
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:societies',
            'location' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Society name is required.',
            'name.unique' => 'This society name already exists.',
            'status.required' => 'Please select a status.',
        ];
    }
}
```

**Usage in Controller:**
```php
public function store(StoreSocietyRequest $request)
{
    Society::create($request->validated());
    return redirect()->route('societies.index')->with('success', 'Created!');
}
```

---

## 🔐 Step 7: Authorization Policies

### Example: SocietyPolicy
```php
// app/Policies/SocietyPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\Society;
use Illuminate\Auth\Access\HandlesAuthorization;

class SocietyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true; // All authenticated users can view
    }

    public function view(User $user, Society $society)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->hasPermission('create-societies');
    }

    public function update(User $user, Society $society)
    {
        return $user->hasPermission('edit-societies');
    }

    public function delete(User $user, Society $society)
    {
        return $user->hasPermission('delete-societies');
    }
}
```

**Register in AuthServiceProvider:**
```php
// app/Providers/AuthServiceProvider.php

protected $policies = [
    Society::class => SocietyPolicy::class,
    Block::class => BlockPolicy::class,
    // ... other policies
];
```

---

## 🌱 Step 8: Database Seeders

### Example: SocietySeeder
```php
// database/seeders/SocietySeeder.php

namespace Database\Seeders;

use App\Models\Society;
use Illuminate\Database\Seeder;

class SocietySeeder extends Seeder
{
    public function run()
    {
        $societies = [
            ['name' => 'DHA Phase 1', 'location' => 'Islamabad', 'status' => 'active'],
            ['name' => 'Bahria Town', 'location' => 'Rawalpindi', 'status' => 'active'],
            ['name' => 'Gulberg Greens', 'location' => 'Islamabad', 'status' => 'active'],
        ];

        foreach ($societies as $society) {
            Society::create($society);
        }
    }
}
```

**Run seeders:**
```bash
php artisan db:seed --class=SocietySeeder
# Or run all seeders
php artisan db:seed
```

---

## 🔄 Step 9: AJAX Endpoints for Cascading Dropdowns

### BlockController - Get Blocks by Society
```php
// app/Http/Controllers/BlockController.php

public function getBySociety(Society $society)
{
    $blocks = $society->blocks()
        ->where('status', 'active')
        ->select('id', 'name')
        ->orderBy('name')
        ->get();

    return response()->json($blocks);
}
```

### Frontend JavaScript (Already in create.blade.php)
```javascript
document.getElementById('societySelect').addEventListener('change', function() {
    const societyId = this.value;
    const blockSelect = document.getElementById('blockSelect');

    if (societyId) {
        fetch(`/api/blocks-by-society/${societyId}`)
            .then(response => response.json())
            .then(blocks => {
                blockSelect.innerHTML = '<option value="">Select Block</option>';
                blocks.forEach(block => {
                    blockSelect.innerHTML += `<option value="${block.id}">${block.name}</option>`;
                });
            });
    }
});
```

---

## 📊 Step 10: Statistics Calculation

### Example: Dashboard Statistics
```php
// app/Http/Controllers/DashboardController.php

public function index()
{
    $stats = [
        'total_properties' => Property::count(),
        'available_plots' => Plot::where('status', 'available')->count(),
        'active_deals' => Deal::whereIn('status', ['pending', 'approved'])->count(),
        'total_revenue' => Deal::completed()->sum('amount'),
        'pending_installments' => Installment::where('status', 'pending')->sum('amount'),
        'overdue_installments' => Installment::overdue()->count(),
        'monthly_expenses' => Expense::thisMonth()->sum('amount'),
    ];

    // Chart data
    $monthlyDeals = Deal::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->whereYear('created_at', now()->year)
        ->groupBy('month')
        ->pluck('count', 'month');

    return view('dashboard', compact('stats', 'monthlyDeals'));
}
```

---

## 📁 Step 11: File Upload Handling

### Example: Property Images
```php
// app/Http/Controllers/PropertyController.php

public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'images.*' => 'image|mimes:jpeg,png,jpg|max:5120', // 5MB max
    ]);

    $property = Property::create($validated);

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            $path = $image->store('properties', 'public');

            PropertyImage::create([
                'property_id' => $property->id,
                'image_path' => $path,
            ]);
        }
    }

    return redirect()->route('properties.show', $property);
}
```

**Storage Configuration:**
```bash
# Create storage link
php artisan storage:link
```

---

## 🧪 Step 12: Testing

### Feature Test Example
```php
// tests/Feature/SocietyTest.php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Society;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SocietyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_society()
    {
        $data = [
            'name' => 'Test Society',
            'location' => 'Test Location',
            'status' => 'active',
        ];

        $response = $this->post(route('societies.store'), $data);

        $response->assertRedirect(route('societies.index'));
        $this->assertDatabaseHas('societies', ['name' => 'Test Society']);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->post(route('societies.store'), []);

        $response->assertSessionHasErrors(['name', 'status']);
    }
}
```

**Run tests:**
```bash
php artisan test
```

---

## ✅ Complete Implementation Checklist

### Models & Migrations
- [ ] Society, Block, Street, Plot
- [ ] Property, PropertyImage
- [ ] Dealer, Client
- [ ] Deal (polymorphic)
- [ ] PropertyFile (polymorphic), Installment
- [ ] Expense
- [ ] AccountPayment (polymorphic)

### Controllers
- [ ] SocietyController (full CRUD)
- [ ] BlockController (full CRUD + getBySociety)
- [ ] StreetController (full CRUD + getByBlock)
- [ ] PlotController (full CRUD + getByStreet)
- [ ] PropertyController (full CRUD + image handling)
- [ ] DealerController (full CRUD + performance)
- [ ] DealController (full CRUD + commission calculation)
- [ ] PropertyFileController (full CRUD + statement)
- [ ] InstallmentController (markPaid method)
- [ ] ExpenseController (full CRUD)
- [ ] AccountPaymentController (create + show + index)

### Routes
- [ ] All resource routes defined
- [ ] Custom routes (performance, statement, mark-paid)
- [ ] AJAX routes for cascading dropdowns

### Validation
- [ ] Form Request classes for all store/update operations
- [ ] Custom validation messages
- [ ] File upload validation

### Authorization
- [ ] Policy classes for each model
- [ ] Permission checks in controllers
- [ ] @can directives working in views

### Seeders
- [ ] Sample data for all modules
- [ ] Realistic test data
- [ ] Run seeders successfully

### File Storage
- [ ] Storage link created
- [ ] Property images uploading
- [ ] Expense attachments uploading
- [ ] File paths stored correctly

### Testing
- [ ] Feature tests for main CRUD operations
- [ ] Validation tests
- [ ] Relationship tests
- [ ] Authorization tests

---

## 🚀 Quick Start Commands

```bash
# 1. Create all models with migrations, controllers, and resources
php artisan make:model Society -mcr
php artisan make:model Block -mcr
# ... repeat for all models

# 2. Run migrations
php artisan migrate

# 3. Create storage link
php artisan storage:link

# 4. Run seeders
php artisan db:seed

# 5. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# 6. Start development server
php artisan serve
```

---

## 📝 Notes

- All frontend views are already created and styled
- Views expect specific variable names (check blade files)
- AJAX endpoints return JSON responses
- File uploads go to `storage/app/public/`
- Statistics need to be calculated in controllers
- Chart.js data should be passed as JSON to views

**All 45 frontend views are complete and waiting for backend integration!**

# Plot & Society Management - Backend Logic Guide
## Real Estate CRM System

---

## 📋 Table of Contents
1. [Database Structure](#database-structure)
2. [Eloquent Models & Relationships](#eloquent-models--relationships)
3. [Plot Assignment Logic](#plot-assignment-logic)
4. [Auto Status Changes](#auto-status-changes)
5. [Plot History Tracking](#plot-history-tracking)
6. [Service Layer](#service-layer)
7. [Complete Implementation](#complete-implementation)
8. [API Endpoints](#api-endpoints)

---

## 🗄️ 1. DATABASE STRUCTURE

### ERD Diagram

```
┌─────────────────┐
│   societies     │
│─────────────────│
│ id              │
│ name            │
│ location        │
│ total_blocks    │
│ description     │
│ is_active       │
└────────┬────────┘
         │ 1
         │ has many
         │ *
┌────────▼────────┐
│     blocks      │
│─────────────────│
│ id              │
│ society_id      │◄── FK
│ block_name      │
│ total_streets   │
│ description     │
└────────┬────────┘
         │ 1
         │ has many
         │ *
┌────────▼────────┐
│    streets      │
│─────────────────│
│ id              │
│ block_id        │◄── FK
│ street_name     │
│ total_plots     │
│ description     │
└────────┬────────┘
         │ 1
         │ has many
         │ *
┌────────▼────────────────┐
│        plots            │
│─────────────────────────│
│ id                      │
│ society_id              │◄── FK
│ block_id                │◄── FK
│ street_id               │◄── FK (nullable)
│ plot_number             │
│ size_marla              │
│ size_sqft               │
│ price_per_marla         │
│ total_price             │
│ status (enum)           │ ◄── available/booked/sold
│ map_location (JSON)     │
│ is_corner               │
│ is_park_facing          │
│ current_owner_id (FK)   │◄─┐
│ notes                   │  │
│ timestamps              │  │
│ deleted_at              │  │
└─────────────────────────┘  │
                             │ belongs to
┌────────────────────────────▼──┐
│         clients               │
│───────────────────────────────│
│ id                            │
│ name                          │
│ email, phone, cnic            │
│ address                       │
│ dealer_id (FK)                │
│ timestamps                    │
└───────────────────────────────┘

┌─────────────────────────────┐
│     plot_histories          │
│─────────────────────────────│
│ id                          │
│ plot_id (FK)                │
│ action (enum)               │ ◄── status_change/assignment/transfer
│ old_status                  │
│ new_status                  │
│ old_owner_id (FK)           │
│ new_owner_id (FK)           │
│ changed_by (FK - users)     │
│ deal_id (FK)                │
│ notes                       │
│ timestamp                   │
└─────────────────────────────┘
```

### Migrations

```php
// database/migrations/xxxx_create_societies_table.php
Schema::create('societies', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('location')->nullable();
    $table->text('description')->nullable();
    $table->integer('total_blocks')->default(0);
    $table->integer('total_plots')->default(0);
    $table->boolean('is_active')->default(true);
    $table->json('amenities')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

// database/migrations/xxxx_create_blocks_table.php
Schema::create('blocks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('society_id')->constrained()->onDelete('cascade');
    $table->string('block_name');
    $table->string('block_code')->nullable();
    $table->text('description')->nullable();
    $table->integer('total_streets')->default(0);
    $table->integer('total_plots')->default(0);
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['society_id', 'block_name']);
});

// database/migrations/xxxx_create_streets_table.php
Schema::create('streets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('block_id')->constrained()->onDelete('cascade');
    $table->string('street_name');
    $table->string('street_code')->nullable();
    $table->text('description')->nullable();
    $table->integer('total_plots')->default(0);
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['block_id', 'street_name']);
});

// database/migrations/xxxx_create_plots_table.php
Schema::create('plots', function (Blueprint $table) {
    $table->id();
    $table->foreignId('society_id')->constrained()->onDelete('cascade');
    $table->foreignId('block_id')->constrained()->onDelete('cascade');
    $table->foreignId('street_id')->nullable()->constrained()->onDelete('set null');

    $table->string('plot_number');
    $table->decimal('size_marla', 10, 2);
    $table->decimal('size_sqft', 10, 2);
    $table->decimal('price_per_marla', 12, 2);
    $table->decimal('total_price', 15, 2);

    $table->enum('status', ['available', 'booked', 'sold', 'reserved'])
        ->default('available')
        ->index();

    $table->json('map_location')->nullable()->comment('lat, lng, coordinates');
    $table->string('map_image_path')->nullable();

    $table->boolean('is_corner')->default(false);
    $table->boolean('is_park_facing')->default(false);
    $table->boolean('is_main_road')->default(false);

    $table->foreignId('current_owner_id')->nullable()
        ->constrained('clients')
        ->onDelete('set null');

    $table->text('notes')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->unique(['society_id', 'block_id', 'plot_number']);
    $table->index(['status', 'society_id']);
});

// database/migrations/xxxx_create_plot_histories_table.php
Schema::create('plot_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plot_id')->constrained()->onDelete('cascade');

    $table->enum('action', [
        'created',
        'status_changed',
        'assigned',
        'transferred',
        'booking_confirmed',
        'payment_received',
        'deal_completed'
    ])->index();

    $table->string('old_status')->nullable();
    $table->string('new_status')->nullable();

    $table->foreignId('old_owner_id')->nullable()
        ->constrained('clients')
        ->onDelete('set null');

    $table->foreignId('new_owner_id')->nullable()
        ->constrained('clients')
        ->onDelete('set null');

    $table->foreignId('changed_by')
        ->constrained('users')
        ->onDelete('cascade');

    $table->foreignId('deal_id')->nullable()
        ->constrained()
        ->onDelete('set null');

    $table->decimal('price_at_time', 15, 2)->nullable();
    $table->text('notes')->nullable();
    $table->json('metadata')->nullable();

    $table->timestamp('action_date')->useCurrent();
    $table->timestamps();

    $table->index(['plot_id', 'action_date']);
});
```

---

## 🎭 2. ELOQUENT MODELS & RELATIONSHIPS

### Society Model

```php
// app/Models/Society.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Society extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'total_blocks',
        'total_plots',
        'is_active',
        'amenities',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amenities' => 'array',
        'total_blocks' => 'integer',
        'total_plots' => 'integer',
    ];

    /**
     * Boot method - Auto generate slug
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($society) {
            if (empty($society->slug)) {
                $society->slug = Str::slug($society->name);
            }
        });

        static::updating(function ($society) {
            if ($society->isDirty('name')) {
                $society->slug = Str::slug($society->name);
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * One Society has many Blocks
     */
    public function blocks()
    {
        return $this->hasMany(Block::class);
    }

    /**
     * One Society has many Plots (direct relationship)
     */
    public function plots()
    {
        return $this->hasMany(Plot::class);
    }

    /**
     * Get all streets through blocks
     */
    public function streets()
    {
        return $this->hasManyThrough(Street::class, Block::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get available plots count
     */
    public function getAvailablePlotsCountAttribute()
    {
        return $this->plots()->where('status', 'available')->count();
    }

    /**
     * Get sold plots count
     */
    public function getSoldPlotsCountAttribute()
    {
        return $this->plots()->where('status', 'sold')->count();
    }

    /**
     * Get booked plots count
     */
    public function getBookedPlotsCountAttribute()
    {
        return $this->plots()->where('status', 'booked')->count();
    }

    /**
     * Update total blocks count
     */
    public function updateBlocksCount()
    {
        $this->update(['total_blocks' => $this->blocks()->count()]);
    }

    /**
     * Update total plots count
     */
    public function updatePlotsCount()
    {
        $this->update(['total_plots' => $this->plots()->count()]);
    }
}
```

### Block Model

```php
// app/Models/Block.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Block extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id',
        'block_name',
        'block_code',
        'description',
        'total_streets',
        'total_plots',
    ];

    protected $casts = [
        'total_streets' => 'integer',
        'total_plots' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Block belongs to Society
     */
    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * One Block has many Streets
     */
    public function streets()
    {
        return $this->hasMany(Street::class);
    }

    /**
     * One Block has many Plots
     */
    public function plots()
    {
        return $this->hasMany(Plot::class);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get full block identifier (e.g., "Block A - DHA Phase 5")
     */
    public function getFullNameAttribute()
    {
        return "{$this->block_name} - {$this->society->name}";
    }

    /**
     * Get available plots
     */
    public function availablePlots()
    {
        return $this->plots()->where('status', 'available');
    }

    /**
     * Update counts
     */
    public function updateCounts()
    {
        $this->update([
            'total_streets' => $this->streets()->count(),
            'total_plots' => $this->plots()->count(),
        ]);
    }
}
```

### Street Model

```php
// app/Models/Street.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Street extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'block_id',
        'street_name',
        'street_code',
        'description',
        'total_plots',
    ];

    protected $casts = [
        'total_plots' => 'integer',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Street belongs to Block
     */
    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Get society through block
     */
    public function society()
    {
        return $this->block->society();
    }

    /**
     * One Street has many Plots
     */
    public function plots()
    {
        return $this->hasMany(Plot::class);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get full street address
     */
    public function getFullAddressAttribute()
    {
        return "{$this->street_name}, {$this->block->block_name}, {$this->block->society->name}";
    }

    /**
     * Get available plots
     */
    public function availablePlots()
    {
        return $this->plots()->where('status', 'available');
    }

    /**
     * Update plots count
     */
    public function updatePlotsCount()
    {
        $this->update(['total_plots' => $this->plots()->count()]);
    }
}
```

### Plot Model

```php
// app/Models/Plot.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id',
        'block_id',
        'street_id',
        'plot_number',
        'size_marla',
        'size_sqft',
        'price_per_marla',
        'total_price',
        'status',
        'map_location',
        'map_image_path',
        'is_corner',
        'is_park_facing',
        'is_main_road',
        'current_owner_id',
        'notes',
    ];

    protected $casts = [
        'size_marla' => 'decimal:2',
        'size_sqft' => 'decimal:2',
        'price_per_marla' => 'decimal:2',
        'total_price' => 'decimal:2',
        'map_location' => 'array',
        'is_corner' => 'boolean',
        'is_park_facing' => 'boolean',
        'is_main_road' => 'boolean',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Plot belongs to Society
     */
    public function society()
    {
        return $this->belongsTo(Society::class);
    }

    /**
     * Plot belongs to Block
     */
    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    /**
     * Plot belongs to Street (optional)
     */
    public function street()
    {
        return $this->belongsTo(Street::class);
    }

    /**
     * Plot belongs to current owner (Client)
     */
    public function currentOwner()
    {
        return $this->belongsTo(Client::class, 'current_owner_id');
    }

    /**
     * Plot has many histories
     */
    public function histories()
    {
        return $this->hasMany(PlotHistory::class)->orderBy('action_date', 'desc');
    }

    /**
     * Plot has many deals (polymorphic)
     */
    public function deals()
    {
        return $this->morphMany(Deal::class, 'dealable');
    }

    /**
     * Plot has many files (polymorphic)
     */
    public function files()
    {
        return $this->morphMany(PropertyFile::class, 'fileable');
    }

    // ==================== SCOPES ====================

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    public function scopeSold($query)
    {
        return $query->where('status', 'sold');
    }

    public function scopeCorner($query)
    {
        return $query->where('is_corner', true);
    }

    public function scopeParkFacing($query)
    {
        return $query->where('is_park_facing', true);
    }

    public function scopeInSociety($query, $societyId)
    {
        return $query->where('society_id', $societyId);
    }

    public function scopeInBlock($query, $blockId)
    {
        return $query->where('block_id', $blockId);
    }

    // ==================== ACCESSORS ====================

    /**
     * Get full plot identifier
     */
    public function getFullIdentifierAttribute()
    {
        $identifier = "{$this->plot_number}, {$this->block->block_name}";

        if ($this->street) {
            $identifier .= ", {$this->street->street_name}";
        }

        $identifier .= ", {$this->society->name}";

        return $identifier;
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'available' => 'success',
            'booked' => 'warning',
            'sold' => 'danger',
            'reserved' => 'info',
            default => 'secondary',
        };
    }

    /**
     * Check if plot is available for booking
     */
    public function getIsAvailableAttribute()
    {
        return $this->status === 'available';
    }

    /**
     * Check if plot is owned
     */
    public function getIsOwnedAttribute()
    {
        return !is_null($this->current_owner_id);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Calculate total price based on size and price per marla
     */
    public function calculateTotalPrice()
    {
        $this->total_price = $this->size_marla * $this->price_per_marla;
        return $this->total_price;
    }

    /**
     * Convert marla to sqft
     */
    public static function marlaToSqft($marla)
    {
        return $marla * 272.25; // 1 Marla = 272.25 sqft
    }

    /**
     * Convert sqft to marla
     */
    public static function sqftToMarla($sqft)
    {
        return $sqft / 272.25;
    }

    /**
     * Get premium amount (if corner, park facing, etc.)
     */
    public function getPremiumPercentage()
    {
        $premium = 0;

        if ($this->is_corner) {
            $premium += 10; // 10% premium for corner
        }

        if ($this->is_park_facing) {
            $premium += 15; // 15% premium for park facing
        }

        if ($this->is_main_road) {
            $premium += 20; // 20% premium for main road
        }

        return $premium;
    }

    /**
     * Get price with premium
     */
    public function getPriceWithPremium()
    {
        $premiumPercentage = $this->getPremiumPercentage();
        $premiumAmount = ($this->total_price * $premiumPercentage) / 100;

        return $this->total_price + $premiumAmount;
    }
}
```

### PlotHistory Model

```php
// app/Models/PlotHistory.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlotHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'plot_id',
        'action',
        'old_status',
        'new_status',
        'old_owner_id',
        'new_owner_id',
        'changed_by',
        'deal_id',
        'price_at_time',
        'notes',
        'metadata',
        'action_date',
    ];

    protected $casts = [
        'metadata' => 'array',
        'price_at_time' => 'decimal:2',
        'action_date' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function plot()
    {
        return $this->belongsTo(Plot::class);
    }

    public function oldOwner()
    {
        return $this->belongsTo(Client::class, 'old_owner_id');
    }

    public function newOwner()
    {
        return $this->belongsTo(Client::class, 'new_owner_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    // ==================== SCOPES ====================

    public function scopeStatusChanges($query)
    {
        return $query->where('action', 'status_changed');
    }

    public function scopeAssignments($query)
    {
        return $query->where('action', 'assigned');
    }

    public function scopeTransfers($query)
    {
        return $query->where('action', 'transferred');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get formatted action description
     */
    public function getDescriptionAttribute()
    {
        return match($this->action) {
            'created' => 'Plot created',
            'status_changed' => "Status changed from {$this->old_status} to {$this->new_status}",
            'assigned' => "Assigned to {$this->newOwner->name}",
            'transferred' => "Transferred from {$this->oldOwner->name} to {$this->newOwner->name}",
            'booking_confirmed' => 'Booking confirmed',
            'payment_received' => 'Payment received',
            'deal_completed' => 'Deal completed',
            default => 'Unknown action',
        };
    }
}
```

---

## 📦 3. PLOT ASSIGNMENT LOGIC

### Service Class for Plot Assignment

```php
// app/Services/PlotAssignmentService.php

<?php

namespace App\Services;

use App\Models\Plot;
use App\Models\Client;
use App\Models\Deal;
use App\Models\PlotHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class PlotAssignmentService
{
    /**
     * Assign plot to a client
     *
     * @param Plot $plot
     * @param Client $client
     * @param array $data ['deal_id', 'notes', 'price']
     * @return Plot
     * @throws Exception
     */
    public function assignPlot(Plot $plot, Client $client, array $data = [])
    {
        // Check if plot is available
        if (!$plot->is_available) {
            throw new Exception("Plot is not available for assignment. Current status: {$plot->status}");
        }

        DB::beginTransaction();

        try {
            // Store old values for history
            $oldStatus = $plot->status;
            $oldOwner = $plot->current_owner_id;

            // Update plot
            $plot->update([
                'current_owner_id' => $client->id,
                'status' => 'booked',
            ]);

            // Create history record
            $this->createHistory($plot, [
                'action' => 'assigned',
                'old_status' => $oldStatus,
                'new_status' => 'booked',
                'old_owner_id' => $oldOwner,
                'new_owner_id' => $client->id,
                'deal_id' => $data['deal_id'] ?? null,
                'price_at_time' => $data['price'] ?? $plot->total_price,
                'notes' => $data['notes'] ?? "Plot assigned to {$client->name}",
            ]);

            DB::commit();

            return $plot->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Transfer plot from one client to another
     *
     * @param Plot $plot
     * @param Client $newClient
     * @param array $data
     * @return Plot
     * @throws Exception
     */
    public function transferPlot(Plot $plot, Client $newClient, array $data = [])
    {
        if (!$plot->is_owned) {
            throw new Exception("Plot has no owner to transfer from.");
        }

        if ($plot->current_owner_id === $newClient->id) {
            throw new Exception("Plot is already owned by this client.");
        }

        DB::beginTransaction();

        try {
            $oldOwner = $plot->current_owner_id;
            $oldStatus = $plot->status;

            // Update plot
            $plot->update([
                'current_owner_id' => $newClient->id,
                'status' => $data['status'] ?? 'booked',
            ]);

            // Create history
            $this->createHistory($plot, [
                'action' => 'transferred',
                'old_status' => $oldStatus,
                'new_status' => $plot->status,
                'old_owner_id' => $oldOwner,
                'new_owner_id' => $newClient->id,
                'deal_id' => $data['deal_id'] ?? null,
                'price_at_time' => $data['transfer_price'] ?? $plot->total_price,
                'notes' => $data['notes'] ?? "Plot transferred to {$newClient->name}",
                'metadata' => [
                    'transfer_fee' => $data['transfer_fee'] ?? 0,
                    'transfer_date' => now(),
                ],
            ]);

            DB::commit();

            return $plot->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Release plot (remove owner)
     *
     * @param Plot $plot
     * @param string|null $reason
     * @return Plot
     */
    public function releasePlot(Plot $plot, ?string $reason = null)
    {
        DB::beginTransaction();

        try {
            $oldOwner = $plot->current_owner_id;
            $oldStatus = $plot->status;

            // Update plot
            $plot->update([
                'current_owner_id' => null,
                'status' => 'available',
            ]);

            // Create history
            $this->createHistory($plot, [
                'action' => 'status_changed',
                'old_status' => $oldStatus,
                'new_status' => 'available',
                'old_owner_id' => $oldOwner,
                'new_owner_id' => null,
                'notes' => $reason ?? 'Plot released and made available',
            ]);

            DB::commit();

            return $plot->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark plot as sold
     *
     * @param Plot $plot
     * @param array $data
     * @return Plot
     */
    public function markAsSold(Plot $plot, array $data = [])
    {
        if (!$plot->is_owned) {
            throw new Exception("Plot must have an owner before marking as sold.");
        }

        DB::beginTransaction();

        try {
            $oldStatus = $plot->status;

            // Update plot
            $plot->update([
                'status' => 'sold',
            ]);

            // Create history
            $this->createHistory($plot, [
                'action' => 'deal_completed',
                'old_status' => $oldStatus,
                'new_status' => 'sold',
                'new_owner_id' => $plot->current_owner_id,
                'deal_id' => $data['deal_id'] ?? null,
                'price_at_time' => $data['final_price'] ?? $plot->total_price,
                'notes' => $data['notes'] ?? 'Plot marked as sold',
                'metadata' => [
                    'sold_date' => now(),
                    'payment_complete' => $data['payment_complete'] ?? true,
                ],
            ]);

            DB::commit();

            return $plot->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create history record
     *
     * @param Plot $plot
     * @param array $data
     * @return PlotHistory
     */
    protected function createHistory(Plot $plot, array $data)
    {
        return PlotHistory::create([
            'plot_id' => $plot->id,
            'action' => $data['action'],
            'old_status' => $data['old_status'] ?? null,
            'new_status' => $data['new_status'] ?? null,
            'old_owner_id' => $data['old_owner_id'] ?? null,
            'new_owner_id' => $data['new_owner_id'] ?? null,
            'changed_by' => Auth::id(),
            'deal_id' => $data['deal_id'] ?? null,
            'price_at_time' => $data['price_at_time'] ?? null,
            'notes' => $data['notes'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'action_date' => now(),
        ]);
    }

    /**
     * Get plot history
     *
     * @param Plot $plot
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPlotHistory(Plot $plot)
    {
        return $plot->histories()
            ->with(['oldOwner', 'newOwner', 'changedBy', 'deal'])
            ->orderBy('action_date', 'desc')
            ->get();
    }

    /**
     * Get available plots in society
     *
     * @param int $societyId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAvailablePlots(int $societyId, array $filters = [])
    {
        $query = Plot::with(['society', 'block', 'street'])
            ->inSociety($societyId)
            ->available();

        // Apply filters
        if (isset($filters['block_id'])) {
            $query->where('block_id', $filters['block_id']);
        }

        if (isset($filters['street_id'])) {
            $query->where('street_id', $filters['street_id']);
        }

        if (isset($filters['is_corner'])) {
            $query->where('is_corner', $filters['is_corner']);
        }

        if (isset($filters['is_park_facing'])) {
            $query->where('is_park_facing', $filters['is_park_facing']);
        }

        if (isset($filters['min_size'])) {
            $query->where('size_marla', '>=', $filters['min_size']);
        }

        if (isset($filters['max_size'])) {
            $query->where('size_marla', '<=', $filters['max_size']);
        }

        return $query->get();
    }
}
```

---

## 🔄 4. AUTO STATUS CHANGES

### Event-Driven Status Changes using Observers

```php
// app/Observers/PlotObserver.php

<?php

namespace App\Observers;

use App\Models\Plot;
use App\Models\PlotHistory;
use Illuminate\Support\Facades\Auth;

class PlotObserver
{
    /**
     * Handle the Plot "created" event.
     */
    public function created(Plot $plot): void
    {
        // Log creation in history
        PlotHistory::create([
            'plot_id' => $plot->id,
            'action' => 'created',
            'new_status' => $plot->status,
            'changed_by' => Auth::id(),
            'price_at_time' => $plot->total_price,
            'notes' => 'Plot created',
            'action_date' => now(),
        ]);

        // Update parent counts
        $plot->block->updateCounts();
        $plot->society->updatePlotsCount();
    }

    /**
     * Handle the Plot "updating" event.
     */
    public function updating(Plot $plot): void
    {
        // Check if status is changing
        if ($plot->isDirty('status')) {
            $this->handleStatusChange($plot);
        }

        // Check if owner is changing
        if ($plot->isDirty('current_owner_id')) {
            $this->handleOwnerChange($plot);
        }

        // Auto-calculate total price if size or price per marla changed
        if ($plot->isDirty('size_marla') || $plot->isDirty('price_per_marla')) {
            $plot->calculateTotalPrice();
        }
    }

    /**
     * Handle the Plot "updated" event.
     */
    public function updated(Plot $plot): void
    {
        // Additional logic after update
    }

    /**
     * Handle the Plot "deleted" event.
     */
    public function deleted(Plot $plot): void
    {
        // Update parent counts
        if ($plot->block) {
            $plot->block->updateCounts();
        }

        if ($plot->society) {
            $plot->society->updatePlotsCount();
        }
    }

    /**
     * Handle status change
     */
    protected function handleStatusChange(Plot $plot): void
    {
        $oldStatus = $plot->getOriginal('status');
        $newStatus = $plot->status;

        // Auto-logic based on status change
        if ($newStatus === 'available') {
            // If status changes to available, clear owner
            if ($plot->current_owner_id !== null) {
                $plot->current_owner_id = null;
            }
        }

        if ($newStatus === 'sold') {
            // Ensure plot has owner when marking as sold
            if ($plot->current_owner_id === null) {
                throw new \Exception('Cannot mark plot as sold without an owner');
            }
        }

        // Log status change
        PlotHistory::create([
            'plot_id' => $plot->id,
            'action' => 'status_changed',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => Auth::id(),
            'notes' => "Status changed from {$oldStatus} to {$newStatus}",
            'action_date' => now(),
        ]);
    }

    /**
     * Handle owner change
     */
    protected function handleOwnerChange(Plot $plot): void
    {
        $oldOwnerId = $plot->getOriginal('current_owner_id');
        $newOwnerId = $plot->current_owner_id;

        // If owner is being set and status is available, change to booked
        if ($newOwnerId !== null && $plot->status === 'available') {
            $plot->status = 'booked';
        }

        // If owner is being removed and status is not available, change to available
        if ($newOwnerId === null && $plot->status !== 'available') {
            $plot->status = 'available';
        }
    }
}
```

### Register Observer

```php
// app/Providers/AppServiceProvider.php

<?php

namespace App\Providers;

use App\Models\Plot;
use App\Observers\PlotObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Plot::observe(PlotObserver::class);
    }
}
```

### Alternative: Using Events

```php
// app/Events/PlotStatusChanged.php

<?php

namespace App\Events;

use App\Models\Plot;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlotStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Plot $plot,
        public string $oldStatus,
        public string $newStatus
    ) {}
}

// app/Listeners/LogPlotStatusChange.php

<?php

namespace App\Listeners;

use App\Events\PlotStatusChanged;
use App\Models\PlotHistory;
use Illuminate\Support\Facades\Auth;

class LogPlotStatusChange
{
    public function handle(PlotStatusChanged $event): void
    {
        PlotHistory::create([
            'plot_id' => $event->plot->id,
            'action' => 'status_changed',
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'changed_by' => Auth::id(),
            'notes' => "Status automatically changed from {$event->oldStatus} to {$event->newStatus}",
            'action_date' => now(),
        ]);
    }
}

// Register in EventServiceProvider
protected $listen = [
    PlotStatusChanged::class => [
        LogPlotStatusChange::class,
    ],
];
```

---

## 📜 5. PLOT HISTORY TRACKING

### Complete History Tracking System

```php
// app/Services/PlotHistoryService.php

<?php

namespace App\Services;

use App\Models\Plot;
use App\Models\PlotHistory;
use Illuminate\Support\Collection;

class PlotHistoryService
{
    /**
     * Get complete plot timeline
     *
     * @param Plot $plot
     * @return Collection
     */
    public function getTimeline(Plot $plot): Collection
    {
        return PlotHistory::where('plot_id', $plot->id)
            ->with(['oldOwner', 'newOwner', 'changedBy', 'deal'])
            ->orderBy('action_date', 'desc')
            ->get()
            ->map(function ($history) {
                return [
                    'id' => $history->id,
                    'action' => $history->action,
                    'description' => $history->description,
                    'old_status' => $history->old_status,
                    'new_status' => $history->new_status,
                    'old_owner' => $history->oldOwner?->name,
                    'new_owner' => $history->newOwner?->name,
                    'changed_by' => $history->changedBy?->name,
                    'deal_id' => $history->deal_id,
                    'price' => $history->price_at_time,
                    'notes' => $history->notes,
                    'date' => $history->action_date->format('d M Y, h:i A'),
                    'timestamp' => $history->action_date,
                ];
            });
    }

    /**
     * Get ownership history
     *
     * @param Plot $plot
     * @return Collection
     */
    public function getOwnershipHistory(Plot $plot): Collection
    {
        return PlotHistory::where('plot_id', $plot->id)
            ->whereIn('action', ['assigned', 'transferred'])
            ->with(['oldOwner', 'newOwner', 'changedBy'])
            ->orderBy('action_date', 'desc')
            ->get();
    }

    /**
     * Get status change history
     *
     * @param Plot $plot
     * @return Collection
     */
    public function getStatusHistory(Plot $plot): Collection
    {
        return PlotHistory::where('plot_id', $plot->id)
            ->where('action', 'status_changed')
            ->orderBy('action_date', 'desc')
            ->get();
    }

    /**
     * Get price history
     *
     * @param Plot $plot
     * @return Collection
     */
    public function getPriceHistory(Plot $plot): Collection
    {
        return PlotHistory::where('plot_id', $plot->id)
            ->whereNotNull('price_at_time')
            ->select('action_date', 'price_at_time', 'action', 'notes')
            ->orderBy('action_date', 'asc')
            ->get();
    }

    /**
     * Get statistics for a plot
     *
     * @param Plot $plot
     * @return array
     */
    public function getStatistics(Plot $plot): array
    {
        $histories = PlotHistory::where('plot_id', $plot->id)->get();

        return [
            'total_changes' => $histories->count(),
            'status_changes' => $histories->where('action', 'status_changed')->count(),
            'ownership_changes' => $histories->whereIn('action', ['assigned', 'transferred'])->count(),
            'total_owners' => $histories->whereNotNull('new_owner_id')->pluck('new_owner_id')->unique()->count(),
            'created_at' => $histories->where('action', 'created')->first()?->action_date,
            'last_updated' => $histories->sortByDesc('action_date')->first()?->action_date,
            'days_since_creation' => $plot->created_at->diffInDays(now()),
        ];
    }

    /**
     * Export plot history to array
     *
     * @param Plot $plot
     * @return array
     */
    public function exportHistory(Plot $plot): array
    {
        $timeline = $this->getTimeline($plot);
        $stats = $this->getStatistics($plot);

        return [
            'plot' => [
                'id' => $plot->id,
                'identifier' => $plot->full_identifier,
                'current_status' => $plot->status,
                'current_owner' => $plot->currentOwner?->name,
            ],
            'statistics' => $stats,
            'timeline' => $timeline->toArray(),
        ];
    }
}
```

---

## 🎯 6. SERVICE LAYER

### Complete Plot Management Service

```php
// app/Services/PlotManagementService.php

<?php

namespace App\Services;

use App\Models\Plot;
use App\Models\Society;
use App\Models\Block;
use App\Models\Street;
use Illuminate\Support\Facades\DB;
use Exception;

class PlotManagementService
{
    /**
     * Create a new plot
     *
     * @param array $data
     * @return Plot
     */
    public function createPlot(array $data): Plot
    {
        DB::beginTransaction();

        try {
            // Convert sqft to marla if provided
            if (isset($data['size_sqft']) && !isset($data['size_marla'])) {
                $data['size_marla'] = Plot::sqftToMarla($data['size_sqft']);
            }

            // Convert marla to sqft if not provided
            if (isset($data['size_marla']) && !isset($data['size_sqft'])) {
                $data['size_sqft'] = Plot::marlaToSqft($data['size_marla']);
            }

            // Calculate total price
            if (isset($data['size_marla']) && isset($data['price_per_marla'])) {
                $data['total_price'] = $data['size_marla'] * $data['price_per_marla'];
            }

            $plot = Plot::create($data);

            DB::commit();

            return $plot;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Bulk create plots
     *
     * @param array $plotsData
     * @return array
     */
    public function bulkCreatePlots(array $plotsData): array
    {
        $created = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($plotsData as $index => $data) {
                try {
                    $created[] = $this->createPlot($data);
                } catch (Exception $e) {
                    $errors[$index] = $e->getMessage();
                }
            }

            if (empty($errors)) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            return [
                'success' => empty($errors),
                'created' => count($created),
                'errors' => $errors,
                'plots' => $created,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get plots with filters
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPlots(array $filters = [])
    {
        $query = Plot::with(['society', 'block', 'street', 'currentOwner']);

        // Apply filters
        if (isset($filters['society_id'])) {
            $query->where('society_id', $filters['society_id']);
        }

        if (isset($filters['block_id'])) {
            $query->where('block_id', $filters['block_id']);
        }

        if (isset($filters['street_id'])) {
            $query->where('street_id', $filters['street_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_corner'])) {
            $query->where('is_corner', $filters['is_corner']);
        }

        if (isset($filters['is_park_facing'])) {
            $query->where('is_park_facing', $filters['is_park_facing']);
        }

        if (isset($filters['min_price'])) {
            $query->where('total_price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('total_price', '<=', $filters['max_price']);
        }

        if (isset($filters['search'])) {
            $query->where('plot_number', 'like', '%' . $filters['search'] . '%');
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get society statistics
     *
     * @param Society $society
     * @return array
     */
    public function getSocietyStatistics(Society $society): array
    {
        $plots = $society->plots;

        return [
            'total_plots' => $plots->count(),
            'available_plots' => $plots->where('status', 'available')->count(),
            'booked_plots' => $plots->where('status', 'booked')->count(),
            'sold_plots' => $plots->where('status', 'sold')->count(),
            'reserved_plots' => $plots->where('status', 'reserved')->count(),
            'total_value' => $plots->sum('total_price'),
            'available_value' => $plots->where('status', 'available')->sum('total_price'),
            'sold_value' => $plots->where('status', 'sold')->sum('total_price'),
            'corner_plots' => $plots->where('is_corner', true)->count(),
            'park_facing_plots' => $plots->where('is_park_facing', true)->count(),
        ];
    }
}
```

---

## 🚀 7. COMPLETE IMPLEMENTATION

### Controller Example

```php
// app/Http/Controllers/PlotController.php

<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Models\Society;
use App\Models\Client;
use App\Services\PlotManagementService;
use App\Services\PlotAssignmentService;
use App\Services\PlotHistoryService;
use Illuminate\Http\Request;

class PlotController extends Controller
{
    public function __construct(
        protected PlotManagementService $plotService,
        protected PlotAssignmentService $assignmentService,
        protected PlotHistoryService $historyService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:plots.view_all')->only('index');
        $this->middleware('permission:plots.create')->only(['create', 'store']);
        $this->middleware('permission:plots.update')->only(['edit', 'update']);
        $this->middleware('permission:plots.assign')->only(['assign', 'processAssignment']);
    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'society_id', 'block_id', 'street_id', 'status',
            'is_corner', 'is_park_facing', 'min_price', 'max_price',
            'search', 'sort_by', 'sort_order', 'per_page'
        ]);

        $plots = $this->plotService->getPlots($filters);
        $societies = Society::active()->get();

        return view('plots.index', compact('plots', 'societies'));
    }

    public function show(Plot $plot)
    {
        $plot->load(['society', 'block', 'street', 'currentOwner']);
        $timeline = $this->historyService->getTimeline($plot);
        $statistics = $this->historyService->getStatistics($plot);

        return view('plots.show', compact('plot', 'timeline', 'statistics'));
    }

    public function create()
    {
        $societies = Society::active()->with('blocks')->get();
        return view('plots.create', compact('societies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'society_id' => 'required|exists:societies,id',
            'block_id' => 'required|exists:blocks,id',
            'street_id' => 'nullable|exists:streets,id',
            'plot_number' => 'required|string',
            'size_marla' => 'required|numeric|min:0',
            'price_per_marla' => 'required|numeric|min:0',
            'is_corner' => 'boolean',
            'is_park_facing' => 'boolean',
            'is_main_road' => 'boolean',
            'map_location' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        try {
            $plot = $this->plotService->createPlot($validated);

            return redirect()->route('plots.show', $plot)
                ->with('success', 'Plot created successfully!');

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error creating plot: ' . $e->getMessage());
        }
    }

    public function assign(Plot $plot)
    {
        if (!$plot->is_available) {
            return back()->with('error', 'Plot is not available for assignment.');
        }

        $clients = Client::active()->get();
        return view('plots.assign', compact('plot', 'clients'));
    }

    public function processAssignment(Request $request, Plot $plot)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'deal_id' => 'nullable|exists:deals,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $client = Client::findOrFail($validated['client_id']);

            $this->assignmentService->assignPlot($plot, $client, [
                'deal_id' => $validated['deal_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()->route('plots.show', $plot)
                ->with('success', "Plot successfully assigned to {$client->name}!");

        } catch (\Exception $e) {
            return back()->with('error', 'Error assigning plot: ' . $e->getMessage());
        }
    }

    public function history(Plot $plot)
    {
        $timeline = $this->historyService->getTimeline($plot);
        $ownershipHistory = $this->historyService->getOwnershipHistory($plot);
        $priceHistory = $this->historyService->getPriceHistory($plot);
        $statistics = $this->historyService->getStatistics($plot);

        return view('plots.history', compact(
            'plot',
            'timeline',
            'ownershipHistory',
            'priceHistory',
            'statistics'
        ));
    }
}
```

---

## 🌐 8. API ENDPOINTS

### API Routes

```php
// routes/api.php

use App\Http\Controllers\Api\PlotApiController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // Plots
    Route::prefix('plots')->group(function () {
        Route::get('/', [PlotApiController::class, 'index'])
            ->middleware('permission:plots.view_all');

        Route::post('/', [PlotApiController::class, 'store'])
            ->middleware('permission:plots.create');

        Route::get('/{plot}', [PlotApiController::class, 'show'])
            ->middleware('permission:plots.view');

        Route::put('/{plot}', [PlotApiController::class, 'update'])
            ->middleware('permission:plots.update');

        Route::delete('/{plot}', [PlotApiController::class, 'destroy'])
            ->middleware('permission:plots.delete');

        // Assignment
        Route::post('/{plot}/assign', [PlotApiController::class, 'assign'])
            ->middleware('permission:plots.assign');

        Route::post('/{plot}/transfer', [PlotApiController::class, 'transfer'])
            ->middleware('permission:plots.assign');

        Route::post('/{plot}/release', [PlotApiController::class, 'release'])
            ->middleware('permission:plots.update');

        Route::post('/{plot}/mark-sold', [PlotApiController::class, 'markAsSold'])
            ->middleware('permission:plots.update');

        // History
        Route::get('/{plot}/history', [PlotApiController::class, 'history'])
            ->middleware('permission:plots.view');
    });

    // Society Statistics
    Route::get('societies/{society}/statistics', [PlotApiController::class, 'societyStatistics'])
        ->middleware('permission:societies.view');
});
```

### API Controller

```php
// app/Http/Controllers/Api/PlotApiController.php

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plot;
use App\Models\Society;
use App\Models\Client;
use App\Services\PlotManagementService;
use App\Services\PlotAssignmentService;
use App\Services\PlotHistoryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PlotApiController extends Controller
{
    public function __construct(
        protected PlotManagementService $plotService,
        protected PlotAssignmentService $assignmentService,
        protected PlotHistoryService $historyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->all();
        $plots = $this->plotService->getPlots($filters);

        return response()->json([
            'success' => true,
            'data' => $plots,
        ]);
    }

    public function show(Plot $plot): JsonResponse
    {
        $plot->load(['society', 'block', 'street', 'currentOwner']);

        return response()->json([
            'success' => true,
            'data' => $plot,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'society_id' => 'required|exists:societies,id',
            'block_id' => 'required|exists:blocks,id',
            'street_id' => 'nullable|exists:streets,id',
            'plot_number' => 'required|string',
            'size_marla' => 'required|numeric|min:0',
            'price_per_marla' => 'required|numeric|min:0',
            'is_corner' => 'boolean',
            'is_park_facing' => 'boolean',
            'map_location' => 'nullable|array',
        ]);

        try {
            $plot = $this->plotService->createPlot($validated);

            return response()->json([
                'success' => true,
                'message' => 'Plot created successfully',
                'data' => $plot,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function assign(Request $request, Plot $plot): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'deal_id' => 'nullable|exists:deals,id',
            'notes' => 'nullable|string',
        ]);

        try {
            $client = Client::findOrFail($validated['client_id']);
            $plot = $this->assignmentService->assignPlot($plot, $client, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Plot assigned successfully',
                'data' => $plot->load('currentOwner'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function history(Plot $plot): JsonResponse
    {
        $data = $this->historyService->exportHistory($plot);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function societyStatistics(Society $society): JsonResponse
    {
        $statistics = $this->plotService->getSocietyStatistics($society);

        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }
}
```

---

## 📊 COMPLETE WORKFLOW DIAGRAM

```
┌─────────────────────────────────────────────────────────────┐
│                    PLOT LIFECYCLE                           │
└─────────────────────────────────────────────────────────────┘

1. CREATE PLOT
   │
   ├─► Status: "available"
   ├─► Owner: NULL
   └─► History: "created" entry added

2. ASSIGN PLOT
   │
   ├─► Observer triggered (PlotObserver::updating)
   ├─► Status: "available" → "booked"
   ├─► Owner: NULL → Client ID
   └─► History: "assigned" entry added

3. PAYMENT PROCESSING
   │
   └─► History: "payment_received" entries added

4. MARK AS SOLD
   │
   ├─► Status: "booked" → "sold"
   ├─► Owner: Remains same
   └─► History: "deal_completed" entry added

5. TRANSFER PLOT (Optional)
   │
   ├─► Status: "sold" → "sold"
   ├─► Owner: Old Client → New Client
   └─► History: "transferred" entry added

6. RELEASE PLOT (Cancel/Rollback)
   │
   ├─► Status: Any → "available"
   ├─► Owner: Any → NULL
   └─► History: "status_changed" entry added
```

---

## ✅ IMPLEMENTATION CHECKLIST

- [ ] Create all migrations (societies, blocks, streets, plots, plot_histories)
- [ ] Create all models with relationships
- [ ] Add `PlotObserver` and register in AppServiceProvider
- [ ] Create `PlotManagementService`
- [ ] Create `PlotAssignmentService`
- [ ] Create `PlotHistoryService`
- [ ] Create controllers (web + API)
- [ ] Add routes (web + API)
- [ ] Add permission checks in middleware
- [ ] Create Blade views
- [ ] Add validation rules
- [ ] Test all workflows
- [ ] Add unit tests

---

**Created**: January 28, 2026
**Laravel Version**: 11.x
**Database**: MySQL 8.0+

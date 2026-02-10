# CRM System - Real Estate Management
## Complete Lead-to-Deal Workflow with Commission Tracking

---

## 📋 Table of Contents
1. [Database Design](#database-design)
2. [Eloquent Models](#eloquent-models)
3. [Status Flow System](#status-flow-system)
4. [Lead Management](#lead-management)
5. [Client Conversion](#client-conversion)
6. [Deal Management](#deal-management)
7. [Follow-up System](#follow-up-system)
8. [Dealer Commission Tracking](#dealer-commission-tracking)
9. [Service Layer](#service-layer)
10. [Complete Workflow](#complete-workflow)

---

## 🗄️ 1. DATABASE DESIGN

### ERD Diagram

```
┌─────────────────────┐
│       dealers       │
│─────────────────────│
│ id                  │
│ user_id (FK)        │
│ commission_rate     │
│ total_commission    │
│ is_active           │
│ cnic, phone         │
│ joining_date        │
└──────┬──────────────┘
       │ 1
       │ has many
       │ *
┌──────▼──────────────────┐          ┌────────────────────┐
│        leads            │          │    lead_sources    │
│─────────────────────────│          │────────────────────│
│ id                      │          │ id                 │
│ name, email, phone      │          │ name               │
│ lead_source_id (FK)     │◄─────────┤ description        │
│ assigned_to (FK dealer) │          └────────────────────┘
│ status (enum)           │
│ interest_type           │ (plot, property, both)
│ budget_min, budget_max  │
│ preferred_location      │
│ priority (enum)         │
│ notes                   │
│ converted_to_client_id  │◄─┐
│ converted_at            │  │
│ timestamps              │  │
└──────┬──────────────────┘  │
       │ 1                   │
       │ has many            │
       │ *                   │
┌──────▼──────────────────┐  │
│      follow_ups         │  │
│─────────────────────────│  │
│ id                      │  │
│ followupable_type       │  │ polymorphic (Lead/Client)
│ followupable_id         │  │
│ dealer_id (FK)          │  │
│ follow_up_date          │  │
│ follow_up_type          │  │ (call, meeting, email, visit)
│ status (enum)           │  │ (scheduled, completed, cancelled)
│ notes, outcome          │  │
│ next_follow_up_date     │  │
│ timestamps              │  │
└─────────────────────────┘  │
                             │
┌──────────────────────────┐ │
│       clients           │◄─┘
│─────────────────────────│
│ id                      │
│ name, email, phone      │
│ cnic, address           │
│ dealer_id (FK)          │
│ is_active               │
│ converted_from_lead_id  │
│ client_type (enum)      │ (buyer, seller, investor)
│ notes                   │
│ timestamps              │
└──────┬──────────────────┘
       │ 1
       │ has many
       │ *
┌──────▼──────────────────────┐
│         deals               │
│─────────────────────────────│
│ id                          │
│ deal_number (unique)        │
│ client_id (FK)              │
│ dealer_id (FK)              │
│ dealable_type               │ polymorphic (Plot/Property)
│ dealable_id                 │
│ deal_type (enum)            │ (sale, purchase, rent)
│ deal_amount                 │
│ commission_amount           │
│ commission_percentage       │
│ status (enum)               │ (pending, approved, completed, cancelled)
│ payment_terms               │
│ notes                       │
│ created_by (FK users)       │
│ approved_by (FK users)      │
│ approved_at                 │
│ completed_at                │
│ timestamps                  │
└──────┬──────────────────────┘
       │ 1
       │ has many
       │ *
┌──────▼──────────────────────┐
│     deal_commissions        │
│─────────────────────────────│
│ id                          │
│ deal_id (FK)                │
│ dealer_id (FK)              │
│ commission_type (enum)      │ (primary, referral, split)
│ commission_percentage       │
│ commission_amount           │
│ payment_status (enum)       │ (pending, paid, cancelled)
│ paid_at                     │
│ payment_reference           │
│ notes                       │
│ timestamps                  │
└─────────────────────────────┘

┌─────────────────────────────┐
│    commission_payments      │
│─────────────────────────────│
│ id                          │
│ dealer_id (FK)              │
│ deal_commission_id (FK)     │
│ amount                      │
│ payment_method              │
│ payment_date                │
│ reference_number            │
│ paid_by (FK users)          │
│ notes                       │
│ timestamps                  │
└─────────────────────────────┘
```

### Complete Migrations

```php
// database/migrations/xxxx_create_dealers_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('cnic')->unique();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->text('address')->nullable();

            $table->decimal('commission_rate', 5, 2)->default(2.00)->comment('Default commission percentage');
            $table->decimal('total_commission_earned', 15, 2)->default(0);
            $table->decimal('total_commission_paid', 15, 2)->default(0);
            $table->decimal('pending_commission', 15, 2)->default(0);

            $table->boolean('is_active')->default(true)->index();
            $table->date('joining_date');
            $table->date('leaving_date')->nullable();

            $table->json('bank_details')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealers');
    }
};
```

```php
// database/migrations/xxxx_create_lead_sources_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_sources');
    }
};
```

```php
// database/migrations/xxxx_create_leads_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Basic info
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->index();
            $table->string('alternate_phone')->nullable();
            $table->text('address')->nullable();

            // Lead details
            $table->foreignId('lead_source_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('dealers')->onDelete('set null');

            $table->enum('status', [
                'new',
                'contacted',
                'qualified',
                'negotiating',
                'converted',
                'lost',
                'cancelled'
            ])->default('new')->index();

            $table->enum('interest_type', ['plot', 'property', 'both'])->default('both');

            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('preferred_location')->nullable();
            $table->string('preferred_size')->nullable();

            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->index();

            // Conversion tracking
            $table->foreignId('converted_to_client_id')->nullable()
                ->constrained('clients')
                ->onDelete('set null');
            $table->timestamp('converted_at')->nullable();

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_to']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
```

```php
// database/migrations/xxxx_create_clients_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->index();
            $table->string('alternate_phone')->nullable();
            $table->string('cnic')->nullable()->unique();
            $table->text('address')->nullable();

            $table->foreignId('dealer_id')->constrained()->onDelete('cascade');

            $table->boolean('is_active')->default(true)->index();

            $table->enum('client_type', ['buyer', 'seller', 'investor', 'tenant'])->default('buyer');

            // Conversion tracking
            $table->foreignId('converted_from_lead_id')->nullable()
                ->constrained('leads')
                ->onDelete('set null');

            $table->text('notes')->nullable();
            $table->json('preferences')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['dealer_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
```

```php
// database/migrations/xxxx_create_follow_ups_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship (Lead or Client)
            $table->string('followupable_type');
            $table->unsignedBigInteger('followupable_id');
            $table->index(['followupable_type', 'followupable_id']);

            $table->foreignId('dealer_id')->constrained()->onDelete('cascade');

            $table->datetime('follow_up_date')->index();
            $table->enum('follow_up_type', ['call', 'meeting', 'email', 'visit', 'whatsapp', 'sms'])
                ->default('call');

            $table->enum('status', ['scheduled', 'completed', 'missed', 'cancelled'])
                ->default('scheduled')
                ->index();

            $table->text('notes')->nullable();
            $table->text('outcome')->nullable();

            $table->datetime('next_follow_up_date')->nullable()->index();
            $table->datetime('completed_at')->nullable();

            $table->timestamps();

            $table->index(['dealer_id', 'follow_up_date']);
            $table->index(['status', 'follow_up_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
```

```php
// database/migrations/xxxx_create_deals_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();

            $table->string('deal_number')->unique()->index();

            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('dealer_id')->constrained()->onDelete('cascade');

            // Polymorphic relationship (Plot or Property)
            $table->string('dealable_type');
            $table->unsignedBigInteger('dealable_id');
            $table->index(['dealable_type', 'dealable_id']);

            $table->enum('deal_type', ['sale', 'purchase', 'rent', 'lease'])->default('sale');

            $table->decimal('deal_amount', 15, 2);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('commission_percentage', 5, 2)->default(0);

            $table->enum('status', [
                'pending',
                'approved',
                'in_progress',
                'completed',
                'cancelled',
                'on_hold'
            ])->default('pending')->index();

            $table->text('payment_terms')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['dealer_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
```

```php
// database/migrations/xxxx_create_deal_commissions_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_commissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('deal_id')->constrained()->onDelete('cascade');
            $table->foreignId('dealer_id')->constrained()->onDelete('cascade');

            $table->enum('commission_type', ['primary', 'referral', 'split', 'bonus'])
                ->default('primary');

            $table->decimal('commission_percentage', 5, 2);
            $table->decimal('commission_amount', 12, 2);

            $table->enum('payment_status', ['pending', 'approved', 'paid', 'cancelled'])
                ->default('pending')
                ->index();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['dealer_id', 'payment_status']);
            $table->index(['deal_id', 'dealer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_commissions');
    }
};
```

```php
// database/migrations/xxxx_create_commission_payments_table.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('dealer_id')->constrained()->onDelete('cascade');
            $table->foreignId('deal_commission_id')->constrained()->onDelete('cascade');

            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'cheque', 'bank_transfer', 'online']);
            $table->date('payment_date')->index();
            $table->string('reference_number')->unique();

            $table->foreignId('paid_by')->constrained('users')->onDelete('cascade');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['dealer_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_payments');
    }
};
```

---

## 🎭 2. ELOQUENT MODELS

### Dealer Model

```php
// app/Models/Dealer.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dealer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'cnic',
        'phone',
        'alternate_phone',
        'address',
        'commission_rate',
        'total_commission_earned',
        'total_commission_paid',
        'pending_commission',
        'is_active',
        'joining_date',
        'leaving_date',
        'bank_details',
        'notes',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_commission_earned' => 'decimal:2',
        'total_commission_paid' => 'decimal:2',
        'pending_commission' => 'decimal:2',
        'is_active' => 'boolean',
        'joining_date' => 'date',
        'leaving_date' => 'date',
        'bank_details' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function commissions()
    {
        return $this->hasMany(DealCommission::class);
    }

    public function commissionPayments()
    {
        return $this->hasMany(CommissionPayment::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTopPerformers($query, $limit = 10)
    {
        return $query->orderBy('total_commission_earned', 'desc')->limit($limit);
    }

    // ==================== ACCESSORS ====================

    public function getFullNameAttribute()
    {
        return $this->user->name;
    }

    public function getPendingCommissionAttribute()
    {
        return $this->total_commission_earned - $this->total_commission_paid;
    }

    // ==================== HELPER METHODS ====================

    public function updateCommissionTotals()
    {
        $this->total_commission_earned = $this->commissions()
            ->whereIn('payment_status', ['approved', 'paid'])
            ->sum('commission_amount');

        $this->total_commission_paid = $this->commissionPayments()->sum('amount');

        $this->pending_commission = $this->total_commission_earned - $this->total_commission_paid;

        $this->save();
    }

    public function getPerformanceStats($startDate = null, $endDate = null)
    {
        $query = $this->deals();

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return [
            'total_deals' => $query->count(),
            'completed_deals' => $query->where('status', 'completed')->count(),
            'pending_deals' => $query->where('status', 'pending')->count(),
            'total_deal_amount' => $query->sum('deal_amount'),
            'total_commission' => $this->commissions()
                ->whereHas('deal', function ($q) use ($startDate, $endDate) {
                    if ($startDate) $q->where('created_at', '>=', $startDate);
                    if ($endDate) $q->where('created_at', '<=', $endDate);
                })
                ->sum('commission_amount'),
            'leads_assigned' => $this->leads()->count(),
            'clients_converted' => $this->clients()->count(),
            'conversion_rate' => $this->calculateConversionRate(),
        ];
    }

    public function calculateConversionRate()
    {
        $totalLeads = $this->leads()->count();
        if ($totalLeads == 0) return 0;

        $convertedLeads = $this->leads()->where('status', 'converted')->count();
        return round(($convertedLeads / $totalLeads) * 100, 2);
    }
}
```

### Lead Model

```php
// app/Models/Lead.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'lead_source_id',
        'assigned_to',
        'status',
        'interest_type',
        'budget_min',
        'budget_max',
        'preferred_location',
        'preferred_size',
        'priority',
        'converted_to_client_id',
        'converted_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'converted_at' => 'datetime',
        'metadata' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class);
    }

    public function assignedDealer()
    {
        return $this->belongsTo(Dealer::class, 'assigned_to');
    }

    public function convertedClient()
    {
        return $this->belongsTo(Client::class, 'converted_to_client_id');
    }

    public function followUps()
    {
        return $this->morphMany(FollowUp::class, 'followupable');
    }

    // ==================== SCOPES ====================

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeContacted($query)
    {
        return $query->where('status', 'contacted');
    }

    public function scopeQualified($query)
    {
        return $query->where('status', 'qualified');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeAssignedTo($query, $dealerId)
    {
        return $query->where('assigned_to', $dealerId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    // ==================== ACCESSORS ====================

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'new' => 'primary',
            'contacted' => 'info',
            'qualified' => 'success',
            'negotiating' => 'warning',
            'converted' => 'success',
            'lost' => 'danger',
            'cancelled' => 'secondary',
            default => 'dark',
        };
    }

    public function getBudgetRangeAttribute()
    {
        if ($this->budget_min && $this->budget_max) {
            return "Rs. " . number_format($this->budget_min) . " - Rs. " . number_format($this->budget_max);
        }
        return "Not specified";
    }

    public function getIsConvertedAttribute()
    {
        return $this->status === 'converted' && $this->converted_to_client_id !== null;
    }

    public function getDaysSinceCreationAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    // ==================== HELPER METHODS ====================

    public function updateStatus($newStatus, $notes = null)
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;

        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . $notes;
        }

        $this->save();

        // Log status change
        activity()
            ->performedOn($this)
            ->withProperties(['old_status' => $oldStatus, 'new_status' => $newStatus])
            ->log('Lead status changed');
    }

    public function assignToDealer(Dealer $dealer)
    {
        $this->assigned_to = $dealer->id;
        $this->save();
    }

    public function getNextFollowUp()
    {
        return $this->followUps()
            ->where('status', 'scheduled')
            ->where('follow_up_date', '>', now())
            ->orderBy('follow_up_date', 'asc')
            ->first();
    }

    public function getLastFollowUp()
    {
        return $this->followUps()
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->first();
    }
}
```

### Client Model

```php
// app/Models/Client.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'alternate_phone',
        'cnic',
        'address',
        'dealer_id',
        'is_active',
        'client_type',
        'converted_from_lead_id',
        'notes',
        'preferences',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'preferences' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function convertedFromLead()
    {
        return $this->belongsTo(Lead::class, 'converted_from_lead_id');
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function propertyFiles()
    {
        return $this->hasMany(PropertyFile::class);
    }

    public function followUps()
    {
        return $this->morphMany(FollowUp::class, 'followupable');
    }

    public function plots()
    {
        return $this->hasMany(Plot::class, 'current_owner_id');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBuyers($query)
    {
        return $query->where('client_type', 'buyer');
    }

    public function scopeOfDealer($query, $dealerId)
    {
        return $query->where('dealer_id', $dealerId);
    }

    // ==================== HELPER METHODS ====================

    public function getTotalInvestment()
    {
        return $this->deals()
            ->where('status', 'completed')
            ->where('deal_type', 'sale')
            ->sum('deal_amount');
    }

    public function getActiveDeals()
    {
        return $this->deals()
            ->whereIn('status', ['pending', 'approved', 'in_progress'])
            ->get();
    }

    public function getCompletedDealsCount()
    {
        return $this->deals()->where('status', 'completed')->count();
    }
}
```

### Deal Model

```php
// app/Models/Deal.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'deal_number',
        'client_id',
        'dealer_id',
        'dealable_type',
        'dealable_id',
        'deal_type',
        'deal_amount',
        'commission_amount',
        'commission_percentage',
        'status',
        'payment_terms',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'deal_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deal) {
            if (empty($deal->deal_number)) {
                $deal->deal_number = self::generateDealNumber();
            }

            if (empty($deal->created_by)) {
                $deal->created_by = auth()->id();
            }
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function dealable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function commissions()
    {
        return $this->hasMany(DealCommission::class);
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOfDealer($query, $dealerId)
    {
        return $query->where('dealer_id', $dealerId);
    }

    // ==================== ACCESSORS ====================

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'on_hold' => 'secondary',
            default => 'dark',
        };
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsApprovedAttribute()
    {
        return in_array($this->status, ['approved', 'in_progress', 'completed']);
    }

    // ==================== HELPER METHODS ====================

    public static function generateDealNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastDeal = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastDeal ? (int) substr($lastDeal->deal_number, -4) + 1 : 1;

        return "DEAL-{$year}{$month}-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function calculateCommission()
    {
        $this->commission_amount = ($this->deal_amount * $this->commission_percentage) / 100;
        $this->save();
    }

    public function approve($approverId, $notes = null)
    {
        $this->status = 'approved';
        $this->approved_by = $approverId;
        $this->approved_at = now();

        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "Approval: " . $notes;
        }

        $this->save();
    }

    public function complete($notes = null)
    {
        $this->status = 'completed';
        $this->completed_at = now();

        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . "Completion: " . $notes;
        }

        $this->save();

        // Mark commissions as approved
        $this->commissions()->update(['payment_status' => 'approved']);
    }
}
```

### FollowUp Model

```php
// app/Models/FollowUp.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'followupable_type',
        'followupable_id',
        'dealer_id',
        'follow_up_date',
        'follow_up_type',
        'status',
        'notes',
        'outcome',
        'next_follow_up_date',
        'completed_at',
    ];

    protected $casts = [
        'follow_up_date' => 'datetime',
        'next_follow_up_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function followupable()
    {
        return $this->morphTo();
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    // ==================== SCOPES ====================

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('follow_up_date', today());
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->whereBetween('follow_up_date', [now(), now()->addDays($days)]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'scheduled')
            ->where('follow_up_date', '<', now());
    }

    // ==================== HELPER METHODS ====================

    public function markAsCompleted($outcome = null, $nextFollowUpDate = null)
    {
        $this->status = 'completed';
        $this->completed_at = now();

        if ($outcome) {
            $this->outcome = $outcome;
        }

        if ($nextFollowUpDate) {
            $this->next_follow_up_date = $nextFollowUpDate;
        }

        $this->save();
    }

    public function markAsMissed()
    {
        $this->status = 'missed';
        $this->save();
    }

    public function reschedule($newDate)
    {
        $this->follow_up_date = $newDate;
        $this->status = 'scheduled';
        $this->save();
    }
}
```

### DealCommission Model

```php
// app/Models/DealCommission.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'dealer_id',
        'commission_type',
        'commission_percentage',
        'commission_amount',
        'payment_status',
        'approved_at',
        'paid_at',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function payment()
    {
        return $this->hasOne(CommissionPayment::class);
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('payment_status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // ==================== HELPER METHODS ====================

    public function approve()
    {
        $this->payment_status = 'approved';
        $this->approved_at = now();
        $this->save();
    }

    public function markAsPaid($paymentReference = null)
    {
        $this->payment_status = 'paid';
        $this->paid_at = now();

        if ($paymentReference) {
            $this->payment_reference = $paymentReference;
        }

        $this->save();

        // Update dealer totals
        $this->dealer->updateCommissionTotals();
    }
}
```

### CommissionPayment Model

```php
// app/Models/CommissionPayment.php

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CommissionPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'dealer_id',
        'deal_commission_id',
        'amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'paid_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ==================== BOOT METHOD ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->reference_number)) {
                $payment->reference_number = self::generateReferenceNumber();
            }
        });

        static::created(function ($payment) {
            // Mark commission as paid
            $payment->commission->markAsPaid($payment->reference_number);
        });
    }

    // ==================== RELATIONSHIPS ====================

    public function dealer()
    {
        return $this->belongsTo(Dealer::class);
    }

    public function commission()
    {
        return $this->belongsTo(DealCommission::class, 'deal_commission_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // ==================== HELPER METHODS ====================

    public static function generateReferenceNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastPayment = self::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastPayment ? (int) substr($lastPayment->reference_number, -5) + 1 : 1;

        return "COM-{$year}{$month}-" . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
```

---

## 🔄 3. STATUS FLOW SYSTEM

### Lead Status Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    LEAD STATUS FLOW                         │
└─────────────────────────────────────────────────────────────┘

NEW (Initial)
  │
  ├─► CONTACTED (First interaction made)
  │     │
  │     ├─► QUALIFIED (Budget & interest confirmed)
  │     │     │
  │     │     ├─► NEGOTIATING (Discussing terms)
  │     │     │     │
  │     │     │     ├─► CONVERTED ✓ (Became client)
  │     │     │     │
  │     │     │     └─► LOST ✗ (Deal fell through)
  │     │     │
  │     │     └─► LOST ✗ (Not qualified)
  │     │
  │     └─► LOST ✗ (No response)
  │
  └─► CANCELLED ✗ (Invalid/Duplicate)

Allowed Transitions:
- new → contacted, cancelled
- contacted → qualified, lost, cancelled
- qualified → negotiating, lost
- negotiating → converted, lost
- converted → (terminal state)
- lost → (terminal state)
- cancelled → (terminal state)
```

### Deal Status Flow

```
┌─────────────────────────────────────────────────────────────┐
│                    DEAL STATUS FLOW                         │
└─────────────────────────────────────────────────────────────┘

PENDING (Initial)
  │
  ├─► APPROVED (Manager approved)
  │     │
  │     ├─► IN_PROGRESS (File/Payment processing)
  │     │     │
  │     │     ├─► COMPLETED ✓ (File created, payment done)
  │     │     │
  │     │     └─► ON_HOLD (Paused temporarily)
  │     │           │
  │     │           └─► IN_PROGRESS (Resumed)
  │     │
  │     └─► CANCELLED ✗ (Deal cancelled)
  │
  ├─► ON_HOLD (Pending documents/approval)
  │     │
  │     └─► PENDING (Resubmitted)
  │
  └─► CANCELLED ✗ (Client withdrew)

Commission Triggers:
- APPROVED → Commission created (status: pending)
- COMPLETED → Commission status: approved
- Commission paid → Commission status: paid
```

### Status Transition Service

```php
// app/Services/StatusTransitionService.php

<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Deal;
use Exception;

class StatusTransitionService
{
    /**
     * Valid lead status transitions
     */
    protected array $leadTransitions = [
        'new' => ['contacted', 'cancelled'],
        'contacted' => ['qualified', 'lost', 'cancelled'],
        'qualified' => ['negotiating', 'lost'],
        'negotiating' => ['converted', 'lost'],
        'converted' => [],
        'lost' => [],
        'cancelled' => [],
    ];

    /**
     * Valid deal status transitions
     */
    protected array $dealTransitions = [
        'pending' => ['approved', 'on_hold', 'cancelled'],
        'approved' => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'on_hold', 'cancelled'],
        'on_hold' => ['pending', 'in_progress', 'cancelled'],
        'completed' => [],
        'cancelled' => [],
    ];

    /**
     * Transition lead status
     */
    public function transitionLead(Lead $lead, string $newStatus, string $notes = null): Lead
    {
        if (!$this->canTransitionLead($lead->status, $newStatus)) {
            throw new Exception(
                "Cannot transition from {$lead->status} to {$newStatus}"
            );
        }

        $lead->updateStatus($newStatus, $notes);

        return $lead;
    }

    /**
     * Transition deal status
     */
    public function transitionDeal(Deal $deal, string $newStatus, array $data = []): Deal
    {
        if (!$this->canTransitionDeal($deal->status, $newStatus)) {
            throw new Exception(
                "Cannot transition from {$deal->status} to {$newStatus}"
            );
        }

        $oldStatus = $deal->status;
        $deal->status = $newStatus;

        // Handle specific transitions
        if ($newStatus === 'approved' && $oldStatus === 'pending') {
            $deal->approve(auth()->id(), $data['notes'] ?? null);
        }

        if ($newStatus === 'completed') {
            $deal->complete($data['notes'] ?? null);
        }

        $deal->save();

        return $deal;
    }

    /**
     * Check if lead transition is valid
     */
    public function canTransitionLead(string $from, string $to): bool
    {
        return in_array($to, $this->leadTransitions[$from] ?? []);
    }

    /**
     * Check if deal transition is valid
     */
    public function canTransitionDeal(string $from, string $to): bool
    {
        return in_array($to, $this->dealTransitions[$from] ?? []);
    }

    /**
     * Get available lead transitions
     */
    public function getAvailableLeadTransitions(string $currentStatus): array
    {
        return $this->leadTransitions[$currentStatus] ?? [];
    }

    /**
     * Get available deal transitions
     */
    public function getAvailableDealTransitions(string $currentStatus): array
    {
        return $this->dealTransitions[$currentStatus] ?? [];
    }
}
```

---

## 📞 4. LEAD MANAGEMENT SERVICE

```php
// app/Services/LeadManagementService.php

<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Dealer;
use App\Models\FollowUp;
use Illuminate\Support\Facades\DB;
use Exception;

class LeadManagementService
{
    /**
     * Create new lead
     */
    public function createLead(array $data): Lead
    {
        DB::beginTransaction();

        try {
            $lead = Lead::create($data);

            // Auto-assign to dealer if specified
            if (isset($data['assigned_to'])) {
                $this->assignLeadToDealer($lead, $data['assigned_to']);
            }

            // Schedule initial follow-up
            if (isset($data['schedule_follow_up']) && $data['schedule_follow_up']) {
                $this->scheduleFollowUp($lead, [
                    'dealer_id' => $data['assigned_to'] ?? null,
                    'follow_up_date' => $data['follow_up_date'] ?? now()->addDay(),
                    'follow_up_type' => $data['follow_up_type'] ?? 'call',
                    'notes' => 'Initial follow-up',
                ]);
            }

            DB::commit();
            return $lead;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Assign lead to dealer
     */
    public function assignLeadToDealer(Lead $lead, int $dealerId): Lead
    {
        $dealer = Dealer::active()->findOrFail($dealerId);

        $lead->assignToDealer($dealer);

        // Update status to contacted if still new
        if ($lead->status === 'new') {
            $lead->updateStatus('contacted', 'Lead assigned to ' . $dealer->full_name);
        }

        return $lead->fresh();
    }

    /**
     * Bulk assign leads to dealers
     */
    public function bulkAssignLeads(array $leadIds, int $dealerId): int
    {
        $dealer = Dealer::active()->findOrFail($dealerId);

        $count = Lead::whereIn('id', $leadIds)
            ->whereIn('status', ['new', 'contacted'])
            ->update(['assigned_to' => $dealerId]);

        return $count;
    }

    /**
     * Reassign lead from one dealer to another
     */
    public function reassignLead(Lead $lead, int $newDealerId, string $reason = null): Lead
    {
        $oldDealer = $lead->assignedDealer;
        $newDealer = Dealer::active()->findOrFail($newDealerId);

        $lead->assigned_to = $newDealerId;

        $notes = "Reassigned from {$oldDealer->full_name} to {$newDealer->full_name}";
        if ($reason) {
            $notes .= " - Reason: {$reason}";
        }

        $lead->notes = ($lead->notes ? $lead->notes . "\n\n" : '') . $notes;
        $lead->save();

        return $lead;
    }

    /**
     * Schedule follow-up
     */
    public function scheduleFollowUp(Lead $lead, array $data): FollowUp
    {
        return $lead->followUps()->create([
            'dealer_id' => $data['dealer_id'] ?? $lead->assigned_to,
            'follow_up_date' => $data['follow_up_date'],
            'follow_up_type' => $data['follow_up_type'] ?? 'call',
            'notes' => $data['notes'] ?? null,
            'status' => 'scheduled',
        ]);
    }

    /**
     * Complete follow-up and schedule next
     */
    public function completeFollowUp(FollowUp $followUp, array $data): FollowUp
    {
        $followUp->markAsCompleted(
            $data['outcome'] ?? null,
            $data['next_follow_up_date'] ?? null
        );

        // Create next follow-up if date provided
        if (isset($data['next_follow_up_date'])) {
            $this->scheduleFollowUp($followUp->followupable, [
                'dealer_id' => $followUp->dealer_id,
                'follow_up_date' => $data['next_follow_up_date'],
                'follow_up_type' => $data['next_follow_up_type'] ?? 'call',
                'notes' => $data['next_notes'] ?? null,
            ]);
        }

        return $followUp;
    }

    /**
     * Mark leads as lost
     */
    public function markAsLost(Lead $lead, string $reason): Lead
    {
        $lead->updateStatus('lost', "Lost reason: {$reason}");

        // Cancel scheduled follow-ups
        $lead->followUps()->where('status', 'scheduled')->update(['status' => 'cancelled']);

        return $lead;
    }

    /**
     * Get dealer's lead dashboard
     */
    public function getDealerDashboard(int $dealerId): array
    {
        $leads = Lead::where('assigned_to', $dealerId);

        return [
            'total_leads' => $leads->count(),
            'new_leads' => $leads->where('status', 'new')->count(),
            'contacted' => $leads->where('status', 'contacted')->count(),
            'qualified' => $leads->where('status', 'qualified')->count(),
            'converted' => $leads->where('status', 'converted')->count(),
            'lost' => $leads->where('status', 'lost')->count(),
            'today_follow_ups' => FollowUp::where('dealer_id', $dealerId)
                ->scheduled()
                ->today()
                ->count(),
            'overdue_follow_ups' => FollowUp::where('dealer_id', $dealerId)
                ->overdue()
                ->count(),
            'conversion_rate' => $this->calculateConversionRate($dealerId),
        ];
    }

    /**
     * Calculate conversion rate
     */
    protected function calculateConversionRate(int $dealerId): float
    {
        $totalLeads = Lead::where('assigned_to', $dealerId)->count();
        if ($totalLeads == 0) return 0;

        $converted = Lead::where('assigned_to', $dealerId)
            ->where('status', 'converted')
            ->count();

        return round(($converted / $totalLeads) * 100, 2);
    }

    /**
     * Get overdue follow-ups
     */
    public function getOverdueFollowUps(int $dealerId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = FollowUp::overdue()->with(['followupable', 'dealer']);

        if ($dealerId) {
            $query->where('dealer_id', $dealerId);
        }

        return $query->orderBy('follow_up_date', 'asc')->get();
    }

    /**
     * Get today's follow-ups
     */
    public function getTodayFollowUps(int $dealerId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = FollowUp::today()->scheduled()->with(['followupable', 'dealer']);

        if ($dealerId) {
            $query->where('dealer_id', $dealerId);
        }

        return $query->orderBy('follow_up_date', 'asc')->get();
    }
}
```

---

## 🔄 5. CLIENT CONVERSION SERVICE

```php
// app/Services/ClientConversionService.php

<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Exception;

class ClientConversionService
{
    /**
     * Convert lead to client
     */
    public function convertLeadToClient(Lead $lead, array $additionalData = []): Client
    {
        if ($lead->status === 'converted') {
            throw new Exception('Lead is already converted to client');
        }

        if (!$lead->assigned_to) {
            throw new Exception('Lead must be assigned to a dealer before conversion');
        }

        DB::beginTransaction();

        try {
            // Create client
            $client = Client::create([
                'name' => $additionalData['name'] ?? $lead->name,
                'email' => $additionalData['email'] ?? $lead->email,
                'phone' => $additionalData['phone'] ?? $lead->phone,
                'alternate_phone' => $additionalData['alternate_phone'] ?? $lead->alternate_phone,
                'cnic' => $additionalData['cnic'] ?? null,
                'address' => $additionalData['address'] ?? $lead->address,
                'dealer_id' => $lead->assigned_to,
                'is_active' => true,
                'client_type' => $additionalData['client_type'] ?? 'buyer',
                'converted_from_lead_id' => $lead->id,
                'notes' => $additionalData['notes'] ?? $lead->notes,
                'preferences' => [
                    'interest_type' => $lead->interest_type,
                    'budget_min' => $lead->budget_min,
                    'budget_max' => $lead->budget_max,
                    'preferred_location' => $lead->preferred_location,
                    'preferred_size' => $lead->preferred_size,
                ],
            ]);

            // Update lead
            $lead->status = 'converted';
            $lead->converted_to_client_id = $client->id;
            $lead->converted_at = now();
            $lead->save();

            // Transfer follow-ups to client
            $this->transferFollowUps($lead, $client);

            DB::commit();

            return $client;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Transfer follow-ups from lead to client
     */
    protected function transferFollowUps(Lead $lead, Client $client): void
    {
        $scheduledFollowUps = $lead->followUps()
            ->where('status', 'scheduled')
            ->get();

        foreach ($scheduledFollowUps as $followUp) {
            $client->followUps()->create([
                'dealer_id' => $followUp->dealer_id,
                'follow_up_date' => $followUp->follow_up_date,
                'follow_up_type' => $followUp->follow_up_type,
                'notes' => $followUp->notes . ' (Transferred from lead)',
                'status' => 'scheduled',
            ]);

            $followUp->update(['status' => 'cancelled']);
        }
    }

    /**
     * Validate lead is ready for conversion
     */
    public function validateForConversion(Lead $lead): array
    {
        $errors = [];

        if (!$lead->assigned_to) {
            $errors[] = 'Lead must be assigned to a dealer';
        }

        if ($lead->status === 'converted') {
            $errors[] = 'Lead is already converted';
        }

        if (!in_array($lead->status, ['qualified', 'negotiating'])) {
            $errors[] = 'Lead must be qualified or in negotiation stage';
        }

        if (!$lead->phone) {
            $errors[] = 'Phone number is required';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get conversion statistics
     */
    public function getConversionStats($startDate = null, $endDate = null): array
    {
        $query = Lead::where('status', 'converted');

        if ($startDate) {
            $query->where('converted_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('converted_at', '<=', $endDate);
        }

        $totalLeads = Lead::query();
        if ($startDate) $totalLeads->where('created_at', '>=', $startDate);
        if ($endDate) $totalLeads->where('created_at', '<=', $endDate);

        $totalCount = $totalLeads->count();
        $convertedCount = $query->count();

        return [
            'total_leads' => $totalCount,
            'converted_leads' => $convertedCount,
            'conversion_rate' => $totalCount > 0 ? round(($convertedCount / $totalCount) * 100, 2) : 0,
            'by_dealer' => $this->getConversionsByDealer($startDate, $endDate),
            'by_source' => $this->getConversionsBySource($startDate, $endDate),
            'avg_conversion_time' => $this->getAverageConversionTime($startDate, $endDate),
        ];
    }

    /**
     * Get conversions by dealer
     */
    protected function getConversionsByDealer($startDate, $endDate): array
    {
        $query = Lead::where('status', 'converted')
            ->selectRaw('assigned_to, COUNT(*) as count')
            ->groupBy('assigned_to');

        if ($startDate) $query->where('converted_at', '>=', $startDate);
        if ($endDate) $query->where('converted_at', '<=', $endDate);

        return $query->with('assignedDealer:id,user_id')
            ->get()
            ->map(function ($item) {
                return [
                    'dealer_id' => $item->assigned_to,
                    'dealer_name' => $item->assignedDealer->full_name ?? 'Unknown',
                    'conversions' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get conversions by source
     */
    protected function getConversionsBySource($startDate, $endDate): array
    {
        $query = Lead::where('status', 'converted')
            ->selectRaw('lead_source_id, COUNT(*) as count')
            ->groupBy('lead_source_id');

        if ($startDate) $query->where('converted_at', '>=', $startDate);
        if ($endDate) $query->where('converted_at', '<=', $endDate);

        return $query->with('leadSource:id,name')
            ->get()
            ->map(function ($item) {
                return [
                    'source_id' => $item->lead_source_id,
                    'source_name' => $item->leadSource->name ?? 'Unknown',
                    'conversions' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get average conversion time (in days)
     */
    protected function getAverageConversionTime($startDate, $endDate): float
    {
        $query = Lead::where('status', 'converted')
            ->whereNotNull('converted_at');

        if ($startDate) $query->where('converted_at', '>=', $startDate);
        if ($endDate) $query->where('converted_at', '<=', $endDate);

        $leads = $query->get();

        if ($leads->isEmpty()) return 0;

        $totalDays = $leads->sum(function ($lead) {
            return $lead->created_at->diffInDays($lead->converted_at);
        });

        return round($totalDays / $leads->count(), 2);
    }
}
```

---

## 💼 6. DEAL MANAGEMENT SERVICE

```php
// app/Services/DealManagementService.php

<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Client;
use App\Models\Plot;
use Illuminate\Support\Facades\DB;
use Exception;

class DealManagementService
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    /**
     * Create new deal
     */
    public function createDeal(array $data): Deal
    {
        DB::beginTransaction();

        try {
            // Validate property is available
            $this->validatePropertyAvailability($data['dealable_type'], $data['dealable_id']);

            $deal = Deal::create([
                'client_id' => $data['client_id'],
                'dealer_id' => $data['dealer_id'],
                'dealable_type' => $data['dealable_type'],
                'dealable_id' => $data['dealable_id'],
                'deal_type' => $data['deal_type'] ?? 'sale',
                'deal_amount' => $data['deal_amount'],
                'commission_percentage' => $data['commission_percentage'] ?? $this->getDefaultCommissionRate($data['dealer_id']),
                'payment_terms' => $data['payment_terms'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'created_by' => auth()->id(),
            ]);

            // Calculate commission
            $deal->calculateCommission();

            // Create primary commission for dealer
            $this->commissionService->createDealerCommission($deal, $deal->dealer_id, 'primary');

            // Handle split commissions if any
            if (isset($data['split_commissions']) && is_array($data['split_commissions'])) {
                foreach ($data['split_commissions'] as $split) {
                    $this->commissionService->createSplitCommission(
                        $deal,
                        $split['dealer_id'],
                        $split['percentage']
                    );
                }
            }

            DB::commit();

            return $deal;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve deal
     */
    public function approveDeal(Deal $deal, array $data = []): Deal
    {
        DB::beginTransaction();

        try {
            $deal->approve(auth()->id(), $data['notes'] ?? null);

            // Update property status to booked
            $this->updatePropertyStatus($deal->dealable, 'booked');

            // Approve commissions
            $deal->commissions()->update(['payment_status' => 'approved']);

            DB::commit();

            return $deal->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Complete deal
     */
    public function completeDeal(Deal $deal, array $data = []): Deal
    {
        if ($deal->status !== 'approved' && $deal->status !== 'in_progress') {
            throw new Exception('Only approved or in-progress deals can be completed');
        }

        DB::beginTransaction();

        try {
            $deal->complete($data['notes'] ?? null);

            // Update property status to sold
            $this->updatePropertyStatus($deal->dealable, 'sold');

            // Assign property to client if plot
            if ($deal->dealable_type === Plot::class) {
                $plot = $deal->dealable;
                $plot->current_owner_id = $deal->client_id;
                $plot->save();
            }

            DB::commit();

            return $deal->fresh();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancel deal
     */
    public function cancelDeal(Deal $deal, string $reason): Deal
    {
        DB::beginTransaction();

        try {
            $deal->status = 'cancelled';
            $deal->notes = ($deal->notes ? $deal->notes . "\n\n" : '') . "Cancellation: {$reason}";
            $deal->save();

            // Release property
            $this->updatePropertyStatus($deal->dealable, 'available');

            // Cancel commissions
            $deal->commissions()->update(['payment_status' => 'cancelled']);

            DB::commit();

            return $deal;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Validate property availability
     */
    protected function validatePropertyAvailability(string $type, int $id): void
    {
        if ($type === Plot::class) {
            $plot = Plot::findOrFail($id);
            if ($plot->status !== 'available') {
                throw new Exception('Plot is not available for sale');
            }
        }
        // Add other property type validations here
    }

    /**
     * Update property status
     */
    protected function updatePropertyStatus($property, string $status): void
    {
        if ($property instanceof Plot) {
            $property->status = $status;
            $property->save();
        }
        // Handle other property types
    }

    /**
     * Get default commission rate for dealer
     */
    protected function getDefaultCommissionRate(int $dealerId): float
    {
        $dealer = \App\Models\Dealer::find($dealerId);
        return $dealer->commission_rate ?? 2.00;
    }

    /**
     * Get deal statistics
     */
    public function getDealStatistics(array $filters = []): array
    {
        $query = Deal::query();

        if (isset($filters['dealer_id'])) {
            $query->where('dealer_id', $filters['dealer_id']);
        }

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return [
            'total_deals' => $query->count(),
            'pending_deals' => (clone $query)->where('status', 'pending')->count(),
            'approved_deals' => (clone $query)->where('status', 'approved')->count(),
            'completed_deals' => (clone $query)->where('status', 'completed')->count(),
            'cancelled_deals' => (clone $query)->where('status', 'cancelled')->count(),
            'total_deal_value' => (clone $query)->where('status', 'completed')->sum('deal_amount'),
            'total_commission' => (clone $query)->where('status', 'completed')->sum('commission_amount'),
            'avg_deal_value' => (clone $query)->where('status', 'completed')->avg('deal_amount'),
            'by_type' => $this->getDealsByType($filters),
        ];
    }

    /**
     * Get deals by type
     */
    protected function getDealsByType(array $filters): array
    {
        $query = Deal::selectRaw('deal_type, COUNT(*) as count, SUM(deal_amount) as total_value')
            ->groupBy('deal_type');

        if (isset($filters['dealer_id'])) {
            $query->where('dealer_id', $filters['dealer_id']);
        }

        if (isset($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->get()->toArray();
    }
}
```

---

## 💰 7. DEALER COMMISSION TRACKING SERVICE

```php
// app/Services/CommissionService.php

<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\DealCommission;
use App\Models\CommissionPayment;
use App\Models\Dealer;
use Illuminate\Support\Facades\DB;
use Exception;

class CommissionService
{
    /**
     * Create dealer commission
     */
    public function createDealerCommission(Deal $deal, int $dealerId, string $type = 'primary'): DealCommission
    {
        $dealer = Dealer::findOrFail($dealerId);

        $percentage = $type === 'primary' ? $deal->commission_percentage : 0;
        $amount = $type === 'primary' ? $deal->commission_amount : 0;

        return DealCommission::create([
            'deal_id' => $deal->id,
            'dealer_id' => $dealerId,
            'commission_type' => $type,
            'commission_percentage' => $percentage,
            'commission_amount' => $amount,
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Create split commission
     */
    public function createSplitCommission(Deal $deal, int $dealerId, float $percentage): DealCommission
    {
        $amount = ($deal->deal_amount * $percentage) / 100;

        return DealCommission::create([
            'deal_id' => $deal->id,
            'dealer_id' => $dealerId,
            'commission_type' => 'split',
            'commission_percentage' => $percentage,
            'commission_amount' => $amount,
            'payment_status' => 'pending',
        ]);
    }

    /**
     * Pay commission
     */
    public function payCommission(DealCommission $commission, array $data): CommissionPayment
    {
        if ($commission->payment_status === 'paid') {
            throw new Exception('Commission is already paid');
        }

        if ($commission->payment_status !== 'approved') {
            throw new Exception('Commission must be approved before payment');
        }

        DB::beginTransaction();

        try {
            $payment = CommissionPayment::create([
                'dealer_id' => $commission->dealer_id,
                'deal_commission_id' => $commission->id,
                'amount' => $data['amount'] ?? $commission->commission_amount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'] ?? now(),
                'paid_by' => auth()->id(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Update dealer totals
            $commission->dealer->updateCommissionTotals();

            DB::commit();

            return $payment;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get dealer commission summary
     */
    public function getDealerCommissionSummary(int $dealerId, $startDate = null, $endDate = null): array
    {
        $query = DealCommission::where('dealer_id', $dealerId);

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $commissions = $query->get();

        return [
            'total_earned' => $commissions->whereIn('payment_status', ['approved', 'paid'])->sum('commission_amount'),
            'total_paid' => $commissions->where('payment_status', 'paid')->sum('commission_amount'),
            'pending_approval' => $commissions->where('payment_status', 'pending')->sum('commission_amount'),
            'approved_unpaid' => $commissions->where('payment_status', 'approved')->sum('commission_amount'),
            'total_commissions' => $commissions->count(),
            'paid_commissions' => $commissions->where('payment_status', 'paid')->count(),
            'pending_commissions' => $commissions->where('payment_status', 'pending')->count(),
            'by_type' => $this->getCommissionsByType($dealerId, $startDate, $endDate),
            'recent_payments' => $this->getRecentPayments($dealerId, 5),
        ];
    }

    /**
     * Get commissions by type
     */
    protected function getCommissionsByType(int $dealerId, $startDate, $endDate): array
    {
        $query = DealCommission::where('dealer_id', $dealerId)
            ->selectRaw('commission_type, COUNT(*) as count, SUM(commission_amount) as total')
            ->groupBy('commission_type');

        if ($startDate) $query->where('created_at', '>=', $startDate);
        if ($endDate) $query->where('created_at', '<=', $endDate);

        return $query->get()->toArray();
    }

    /**
     * Get recent payments
     */
    protected function getRecentPayments(int $dealerId, int $limit = 5): array
    {
        return CommissionPayment::where('dealer_id', $dealerId)
            ->with('commission.deal')
            ->orderBy('payment_date', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get pending commissions for approval
     */
    public function getPendingCommissions(int $dealerId = null)
    {
        $query = DealCommission::where('payment_status', 'pending')
            ->with(['deal', 'dealer.user']);

        if ($dealerId) {
            $query->where('dealer_id', $dealerId);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    /**
     * Bulk approve commissions
     */
    public function bulkApproveCommissions(array $commissionIds): int
    {
        return DealCommission::whereIn('id', $commissionIds)
            ->where('payment_status', 'pending')
            ->update([
                'payment_status' => 'approved',
                'approved_at' => now(),
            ]);
    }

    /**
     * Generate commission report
     */
    public function generateCommissionReport($startDate, $endDate): array
    {
        $commissions = DealCommission::whereBetween('created_at', [$startDate, $endDate])
            ->with(['dealer.user', 'deal'])
            ->get();

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'total_commissions' => $commissions->sum('commission_amount'),
            'total_paid' => $commissions->where('payment_status', 'paid')->sum('commission_amount'),
            'total_pending' => $commissions->where('payment_status', 'pending')->sum('commission_amount'),
            'total_approved' => $commissions->where('payment_status', 'approved')->sum('commission_amount'),
            'by_dealer' => $this->groupCommissionsByDealer($commissions),
            'by_type' => $commissions->groupBy('commission_type')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'total' => $items->sum('commission_amount'),
                ];
            }),
        ];
    }

    /**
     * Group commissions by dealer
     */
    protected function groupCommissionsByDealer($commissions): array
    {
        return $commissions->groupBy('dealer_id')->map(function ($items) {
            $dealer = $items->first()->dealer;
            return [
                'dealer_name' => $dealer->full_name,
                'total_earned' => $items->sum('commission_amount'),
                'total_paid' => $items->where('payment_status', 'paid')->sum('commission_amount'),
                'pending' => $items->where('payment_status', 'pending')->sum('commission_amount'),
                'commission_count' => $items->count(),
            ];
        })->values()->toArray();
    }
}
```

---

## 🔄 8. COMPLETE WORKFLOW

### Full CRM Flow Diagram

```
┌───────────────────────────────────────────────────────────────┐
│                    COMPLETE CRM WORKFLOW                      │
└───────────────────────────────────────────────────────────────┘

PHASE 1: LEAD GENERATION
────────────────────────
Lead Created (new)
  │
  ├─→ Assigned to Dealer
  │     └─→ Follow-up Scheduled
  │
  └─→ Status: NEW → CONTACTED

PHASE 2: LEAD NURTURING
────────────────────────
Follow-up Calls/Meetings
  │
  ├─→ Qualified (budget confirmed)
  │     └─→ Multiple Follow-ups
  │           │
  │           ├─→ Status: CONTACTED → QUALIFIED
  │           │
  │           └─→ Property Shown
  │                 └─→ Status: QUALIFIED → NEGOTIATING

PHASE 3: CLIENT CONVERSION
────────────────────────────
Lead Converted
  │
  ├─→ Client Created
  │     ├─→ CNIC collected
  │     ├─→ Address verified
  │     └─→ Client Type set
  │
  ├─→ Status: NEGOTIATING → CONVERTED
  │
  └─→ Follow-ups transferred to Client

PHASE 4: DEAL CREATION
────────────────────────
Deal Initiated
  │
  ├─→ Property Selected (Plot/Property)
  │     └─→ Property Status: available → booked
  │
  ├─→ Deal Amount finalized
  │
  ├─→ Commission Calculated (2-5%)
  │
  ├─→ Deal Status: PENDING
  │
  └─→ Commission Created (status: pending)

PHASE 5: DEAL APPROVAL
────────────────────────
Manager Review
  │
  ├─→ Deal Approved
  │     ├─→ Status: PENDING → APPROVED
  │     ├─→ Commission: pending → approved
  │     └─→ Approved by: Manager ID
  │
  └─→ Status: APPROVED → IN_PROGRESS

PHASE 6: PROPERTY FILE CREATION
──────────────────────────────────
File Management System
  │
  ├─→ Property File Created
  │     ├─→ File Number: FILE-202601-0001
  │     ├─→ Total Price
  │     ├─→ Down Payment
  │     └─→ Installment Plan
  │
  ├─→ Installments Auto-generated
  │     └─→ Monthly/Quarterly schedule
  │
  └─→ Plot Assigned to Client
        └─→ Plot Status: booked → sold

PHASE 7: DEAL COMPLETION
────────────────────────
Deal Finalized
  │
  ├─→ Status: IN_PROGRESS → COMPLETED
  │
  ├─→ Property Ownership Transferred
  │
  └─→ Commission Payment Initiated

PHASE 8: COMMISSION PAYMENT
────────────────────────────
Commission Processing
  │
  ├─→ Commission Status: approved → paid
  │
  ├─→ Payment Record Created
  │     ├─→ Reference: COM-202601-00001
  │     ├─→ Payment Method
  │     └─→ Payment Date
  │
  └─→ Dealer Totals Updated
        ├─→ Total Earned
        ├─→ Total Paid
        └─→ Pending Commission
```

### Step-by-Step Implementation

```php
// Example: Complete workflow from lead to payment

use App\Services\LeadManagementService;
use App\Services\ClientConversionService;
use App\Services\DealManagementService;
use App\Services\CommissionService;
use App\Services\FileManagementService;

// STEP 1: Create Lead
$leadService = app(LeadManagementService::class);
$lead = $leadService->createLead([
    'name' => 'Ahmed Ali',
    'email' => 'ahmed@example.com',
    'phone' => '03001234567',
    'lead_source_id' => 1,
    'assigned_to' => 1, // Dealer ID
    'interest_type' => 'plot',
    'budget_min' => 5000000,
    'budget_max' => 7000000,
    'preferred_location' => 'Bahria Town',
    'priority' => 'high',
    'schedule_follow_up' => true,
    'follow_up_date' => now()->addDay(),
]);

// STEP 2: Follow-ups
$followUp = $leadService->scheduleFollowUp($lead, [
    'dealer_id' => 1,
    'follow_up_date' => now()->addDays(2),
    'follow_up_type' => 'meeting',
    'notes' => 'Show properties in Bahria Town',
]);

// Complete follow-up
$leadService->completeFollowUp($followUp, [
    'outcome' => 'Client interested in 10 marla plot',
    'next_follow_up_date' => now()->addDays(3),
    'next_follow_up_type' => 'visit',
]);

// STEP 3: Update lead status
$statusService = app(StatusTransitionService::class);
$statusService->transitionLead($lead, 'contacted');
$statusService->transitionLead($lead, 'qualified');
$statusService->transitionLead($lead, 'negotiating');

// STEP 4: Convert to Client
$conversionService = app(ClientConversionService::class);
$client = $conversionService->convertLeadToClient($lead, [
    'cnic' => '12345-1234567-1',
    'address' => 'House 123, Street 4, Islamabad',
    'client_type' => 'buyer',
]);

// STEP 5: Create Deal
$dealService = app(DealManagementService::class);
$deal = $dealService->createDeal([
    'client_id' => $client->id,
    'dealer_id' => 1,
    'dealable_type' => \App\Models\Plot::class,
    'dealable_id' => 25, // Plot ID
    'deal_type' => 'sale',
    'deal_amount' => 6000000,
    'commission_percentage' => 2.5,
    'payment_terms' => '30% down, 70% in 24 monthly installments',
]);

// STEP 6: Approve Deal (by Manager)
$deal = $dealService->approveDeal($deal, [
    'notes' => 'Approved after document verification',
]);

// STEP 7: Create Property File
$fileService = app(FileManagementService::class);
$propertyFile = $fileService->createFile([
    'client_id' => $client->id,
    'fileable_type' => \App\Models\Plot::class,
    'fileable_id' => 25,
    'total_price' => 6000000,
    'down_payment' => 1800000,
    'installment_frequency' => 'monthly',
    'installment_count' => 24,
    'installment_amount' => 175000,
    'start_date' => now()->addMonth(),
]);

// STEP 8: Complete Deal
$deal = $dealService->completeDeal($deal, [
    'notes' => 'All documents signed, file created',
]);

// STEP 9: Pay Commission
$commissionService = app(CommissionService::class);
$commission = $deal->commissions()->first();
$payment = $commissionService->payCommission($commission, [
    'amount' => $commission->commission_amount,
    'payment_method' => 'bank_transfer',
    'payment_date' => now(),
    'notes' => 'Commission paid for Deal #' . $deal->deal_number,
]);

// DONE! Complete workflow executed
```

---

## 🎮 9. CONTROLLER IMPLEMENTATION

### LeadController

```php
// app/Http/Controllers/LeadController.php

<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Services\LeadManagementService;
use App\Services\StatusTransitionService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    protected LeadManagementService $leadService;
    protected StatusTransitionService $statusService;

    public function __construct(
        LeadManagementService $leadService,
        StatusTransitionService $statusService
    ) {
        $this->leadService = $leadService;
        $this->statusService = $statusService;
    }

    public function index(Request $request)
    {
        $query = Lead::with(['leadSource', 'assignedDealer.user']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $leads = $query->latest()->paginate(20);

        return view('leads.index', compact('leads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'assigned_to' => 'nullable|exists:dealers,id',
            'interest_type' => 'required|in:plot,property,both',
            'budget_min' => 'nullable|numeric',
            'budget_max' => 'nullable|numeric|gte:budget_min',
            'preferred_location' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $lead = $this->leadService->createLead($validated);

        return redirect()
            ->route('leads.show', $lead)
            ->with('success', 'Lead created successfully');
    }

    public function assignDealer(Request $request, Lead $lead)
    {
        $request->validate([
            'dealer_id' => 'required|exists:dealers,id',
        ]);

        $this->leadService->assignLeadToDealer($lead, $request->dealer_id);

        return back()->with('success', 'Lead assigned successfully');
    }

    public function updateStatus(Request $request, Lead $lead)
    {
        $request->validate([
            'status' => 'required|in:new,contacted,qualified,negotiating,converted,lost,cancelled',
            'notes' => 'nullable|string',
        ]);

        $this->statusService->transitionLead($lead, $request->status, $request->notes);

        return back()->with('success', 'Status updated successfully');
    }

    public function scheduleFollowUp(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'follow_up_date' => 'required|date',
            'follow_up_type' => 'required|in:call,meeting,email,visit,whatsapp,sms',
            'notes' => 'nullable|string',
        ]);

        $validated['dealer_id'] = $lead->assigned_to;

        $this->leadService->scheduleFollowUp($lead, $validated);

        return back()->with('success', 'Follow-up scheduled successfully');
    }
}
```

### DealController

```php
// app/Http/Controllers/DealController.php

<?php

namespace App\Http/Controllers;

use App\Models\Deal;
use App\Services\DealManagementService;
use Illuminate\Http\Request;

class DealController extends Controller
{
    protected DealManagementService $dealService;

    public function __construct(DealManagementService $dealService)
    {
        $this->dealService = $dealService;
    }

    public function index(Request $request)
    {
        $query = Deal::with(['client', 'dealer.user', 'dealable']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('dealer_id')) {
            $query->where('dealer_id', $request->dealer_id);
        }

        if (auth()->user()->hasRole('Dealer/Agent')) {
            $query->where('dealer_id', auth()->user()->dealer->id);
        }

        $deals = $query->latest()->paginate(20);

        return view('deals.index', compact('deals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'dealer_id' => 'required|exists:dealers,id',
            'dealable_type' => 'required|string',
            'dealable_id' => 'required|integer',
            'deal_type' => 'required|in:sale,purchase,rent,lease',
            'deal_amount' => 'required|numeric|min:0',
            'commission_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_terms' => 'nullable|string',
        ]);

        $deal = $this->dealService->createDeal($validated);

        return redirect()
            ->route('deals.show', $deal)
            ->with('success', 'Deal created successfully');
    }

    public function approve(Request $request, Deal $deal)
    {
        $this->authorize('approve', $deal);

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $this->dealService->approveDeal($deal, $validated);

        return back()->with('success', 'Deal approved successfully');
    }

    public function complete(Request $request, Deal $deal)
    {
        $this->authorize('complete', $deal);

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $this->dealService->completeDeal($deal, $validated);

        return back()->with('success', 'Deal completed successfully');
    }

    public function cancel(Request $request, Deal $deal)
    {
        $this->authorize('cancel', $deal);

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $this->dealService->cancelDeal($deal, $validated['reason']);

        return back()->with('success', 'Deal cancelled');
    }
}
```

### CommissionController

```php
// app/Http/Controllers/CommissionController.php

<?php

namespace App\Http/Controllers;

use App\Models\DealCommission;
use App\Services\CommissionService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    protected CommissionService $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $query = DealCommission::with(['deal', 'dealer.user']);

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('dealer_id')) {
            $query->where('dealer_id', $request->dealer_id);
        }

        $commissions = $query->latest()->paginate(20);

        return view('commissions.index', compact('commissions'));
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            'commission_ids' => 'required|array',
            'commission_ids.*' => 'exists:deal_commissions,id',
        ]);

        $count = $this->commissionService->bulkApproveCommissions($validated['commission_ids']);

        return back()->with('success', "{$count} commissions approved");
    }

    public function pay(Request $request, DealCommission $commission)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,cheque,bank_transfer,online',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $payment = $this->commissionService->payCommission($commission, $validated);

        return back()->with('success', 'Commission paid successfully');
    }

    public function dealerSummary(Request $request, $dealerId)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $summary = $this->commissionService->getDealerCommissionSummary(
            $dealerId,
            $startDate,
            $endDate
        );

        return view('commissions.dealer-summary', compact('summary', 'dealerId'));
    }
}
```

---

## 📊 10. DASHBOARD & REPORTING

### CRM Dashboard Query Examples

```php
// app/Services/DashboardService.php

<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Client;
use App\Models\Deal;
use App\Models\FollowUp;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getOverview($startDate = null, $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        return [
            'leads' => $this->getLeadMetrics($startDate, $endDate),
            'clients' => $this->getClientMetrics($startDate, $endDate),
            'deals' => $this->getDealMetrics($startDate, $endDate),
            'follow_ups' => $this->getFollowUpMetrics(),
            'top_performers' => $this->getTopPerformers($startDate, $endDate),
        ];
    }

    protected function getLeadMetrics($startDate, $endDate): array
    {
        return [
            'total' => Lead::whereBetween('created_at', [$startDate, $endDate])->count(),
            'new' => Lead::where('status', 'new')->count(),
            'contacted' => Lead::where('status', 'contacted')->count(),
            'qualified' => Lead::where('status', 'qualified')->count(),
            'converted' => Lead::where('status', 'converted')
                ->whereBetween('converted_at', [$startDate, $endDate])
                ->count(),
            'conversion_rate' => $this->calculateConversionRate($startDate, $endDate),
        ];
    }

    protected function getClientMetrics($startDate, $endDate): array
    {
        return [
            'total' => Client::count(),
            'new' => Client::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active' => Client::where('is_active', true)->count(),
            'with_deals' => Client::has('deals')->count(),
        ];
    }

    protected function getDealMetrics($startDate, $endDate): array
    {
        $deals = Deal::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total' => $deals->count(),
            'pending' => (clone $deals)->where('status', 'pending')->count(),
            'approved' => (clone $deals)->where('status', 'approved')->count(),
            'completed' => (clone $deals)->where('status', 'completed')->count(),
            'total_value' => (clone $deals)->where('status', 'completed')->sum('deal_amount'),
            'total_commission' => (clone $deals)->where('status', 'completed')->sum('commission_amount'),
        ];
    }

    protected function getFollowUpMetrics(): array
    {
        return [
            'today' => FollowUp::today()->scheduled()->count(),
            'overdue' => FollowUp::overdue()->count(),
            'this_week' => FollowUp::upcoming(7)->scheduled()->count(),
        ];
    }

    protected function getTopPerformers($startDate, $endDate, $limit = 10): array
    {
        return DB::table('dealers')
            ->join('deals', 'dealers.id', '=', 'deals.dealer_id')
            ->join('users', 'dealers.user_id', '=', 'users.id')
            ->select(
                'dealers.id',
                'users.name',
                DB::raw('COUNT(deals.id) as deal_count'),
                DB::raw('SUM(deals.deal_amount) as total_value'),
                DB::raw('SUM(deals.commission_amount) as total_commission')
            )
            ->where('deals.status', 'completed')
            ->whereBetween('deals.completed_at', [$startDate, $endDate])
            ->groupBy('dealers.id', 'users.name')
            ->orderBy('total_commission', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    protected function calculateConversionRate($startDate, $endDate): float
    {
        $total = Lead::whereBetween('created_at', [$startDate, $endDate])->count();
        if ($total == 0) return 0;

        $converted = Lead::where('status', 'converted')
            ->whereBetween('converted_at', [$startDate, $endDate])
            ->count();

        return round(($converted / $total) * 100, 2);
    }
}
```

---

## ✅ IMPLEMENTATION CHECKLIST

### Database Setup
- [ ] Run all migrations in order
- [ ] Seed lead sources table
- [ ] Create initial dealers
- [ ] Test foreign key constraints

### Model Setup
- [ ] Implement all models with relationships
- [ ] Add scopes and accessors
- [ ] Test polymorphic relationships
- [ ] Add model observers if needed

### Service Layer
- [ ] LeadManagementService
- [ ] ClientConversionService
- [ ] DealManagementService
- [ ] CommissionService
- [ ] StatusTransitionService
- [ ] DashboardService

### Controllers
- [ ] LeadController (CRUD + actions)
- [ ] ClientController
- [ ] DealController
- [ ] FollowUpController
- [ ] CommissionController
- [ ] DealerController

### Views (if using Blade)
- [ ] Lead management interface
- [ ] Client profile pages
- [ ] Deal creation/approval forms
- [ ] Follow-up calendar
- [ ] Commission tracking dashboard
- [ ] Dealer performance reports

### Permissions
- [ ] Integrate with Spatie Permission
- [ ] Define role-specific access
- [ ] Implement authorization policies
- [ ] Test permission gates

### Testing
- [ ] Unit tests for services
- [ ] Feature tests for workflows
- [ ] Test status transitions
- [ ] Test commission calculations

### Additional Features
- [ ] Email notifications
- [ ] SMS reminders
- [ ] Calendar integration
- [ ] Export/reporting
- [ ] Mobile responsiveness

---

## 🎯 KEY FEATURES SUMMARY

✅ **Complete Lead Management**
- Lead capture with source tracking
- Auto-assignment to dealers
- Status flow management
- Priority flagging

✅ **Follow-up System**
- Polymorphic (works for leads & clients)
- Multiple follow-up types
- Overdue tracking
- Next follow-up scheduling

✅ **Client Conversion**
- Seamless lead-to-client conversion
- Follow-up transfer
- History preservation
- Conversion analytics

✅ **Deal Management**
- Property linking (polymorphic)
- Auto deal number generation
- Approval workflow
- Status transitions

✅ **Commission Tracking**
- Auto commission calculation
- Multiple commission types (primary/split/referral)
- Approval workflow
- Payment tracking
- Dealer totals auto-update

✅ **Reporting & Analytics**
- Conversion rates
- Dealer performance
- Deal statistics
- Commission reports

---

## 🚀 USAGE EXAMPLES

See Section 8 (Complete Workflow) for full implementation examples from lead creation to commission payment.

---

**Document Version:** 1.0
**Laravel Version:** 11.x
**Created:** January 2026

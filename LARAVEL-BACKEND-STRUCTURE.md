# Laravel Backend Structure - Real Estate CRM & Property Management System

## 📋 Table of Contents
1. [Complete Folder Structure](#complete-folder-structure)
2. [Models Architecture](#models-architecture)
3. [Controllers Per Module](#controllers-per-module)
4. [Middleware Implementation](#middleware-implementation)
5. [Service Layer](#service-layer)
6. [API vs Web Routes](#api-vs-web-routes)
7. [Best Practices](#best-practices)

---

## 🗂️ 1. COMPLETE FOLDER STRUCTURE

```
realestatecrm/
│
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── GenerateMonthlyReports.php
│   │       ├── CheckOverdueInstallments.php
│   │       └── SendFollowUpReminders.php
│   │
│   ├── Events/
│   │   ├── DealCreated.php
│   │   ├── PaymentReceived.php
│   │   ├── LeadConverted.php
│   │   └── FileTransferred.php
│   │
│   ├── Exceptions/
│   │   ├── Handler.php
│   │   ├── InsufficientPermissionException.php
│   │   ├── InvalidFileStatusException.php
│   │   └── PaymentProcessingException.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── V1/
│   │   │   │   │   ├── Auth/
│   │   │   │   │   │   ├── LoginController.php
│   │   │   │   │   │   ├── RegisterController.php
│   │   │   │   │   │   └── PasswordResetController.php
│   │   │   │   │   │
│   │   │   │   │   ├── UserManagement/
│   │   │   │   │   │   ├── UserController.php
│   │   │   │   │   │   ├── RoleController.php
│   │   │   │   │   │   └── PermissionController.php
│   │   │   │   │   │
│   │   │   │   │   ├── Society/
│   │   │   │   │   │   ├── SocietyController.php
│   │   │   │   │   │   ├── BlockController.php
│   │   │   │   │   │   └── StreetController.php
│   │   │   │   │   │
│   │   │   │   │   ├── Plot/
│   │   │   │   │   │   ├── PlotController.php
│   │   │   │   │   │   └── PlotMapController.php
│   │   │   │   │   │
│   │   │   │   │   ├── Property/
│   │   │   │   │   │   ├── PropertyController.php
│   │   │   │   │   │   ├── PropertyTypeController.php
│   │   │   │   │   │   └── PropertyImageController.php
│   │   │   │   │   │
│   │   │   │   │   ├── CRM/
│   │   │   │   │   │   ├── LeadController.php
│   │   │   │   │   │   ├── ClientController.php
│   │   │   │   │   │   ├── DealerController.php
│   │   │   │   │   │   ├── DealController.php
│   │   │   │   │   │   └── FollowUpController.php
│   │   │   │   │   │
│   │   │   │   │   ├── File/
│   │   │   │   │   │   ├── PropertyFileController.php
│   │   │   │   │   │   ├── FileInstallmentController.php
│   │   │   │   │   │   ├── FilePaymentController.php
│   │   │   │   │   │   └── FileTransferController.php
│   │   │   │   │   │
│   │   │   │   │   ├── Payment/
│   │   │   │   │   │   ├── PaymentController.php
│   │   │   │   │   │   └── ExpenseController.php
│   │   │   │   │   │
│   │   │   │   │   ├── Report/
│   │   │   │   │   │   ├── ReportController.php
│   │   │   │   │   │   ├── SalesReportController.php
│   │   │   │   │   │   ├── RevenueReportController.php
│   │   │   │   │   │   └── CommissionReportController.php
│   │   │   │   │   │
│   │   │   │   │   └── DashboardController.php
│   │   │   │   │
│   │   │   │   └── V2/
│   │   │   │       └── (Future API versions)
│   │   │   │
│   │   │   └── Web/
│   │   │       ├── DashboardController.php
│   │   │       ├── ProfileController.php
│   │   │       ├── SocietyController.php
│   │   │       ├── PlotController.php
│   │   │       ├── PropertyController.php
│   │   │       ├── ClientController.php
│   │   │       ├── LeadController.php
│   │   │       ├── DealController.php
│   │   │       ├── PropertyFileController.php
│   │   │       ├── PaymentController.php
│   │   │       └── ReportController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── Authenticate.php
│   │   │   ├── CheckRole.php
│   │   │   ├── CheckPermission.php
│   │   │   ├── EnsureUserIsActive.php
│   │   │   ├── LogUserActivity.php
│   │   │   ├── ValidateApiToken.php
│   │   │   └── TrackApiUsage.php
│   │   │
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginRequest.php
│   │   │   │   └── RegisterRequest.php
│   │   │   │
│   │   │   ├── User/
│   │   │   │   ├── StoreUserRequest.php
│   │   │   │   └── UpdateUserRequest.php
│   │   │   │
│   │   │   ├── Society/
│   │   │   │   ├── StoreSocietyRequest.php
│   │   │   │   ├── UpdateSocietyRequest.php
│   │   │   │   ├── StoreBlockRequest.php
│   │   │   │   └── StoreStreetRequest.php
│   │   │   │
│   │   │   ├── Plot/
│   │   │   │   ├── StorePlotRequest.php
│   │   │   │   └── UpdatePlotRequest.php
│   │   │   │
│   │   │   ├── Property/
│   │   │   │   ├── StorePropertyRequest.php
│   │   │   │   └── UpdatePropertyRequest.php
│   │   │   │
│   │   │   ├── CRM/
│   │   │   │   ├── StoreLeadRequest.php
│   │   │   │   ├── UpdateLeadRequest.php
│   │   │   │   ├── StoreClientRequest.php
│   │   │   │   ├── UpdateClientRequest.php
│   │   │   │   ├── StoreDealRequest.php
│   │   │   │   └── StoreFollowUpRequest.php
│   │   │   │
│   │   │   ├── File/
│   │   │   │   ├── StorePropertyFileRequest.php
│   │   │   │   ├── StoreFilePaymentRequest.php
│   │   │   │   └── TransferFileRequest.php
│   │   │   │
│   │   │   └── Payment/
│   │   │       ├── StorePaymentRequest.php
│   │   │       └── StoreExpenseRequest.php
│   │   │
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       ├── RoleResource.php
│   │       ├── SocietyResource.php
│   │       ├── BlockResource.php
│   │       ├── PlotResource.php
│   │       ├── PropertyResource.php
│   │       ├── LeadResource.php
│   │       ├── ClientResource.php
│   │       ├── DealResource.php
│   │       ├── PropertyFileResource.php
│   │       ├── PaymentResource.php
│   │       └── ReportResource.php
│   │
│   ├── Listeners/
│   │   ├── SendDealNotification.php
│   │   ├── UpdateClientStats.php
│   │   ├── GeneratePaymentReceipt.php
│   │   └── NotifyDealerOfNewLead.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── Society.php
│   │   ├── Block.php
│   │   ├── Street.php
│   │   ├── Plot.php
│   │   ├── PlotMap.php
│   │   ├── PropertyType.php
│   │   ├── Property.php
│   │   ├── PropertyImage.php
│   │   ├── Dealer.php
│   │   ├── Lead.php
│   │   ├── Client.php
│   │   ├── FollowUp.php
│   │   ├── Deal.php
│   │   ├── PropertyFile.php
│   │   ├── FileInstallment.php
│   │   ├── FilePayment.php
│   │   ├── FileTransfer.php
│   │   ├── Payment.php
│   │   ├── Expense.php
│   │   └── Report.php
│   │
│   ├── Notifications/
│   │   ├── NewLeadAssigned.php
│   │   ├── DealApproved.php
│   │   ├── PaymentReceived.php
│   │   ├── InstallmentDueReminder.php
│   │   └── FollowUpScheduled.php
│   │
│   ├── Observers/
│   │   ├── DealObserver.php
│   │   ├── PaymentObserver.php
│   │   ├── PropertyFileObserver.php
│   │   └── LeadObserver.php
│   │
│   ├── Policies/
│   │   ├── UserPolicy.php
│   │   ├── SocietyPolicy.php
│   │   ├── PlotPolicy.php
│   │   ├── PropertyPolicy.php
│   │   ├── LeadPolicy.php
│   │   ├── ClientPolicy.php
│   │   ├── DealPolicy.php
│   │   ├── PropertyFilePolicy.php
│   │   └── PaymentPolicy.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   ├── RouteServiceProvider.php
│   │   └── RepositoryServiceProvider.php
│   │
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   ├── UserRepositoryInterface.php
│   │   │   ├── SocietyRepositoryInterface.php
│   │   │   ├── PlotRepositoryInterface.php
│   │   │   ├── PropertyRepositoryInterface.php
│   │   │   ├── LeadRepositoryInterface.php
│   │   │   ├── ClientRepositoryInterface.php
│   │   │   ├── DealRepositoryInterface.php
│   │   │   └── PaymentRepositoryInterface.php
│   │   │
│   │   └── Eloquent/
│   │       ├── UserRepository.php
│   │       ├── SocietyRepository.php
│   │       ├── PlotRepository.php
│   │       ├── PropertyRepository.php
│   │       ├── LeadRepository.php
│   │       ├── ClientRepository.php
│   │       ├── DealRepository.php
│   │       └── PaymentRepository.php
│   │
│   ├── Services/
│   │   ├── Auth/
│   │   │   ├── AuthService.php
│   │   │   └── PermissionService.php
│   │   │
│   │   ├── User/
│   │   │   └── UserService.php
│   │   │
│   │   ├── Society/
│   │   │   ├── SocietyService.php
│   │   │   └── PlotService.php
│   │   │
│   │   ├── Property/
│   │   │   └── PropertyService.php
│   │   │
│   │   ├── CRM/
│   │   │   ├── LeadService.php
│   │   │   ├── ClientService.php
│   │   │   ├── DealService.php
│   │   │   └── FollowUpService.php
│   │   │
│   │   ├── File/
│   │   │   ├── PropertyFileService.php
│   │   │   ├── InstallmentService.php
│   │   │   └── FileTransferService.php
│   │   │
│   │   ├── Payment/
│   │   │   ├── PaymentService.php
│   │   │   ├── PaymentProcessingService.php
│   │   │   └── ExpenseService.php
│   │   │
│   │   ├── Report/
│   │   │   ├── ReportService.php
│   │   │   ├── SalesReportService.php
│   │   │   ├── RevenueReportService.php
│   │   │   └── DashboardService.php
│   │   │
│   │   └── Common/
│   │       ├── FileUploadService.php
│   │       ├── NotificationService.php
│   │       ├── PdfGenerationService.php
│   │       └── UnitConversionService.php
│   │
│   ├── Traits/
│   │   ├── HasPermissions.php
│   │   ├── HasRoles.php
│   │   ├── Loggable.php
│   │   ├── Searchable.php
│   │   └── HasUuid.php
│   │
│   └── helpers.php
│
├── bootstrap/
│   ├── app.php
│   └── providers.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   ├── permissions.php (custom)
│   ├── services.php
│   └── repository.php (custom)
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── SocietyFactory.php
│   │   ├── PlotFactory.php
│   │   ├── PropertyFactory.php
│   │   ├── LeadFactory.php
│   │   └── ClientFactory.php
│   │
│   ├── migrations/
│   │   └── (25 migration files as created)
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       ├── PermissionSeeder.php
│       ├── RolePermissionSeeder.php
│       ├── UserSeeder.php
│       ├── SocietySeeder.php
│       └── PropertyTypeSeeder.php
│
├── routes/
│   ├── api.php
│   ├── web.php
│   ├── console.php
│   └── channels.php
│
├── storage/
│   ├── app/
│   │   ├── public/
│   │   │   ├── plots/
│   │   │   ├── properties/
│   │   │   ├── documents/
│   │   │   └── receipts/
│   │   └── private/
│   └── logs/
│
└── tests/
    ├── Feature/
    │   ├── Auth/
    │   ├── User/
    │   ├── Society/
    │   ├── Plot/
    │   ├── Property/
    │   ├── CRM/
    │   ├── File/
    │   └── Payment/
    │
    └── Unit/
        ├── Models/
        ├── Services/
        └── Repositories/
```

---

## 🎯 2. MODELS ARCHITECTURE

### Complete Models List with Relationships

#### **User Management Module**

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, HasPermissions;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'cnic',
        'address', 'profile_image', 'is_active'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function roles(): BelongsToMany
    public function permissions(): BelongsToMany (through roles)
    public function assignedLeads(): HasMany
    public function assignedClients(): HasMany
    public function deals(): HasMany
    public function followUps(): HasMany
    public function receivedPayments(): HasMany
    public function createdReports(): HasMany

    // Methods
    public function hasRole(string $role): bool
    public function hasPermission(string $permission): bool
    public function hasAnyRole(array $roles): bool
    public function isAdmin(): bool
    public function isDealer(): bool
}

// app/Models/Role.php
class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    // Relationships
    public function users(): BelongsToMany
    public function permissions(): BelongsToMany

    // Methods
    public function givePermission(Permission $permission): void
    public function revokePermission(Permission $permission): void
    public function hasPermission(string $permission): bool
}

// app/Models/Permission.php
class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'module', 'description'];

    // Relationships
    public function roles(): BelongsToMany
}
```

#### **Society & Plot Module**

```php
// app/Models/Society.php
class Society extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'name', 'slug', 'location', 'city', 'total_area',
        'description', 'map_location', 'contact_person',
        'contact_phone', 'status', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'total_area' => 'decimal:2',
    ];

    // Relationships
    public function blocks(): HasMany
    public function plots(): HasMany
    public function properties(): HasMany
    public function leads(): HasMany

    // Scopes
    public function scopeActive($query)
    public function scopeByStatus($query, $status)

    // Accessors
    public function getTotalPlotsAttribute(): int
    public function getAvailablePlotsAttribute(): int
}

// app/Models/Block.php
class Block extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'society_id', 'name', 'block_number',
        'total_plots', 'description', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    // Relationships
    public function society(): BelongsTo
    public function streets(): HasMany
    public function plots(): HasMany
    public function properties(): HasMany

    // Scopes
    public function scopeActive($query)
}

// app/Models/Street.php
class Street extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'block_id', 'name', 'street_number', 'width', 'description'
    ];

    protected $casts = ['width' => 'decimal:2'];

    // Relationships
    public function block(): BelongsTo
    public function plots(): HasMany
    public function properties(): HasMany
}

// app/Models/Plot.php
class Plot extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'society_id', 'block_id', 'street_id', 'plot_number',
        'size_marla', 'size_kanal', 'size_in_sqft',
        'category', 'plot_type', 'is_corner', 'is_park_facing',
        'is_main_road_facing', 'price_per_marla', 'total_price',
        'status', 'description'
    ];

    protected $casts = [
        'size_marla' => 'decimal:2',
        'size_kanal' => 'decimal:2',
        'size_in_sqft' => 'decimal:2',
        'price_per_marla' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_corner' => 'boolean',
        'is_park_facing' => 'boolean',
        'is_main_road_facing' => 'boolean',
    ];

    // Relationships
    public function society(): BelongsTo
    public function block(): BelongsTo
    public function street(): BelongsTo
    public function maps(): HasMany (PlotMap)
    public function deals(): MorphMany
    public function propertyFiles(): MorphMany

    // Scopes
    public function scopeAvailable($query)
    public function scopeSold($query)
    public function scopeByCategory($query, $category)

    // Methods
    public function isAvailable(): bool
    public function markAsBooked(): void
    public function markAsSold(): void
}

// app/Models/PlotMap.php
class PlotMap extends Model
{
    protected $fillable = [
        'plot_id', 'title', 'file_name', 'file_path',
        'file_type', 'file_size', 'map_type',
        'uploaded_by', 'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'file_size' => 'integer',
    ];

    // Relationships
    public function plot(): BelongsTo
    public function uploadedBy(): BelongsTo (User)
}
```

#### **Property Module**

```php
// app/Models/PropertyType.php
class PropertyType extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    // Relationships
    public function properties(): HasMany

    // Scopes
    public function scopeActive($query)
}

// app/Models/Property.php
class Property extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'property_type_id', 'society_id', 'block_id',
        'street_id', 'plot_id', 'property_number', 'title',
        'address', 'size_marla', 'size_kanal', 'size_in_sqft',
        'bedrooms', 'bathrooms', 'kitchens', 'floors',
        'property_for', 'price_sale', 'price_rent',
        'status', 'is_featured', 'year_built',
        'description', 'amenities', 'latitude', 'longitude'
    ];

    protected $casts = [
        'size_marla' => 'decimal:2',
        'size_kanal' => 'decimal:2',
        'size_in_sqft' => 'decimal:2',
        'price_sale' => 'decimal:2',
        'price_rent' => 'decimal:2',
        'is_featured' => 'boolean',
        'amenities' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // Relationships
    public function propertyType(): BelongsTo
    public function society(): BelongsTo
    public function block(): BelongsTo
    public function street(): BelongsTo
    public function plot(): BelongsTo
    public function images(): HasMany (PropertyImage)
    public function deals(): MorphMany
    public function propertyFiles(): MorphMany

    // Scopes
    public function scopeAvailable($query)
    public function scopeFeatured($query)
    public function scopeForSale($query)
    public function scopeForRent($query)
}

// app/Models/PropertyImage.php
class PropertyImage extends Model
{
    protected $fillable = [
        'property_id', 'title', 'file_name', 'file_path',
        'file_size', 'image_type', 'is_primary',
        'sort_order', 'uploaded_by'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function property(): BelongsTo
    public function uploadedBy(): BelongsTo (User)
}
```

#### **CRM Module**

```php
// app/Models/Dealer.php
class Dealer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'dealer_code', 'name', 'phone',
        'email', 'cnic', 'address', 'commission_percentage',
        'total_deals', 'total_commission', 'join_date', 'status'
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'total_commission' => 'decimal:2',
        'join_date' => 'date',
    ];

    // Relationships
    public function user(): BelongsTo
    public function deals(): HasMany
    public function expenses(): HasMany
}

// app/Models/Lead.php
class Lead extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'name', 'email', 'phone', 'whatsapp', 'cnic',
        'address', 'source', 'interest_type', 'society_id',
        'budget_min', 'budget_max', 'priority', 'status',
        'assigned_to', 'converted_to_client_id', 'notes'
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    // Relationships
    public function society(): BelongsTo
    public function assignedTo(): BelongsTo (User)
    public function convertedToClient(): BelongsTo (Client)
    public function followUps(): MorphMany

    // Scopes
    public function scopeNew($query)
    public function scopeConverted($query)
    public function scopeByPriority($query, $priority)

    // Methods
    public function convertToClient(array $data): Client
    public function isConverted(): bool
}

// app/Models/Client.php
class Client extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    protected $fillable = [
        'client_code', 'name', 'email', 'phone', 'whatsapp',
        'cnic', 'address', 'city', 'client_type',
        'assigned_to', 'total_purchases', 'total_sales',
        'documents', 'notes', 'is_active'
    ];

    protected $casts = [
        'total_purchases' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'documents' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function assignedTo(): BelongsTo (User)
    public function deals(): HasMany
    public function propertyFiles(): HasMany
    public function payments(): HasMany
    public function followUps(): MorphMany

    // Scopes
    public function scopeActive($query)
    public function scopeBuyers($query)
    public function scopeSellers($query)
}

// app/Models/FollowUp.php
class FollowUp extends Model
{
    protected $fillable = [
        'followable_type', 'followable_id', 'assigned_to',
        'follow_up_type', 'scheduled_at', 'completed_at',
        'status', 'notes', 'outcome', 'next_action'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function followable(): MorphTo
    public function assignedTo(): BelongsTo (User)

    // Scopes
    public function scopePending($query)
    public function scopeToday($query)
    public function scopeOverdue($query)

    // Methods
    public function markCompleted(string $outcome): void
    public function isOverdue(): bool
}

// app/Models/Deal.php
class Deal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'deal_number', 'client_id', 'dealer_id',
        'dealable_type', 'dealable_id', 'deal_type',
        'deal_amount', 'commission_percentage', 'commission_amount',
        'payment_terms', 'down_payment', 'installment_months',
        'status', 'approved_by', 'approved_at', 'completed_at',
        'deal_date', 'notes'
    ];

    protected $casts = [
        'deal_amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'deal_date' => 'date',
    ];

    // Relationships
    public function client(): BelongsTo
    public function dealer(): BelongsTo
    public function dealable(): MorphTo
    public function approvedBy(): BelongsTo (User)
    public function propertyFile(): HasOne

    // Methods
    public function calculateCommission(): void
    public function approve(User $user): void
    public function generateDealNumber(): string
}
```

#### **File & Installment Module**

```php
// app/Models/PropertyFile.php
class PropertyFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'file_number', 'client_id', 'deal_id',
        'fileable_type', 'fileable_id', 'total_amount',
        'down_payment', 'paid_amount', 'remaining_amount',
        'installment_plan', 'total_installments', 'paid_installments',
        'status', 'start_date', 'completion_date',
        'transferred_to_client_id', 'transfer_date', 'transfer_fee'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'transfer_fee' => 'decimal:2',
        'start_date' => 'date',
        'completion_date' => 'date',
        'transfer_date' => 'date',
    ];

    // Relationships
    public function client(): BelongsTo
    public function deal(): BelongsTo
    public function fileable(): MorphTo
    public function installments(): HasMany
    public function payments(): HasMany
    public function transfers(): HasMany
    public function transferredToClient(): BelongsTo (Client)

    // Methods
    public function generateFileNumber(): string
    public function updatePaymentStatus(): void
    public function transferToClient(Client $newClient, $fee): void
}

// app/Models/FileInstallment.php
class FileInstallment extends Model
{
    protected $fillable = [
        'property_file_id', 'installment_number', 'amount',
        'due_date', 'paid_date', 'paid_amount', 'status',
        'days_overdue', 'late_fee', 'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    // Relationships
    public function propertyFile(): BelongsTo
    public function payments(): HasMany

    // Scopes
    public function scopePending($query)
    public function scopeOverdue($query)

    // Methods
    public function markAsPaid(Payment $payment): void
    public function calculateOverdue(): array
}

// app/Models/FilePayment.php
class FilePayment extends Model
{
    protected $fillable = [
        'receipt_number', 'property_file_id', 'file_installment_id',
        'client_id', 'amount', 'payment_date', 'payment_method',
        'cheque_number', 'bank_name', 'transaction_reference',
        'received_by', 'status', 'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function propertyFile(): BelongsTo
    public function installment(): BelongsTo
    public function client(): BelongsTo
    public function receivedBy(): BelongsTo (User)

    // Methods
    public function generateReceiptNumber(): string
}

// app/Models/FileTransfer.php
class FileTransfer extends Model
{
    protected $fillable = [
        'property_file_id', 'from_client_id', 'to_client_id',
        'transfer_date', 'transfer_fee', 'outstanding_amount',
        'transfer_reason', 'approved_by', 'approved_at',
        'status', 'documents', 'notes'
    ];

    protected $casts = [
        'transfer_fee' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'transfer_date' => 'date',
        'approved_at' => 'datetime',
        'documents' => 'array',
    ];

    // Relationships
    public function propertyFile(): BelongsTo
    public function fromClient(): BelongsTo (Client)
    public function toClient(): BelongsTo (Client)
    public function approvedBy(): BelongsTo (User)
}
```

#### **Payment & Financial Module**

```php
// app/Models/Payment.php
class Payment extends Model
{
    protected $fillable = [
        'receipt_number', 'payment_type', 'client_id', 'deal_id',
        'payable_type', 'payable_id', 'amount', 'payment_date',
        'payment_method', 'cheque_number', 'bank_name',
        'transaction_reference', 'received_by', 'status', 'description'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function client(): BelongsTo
    public function deal(): BelongsTo
    public function payable(): MorphTo
    public function receivedBy(): BelongsTo (User)

    // Scopes
    public function scopeCompleted($query)
    public function scopeToday($query)
}

// app/Models/Expense.php
class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_number', 'category', 'title', 'description',
        'amount', 'expense_date', 'payment_method', 'paid_to',
        'dealer_id', 'receipt_file', 'approved_by', 'recorded_by', 'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    // Relationships
    public function dealer(): BelongsTo
    public function approvedBy(): BelongsTo (User)
    public function recordedBy(): BelongsTo (User)
}
```

#### **Report Module**

```php
// app/Models/Report.php
class Report extends Model
{
    protected $fillable = [
        'report_type', 'title', 'description', 'parameters',
        'file_path', 'generated_by', 'date_from', 'date_to',
        'total_records', 'total_amount', 'status'
    ];

    protected $casts = [
        'parameters' => 'array',
        'total_amount' => 'decimal:2',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    // Relationships
    public function generatedBy(): BelongsTo (User)
}
```

---

## 🎮 3. CONTROLLERS PER MODULE

### Controller Architecture & Responsibilities

#### **Module 1: User Management**

```php
// app/Http/Controllers/Api/V1/UserManagement/UserController.php
class UserController extends Controller
{
    public function __construct(
        private UserService $userService,
        private PermissionService $permissionService
    ) {}

    // CRUD Methods
    public function index(Request $request): JsonResponse
    {
        // List users with filters, pagination, search
        // Permission: users.view_all
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        // Create new user
        // Permission: users.create
    }

    public function show(User $user): JsonResponse
    {
        // Show single user details
        // Permission: users.view
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        // Update user
        // Permission: users.update
    }

    public function destroy(User $user): JsonResponse
    {
        // Soft delete user
        // Permission: users.delete
    }

    // Additional Methods
    public function activate(User $user): JsonResponse
    public function deactivate(User $user): JsonResponse
    public function assignRole(Request $request, User $user): JsonResponse
    public function revokeRole(Request $request, User $user): JsonResponse
    public function updatePassword(Request $request, User $user): JsonResponse
}

// app/Http/Controllers/Api/V1/UserManagement/RoleController.php
class RoleController extends Controller
{
    public function index(): JsonResponse
    public function store(Request $request): JsonResponse
    public function show(Role $role): JsonResponse
    public function update(Request $request, Role $role): JsonResponse
    public function destroy(Role $role): JsonResponse
    public function assignPermission(Request $request, Role $role): JsonResponse
    public function revokePermission(Request $request, Role $role): JsonResponse
}

// app/Http/Controllers/Api/V1/UserManagement/PermissionController.php
class PermissionController extends Controller
{
    public function index(): JsonResponse
    public function byModule(string $module): JsonResponse
    public function store(Request $request): JsonResponse
}
```

#### **Module 2: Society & Plot Management**

```php
// app/Http/Controllers/Api/V1/Society/SocietyController.php
class SocietyController extends Controller
{
    public function __construct(
        private SocietyService $societyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // List societies with stats
        // Filters: status, city, search
        // Permission: societies.view_all
    }

    public function store(StoreSocietyRequest $request): JsonResponse
    {
        // Create society
        // Permission: societies.create
    }

    public function show(Society $society): JsonResponse
    {
        // Show society with blocks, plots count
        // Permission: societies.view
    }

    public function update(UpdateSocietyRequest $request, Society $society): JsonResponse
    public function destroy(Society $society): JsonResponse

    // Custom Methods
    public function stats(Society $society): JsonResponse
    {
        // Total blocks, plots, available plots, sold plots
    }

    public function availablePlots(Society $society): JsonResponse
}

// app/Http/Controllers/Api/V1/Society/BlockController.php
class BlockController extends Controller
{
    public function index(Society $society): JsonResponse
    public function store(Request $request, Society $society): JsonResponse
    public function show(Block $block): JsonResponse
    public function update(Request $request, Block $block): JsonResponse
    public function destroy(Block $block): JsonResponse
}

// app/Http/Controllers/Api/V1/Society/StreetController.php
class StreetController extends Controller
{
    public function index(Block $block): JsonResponse
    public function store(Request $request, Block $block): JsonResponse
    public function show(Street $street): JsonResponse
    public function update(Request $request, Street $street): JsonResponse
    public function destroy(Street $street): JsonResponse
}
```

#### **Module 3: Plot Management**

```php
// app/Http/Controllers/Api/V1/Plot/PlotController.php
class PlotController extends Controller
{
    public function __construct(
        private PlotService $plotService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // List plots with filters
        // Filters: society_id, block_id, status, category,
        //          price_range, size_range
        // Permission: plots.view_all
    }

    public function store(StorePlotRequest $request): JsonResponse
    {
        // Create plot with automatic unit conversion
        // Permission: plots.create
    }

    public function show(Plot $plot): JsonResponse
    {
        // Show plot with all details, maps
        // Permission: plots.view
    }

    public function update(UpdatePlotRequest $request, Plot $plot): JsonResponse
    public function destroy(Plot $plot): JsonResponse

    // Status Management
    public function markAsBooked(Plot $plot): JsonResponse
    public function markAsSold(Plot $plot): JsonResponse
    public function markAsAvailable(Plot $plot): JsonResponse

    // Filtering
    public function available(Request $request): JsonResponse
    public function sold(Request $request): JsonResponse
    public function byCategory(Request $request, string $category): JsonResponse
}

// app/Http/Controllers/Api/V1/Plot/PlotMapController.php
class PlotMapController extends Controller
{
    public function index(Plot $plot): JsonResponse
    public function store(Request $request, Plot $plot): JsonResponse
    public function show(PlotMap $plotMap): JsonResponse
    public function update(Request $request, PlotMap $plotMap): JsonResponse
    public function destroy(PlotMap $plotMap): JsonResponse
    public function setPrimary(PlotMap $plotMap): JsonResponse
}
```

#### **Module 4: Property Management**

```php
// app/Http/Controllers/Api/V1/Property/PropertyController.php
class PropertyController extends Controller
{
    public function __construct(
        private PropertyService $propertyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // List properties with filters
        // Filters: type, society, status, property_for,
        //          price_range, bedrooms, bathrooms
        // Permission: properties.view_all
    }

    public function store(StorePropertyRequest $request): JsonResponse
    public function show(Property $property): JsonResponse
    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    public function destroy(Property $property): JsonResponse

    // Feature Management
    public function markAsFeatured(Property $property): JsonResponse
    public function removeFeatured(Property $property): JsonResponse

    // Filtering
    public function featured(): JsonResponse
    public function forSale(Request $request): JsonResponse
    public function forRent(Request $request): JsonResponse
    public function byType(Request $request, PropertyType $type): JsonResponse
}

// app/Http/Controllers/Api/V1/Property/PropertyTypeController.php
class PropertyTypeController extends Controller
{
    public function index(): JsonResponse
    public function store(Request $request): JsonResponse
    public function show(PropertyType $type): JsonResponse
    public function update(Request $request, PropertyType $type): JsonResponse
    public function destroy(PropertyType $type): JsonResponse
}

// app/Http/Controllers/Api/V1/Property/PropertyImageController.php
class PropertyImageController extends Controller
{
    public function index(Property $property): JsonResponse
    public function store(Request $request, Property $property): JsonResponse
    public function destroy(PropertyImage $image): JsonResponse
    public function setPrimary(PropertyImage $image): JsonResponse
    public function reorder(Request $request, Property $property): JsonResponse
}
```

#### **Module 5: CRM System**

```php
// app/Http/Controllers/Api/V1/CRM/LeadController.php
class LeadController extends Controller
{
    public function __construct(
        private LeadService $leadService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // List leads with filters
        // Filters: status, priority, source, assigned_to, date_range
        // User can see only assigned leads or all (based on permission)
        // Permission: leads.view or leads.view_all
    }

    public function store(StoreLeadRequest $request): JsonResponse
    public function show(Lead $lead): JsonResponse
    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    public function destroy(Lead $lead): JsonResponse

    // Lead Management
    public function assign(Request $request, Lead $lead): JsonResponse
    public function convert(Request $request, Lead $lead): JsonResponse
    {
        // Convert lead to client
        // Permission: leads.convert
    }

    public function updateStatus(Request $request, Lead $lead): JsonResponse

    // Filtering
    public function newLeads(): JsonResponse
    public function myLeads(): JsonResponse
    public function converted(): JsonResponse
    public function byPriority(string $priority): JsonResponse
}

// app/Http/Controllers/Api/V1/CRM/ClientController.php
class ClientController extends Controller
{
    public function __construct(
        private ClientService $clientService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // List clients with filters
        // Filters: type, assigned_to, search, date_range
        // Permission: clients.view or clients.view_all
    }

    public function store(StoreClientRequest $request): JsonResponse
    public function show(Client $client): JsonResponse
    {
        // Show client with stats: deals, files, payments, follow-ups
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    public function destroy(Client $client): JsonResponse

    // Client Management
    public function assign(Request $request, Client $client): JsonResponse
    public function activate(Client $client): JsonResponse
    public function deactivate(Client $client): JsonResponse

    // Statistics
    public function stats(Client $client): JsonResponse
    public function transactions(Client $client): JsonResponse
}

// app/Http/Controllers/Api/V1/CRM/DealerController.php
class DealerController extends Controller
{
    public function index(): JsonResponse
    public function store(Request $request): JsonResponse
    public function show(Dealer $dealer): JsonResponse
    public function update(Request $request, Dealer $dealer): JsonResponse
    public function destroy(Dealer $dealer): JsonResponse
    public function stats(Dealer $dealer): JsonResponse
    public function commissionReport(Request $request, Dealer $dealer): JsonResponse
}

// app/Http/Controllers/Api/V1/CRM/DealController.php
class DealController extends Controller
{
    public function __construct(
        private DealService $dealService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // List deals with filters
        // Filters: status, deal_type, client, dealer, date_range
        // Permission: deals.view_all or deals.view (own deals)
    }

    public function store(StoreDealRequest $request): JsonResponse
    {
        // Create deal with commission calculation
        // Permission: deals.create
    }

    public function show(Deal $deal): JsonResponse
    public function update(Request $request, Deal $deal): JsonResponse
    public function destroy(Deal $deal): JsonResponse

    // Deal Workflow
    public function approve(Request $request, Deal $deal): JsonResponse
    {
        // Approve deal (Manager/Admin only)
        // Permission: deals.approve
    }

    public function complete(Deal $deal): JsonResponse
    public function cancel(Request $request, Deal $deal): JsonResponse

    // Filtering
    public function pending(): JsonResponse
    public function approved(): JsonResponse
    public function myDeals(): JsonResponse
}

// app/Http/Controllers/Api/V1/CRM/FollowUpController.php
class FollowUpController extends Controller
{
    public function __construct(
        private FollowUpService $followUpService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // List follow-ups
        // Filters: status, type, assigned_to, date
    }

    public function store(StoreFollowUpRequest $request): JsonResponse
    public function show(FollowUp $followUp): JsonResponse
    public function update(Request $request, FollowUp $followUp): JsonResponse
    public function destroy(FollowUp $followUp): JsonResponse

    // Follow-up Management
    public function complete(Request $request, FollowUp $followUp): JsonResponse
    public function reschedule(Request $request, FollowUp $followUp): JsonResponse

    // Filtering
    public function today(): JsonResponse
    public function upcoming(): JsonResponse
    public function overdue(): JsonResponse
    public function myFollowUps(): JsonResponse
}
```

#### **Module 6: File & Installment System**

```php
// app/Http/Controllers/Api/V1/File/PropertyFileController.php
class PropertyFileController extends Controller
{
    public function __construct(
        private PropertyFileService $fileService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // List files with filters
        // Filters: status, client, society, date_range
        // Permission: files.view_all or files.view
    }

    public function store(StorePropertyFileRequest $request): JsonResponse
    {
        // Create file with auto-generated installments
        // Permission: files.create
    }

    public function show(PropertyFile $file): JsonResponse
    {
        // Show file with installments, payments
    }

    public function update(Request $request, PropertyFile $file): JsonResponse
    public function destroy(PropertyFile $file): JsonResponse

    // File Management
    public function transfer(TransferFileRequest $request, PropertyFile $file): JsonResponse
    {
        // Transfer file to new client
        // Permission: files.transfer
    }

    public function complete(PropertyFile $file): JsonResponse

    // Statistics
    public function paymentHistory(PropertyFile $file): JsonResponse
    public function pendingInstallments(PropertyFile $file): JsonResponse
}

// app/Http/Controllers/Api/V1/File/FileInstallmentController.php
class FileInstallmentController extends Controller
{
    public function index(PropertyFile $file): JsonResponse
    public function show(FileInstallment $installment): JsonResponse
    public function update(Request $request, FileInstallment $installment): JsonResponse

    // Installment Management
    public function markAsPaid(Request $request, FileInstallment $installment): JsonResponse
    public function waive(Request $request, FileInstallment $installment): JsonResponse

    // Filtering
    public function pending(PropertyFile $file): JsonResponse
    public function overdue(PropertyFile $file): JsonResponse
}

// app/Http/Controllers/Api/V1/File/FilePaymentController.php
class FilePaymentController extends Controller
{
    public function index(PropertyFile $file): JsonResponse
    public function store(StoreFilePaymentRequest $request): JsonResponse
    {
        // Record payment with receipt generation
        // Update installment status
        // Permission: files.receive_payment
    }

    public function show(FilePayment $payment): JsonResponse
    public function downloadReceipt(FilePayment $payment): Response
}

// app/Http/Controllers/Api/V1/File/FileTransferController.php
class FileTransferController extends Controller
{
    public function index(PropertyFile $file): JsonResponse
    public function show(FileTransfer $transfer): JsonResponse
    public function approve(FileTransfer $transfer): JsonResponse
    public function reject(Request $request, FileTransfer $transfer): JsonResponse
}
```

#### **Module 7: Payment System**

```php
// app/Http/Controllers/Api/V1/Payment/PaymentController.php
class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        // List all payments
        // Filters: type, status, date_range, client, method
        // Permission: payments.view_all
    }

    public function store(StorePaymentRequest $request): JsonResponse
    {
        // Record general payment (token, booking, etc.)
        // Permission: payments.create
    }

    public function show(Payment $payment): JsonResponse
    public function update(Request $request, Payment $payment): JsonResponse
    public function cancel(Request $request, Payment $payment): JsonResponse

    // Receipt Management
    public function downloadReceipt(Payment $payment): Response
    public function emailReceipt(Request $request, Payment $payment): JsonResponse

    // Filtering
    public function today(): JsonResponse
    public function byMethod(string $method): JsonResponse
    public function byType(string $type): JsonResponse
}

// app/Http/Controllers/Api/V1/Payment/ExpenseController.php
class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService
    ) {}

    public function index(Request $request): JsonResponse
    public function store(Request $request): JsonResponse
    public function show(Expense $expense): JsonResponse
    public function update(Request $request, Expense $expense): JsonResponse
    public function destroy(Expense $expense): JsonResponse

    // Expense Management
    public function approve(Expense $expense): JsonResponse
    public function reject(Request $request, Expense $expense): JsonResponse

    // Filtering
    public function byCategory(string $category): JsonResponse
    public function pending(): JsonResponse
}
```

#### **Module 8: Reports & Dashboard**

```php
// app/Http/Controllers/Api/V1/DashboardController.php
class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    public function index(): JsonResponse
    {
        // Return dashboard data based on user role
        // - Admins: All statistics
        // - Managers: Society-wise stats
        // - Dealers: Own performance stats
        return [
            'stats' => [
                'societies', 'plots', 'properties',
                'clients', 'leads', 'deals', 'files'
            ],
            'revenue' => [
                'today', 'month', 'year'
            ],
            'charts' => [
                'sales_trend', 'revenue_trend'
            ],
            'recent_activities' => [
                'leads', 'deals', 'payments'
            ],
            'alerts' => [
                'overdue_followups', 'overdue_installments'
            ]
        ];
    }

    public function stats(): JsonResponse
    public function recentActivities(): JsonResponse
    public function alerts(): JsonResponse
}

// app/Http/Controllers/Api/V1/Report/ReportController.php
class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {}

    public function index(): JsonResponse
    public function generate(Request $request): JsonResponse
    {
        // Generate report based on type and parameters
        // Permission: reports.generate
    }

    public function show(Report $report): JsonResponse
    public function download(Report $report): Response
    public function email(Request $request, Report $report): JsonResponse
}

// app/Http/Controllers/Api/V1/Report/SalesReportController.php
class SalesReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    {
        // Sales report by date range, dealer, society
    }

    public function dealerPerformance(Request $request): JsonResponse
    public function societyWise(Request $request): JsonResponse
    public function monthlyComparison(Request $request): JsonResponse
}

// app/Http/Controllers/Api/V1/Report/RevenueReportController.php
class RevenueReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    public function byMonth(Request $request): JsonResponse
    public function byQuarter(Request $request): JsonResponse
    public function byYear(Request $request): JsonResponse
    public function profitLoss(Request $request): JsonResponse
}

// app/Http/Controllers/Api/V1/Report/CommissionReportController.php
class CommissionReportController extends Controller
{
    public function generate(Request $request): JsonResponse
    public function byDealer(Request $request, Dealer $dealer): JsonResponse
    public function pending(): JsonResponse
    public function paid(Request $request): JsonResponse
}
```

---

## 🛡️ 4. MIDDLEWARE IMPLEMENTATION

### Middleware Files

```php
// app/Http/Middleware/CheckRole.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (!$request->user()->hasAnyRole($roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions. Required roles: ' . implode(', ', $roles)
            ], 403);
        }

        return $next($request);
    }
}

// app/Http/Middleware/CheckPermission.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        if (!$request->user()->hasPermission($permission)) {
            return response()->json([
                'success' => false,
                'message' => "You don't have permission: {$permission}"
            ], 403);
        }

        return $next($request);
    }
}

// app/Http/Middleware/EnsureUserIsActive.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && !$request->user()->is_active) {
            auth()->logout();

            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact administrator.'
            ], 403);
        }

        return $next($request);
    }
}

// app/Http/Middleware/LogUserActivity.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            Log::channel('user_activity')->info('User Activity', [
                'user_id' => $request->user()->id,
                'user_email' => $request->user()->email,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $next($request);
    }
}

// app/Http/Middleware/ValidateApiToken.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token required'
            ], 401);
        }

        // Additional token validation logic
        // Check token expiry, revocation, etc.

        return $next($request);
    }
}
```

### Middleware Registration

```php
// bootstrap/app.php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global Middleware
        $middleware->web(append: [
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\ValidateApiToken::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        // Middleware Aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'log.activity' => \App\Http\Middleware\LogUserActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### Middleware Usage in Routes

```php
// Example route with middleware

// Single role check
Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin']);

// Multiple roles check
Route::get('/manager/reports', [ReportController::class, 'index'])
    ->middleware(['auth', 'role:admin,manager']);

// Permission check
Route::post('/plots', [PlotController::class, 'store'])
    ->middleware(['auth', 'permission:plots.create']);

// Multiple middleware
Route::group(['middleware' => ['auth', 'permission:deals.approve', 'log.activity']], function () {
    Route::post('/deals/{deal}/approve', [DealController::class, 'approve']);
});
```

---

## ⚙️ 5. SERVICE LAYER (Optional but Recommended)

### Why Use Service Layer?

- **Separation of Concerns**: Keep controllers thin, business logic in services
- **Reusability**: Use same service in multiple controllers
- **Testability**: Easier to unit test business logic
- **Maintainability**: Single source of truth for business operations

### Service Examples

```php
// app/Services/CRM/LeadService.php
<?php

namespace App\Services\CRM;

use App\Models\Lead;
use App\Models\Client;
use App\Repositories\Contracts\LeadRepositoryInterface;
use Illuminate\Support\Facades\DB;

class LeadService
{
    public function __construct(
        private LeadRepositoryInterface $leadRepository
    ) {}

    /**
     * Get leads with filters and pagination
     */
    public function getLeads(array $filters = [], int $perPage = 15)
    {
        return $this->leadRepository->getWithFilters($filters, $perPage);
    }

    /**
     * Create new lead
     */
    public function createLead(array $data): Lead
    {
        // Business logic
        if (isset($data['budget_max']) && $data['budget_max'] < $data['budget_min']) {
            throw new \InvalidArgumentException('Max budget cannot be less than min budget');
        }

        return $this->leadRepository->create($data);
    }

    /**
     * Convert lead to client
     */
    public function convertToClient(Lead $lead, array $clientData): Client
    {
        DB::beginTransaction();
        try {
            // Create client
            $client = Client::create([
                'client_code' => $this->generateClientCode(),
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'whatsapp' => $lead->whatsapp,
                'cnic' => $lead->cnic,
                'address' => $lead->address,
                'assigned_to' => $lead->assigned_to,
                ...$clientData
            ]);

            // Update lead
            $lead->update([
                'status' => 'converted',
                'converted_to_client_id' => $client->id
            ]);

            DB::commit();

            return $client;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get leads by priority
     */
    public function getLeadsByPriority(string $priority)
    {
        return $this->leadRepository->getByPriority($priority);
    }

    /**
     * Assign lead to user
     */
    public function assignLead(Lead $lead, int $userId): Lead
    {
        $lead->update(['assigned_to' => $userId]);

        // Send notification
        event(new LeadAssigned($lead, $userId));

        return $lead->fresh();
    }

    private function generateClientCode(): string
    {
        $year = date('Y');
        $lastClient = Client::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastClient ? (int)substr($lastClient->client_code, -4) + 1 : 1;

        return 'CLT-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}

// app/Services/File/PropertyFileService.php
<?php

namespace App\Services\File;

use App\Models\PropertyFile;
use App\Models\FileInstallment;
use App\Services\File\InstallmentService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PropertyFileService
{
    public function __construct(
        private InstallmentService $installmentService
    ) {}

    /**
     * Create property file with installments
     */
    public function createFile(array $data): PropertyFile
    {
        DB::beginTransaction();
        try {
            // Create file
            $file = PropertyFile::create([
                'file_number' => $this->generateFileNumber(),
                'client_id' => $data['client_id'],
                'deal_id' => $data['deal_id'],
                'fileable_type' => $data['fileable_type'],
                'fileable_id' => $data['fileable_id'],
                'total_amount' => $data['total_amount'],
                'down_payment' => $data['down_payment'],
                'paid_amount' => $data['down_payment'],
                'remaining_amount' => $data['total_amount'] - $data['down_payment'],
                'installment_plan' => $data['installment_plan'],
                'total_installments' => $data['total_installments'],
                'start_date' => $data['start_date'],
            ]);

            // Generate installments
            $this->installmentService->generateInstallments(
                $file,
                $data['total_amount'] - $data['down_payment'],
                $data['total_installments'],
                $data['installment_plan'],
                Carbon::parse($data['start_date'])
            );

            DB::commit();

            return $file->load('installments');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Transfer file to new client
     */
    public function transferFile(PropertyFile $file, int $newClientId, float $transferFee): PropertyFile
    {
        DB::beginTransaction();
        try {
            $oldClientId = $file->client_id;

            // Create transfer record
            $file->transfers()->create([
                'from_client_id' => $oldClientId,
                'to_client_id' => $newClientId,
                'transfer_date' => now(),
                'transfer_fee' => $transferFee,
                'outstanding_amount' => $file->remaining_amount,
                'status' => 'completed'
            ]);

            // Update file
            $file->update([
                'client_id' => $newClientId,
                'transferred_to_client_id' => $newClientId,
                'transfer_date' => now(),
                'transfer_fee' => $transferFee,
            ]);

            DB::commit();

            return $file->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update payment status after payment
     */
    public function updatePaymentStatus(PropertyFile $file): void
    {
        $file->update([
            'paid_amount' => $file->payments()->sum('amount'),
            'paid_installments' => $file->installments()->where('status', 'paid')->count(),
        ]);

        $file->update([
            'remaining_amount' => $file->total_amount - $file->paid_amount,
        ]);

        if ($file->remaining_amount <= 0) {
            $file->update([
                'status' => 'completed',
                'completion_date' => now()
            ]);
        }
    }

    private function generateFileNumber(): string
    {
        $year = date('Y');
        $lastFile = PropertyFile::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastFile ? (int)substr($lastFile->file_number, -5) + 1 : 1;

        return 'FILE-' . $year . '-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}

// app/Services/Payment/PaymentService.php
<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\FilePayment;
use App\Services\File\PropertyFileService;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private PropertyFileService $fileService
    ) {}

    /**
     * Record file payment
     */
    public function recordFilePayment(array $data): FilePayment
    {
        DB::beginTransaction();
        try {
            $payment = FilePayment::create([
                'receipt_number' => $this->generateReceiptNumber(),
                'property_file_id' => $data['property_file_id'],
                'file_installment_id' => $data['file_installment_id'] ?? null,
                'client_id' => $data['client_id'],
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'cheque_number' => $data['cheque_number'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'received_by' => auth()->id(),
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            // Update installment if specified
            if (isset($data['file_installment_id'])) {
                $installment = $payment->installment;
                $installment->update([
                    'paid_amount' => $installment->paid_amount + $data['amount'],
                    'status' => $installment->paid_amount + $data['amount'] >= $installment->amount ? 'paid' : 'partial',
                    'paid_date' => $installment->paid_amount + $data['amount'] >= $installment->amount ? now() : null,
                ]);
            }

            // Update file payment status
            $this->fileService->updatePaymentStatus($payment->propertyFile);

            DB::commit();

            // Generate receipt PDF
            event(new PaymentReceived($payment));

            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generateReceiptNumber(): string
    {
        $year = date('Y');
        $lastPayment = FilePayment::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $number = $lastPayment ? (int)substr($lastPayment->receipt_number, -6) + 1 : 1;

        return 'RCT-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
}

// app/Services/Report/DashboardService.php
<?php

namespace App\Services\Report;

use App\Models\{Society, Plot, Property, Client, Lead, Deal, PropertyFile, Payment};
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get dashboard statistics based on user role
     */
    public function getDashboardStats($user): array
    {
        if ($user->isAdmin() || $user->isManager()) {
            return $this->getAdminStats();
        } elseif ($user->isDealer()) {
            return $this->getDealerStats($user);
        }

        return [];
    }

    private function getAdminStats(): array
    {
        return [
            'inventory' => [
                'total_societies' => Society::count(),
                'total_plots' => Plot::count(),
                'available_plots' => Plot::where('status', 'available')->count(),
                'sold_plots' => Plot::where('status', 'sold')->count(),
                'total_properties' => Property::count(),
                'available_properties' => Property::where('status', 'available')->count(),
            ],
            'crm' => [
                'total_clients' => Client::count(),
                'active_leads' => Lead::whereNotIn('status', ['converted', 'lost'])->count(),
                'converted_leads' => Lead::where('status', 'converted')->count(),
                'active_deals' => Deal::where('status', 'approved')->count(),
                'pending_deals' => Deal::where('status', 'pending')->count(),
            ],
            'financial' => [
                'revenue_today' => Payment::whereDate('payment_date', today())->sum('amount'),
                'revenue_month' => Payment::whereMonth('payment_date', now()->month)->sum('amount'),
                'revenue_year' => Payment::whereYear('payment_date', now()->year)->sum('amount'),
                'pending_payments' => PropertyFile::sum('remaining_amount'),
                'active_files' => PropertyFile::where('status', 'active')->count(),
            ],
            'recent_activities' => [
                'leads' => Lead::with('assignedTo')->latest()->limit(5)->get(),
                'deals' => Deal::with('client', 'dealer')->latest()->limit(5)->get(),
                'payments' => Payment::with('client')->latest()->limit(5)->get(),
            ],
        ];
    }

    private function getDealerStats($user): array
    {
        return [
            'my_stats' => [
                'my_leads' => Lead::where('assigned_to', $user->id)->whereNotIn('status', ['converted', 'lost'])->count(),
                'my_clients' => Client::where('assigned_to', $user->id)->count(),
                'my_deals' => Deal::where('dealer_id', $user->id)->count(),
                'my_commission' => Deal::where('dealer_id', $user->id)->sum('commission_amount'),
            ],
            'performance' => [
                'deals_this_month' => Deal::where('dealer_id', $user->id)
                    ->whereMonth('deal_date', now()->month)
                    ->count(),
                'conversion_rate' => $this->calculateConversionRate($user->id),
            ],
        ];
    }

    private function calculateConversionRate($userId): float
    {
        $totalLeads = Lead::where('assigned_to', $userId)->count();
        $convertedLeads = Lead::where('assigned_to', $userId)->where('status', 'converted')->count();

        return $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 2) : 0;
    }
}
```

---

## 🌐 6. API vs WEB ROUTES SEPARATION

### API Routes Structure

```php
// routes/api.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth;
use App\Http\Controllers\Api\V1\UserManagement;
use App\Http\Controllers\Api\V1\Society;
use App\Http\Controllers\Api\V1\Plot;
use App\Http\Controllers\Api\V1\Property;
use App\Http\Controllers\Api\V1\CRM;
use App\Http\Controllers\Api\V1\File;
use App\Http\Controllers\Api\V1\Payment;
use App\Http\Controllers\Api\V1\Report;
use App\Http\Controllers\Api\V1\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - Version 1
|--------------------------------------------------------------------------
*/

// Public Routes (No Authentication)
Route::prefix('v1')->group(function () {

    // Authentication
    Route::post('/login', [Auth\LoginController::class, 'login']);
    Route::post('/register', [Auth\RegisterController::class, 'register']);
    Route::post('/forgot-password', [Auth\PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [Auth\PasswordResetController::class, 'reset']);

    // Public property listings (for website)
    Route::get('/properties/featured', [Property\PropertyController::class, 'featured']);
    Route::get('/properties/{property}', [Property\PropertyController::class, 'show']);
    Route::get('/societies', [Society\SocietyController::class, 'index']);
});

// Protected Routes (Require Authentication)
Route::prefix('v1')->middleware(['auth:sanctum', 'active.user'])->group(function () {

    // Authentication
    Route::post('/logout', [Auth\LoginController::class, 'logout']);
    Route::get('/me', [Auth\LoginController::class, 'me']);
    Route::put('/profile', [Auth\LoginController::class, 'updateProfile']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/alerts', [DashboardController::class, 'alerts']);

    // User Management Module
    Route::prefix('users')->middleware('permission:users.view_all')->group(function () {
        Route::get('/', [UserManagement\UserController::class, 'index']);
        Route::post('/', [UserManagement\UserController::class, 'store'])->middleware('permission:users.create');
        Route::get('/{user}', [UserManagement\UserController::class, 'show']);
        Route::put('/{user}', [UserManagement\UserController::class, 'update'])->middleware('permission:users.update');
        Route::delete('/{user}', [UserManagement\UserController::class, 'destroy'])->middleware('permission:users.delete');
        Route::post('/{user}/activate', [UserManagement\UserController::class, 'activate']);
        Route::post('/{user}/deactivate', [UserManagement\UserController::class, 'deactivate']);
        Route::post('/{user}/assign-role', [UserManagement\UserController::class, 'assignRole']);
    });

    // Roles
    Route::apiResource('roles', UserManagement\RoleController::class)->middleware('permission:roles.view_all');
    Route::post('/roles/{role}/permissions', [UserManagement\RoleController::class, 'assignPermission']);

    // Permissions
    Route::get('/permissions', [UserManagement\PermissionController::class, 'index']);
    Route::get('/permissions/module/{module}', [UserManagement\PermissionController::class, 'byModule']);

    // Society Module
    Route::prefix('societies')->group(function () {
        Route::get('/', [Society\SocietyController::class, 'index'])->middleware('permission:societies.view_all');
        Route::post('/', [Society\SocietyController::class, 'store'])->middleware('permission:societies.create');
        Route::get('/{society}', [Society\SocietyController::class, 'show']);
        Route::put('/{society}', [Society\SocietyController::class, 'update'])->middleware('permission:societies.update');
        Route::delete('/{society}', [Society\SocietyController::class, 'destroy'])->middleware('permission:societies.delete');
        Route::get('/{society}/stats', [Society\SocietyController::class, 'stats']);
        Route::get('/{society}/available-plots', [Society\SocietyController::class, 'availablePlots']);

        // Blocks
        Route::get('/{society}/blocks', [Society\BlockController::class, 'index']);
        Route::post('/{society}/blocks', [Society\BlockController::class, 'store']);
    });

    Route::apiResource('blocks', Society\BlockController::class)->except(['index', 'store']);
    Route::get('/blocks/{block}/streets', [Society\StreetController::class, 'index']);
    Route::post('/blocks/{block}/streets', [Society\StreetController::class, 'store']);
    Route::apiResource('streets', Society\StreetController::class)->except(['index', 'store']);

    // Plot Module
    Route::prefix('plots')->group(function () {
        Route::get('/', [Plot\PlotController::class, 'index'])->middleware('permission:plots.view_all');
        Route::post('/', [Plot\PlotController::class, 'store'])->middleware('permission:plots.create');
        Route::get('/available', [Plot\PlotController::class, 'available']);
        Route::get('/sold', [Plot\PlotController::class, 'sold']);
        Route::get('/category/{category}', [Plot\PlotController::class, 'byCategory']);
        Route::get('/{plot}', [Plot\PlotController::class, 'show']);
        Route::put('/{plot}', [Plot\PlotController::class, 'update'])->middleware('permission:plots.update');
        Route::delete('/{plot}', [Plot\PlotController::class, 'destroy'])->middleware('permission:plots.delete');
        Route::post('/{plot}/mark-booked', [Plot\PlotController::class, 'markAsBooked']);
        Route::post('/{plot}/mark-sold', [Plot\PlotController::class, 'markAsSold']);
        Route::post('/{plot}/mark-available', [Plot\PlotController::class, 'markAsAvailable']);

        // Plot Maps
        Route::get('/{plot}/maps', [Plot\PlotMapController::class, 'index']);
        Route::post('/{plot}/maps', [Plot\PlotMapController::class, 'store']);
    });

    Route::apiResource('plot-maps', Plot\PlotMapController::class)->except(['index', 'store']);

    // Property Module
    Route::prefix('properties')->group(function () {
        Route::get('/', [Property\PropertyController::class, 'index'])->middleware('permission:properties.view_all');
        Route::post('/', [Property\PropertyController::class, 'store'])->middleware('permission:properties.create');
        Route::get('/for-sale', [Property\PropertyController::class, 'forSale']);
        Route::get('/for-rent', [Property\PropertyController::class, 'forRent']);
        Route::get('/type/{type}', [Property\PropertyController::class, 'byType']);
        Route::get('/{property}', [Property\PropertyController::class, 'show']);
        Route::put('/{property}', [Property\PropertyController::class, 'update'])->middleware('permission:properties.update');
        Route::delete('/{property}', [Property\PropertyController::class, 'destroy'])->middleware('permission:properties.delete');
        Route::post('/{property}/mark-featured', [Property\PropertyController::class, 'markAsFeatured']);

        // Property Images
        Route::get('/{property}/images', [Property\PropertyImageController::class, 'index']);
        Route::post('/{property}/images', [Property\PropertyImageController::class, 'store']);
    });

    Route::apiResource('property-types', Property\PropertyTypeController::class);
    Route::apiResource('property-images', Property\PropertyImageController::class)->except(['index', 'store']);

    // CRM Module
    Route::prefix('leads')->group(function () {
        Route::get('/', [CRM\LeadController::class, 'index']);
        Route::post('/', [CRM\LeadController::class, 'store'])->middleware('permission:leads.create');
        Route::get('/new', [CRM\LeadController::class, 'newLeads']);
        Route::get('/my-leads', [CRM\LeadController::class, 'myLeads']);
        Route::get('/converted', [CRM\LeadController::class, 'converted']);
        Route::get('/priority/{priority}', [CRM\LeadController::class, 'byPriority']);
        Route::get('/{lead}', [CRM\LeadController::class, 'show']);
        Route::put('/{lead}', [CRM\LeadController::class, 'update'])->middleware('permission:leads.update');
        Route::delete('/{lead}', [CRM\LeadController::class, 'destroy'])->middleware('permission:leads.delete');
        Route::post('/{lead}/assign', [CRM\LeadController::class, 'assign'])->middleware('permission:leads.assign');
        Route::post('/{lead}/convert', [CRM\LeadController::class, 'convert'])->middleware('permission:leads.convert');
        Route::put('/{lead}/status', [CRM\LeadController::class, 'updateStatus']);
    });

    Route::prefix('clients')->group(function () {
        Route::get('/', [CRM\ClientController::class, 'index']);
        Route::post('/', [CRM\ClientController::class, 'store'])->middleware('permission:clients.create');
        Route::get('/{client}', [CRM\ClientController::class, 'show']);
        Route::put('/{client}', [CRM\ClientController::class, 'update'])->middleware('permission:clients.update');
        Route::delete('/{client}', [CRM\ClientController::class, 'destroy'])->middleware('permission:clients.delete');
        Route::post('/{client}/assign', [CRM\ClientController::class, 'assign']);
        Route::get('/{client}/stats', [CRM\ClientController::class, 'stats']);
        Route::get('/{client}/transactions', [CRM\ClientController::class, 'transactions']);
    });

    Route::apiResource('dealers', CRM\DealerController::class);
    Route::get('/dealers/{dealer}/stats', [CRM\DealerController::class, 'stats']);
    Route::get('/dealers/{dealer}/commission-report', [CRM\DealerController::class, 'commissionReport']);

    Route::prefix('deals')->group(function () {
        Route::get('/', [CRM\DealController::class, 'index']);
        Route::post('/', [CRM\DealController::class, 'store'])->middleware('permission:deals.create');
        Route::get('/pending', [CRM\DealController::class, 'pending']);
        Route::get('/approved', [CRM\DealController::class, 'approved']);
        Route::get('/my-deals', [CRM\DealController::class, 'myDeals']);
        Route::get('/{deal}', [CRM\DealController::class, 'show']);
        Route::put('/{deal}', [CRM\DealController::class, 'update'])->middleware('permission:deals.update');
        Route::delete('/{deal}', [CRM\DealController::class, 'destroy'])->middleware('permission:deals.delete');
        Route::post('/{deal}/approve', [CRM\DealController::class, 'approve'])->middleware('permission:deals.approve');
        Route::post('/{deal}/complete', [CRM\DealController::class, 'complete']);
        Route::post('/{deal}/cancel', [CRM\DealController::class, 'cancel']);
    });

    Route::prefix('follow-ups')->group(function () {
        Route::get('/', [CRM\FollowUpController::class, 'index']);
        Route::post('/', [CRM\FollowUpController::class, 'store']);
        Route::get('/today', [CRM\FollowUpController::class, 'today']);
        Route::get('/upcoming', [CRM\FollowUpController::class, 'upcoming']);
        Route::get('/overdue', [CRM\FollowUpController::class, 'overdue']);
        Route::get('/my-follow-ups', [CRM\FollowUpController::class, 'myFollowUps']);
        Route::get('/{followUp}', [CRM\FollowUpController::class, 'show']);
        Route::put('/{followUp}', [CRM\FollowUpController::class, 'update']);
        Route::delete('/{followUp}', [CRM\FollowUpController::class, 'destroy']);
        Route::post('/{followUp}/complete', [CRM\FollowUpController::class, 'complete']);
        Route::post('/{followUp}/reschedule', [CRM\FollowUpController::class, 'reschedule']);
    });

    // File & Installment Module
    Route::prefix('files')->group(function () {
        Route::get('/', [File\PropertyFileController::class, 'index']);
        Route::post('/', [File\PropertyFileController::class, 'store'])->middleware('permission:files.create');
        Route::get('/{file}', [File\PropertyFileController::class, 'show']);
        Route::put('/{file}', [File\PropertyFileController::class, 'update'])->middleware('permission:files.update');
        Route::delete('/{file}', [File\PropertyFileController::class, 'destroy'])->middleware('permission:files.delete');
        Route::post('/{file}/transfer', [File\PropertyFileController::class, 'transfer'])->middleware('permission:files.transfer');
        Route::post('/{file}/complete', [File\PropertyFileController::class, 'complete']);
        Route::get('/{file}/payment-history', [File\PropertyFileController::class, 'paymentHistory']);
        Route::get('/{file}/pending-installments', [File\PropertyFileController::class, 'pendingInstallments']);

        // Installments
        Route::get('/{file}/installments', [File\FileInstallmentController::class, 'index']);
        Route::get('/{file}/installments/pending', [File\FileInstallmentController::class, 'pending']);
        Route::get('/{file}/installments/overdue', [File\FileInstallmentController::class, 'overdue']);
    });

    Route::prefix('installments')->group(function () {
        Route::get('/{installment}', [File\FileInstallmentController::class, 'show']);
        Route::put('/{installment}', [File\FileInstallmentController::class, 'update']);
        Route::post('/{installment}/mark-paid', [File\FileInstallmentController::class, 'markAsPaid']);
        Route::post('/{installment}/waive', [File\FileInstallmentController::class, 'waive']);
    });

    Route::prefix('file-payments')->group(function () {
        Route::get('/', [File\FilePaymentController::class, 'index']);
        Route::post('/', [File\FilePaymentController::class, 'store'])->middleware('permission:files.receive_payment');
        Route::get('/{payment}', [File\FilePaymentController::class, 'show']);
        Route::get('/{payment}/download-receipt', [File\FilePaymentController::class, 'downloadReceipt']);
    });

    // Payment Module
    Route::prefix('payments')->group(function () {
        Route::get('/', [Payment\PaymentController::class, 'index'])->middleware('permission:payments.view_all');
        Route::post('/', [Payment\PaymentController::class, 'store'])->middleware('permission:payments.create');
        Route::get('/today', [Payment\PaymentController::class, 'today']);
        Route::get('/method/{method}', [Payment\PaymentController::class, 'byMethod']);
        Route::get('/type/{type}', [Payment\PaymentController::class, 'byType']);
        Route::get('/{payment}', [Payment\PaymentController::class, 'show']);
        Route::put('/{payment}', [Payment\PaymentController::class, 'update']);
        Route::post('/{payment}/cancel', [Payment\PaymentController::class, 'cancel']);
        Route::get('/{payment}/download-receipt', [Payment\PaymentController::class, 'downloadReceipt']);
        Route::post('/{payment}/email-receipt', [Payment\PaymentController::class, 'emailReceipt']);
    });

    Route::prefix('expenses')->group(function () {
        Route::get('/', [Payment\ExpenseController::class, 'index'])->middleware('permission:expenses.view_all');
        Route::post('/', [Payment\ExpenseController::class, 'store'])->middleware('permission:expenses.create');
        Route::get('/category/{category}', [Payment\ExpenseController::class, 'byCategory']);
        Route::get('/pending', [Payment\ExpenseController::class, 'pending']);
        Route::get('/{expense}', [Payment\ExpenseController::class, 'show']);
        Route::put('/{expense}', [Payment\ExpenseController::class, 'update']);
        Route::delete('/{expense}', [Payment\ExpenseController::class, 'destroy']);
        Route::post('/{expense}/approve', [Payment\ExpenseController::class, 'approve'])->middleware('permission:expenses.approve');
        Route::post('/{expense}/reject', [Payment\ExpenseController::class, 'reject']);
    });

    // Reports Module
    Route::prefix('reports')->middleware('permission:reports.generate')->group(function () {
        Route::get('/', [Report\ReportController::class, 'index']);
        Route::post('/generate', [Report\ReportController::class, 'generate']);
        Route::get('/{report}', [Report\ReportController::class, 'show']);
        Route::get('/{report}/download', [Report\ReportController::class, 'download']);
        Route::post('/{report}/email', [Report\ReportController::class, 'email']);

        // Specific Reports
        Route::post('/sales', [Report\SalesReportController::class, 'generate']);
        Route::get('/sales/dealer-performance', [Report\SalesReportController::class, 'dealerPerformance']);
        Route::get('/sales/society-wise', [Report\SalesReportController::class, 'societyWise']);

        Route::post('/revenue', [Report\RevenueReportController::class, 'generate']);
        Route::get('/revenue/monthly', [Report\RevenueReportController::class, 'byMonth']);
        Route::get('/revenue/profit-loss', [Report\RevenueReportController::class, 'profitLoss']);

        Route::post('/commission', [Report\CommissionReportController::class, 'generate']);
        Route::get('/commission/dealer/{dealer}', [Report\CommissionReportController::class, 'byDealer']);
        Route::get('/commission/pending', [Report\CommissionReportController::class, 'pending']);
    });
});
```

### Web Routes Structure

```php
// routes/web.php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web;

/*
|--------------------------------------------------------------------------
| Web Routes (Blade Views)
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('home');

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');
});

// Authenticated Routes
Route::middleware(['auth', 'active.user'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [Web\DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [Web\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Web\ProfileController::class, 'update'])->name('profile.update');

    // Societies
    Route::resource('societies', Web\SocietyController::class)
        ->middleware('permission:societies.view_all');

    // Plots
    Route::resource('plots', Web\PlotController::class)
        ->middleware('permission:plots.view_all');

    // Properties
    Route::resource('properties', Web\PropertyController::class)
        ->middleware('permission:properties.view_all');

    // Clients
    Route::resource('clients', Web\ClientController::class);

    // Leads
    Route::resource('leads', Web\LeadController::class);
    Route::post('/leads/{lead}/convert', [Web\LeadController::class, 'convert'])
        ->name('leads.convert');

    // Deals
    Route::resource('deals', Web\DealController::class);
    Route::post('/deals/{deal}/approve', [Web\DealController::class, 'approve'])
        ->name('deals.approve')
        ->middleware('permission:deals.approve');

    // Files
    Route::resource('files', Web\PropertyFileController::class);
    Route::post('/files/{file}/transfer', [Web\PropertyFileController::class, 'transfer'])
        ->name('files.transfer')
        ->middleware('permission:files.transfer');

    // Payments
    Route::resource('payments', Web\PaymentController::class);
    Route::get('/payments/{payment}/receipt', [Web\PaymentController::class, 'downloadReceipt'])
        ->name('payments.receipt');

    // Reports
    Route::get('/reports', [Web\ReportController::class, 'index'])
        ->name('reports.index')
        ->middleware('permission:reports.generate');
    Route::post('/reports/generate', [Web\ReportController::class, 'generate'])
        ->name('reports.generate');
});
```

### Key Differences: API vs Web

| Aspect | API Routes | Web Routes |
|--------|-----------|------------|
| **Authentication** | Token-based (Sanctum) | Session-based (Cookies) |
| **Response Format** | JSON | HTML (Blade views) |
| **Middleware** | `auth:sanctum` | `auth` (web guard) |
| **Route Prefix** | `/api/v1/...` | No prefix |
| **CSRF Protection** | Not required | Required for POST/PUT/DELETE |
| **Resource Naming** | `apiResource` | `resource` |
| **Versioning** | `/v1`, `/v2`, etc. | Not typically versioned |
| **Error Handling** | JSON error responses | Redirect with flash messages |
| **Intended For** | Mobile apps, SPAs, Third-party integrations | Traditional web interface |

---

## ✅ 7. BEST PRACTICES

### Code Organization

1. **Keep Controllers Thin**
   - Move business logic to Services
   - Use Form Requests for validation
   - Return API Resources for responses

2. **Use Repository Pattern (Optional)**
   - Abstract database queries
   - Easier to switch ORMs
   - Better for testing

3. **Leverage Laravel Features**
   - Eloquent Relationships
   - Scopes for query reusability
   - Accessors & Mutators
   - Events & Listeners
   - Observers for model lifecycle

4. **API Versioning**
   - Version your API (`/api/v1/...`)
   - Never break existing versions
   - Deprecate old versions gradually

5. **Error Handling**
   - Use try-catch in services
   - Return consistent error responses
   - Log all exceptions

6. **Security**
   - Always validate input
   - Use middleware for authorization
   - Never trust user input
   - Implement rate limiting
   - Use HTTPS in production

7. **Performance**
   - Use eager loading to avoid N+1 queries
   - Cache frequently accessed data
   - Index database columns properly
   - Implement pagination everywhere

8. **Testing**
   - Write Feature tests for workflows
   - Write Unit tests for services
   - Test authorization rules
   - Test validation rules

---

## 📚 Additional Resources

- **Laravel Documentation**: https://laravel.com/docs
- **Laravel API Resources**: https://laravel.com/docs/eloquent-resources
- **Laravel Sanctum**: https://laravel.com/docs/sanctum
- **Repository Pattern**: https://github.com/bosnadev/repository

---

**Version**: 1.0
**Created**: January 28, 2026
**Framework**: Laravel 11.x
